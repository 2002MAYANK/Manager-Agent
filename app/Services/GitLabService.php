<?php

namespace App\Services;

use App\Models\CommitLog;
use App\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GitLabService
{
    protected string $token;
    protected string $baseUrl;


    // public function __construct()
    // {
    //     $this->token = env('GITLAB_TOKEN') ?? '';
    // }
    public function __construct()
    {
        $this->token = env('GITLAB_TOKEN', '');
        $this->baseUrl = rtrim(env('GITLAB_URL', ''), '/');
    }


    /**
     * Fetch all projects/repositories accessible to the token.
     */
    public function getProjects()
    {
        if (empty($this->token)) {
            Log::error('[GitLabService] GITLAB_TOKEN is not configured.');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'PRIVATE-TOKEN' => $this->token
            ])->get($this->baseUrl . '/api/v4/projects', [
                'membership' => 'true',
                'simple' => 'true',
                'per_page' => 100,
                'order_by' => 'last_activity_at'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('[GitLabService] Failed to fetch projects: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('[GitLabService] Exception fetching projects: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get specific project details.
     */
    public function getProjectDetails($projectId)
    {
        if (empty($this->token)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'PRIVATE-TOKEN' => $this->token
            ])->get($this->baseUrl . "/api/v4/projects/{$projectId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("[GitLabService] Failed to fetch project details for ID {$projectId}: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("[GitLabService] Exception fetching project details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get commits for a project.
     */
    public function getProjectCommits($projectId)
    {
        if (empty($this->token)) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'PRIVATE-TOKEN' => $this->token
            ])->get($this->baseUrl . "/api/v4/projects/{$projectId}/repository/commits", [
                'per_page' => 100,
                'with_stats' => 'true'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("[GitLabService] Failed to fetch project commits for ID {$projectId}: " . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error("[GitLabService] Exception fetching project commits: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync commits from a project to commit_logs table.
     */
    public function syncCommits($projectId): array
    {
        $project = $this->getProjectDetails($projectId);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found on GitLab.'];
        }

        $commits = $this->getProjectCommits($projectId);
        if (empty($commits)) {
            return ['success' => true, 'message' => 'No commits found to sync.', 'count' => 0];
        }

        $repoName = $project['name'] ?? 'GitLab Repository';
        $syncCount = 0;
        
        $contributors = [];
        $latestCommitDate = null;

        foreach ($commits as $commit) {
            $authorName = $commit['author_name'] ?? 'Unknown Author';
            $authorEmail = $commit['author_email'] ?? '';

            // Update contributors tracking
            if (!isset($contributors[$authorName])) {
                $contributors[$authorName] = 0;
            }
            $contributors[$authorName]++;

            // Update latest commit date
            $commitDate = Carbon::parse($commit['committed_date'] ?? $commit['created_at'] ?? now());
            if (!$latestCommitDate || $commitDate->gt($latestCommitDate)) {
                $latestCommitDate = $commitDate;
            }

            // 1. Employee lookup / creation
            $employee = null;
            if (!empty($authorEmail)) {
                $employee = Employee::where('email', $authorEmail)->first();
            }
            if (!$employee) {
                $employee = Employee::where('name', $authorName)->first();
            }
            if (!$employee) {
                $employee = Employee::where('name', 'like', '%' . $authorName . '%')->first();
            }
            if (!$employee) {
                // Generate a valid company-like email
                $slug = Str::slug($authorName);
                $email = !empty($authorEmail) ? $authorEmail : ($slug . '@company.com');

                // Ensure email is unique
                if (Employee::where('email', $email)->exists()) {
                    $email = $slug . '.' . rand(10, 99) . '@company.com';
                }

                $employee = Employee::create([
                    'name' => $authorName,
                    'email' => $email,
                    'department' => 'GitLab Sync',
                    'designation' => 'Software Engineer',
                ]);
            }

            // Extract additions/deletions stats safely
            $stats = $commit['stats'] ?? null;

            if ($stats) {
                $linesAdded = $stats['additions'] ?? 0;
                $linesDeleted = $stats['deletions'] ?? 0;
            } else {
                // Handle missing stats gracefully by preserving existing db values or defaulting to 0
                $existing = CommitLog::where('commit_hash', $commit['id'])->first();
                $linesAdded = $existing ? $existing->lines_added : 0;
                $linesDeleted = $existing ? $existing->lines_deleted : 0;
            }

            // 2. CommitLog insert or update
            CommitLog::updateOrCreate(
                ['commit_hash' => $commit['id']],
                [
                    'employee_id' => $employee->id,
                    'commit_message' => $commit['message'] ?? $commit['title'] ?? 'No commit message',
                    'commit_date' => $commitDate,
                    'repository_name' => $repoName,
                    'lines_added' => $linesAdded,
                    'lines_deleted' => $linesDeleted,
                ]
            );

            $syncCount++;
        }

        // Determine most active contributor
        $mostActiveContributor = 'N/A';
        if (!empty($contributors)) {
            arsort($contributors);
            $mostActiveContributor = array_key_first($contributors);
        }

        return [
            'success' => true,
            'message' => "Successfully synced {$syncCount} commits from repository '{$repoName}'.",
            'count' => $syncCount,
            'repository_name' => $repoName,
            'total_contributors' => count($contributors),
            'most_active_contributor' => $mostActiveContributor,
            'last_commit_date' => $latestCommitDate ? $latestCommitDate->format('Y-m-d H:i') : 'N/A'
        ];
    }
}
