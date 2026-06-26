<?php

namespace App\Services;

use App\Models\Attendence;
use App\Models\CommitLog;
use App\Models\Employee;
use App\Models\Meeting;
use App\Models\MeetingTranscript;
use App\Models\Report;
use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Project;

class ManagerAgentService
{
    /**
     * Create a new class instance.
     */
    public function getPerformanceSummaryData($startDate = null, $endDate = null)
    {

        // 1. Overall stats
        $totalEmployees = Employee::count();

        $tasksStatsQuery = DB::table('tasks')
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END), 0) as pending
            ');
        if ($startDate && $endDate) {
            $tasksStatsQuery->whereBetween('assigned_date', [$startDate, $endDate]);
        }
        $tasksStats = $tasksStatsQuery->first();

        $commitsCountQuery = DB::table('commit_logs');
        if ($startDate && $endDate) {
            $commitsCountQuery->whereBetween('commit_date', [$startDate, $endDate]);
        }
        $totalCommits = $commitsCountQuery->count();

        $meetingsCountQuery = DB::table('meetings');
        if ($startDate && $endDate) {
            $meetingsCountQuery->whereBetween('meeting_date', [$startDate, $endDate]);
        }
        $totalMeetings = $meetingsCountQuery->count();

        // 2. Database aggregates grouped by employee_id within range
        $empTasksQuery = DB::table('tasks')
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as total_tasks'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks')
            );
        if ($startDate && $endDate) {
            $empTasksQuery->whereBetween('assigned_date', [$startDate, $endDate]);
        }
        $empTasks = $empTasksQuery->groupBy('employee_id')->get()->keyBy('employee_id');

        $empCommitsQuery = DB::table('commit_logs')
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as total_commits'),
                DB::raw('SUM(lines_added) as lines_added'),
                DB::raw('SUM(lines_deleted) as lines_deleted')
            );
        if ($startDate && $endDate) {
            $empCommitsQuery->whereBetween('commit_date', [$startDate, $endDate]);
        }
        $empCommits = $empCommitsQuery->groupBy('employee_id')->get()->keyBy('employee_id');

        $empAttendanceQuery = DB::table('attendences')
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as total_attendance'),
                DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_days')
            );
        if ($startDate && $endDate) {
            $empAttendanceQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $empAttendance = $empAttendanceQuery->groupBy('employee_id')->get()->keyBy('employee_id');

        $empMeetingsQuery = DB::table('employee_meeting as em')
            ->join('meetings as m', 'm.id', '=', 'em.meeting_id')
            ->select('em.employee_id', DB::raw('COUNT(*) as total_meetings'));
        if ($startDate && $endDate) {
            $empMeetingsQuery->whereBetween('m.meeting_date', [$startDate, $endDate]);
        }
        $empMeetings = $empMeetingsQuery->groupBy('em.employee_id')->get()->keyBy('employee_id');

        $empTranscriptsQuery = DB::table('meeting_transcripts as mt')
            ->join('meetings as m', 'm.id', '=', 'mt.meeting_id')
            ->select(
                'mt.employee_id',
                DB::raw('COUNT(*) as transcript_entries'),
                DB::raw('SUM(LENGTH(mt.spoken_text) - LENGTH(REPLACE(mt.spoken_text, " ", "")) + 1) as word_count')
            );
        if ($startDate && $endDate) {
            $empTranscriptsQuery->whereBetween('m.meeting_date', [$startDate, $endDate]);
        }
        $empTranscripts = $empTranscriptsQuery->whereNotNull('mt.employee_id')->groupBy('mt.employee_id')->get()->keyBy('employee_id');

        // 3. Load lightweight employees details
        if ($startDate && $endDate) {
            $activeIds = array_unique(array_merge(
                $empTasks->keys()->all(),
                $empCommits->keys()->all(),
                $empAttendance->keys()->all(),
                $empMeetings->keys()->all(),
                $empTranscripts->keys()->all()
            ));

            $employeesQuery = DB::table('employees')
                ->leftJoin('teams', 'teams.id', '=', 'employees.team_id')
                ->select('employees.id', 'employees.name', 'employees.email', 'employees.team_id', 'teams.name as team_name');

            if (empty($activeIds)) {
                $employees = collect();
            } else {
                $employees = $employeesQuery->whereIn('employees.id', $activeIds)->get();
            }
        } else {
            $employees = DB::table('employees')
                ->leftJoin('teams', 'teams.id', '=', 'employees.team_id')
                ->select('employees.id', 'employees.name', 'employees.email', 'employees.team_id', 'teams.name as team_name')
                ->get();
        }

        // 4. Consolidate employee metrics and compute score
        $employeeStats = [];
        foreach ($employees as $emp) {
            $id = $emp->id;

            $tasksInfo = $empTasks->get($id);
            $totalTasks = $tasksInfo->total_tasks ?? 0;
            $completedTasks = $tasksInfo->completed_tasks ?? 0;
            $pendingTasks = $totalTasks - $completedTasks;

            $commitsInfo = $empCommits->get($id);
            $totalCommits = $commitsInfo->total_commits ?? 0;
            $linesAdded = $commitsInfo->lines_added ?? 0;
            $linesDeleted = $commitsInfo->lines_deleted ?? 0;

            $attendanceInfo = $empAttendance->get($id);
            $totalAttendance = $attendanceInfo->total_attendance ?? 0;
            $presentDays = $attendanceInfo->present_days ?? 0;
            $attendanceRate = $totalAttendance > 0 ? (int)round(($presentDays / $totalAttendance) * 100) : 100;

            $meetingsInfo = $empMeetings->get($id);
            $totalMeetings = $meetingsInfo->total_meetings ?? 0;

            $transcriptInfo = $empTranscripts->get($id);
            $transcriptEntries = $transcriptInfo->transcript_entries ?? 0;
            $wordCount = $transcriptInfo->word_count ?? 0;

            // Score calculation for candidate rankings
            $taskScore = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
            $commitScore = min(100, $totalCommits * 5);
            $attendScore = $attendanceRate;
            $meetScore = min(100, $totalMeetings * 10);
            $speakScore = min(100, $wordCount / 10);

            $score = ($taskScore * 0.40) + ($commitScore * 0.20) + ($attendScore * 0.15) + ($meetScore * 0.15) + ($speakScore * 0.10);

            $employeeStats[$id] = [
                'name' => $emp->name,
                'email' => $emp->email,
                'team_id' => $emp->team_id,
                'team_name' => $emp->team_name ?? 'No Team',
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'total_commits' => $totalCommits,
                'lines_added' => (int)$linesAdded,
                'lines_deleted' => (int)$linesDeleted,
                'attendance_rate' => $attendanceRate,
                'total_meetings' => $totalMeetings,
                'transcript_entries' => $transcriptEntries,
                'word_count' => (int)$wordCount,
                'score' => round($score, 2)
            ];
        }

        // 5. Build Team Aggregates
        $teams = DB::table('teams')->get();
        $teamsSummary = [];
        foreach ($teams as $team) {
            $teamEmployees = collect($employeeStats)->where('team_id', $team->id);
            $memberCount = $teamEmployees->count();

            $teamTotalTasks = $teamEmployees->sum('total_tasks');
            $teamCompletedTasks = $teamEmployees->sum('completed_tasks');
            $teamCommits = $teamEmployees->sum('total_commits');
            $teamAttendanceRate = $memberCount > 0 ? (int)round($teamEmployees->avg('attendance_rate')) : 100;
            $teamMeetings = $memberCount > 0 ? round($teamEmployees->avg('total_meetings'), 1) : 0;

            $teamsSummary[] = [
                'team_name' => $team->name,
                'description' => $team->description,
                'members_count' => $memberCount,
                'total_tasks' => $teamTotalTasks,
                'completed_tasks' => $teamCompletedTasks,
                'total_commits' => $teamCommits,
                'attendance_rate' => $teamAttendanceRate . '%',
                'average_meetings_attended' => $teamMeetings
            ];
        }

        // 6. Rank employees to select candidates
        $activeEmployees = collect($employeeStats)->filter(function ($emp) {
            return $emp['total_tasks'] > 0 || $emp['total_commits'] > 0 || $emp['total_meetings'] > 0;
        });

        // Top Performers: Sort DESC
        $candidateTopPerformers = $activeEmployees->sortByDesc('score')->take(25)->values()->all();

        // Attention Required: Sort ASC
        $candidateAttentionRequired = $activeEmployees->sortBy('score')->take(25)->values()->all();

        // 7. Overdue Risks list
        $overdueTasksQuery = DB::table('tasks as t')
            ->join('employees as e', 'e.id', '=', 't.employee_id')
            ->leftJoin('teams as team', 'team.id', '=', 'e.team_id')
            ->select('t.title', 'e.name as employee_name', 'e.team_id', 'team.name as team_name', 't.due_date')
            ->where('t.status', '!=', 'completed');

        if ($startDate && $endDate) {
            $overdueTasksQuery->whereBetween('t.assigned_date', [$startDate, $endDate])
                ->where('t.due_date', '<', $endDate);
        } else {
            $overdueTasksQuery->where('t.due_date', '<', Carbon::today()->toDateString());
        }

        $overdueRisks = $overdueTasksQuery->orderBy('t.due_date')
            ->take(15)
            ->get()
            ->map(function ($task) use ($endDate) {
                $dueDate = Carbon::parse($task->due_date);
                $referenceDate = $endDate ? Carbon::parse($endDate) : Carbon::today();
                $daysOverdue = $dueDate->diffInDays($referenceDate, false);
                return [
                    'task_title' => $task->title,
                    'assignee' => $task->employee_name,
                    'team_id' => $task->team_id,
                    'team_name' => $task->team_name ?? 'No Team',
                    'due_date' => $task->due_date,
                    'days_overdue' => $daysOverdue > 0 ? (int)$daysOverdue : 0
                ];
            })
            ->all();

        // 7.5 Build Team Analysis Candidates
        $teamsAnalysisCandidates = [];
        foreach ($teams as $team) {
            $teamEmployees = collect($employeeStats)->where('team_id', $team->id);

            // Top performer candidates for this team
            $teamTop = $teamEmployees->sortByDesc('score')->take(2)->map(function ($emp) {
                return [
                    'name' => $emp['name'],
                    'score' => $emp['score'],
                    'total_tasks' => $emp['total_tasks'],
                    'completed_tasks' => $emp['completed_tasks'],
                    'total_commits' => $emp['total_commits'],
                    'lines_added' => $emp['lines_added'],
                    'lines_deleted' => $emp['lines_deleted'],
                    'attendance_rate' => $emp['attendance_rate'],
                    'total_meetings' => $emp['total_meetings'],
                    'word_count' => $emp['word_count']
                ];
            })->values()->all();

            // Attention required candidates for this team
            $teamAttention = $teamEmployees->sortBy('score')->take(2)->map(function ($emp) {
                return [
                    'name' => $emp['name'],
                    'score' => $emp['score'],
                    'total_tasks' => $emp['total_tasks'],
                    'completed_tasks' => $emp['completed_tasks'],
                    'total_commits' => $emp['total_commits'],
                    'lines_added' => $emp['lines_added'],
                    'lines_deleted' => $emp['lines_deleted'],
                    'attendance_rate' => $emp['attendance_rate'],
                    'total_meetings' => $emp['total_meetings'],
                    'word_count' => $emp['word_count']
                ];
            })->values()->all();

            // Overdue tasks for this team
            $teamOverdue = collect($overdueRisks)
                ->where('team_id', $team->id)
                ->take(3)
                ->map(function ($task) {
                    return [
                        'task_title' => $task['task_title'],
                        'assignee' => $task['assignee'],
                        'days_overdue' => $task['days_overdue']
                    ];
                })
                ->values()
                ->all();

            $teamsAnalysisCandidates[] = [
                'team_name' => $team->name,
                'team_description' => $team->description,
                'top_performer_candidates' => $teamTop,
                'attention_required_candidates' => $teamAttention,
                'overdue_tasks' => $teamOverdue
            ];
        }

        // 8. Meeting Highlights
        $meetingsHighlightsQuery = Meeting::query();
        if ($startDate && $endDate) {
            $meetingsHighlightsQuery->whereBetween('meeting_date', [$startDate, $endDate]);
        }
        $recentMeetings = $meetingsHighlightsQuery->latest('meeting_date')
            ->take(10)
            ->get();

        $meetingsHighlights = [];
        foreach ($recentMeetings as $meeting) {
            $participantCount = DB::table('employee_meeting')->where('meeting_id', $meeting->id)->count();

            $topSpeakersQuery = DB::table('meeting_transcripts')
                ->select('speaker_name', DB::raw('SUM(LENGTH(spoken_text) - LENGTH(REPLACE(spoken_text, " ", "")) + 1) as word_count'))
                ->where('meeting_id', $meeting->id)
                ->groupBy('speaker_name')
                ->orderByDesc('word_count')
                ->take(3)
                ->get();

            $topSpeakers = $topSpeakersQuery->map(fn($s) => [
                'name' => $s->speaker_name,
                'words' => (int) $s->word_count
            ])->all();

            $meetingsHighlights[] = [
                'meeting_title' => $meeting->title,
                'date' => Carbon::parse($meeting->meeting_date)->toDateString(),
                'notes' => Str::limit($meeting->notes, 150),
                'participant_count' => $participantCount,
                'top_speakers' => $topSpeakers
            ];
        }

        // 8.5. Get Commit Insights
        $insightsQuery = DB::table('commit_insights as ci')
            ->join('commit_logs as cl', 'cl.id', '=', 'ci.commit_log_id')
            ->leftJoin('employees as e', 'e.id', '=', 'cl.employee_id')
            ->select('ci.feature_category', 'ci.risk_level', 'ci.technical_complexity', 'ci.summary', 'cl.commit_hash', 'cl.commit_message', 'cl.repository_name', 'e.name as employee_name');

        if ($startDate && $endDate) {
            $insightsQuery->whereBetween('cl.commit_date', [$startDate, $endDate]);
        }

        $commitInsights = $insightsQuery->get();

        $insightsGroupedByCategory = $commitInsights->groupBy('feature_category')->map(function ($items) {
            return $items->count();
        })->all();

        $insightsGroupedByRisk = $commitInsights->groupBy('risk_level')->map(function ($items) {
            return $items->count();
        })->all();

        $riskyCommits = $commitInsights->filter(function ($item) {
            return in_array(strtolower($item->risk_level), ['high', 'medium']);
        })->take(10)->map(function ($item) {
            return [
                'hash' => substr($item->commit_hash, 0, 7),
                'message' => $item->commit_message,
                'employee' => $item->employee_name,
                'repository' => $item->repository_name,
                'category' => $item->feature_category,
                'risk' => $item->risk_level,
                'summary' => $item->summary
            ];
        })->values()->all();

        $projectsQuery = Project::query();
        if ($startDate && $endDate) {
            $projectsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $totalProjects = $projectsQuery->count();
        $completedProjects = (clone $projectsQuery)->where('status', 'Completed')->count();
        $inProgressProjects = (clone $projectsQuery)->where('status', 'In Progress')->count();

        $delayedProjects = (clone $projectsQuery)->where('status', '!=', 'Completed')
            ->where('end_date', '<', Carbon::today())
            ->get()->map(fn($p) => ['name' => $p->name, 'status' => $p->status])->all();

        // 9. Format performance data JSON
        $summaryData = [
            'date_range' => ($startDate && $endDate) ? "{$startDate} to {$endDate}" : "All Time",
            'overall_summary' => [
                'total_employees' => $totalEmployees,
                'total_projects' => $totalProjects,
                'completed_projects' => $completedProjects,
                'in_progress_projects' => $inProgressProjects,
                'total_tasks' => $tasksStats->total,
                'completed_tasks' => $tasksStats->completed,
                'pending_tasks' => $tasksStats->pending,
                'in_progress_tasks' => $tasksStats->in_progress,
                'total_commits' => $totalCommits,
                'total_meetings' => $totalMeetings,
            ],
            'projects_summary' => [
                'delayed_projects' => $delayedProjects
            ],
            'teams_summary' => $teamsSummary,
            'teams_analysis_candidates' => $teamsAnalysisCandidates,
            'candidate_top_performers' => $candidateTopPerformers,
            'candidate_attention_required' => $candidateAttentionRequired,
            'recent_overdue_risks' => $overdueRisks,
            'meetings_highlights' => $meetingsHighlights,
            'commit_insights' => [
                'total_insights_generated' => $commitInsights->count(),
                'grouped_by_category' => $insightsGroupedByCategory,
                'grouped_by_risk' => $insightsGroupedByRisk,
                'risky_commits' => $riskyCommits,
            ],
        ];

        return $summaryData;
    }

    public function generateReport($startDate = null, $endDate = null)
    {
        set_time_limit(300);

        $summaryData = $this->getPerformanceSummaryData($startDate, $endDate);
        $summaryJson = json_encode($summaryData, JSON_PRETTY_PRINT);

        $prompt = "
You are an AI Manager Agent.

Analyze the performance summaries of employees, teams, meetings, and commits provided below.

PERFORMANCE SUMMARY DATA:
{$summaryJson}

Instructions:

1. Calculate Team Productivity Percentage based on overall task completion, attendance, development activity, meeting participation and speaking contributions.
2. Identify the Top Performers based on the candidates provided. Ensure they are selected from the candidate_top_performers list, detailing their specific achievements.
3. Identify employees that require Attention based on low productivity, poor attendance, few commits, pending tasks, delays, or minimal speaking contributions. Select them from the candidate_attention_required list, specifying the reasons.
4. Identify Risks from overdue tasks, project delays, and team performance.
5. Provide Supporting Employee Statistics summarizing the key metrics for each employee.
6. Provide specific Meeting Analysis based on the meeting highlights, attendance, speaking frequency, and discussion involvement.
7. Provide Commit Analysis outlining development activity, code contribution trends, features delivered, active contributors, and any risky commits (high/medium risk) identified.
8. Provide Attendence Analysis summarizing attendance patterns.
9. Provide Task Analysis evaluating task completion rates and bottlenecks.
10. Determine which team performed best during the selected date range as the Top Performing Team, calculating performance scores based on task completion, attendance, commit logs, and meeting contributions of all its members.
11. Generate a detailed Team Analysis for each team in the `teams_analysis_candidates` list. For each team, identify:
    - Top Performer: Best performing employee of that team selected from the team's top_performer_candidates list. Calculate score and provide a detailed reason.
    - Attention Required: Employees of that team requiring attention selected from the team's attention_required_candidates list. Provide employee_name and reason.
    - Team Risks: Specific risks associated with that team based on its overdue_tasks and performance.
12. Identify Top Performing Project and Project Risks based on task completion and delayed projects.
13. Generate an Executive Leadership Summary containing key metrics and the overall organization health score.
14. Generate 4-5 AI Recommendations for promotion, resource allocation, team restructuring, risk mitigation, and productivity improvements.

IMPORTANT:
- Return ONLY valid JSON.
- Do NOT return markdown.
- Do NOT return explanations.
- Do NOT wrap the JSON in ```json blocks.
- Output must be a single JSON object.

Expected JSON format:

{
  \"team_productivity_percentage\": 0,
  \"top_performers\": [
    { \"name\": \"Employee Name\", \"reason\": \"Reason for selection\" }
  ],
  \"top_performing_team\": {
    \"team_name\": \"Team Name\",
    \"score\": 0,
    \"reason\": \"Detailed reason for selecting this team as the top performer\"
  },
  \"team_analysis\": [
    {
      \"team_name\": \"Team Name\",
      \"top_performer\": {
        \"employee_name\": \"Employee Name\",
        \"score\": 0,
        \"reason\": \"Reason details\"
      },
      \"attention_required\": [
        {
          \"employee_name\": \"Employee Name\",
          \"reason\": \"Reason details\"
        }
      ],
      \"risks\": [
        \"Risk description\"
      ]
    }
  ],
  \"attention_required\": [
    { \"name\": \"Employee Name\", \"reason\": \"Reason for attention\" }
  ],
  \"risks\": [
    { \"risk\": \"Risk description\", \"severity\": \"High/Medium/Low\" }
  ],
  \"supporting_employee_statistics\": [
    { \"name\": \"Employee Name\", \"summary\": \"Brief stats summary\" }
  ],
  \"meeting_analysis\": \"Summary of meeting involvement, active speakers, etc.\",
  \"commit_analysis\": \"Summary of commit activity.\",
  \"attendence_analysis\": \"Summary of attendence patterns.\",
  \"task_analysis\": \"Summary of task completions.\",
  \"project_analysis\": {
    \"top_performing_project\": \"Name and reason\",
    \"delayed_projects\": [\"Project name 1\", \"Project name 2\"],
    \"project_risks\": \"Summary of project risks\"
  },
  \"leadership_summary\": {
    \"top_performer\": \"Employee Name\",
    \"best_performing_team\": \"Team Name\",
    \"employees_attention_count\": 0,
    \"teams_attention_count\": 0,
    \"high_risk_projects_count\": 0,
    \"organization_health_score\": 0
  },
  \"ai_recommendations\": [
    {
      \"type\": \"Promotion | Resource Allocation | Restructuring | Risk Mitigation | Productivity\",
      \"title\": \"Short title\",
      \"description\": \"Detailed description\"
    }
  ]
}

Analyze the data and generate the result now.
";
        $report = $this->askNvidia($prompt);

        if (is_array($report)) {
            Report::create([
                'team_productivity' => $report['team_productivity_percentage']
                    ?? $report['team_productivity']
                    ?? 0,
                'top_performers' => $report['top_performers'] ?? [],
                'attention_required' => $report['attention_required'] ?? [],
                'risks' => $report['risks'] ?? [],
                'full_report' => json_encode($report, JSON_PRETTY_PRINT),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => now(),
            ]);
        }

        return $report;
    }

    public function askNvidia($prompt)
    {
        $apiKey = config('services.nvidia.api_key');

        Log::info('[NvidiaAgent] Sending request to NVIDIA NIM...');

        try {
            $response = Http::timeout(240)
                ->withToken($apiKey)
                ->post('https://integrate.api.nvidia.com/v1/chat/completions', [
                    'model'       => 'meta/llama-3.3-70b-instruct',
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.2,
                    'max_tokens'  => 4096,
                    'stream'      => false,
                ]);

            $data = $response->json();

            Log::info('[NvidiaAgent] HTTP status: ' . $response->status());
            Log::info('[NvidiaAgent] Raw response: ' . json_encode($data));

            $text = $data['choices'][0]['message']['content'] ?? null;

            if (!$text) {
                Log::error('[NvidiaAgent] No content in response. Full data: ' . json_encode($data));
                return $data;
            }

            // Attempt 1: direct JSON decode
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('[NvidiaAgent] Successfully parsed JSON directly.');
                return $decoded;
            }

            // Attempt 2: strip accidental markdown fences and retry
            $stripped = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
            $stripped = preg_replace('/\s*```$/', '', $stripped);
            $decoded  = json_decode($stripped, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('[NvidiaAgent] Parsed JSON after stripping markdown fences.');
                return $decoded;
            }

            // Attempt 3: extract first {...} JSON block
            $start = strpos($stripped, '{');
            $end   = strrpos($stripped, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $substr  = substr($stripped, $start, $end - $start + 1);
                $decoded = json_decode($substr, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info('[NvidiaAgent] Parsed JSON by extracting substring.');
                    return $decoded;
                }
            }

            Log::error('[NvidiaAgent] Could not parse JSON from response text: ' . $text);
            return $data;
        } catch (\Throwable $e) {
            Log::error('[NvidiaAgent] Exception: ' . $e->getMessage());
            return null;
        }
    }
}
