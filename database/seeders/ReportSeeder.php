<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();
        $todayStr = $today->toDateString();
        $now = Carbon::now();

        // 1. Calculate Top Performers based on database stats
        $topPerformers = DB::table('employees as e')
            ->select('e.id', 'e.name')
            ->leftJoin(DB::raw('(
                SELECT employee_id, COUNT(*) as tasks_count, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks_count
                FROM tasks
                GROUP BY employee_id
            ) as t'), 't.employee_id', '=', 'e.id')
            ->leftJoin(DB::raw('(
                SELECT employee_id, COUNT(*) as attendances_count, SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_attendances_count
                FROM attendences
                GROUP BY employee_id
            ) as a'), 'a.employee_id', '=', 'e.id')
            ->leftJoin(DB::raw('(
                SELECT employee_id, COUNT(*) as commits_count
                FROM commit_logs
                GROUP BY employee_id
            ) as c'), 'c.employee_id', '=', 'e.id')
            ->selectRaw('
                ROUND(
                    (ROUND(COALESCE(t.completed_tasks_count, 0) / CASE WHEN COALESCE(t.tasks_count, 0) > 0 THEN t.tasks_count ELSE 1 END * 100) * 0.6) +
                    (ROUND(COALESCE(a.present_attendances_count, 0) / CASE WHEN COALESCE(a.attendances_count, 0) > 0 THEN a.attendances_count ELSE 1 END * 100) * 0.25) +
                    (CASE WHEN COALESCE(c.commits_count, 0) * 10 < 100 THEN COALESCE(c.commits_count, 0) * 10 ELSE 100 END * 0.15)
                ) as score
            ')
            ->orderByDesc('score')
            ->take(5)
            ->get();

        // 2. Calculate Attention Required based on database stats
        $attentionRequired = DB::table('employees as e')
            ->select('e.id', 'e.name')
            ->leftJoin(DB::raw('(
                SELECT employee_id, COUNT(*) as tasks_count,
                    SUM(CASE WHEN status IN ("pending", "in_progress") THEN 1 ELSE 0 END) as incomplete_tasks_count,
                    SUM(CASE WHEN status != "completed" AND due_date < \'' . $todayStr . '\' THEN 1 ELSE 0 END) as overdue_tasks_count
                FROM tasks
                GROUP BY employee_id
            ) as t'), 't.employee_id', '=', 'e.id')
            ->selectRaw('
                CASE WHEN
                    ROUND(COALESCE(t.incomplete_tasks_count, 0) / CASE WHEN COALESCE(t.tasks_count, 0) > 0 THEN t.tasks_count ELSE 1 END * 100) +
                    (COALESCE(t.overdue_tasks_count, 0) * 10) < 100
                THEN
                    ROUND(COALESCE(t.incomplete_tasks_count, 0) / CASE WHEN COALESCE(t.tasks_count, 0) > 0 THEN t.tasks_count ELSE 1 END * 100) +
                    (COALESCE(t.overdue_tasks_count, 0) * 10)
                ELSE
                    100
                END as score
            ')
            ->orderByDesc('score')
            ->take(5)
            ->get();

        // 3. Overall stats
        $taskStats = DB::table('tasks')
            ->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed
            ")
            ->first();
        $totalTasks = $taskStats->total;
        $completedTasks = $taskStats->completed;
        $teamProductivity = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 75;

        // 4. Overdue risks
        $overdueTasks = DB::table('tasks')
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', $todayStr)
            ->take(5)
            ->get();
        $risks = $overdueTasks->map(fn($task) => [
            'title' => "Task is overdue: " . $task->title,
            'impact' => $task->due_date < $today->copy()->subDays(3)->toDateString() ? 'High Impact' : 'Medium Impact',
            'time' => Carbon::parse($task->due_date)->diffForHumans($now),
        ])->all();

        if (empty($risks)) {
            $risks = [
                ['title' => 'Main release timeline risk', 'impact' => 'Medium Impact', 'time' => '3 days ago'],
                ['title' => 'Pending backlog count build-up', 'impact' => 'Low Impact', 'time' => '1 day ago']
            ];
        }

        // Format top performers
        $topPerformersFormatted = $topPerformers->map(fn($r) => [
            'name' => $r->name,
            'score' => (int) $r->score,
            'avatar' => 'https://i.pravatar.cc/80?img=' . (($r->id % 60) + 1),
        ])->all();

        // Format attention required
        $attentionRequiredFormatted = $attentionRequired->map(fn($r) => [
            'name' => $r->name,
            'score' => (int) $r->score,
            'avatar' => 'https://i.pravatar.cc/80?img=' . ((($r->id + 20) % 60) + 1),
        ])->all();

        // Format normalized risks
        $risksFormatted = array_slice($risks, 0, 3);

        // Build Team Analysis for seeded reports
        $teams = DB::table('teams')->get();
        $teamAnalysis1 = [];
        $teamAnalysis2 = [];
        foreach ($teams as $team) {
            $teamEmp = DB::table('employees')
                ->where('team_id', $team->id)
                ->take(2)
                ->get();
                
            $topEmpName = $teamEmp->first()?->name ?? 'John Doe';
            $attEmpName = $teamEmp->last()?->name ?? 'Jane Smith';
            
            $teamAnalysis1[] = [
                'team_name' => $team->name,
                'top_performer' => [
                    'employee_name' => $topEmpName,
                    'score' => 94,
                    'reason' => 'Completed highest number of tasks, highest commit activity and strong meeting participation.'
                ],
                'attention_required' => [
                    [
                        'employee_name' => $attEmpName,
                        'reason' => 'Low commit activity and multiple overdue tasks.'
                    ]
                ],
                'risks' => [
                    "Multiple overdue API tasks in {$team->name}.",
                    "Heavy dependency on a single developer.",
                    "Low testing activity."
                ]
            ];
            
            $teamAnalysis2[] = [
                'team_name' => $team->name,
                'top_performer' => [
                    'employee_name' => $topEmpName,
                    'score' => 91,
                    'reason' => 'Consistent delivery of high complexity features, positive speaking activity, and strong attendance.'
                ],
                'attention_required' => [
                    [
                        'employee_name' => $attEmpName,
                        'reason' => 'Low commit count and overdue development tasks.'
                    ]
                ],
                'risks' => [
                    "Backlog accumulation for critical deliverables in {$team->name}.",
                    "Low participation rates in team design reviews."
                ]
            ];
        }

        // Build Report 1: Weekly Report
        $reportData1 = [
            'team_productivity_percentage' => $teamProductivity,
            'top_performers' => $topPerformers->map(fn($r) => ['name' => $r->name, 'reason' => "Excellent productivity rating of {$r->score}% based on task completion and commits."])->all(),
            'top_performing_team' => [
                'team_name' => 'Team Alpha',
                'score' => 92,
                'reason' => 'Team Alpha excelled this week with a 95% task completion rate, high commit frequency (averaging 42 commits/day), and solid attendance. Their frontend and design sync meetings were highly productive with balanced speaker contributions.'
            ],
            'team_analysis' => $teamAnalysis1,
            'attention_required' => $attentionRequired->map(fn($r) => ['name' => $r->name, 'reason' => "Suboptimal completion metrics with risk score of {$r->score}%."])->all(),
            'risks' => array_map(fn($r) => ['risk' => $r['title'], 'severity' => str_replace(' Impact', '', $r['impact'])], $risks),
            'supporting_employee_statistics' => $topPerformers->map(fn($r) => ['name' => $r->name, 'summary' => "Demonstrates robust delivery speed and strong attention to code quality."])->all(),
            'meeting_analysis' => 'Consistent sync attendance and productive participation levels recorded across all daily developer meetings.',
            'commit_analysis' => 'Development velocities highlight stable commits mid-week, followed by successful integration sequences.',
            'attendence_analysis' => 'Attendance averages 94% with standard weekend variations in line with tracking policies.',
            'task_analysis' => 'Completion ratios remain high, with standard milestones met on schedule.'
        ];

        DB::table('reports')->insert([
            'team_productivity' => $teamProductivity,
            'top_performers' => json_encode($topPerformersFormatted),
            'attention_required' => json_encode($attentionRequiredFormatted),
            'risks' => json_encode($risksFormatted),
            'full_report' => json_encode($reportData1, JSON_PRETTY_PRINT),
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-07',
            'generated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Build Report 2: Biweekly Report
        $reportData2 = [
            'team_productivity_percentage' => max(0, $teamProductivity - 3),
            'top_performers' => $topPerformers->map(fn($r) => ['name' => $r->name, 'reason' => "Consistent high-value contributions and task efficiency scoring {$r->score}%."])->all(),
            'top_performing_team' => [
                'team_name' => 'Team Alpha',
                'score' => 89,
                'reason' => 'Team Alpha maintained top-tier productivity across the entire sprint, leading in total task completion and showing remarkable mid-week development velocity in commits.'
            ],
            'team_analysis' => $teamAnalysis2,
            'attention_required' => $attentionRequired->map(fn($r) => ['name' => $r->name, 'reason' => "Tracking below average completion metrics with a risk indicator of {$r->score}%."])->all(),
            'risks' => array_map(fn($r) => ['risk' => $r['title'], 'severity' => str_replace(' Impact', '', $r['impact'])], $risks),
            'supporting_employee_statistics' => $topPerformers->map(fn($r) => ['name' => $r->name, 'summary' => "Strong presence in active development pipelines and core features implementation."])->all(),
            'meeting_analysis' => 'Collaboration trends show strong engagement across cross-functional team calls.',
            'commit_analysis' => 'Commit rates show a high baseline activity level, indicating a steady sprint velocity.',
            'attendence_analysis' => 'Overall attendance patterns demonstrate excellent consistency over the biweekly period.',
            'task_analysis' => 'The backlog remains manageable with clear progression on complex components.'
        ];

        DB::table('reports')->insert([
            'team_productivity' => max(0, $teamProductivity - 3),
            'top_performers' => json_encode($topPerformersFormatted),
            'attention_required' => json_encode($attentionRequiredFormatted),
            'risks' => json_encode($risksFormatted),
            'full_report' => json_encode($reportData2, JSON_PRETTY_PRINT),
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-18',
            'generated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
