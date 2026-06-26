<?php

namespace Tests\Feature;

use App\Models\CommitLog;
use App\Models\CommitInsight;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitLabSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set configuration for GitLab token and Nvidia API key for testing
        config(['services.nvidia.api_key' => 'test-nvidia-key']);

        // Force test values for GitLab env to isolate tests from local .env config
        putenv('GITLAB_URL=https://gitlab.com');
        $_ENV['GITLAB_URL'] = 'https://gitlab.com';
        $_SERVER['GITLAB_URL'] = 'https://gitlab.com';

        putenv('GITLAB_TOKEN=test-gitlab-token');
        $_ENV['GITLAB_TOKEN'] = 'test-gitlab-token';
        $_SERVER['GITLAB_TOKEN'] = 'test-gitlab-token';
    }

    public function test_gitlab_connection_unconfigured(): void
    {
        // Clear GITLAB_TOKEN from all environment sources
        putenv('GITLAB_TOKEN');
        unset($_ENV['GITLAB_TOKEN']);
        unset($_SERVER['GITLAB_TOKEN']);

        $response = $this->getJson('/developer-tools/gitlab/test');

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'GitLab Token (GITLAB_TOKEN) is not configured in the .env file.'
            ]);
    }

    public function test_gitlab_connection_success(): void
    {
        putenv('GITLAB_TOKEN=test-gitlab-token');
        $_ENV['GITLAB_TOKEN'] = 'test-gitlab-token';

        Http::fake([
            'https://gitlab.com/api/v4/projects*' => Http::response([
                ['id' => 101, 'name' => 'Project A', 'name_with_namespace' => 'Namespace / Project A', 'last_activity_at' => '2026-06-19T12:00:00Z']
            ], 200)
        ]);

        $response = $this->getJson('/developer-tools/gitlab/test');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Connection to GitLab successful! Accessible projects: 1'
            ]);
    }

    public function test_gitlab_get_projects_returns_formatted_list(): void
    {
        putenv('GITLAB_TOKEN=test-gitlab-token');
        $_ENV['GITLAB_TOKEN'] = 'test-gitlab-token';

        Http::fake([
            'https://gitlab.com/api/v4/projects*' => Http::response([
                [
                    'id' => 101,
                    'name' => 'Project A',
                    'name_with_namespace' => 'Namespace / Project A',
                    'last_activity_at' => '2026-06-19T12:00:00Z'
                ]
            ], 200)
        ]);

        $response = $this->getJson('/developer-tools/gitlab/projects');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'projects' => [
                    [
                        'id' => 101,
                        'name' => 'Namespace / Project A',
                        'last_activity' => '2026-06-19 12:00'
                    ]
                ]
            ]);
    }

    public function test_gitlab_sync_commits_creates_employees_and_logs(): void
    {
        putenv('GITLAB_TOKEN=test-gitlab-token');
        $_ENV['GITLAB_TOKEN'] = 'test-gitlab-token';

        // Mock GitLab responses
        Http::fake([
            'https://gitlab.com/api/v4/projects/101' => Http::response([
                'id' => 101,
                'name' => 'Project A'
            ], 200),
            'https://gitlab.com/api/v4/projects/101/repository/commits/a1b2c3d4e5f6g7h8i9j0*' => Http::response([
                'id' => 'a1b2c3d4e5f6g7h8i9j0',
                'stats' => [
                    'additions' => 25,
                    'deletions' => 10
                ]
            ], 200),
            'https://gitlab.com/api/v4/projects/101/repository/commits*' => Http::response([
                [
                    'id' => 'a1b2c3d4e5f6g7h8i9j0',
                    'author_name' => 'GitLab User',
                    'author_email' => 'gitlab.user@company.com',
                    'message' => 'Added new GitLab module',
                    'committed_date' => '2026-06-19T12:30:00Z'
                ]
            ], 200)
        ]);

        $response = $this->postJson('/developer-tools/gitlab/sync', [
            'project_id' => 101
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1
            ]);

        // Verify employee created
        $employee = Employee::where('email', 'gitlab.user@company.com')->first();
        $this->assertNotNull($employee);
        $this->assertEquals('GitLab User', $employee->name);

        // Verify commit log created
        $this->assertDatabaseHas('commit_logs', [
            'commit_hash' => 'a1b2c3d4e5f6g7h8i9j0',
            'employee_id' => $employee->id,
            'commit_message' => 'Added new GitLab module',
            'repository_name' => 'Project A',
            'lines_added' => 25,
            'lines_deleted' => 10
        ]);
    }

    public function test_commit_insight_generation_via_nvidia_api(): void
    {
        $employee = Employee::create([
            'name' => 'John Developer',
            'email' => 'john.dev@company.com'
        ]);

        $commit = CommitLog::create([
            'employee_id' => $employee->id,
            'commit_hash' => 'a1b2c3d4e5f6g7h8i9j0',
            'commit_message' => 'Fix database memory leak on report query',
            'commit_date' => now(),
            'repository_name' => 'Project A',
            'lines_added' => 15,
            'lines_deleted' => 5
        ]);

        // Mock NVIDIA NIM endpoint
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'feature_category' => 'Performance Optimization',
                                'business_impact' => 'Prevents report queries from triggering server out-of-memory errors.',
                                'technical_complexity' => 'Medium',
                                'risk_level' => 'Low',
                                'summary' => 'Refactored report retrieval queries to eager load relations and minimize memory consumption.'
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->postJson("/commits/{$commit->id}/insight");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'insight' => [
                    'commit_log_id' => $commit->id,
                    'feature_category' => 'Performance Optimization',
                    'business_impact' => 'Prevents report queries from triggering server out-of-memory errors.',
                    'technical_complexity' => 'Medium',
                    'risk_level' => 'Low',
                    'summary' => 'Refactored report retrieval queries to eager load relations and minimize memory consumption.'
                ]
            ]);

        // Assert cached in DB
        $this->assertDatabaseHas('commit_insights', [
            'commit_log_id' => $commit->id,
            'feature_category' => 'Performance Optimization'
        ]);

        // Call again and verify it retrieves cached data without hitting API
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([], 500) // would fail if hit
        ]);

        $response2 = $this->postJson("/commits/{$commit->id}/insight");
        $response2->assertStatus(200)
            ->assertJsonPath('insight.feature_category', 'Performance Optimization');
    }
}
