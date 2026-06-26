<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Team;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\ManagerAgentService;
use Carbon\Carbon;

class LeadershipInsightsController extends Controller
{
    public function index(ManagerAgentService $agent)
    {
        // 1. Top Performers (Top 10 Employees)
        $topPerformers = Cache::remember('leadership_top_performers', now()->addHours(6), function () {
            return $this->getTopPerformers();
        });
        
        // 2. Best Performing Teams
        $teamPerformance = Cache::remember('leadership_team_performance', now()->addHours(6), function () {
            return $this->getTeamPerformance();
        });
        
        // 3. Employees Requiring Attention
        $employeesAttention = Cache::remember('leadership_employees_attention', now()->addHours(6), function () {
            return $this->getEmployeesRequiringAttention();
        });
        
        // 4. Teams Requiring Attention
        $teamsAttention = Cache::remember('leadership_teams_attention', now()->addHours(6), function () use ($teamPerformance) {
            return $this->getTeamsRequiringAttention($teamPerformance);
        });
        
        // 5. Project Risks
        $projectRisks = Cache::remember('leadership_project_risks', now()->addHours(6), function () {
            return $this->getProjectRisks();
        });
        
        // 6. Leadership Summary (Aggregated)
        $summary = Cache::remember('leadership_summary', now()->addHours(6), function () use ($topPerformers, $teamPerformance, $employeesAttention, $teamsAttention, $projectRisks) {
            $topPerformerName = 'N/A';
            if (count($topPerformers) > 0) {
                $first = $topPerformers[0];
                $topPerformerName = is_object($first) ? $first->name : $first['name'];
            }
            return [
                'top_performer' => $topPerformerName,
                'best_team' => count($teamPerformance) > 0 ? $teamPerformance[0]['team_name'] : 'N/A',
                'employees_attention' => count($employeesAttention),
                'teams_attention' => count($teamsAttention),
                'high_risk_projects' => count(array_filter($projectRisks, fn($p) => $p['risk_level'] === 'High')),
                'health_score' => $this->calculateOrganizationHealth($teamPerformance, $employeesAttention)
            ];
        });

        // 7. AI Recommendations
        $aiRecommendations = Cache::remember('leadership_insights_ai_recommendations', now()->addHours(6), function () use ($agent, $summary, $teamPerformance, $projectRisks) {
            return $this->generateAIRecommendations($agent, $summary, $teamPerformance, $projectRisks);
        });

        // 8. GitLab Insights
        $gitLabInsights = Cache::remember('leadership_gitlab_insights', now()->addHours(6), function () {
            return $this->getGitLabInsights();
        });

        return view('leadership-insights', compact(
            'topPerformers',
            'teamPerformance',
            'employeesAttention',
            'teamsAttention',
            'projectRisks',
            'summary',
            'aiRecommendations',
            'gitLabInsights'
        ));
    }

    public function getGitLabInsights()
    {
        $sevenDaysAgo = Carbon::today()->subDays(7)->toDateString();

        // 1. Top Commit Contributor
        $topContributor = DB::table('commit_logs')
            ->join('employees', 'commit_logs.employee_id', '=', 'employees.id')
            ->select('employees.name', DB::raw('COUNT(*) as commit_count'))
            ->groupBy('employees.name')
            ->orderByDesc('commit_count')
            ->first();

        // 2. Employees with no commits in last 7 days (who are devs)
        // Since we don't have a strict dev role, we assume employees who have committed previously but not in last 7 days
        $allCommitters = DB::table('commit_logs')->distinct()->pluck('employee_id');
        $recentCommitters = DB::table('commit_logs')
            ->where('commit_date', '>=', $sevenDaysAgo)
            ->distinct()
            ->pluck('employee_id');
        
        $noRecentCommitsCount = Employee::whereIn('id', $allCommitters)
            ->whereNotIn('id', $recentCommitters)
            ->count();

        // 3. Most Active Team by commits
        $mostActiveTeam = DB::table('commit_logs')
            ->join('employees', 'commit_logs.employee_id', '=', 'employees.id')
            ->join('teams', 'employees.team_id', '=', 'teams.id')
            ->select('teams.name', DB::raw('COUNT(commit_logs.id) as commit_count'))
            ->groupBy('teams.name')
            ->orderByDesc('commit_count')
            ->first();

        return [
            'top_contributor' => $topContributor ? $topContributor->name : 'N/A',
            'top_contributor_commits' => $topContributor ? $topContributor->commit_count : 0,
            'no_recent_commits_count' => $noRecentCommitsCount,
            'most_active_team' => $mostActiveTeam ? $mostActiveTeam->name : 'N/A',
            'most_active_team_commits' => $mostActiveTeam ? $mostActiveTeam->commit_count : 0
        ];
    }

    public function getTopPerformers()
    {
        $employees = DB::table('employees')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->select('employees.id', 'employees.name', 'teams.name as team_name')
            ->get();

        $taskStats = DB::table('tasks')
            ->select('employee_id', DB::raw('COUNT(*) as total_tasks'), DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $attendanceStats = DB::table('attendences')
            ->select('employee_id', DB::raw('COUNT(*) as total_attendances'), DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_days'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $commitStats = DB::table('commit_logs')
            ->select('employee_id', DB::raw('COUNT(*) as commit_count'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $meetingStats = DB::table('employee_meeting')
            ->select('employee_id', DB::raw('COUNT(*) as meetings_attended'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $metrics = [];
        foreach ($employees as $employee) {
            $tasks = $taskStats->get($employee->id);
            $attendance = $attendanceStats->get($employee->id);
            $commits = $commitStats->get($employee->id);
            $meetings = $meetingStats->get($employee->id);

            $total_tasks = $tasks ? (int)$tasks->total_tasks : 0;
            $completed_tasks = $tasks ? (int)$tasks->completed_tasks : 0;
            $total_attendances = $attendance ? (int)$attendance->total_attendances : 0;
            $present_days = $attendance ? (int)$attendance->present_days : 0;
            $commit_count = $commits ? (int)$commits->commit_count : 0;
            $meetings_attended = $meetings ? (int)$meetings->meetings_attended : 0;

            $taskScore = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0;
            $attendanceScore = $total_attendances > 0 ? ($present_days / $total_attendances) * 100 : 100;
            $commitScore = min(100, $commit_count * 5);
            $meetingScore = min(100, $meetings_attended * 10);

            $performance_score = round(($taskScore * 0.4) + ($attendanceScore * 0.2) + ($commitScore * 0.2) + ($meetingScore * 0.2), 1);
            $attendance_percentage = round($attendanceScore, 1);

            if ($performance_score >= 85) {
                $badge = 'Excellent';
            } elseif ($performance_score >= 70) {
                $badge = 'Good';
            } elseif ($performance_score >= 50) {
                $badge = 'Average';
            } else {
                $badge = 'Needs Improvement';
            }

            $metrics[] = (object)[
                'id' => $employee->id,
                'name' => $employee->name,
                'team_name' => $employee->team_name,
                'total_tasks' => $total_tasks,
                'completed_tasks' => $completed_tasks,
                'total_attendances' => $total_attendances,
                'present_days' => $present_days,
                'commit_count' => $commit_count,
                'meetings_attended' => $meetings_attended,
                'performance_score' => $performance_score,
                'attendance_percentage' => $attendance_percentage,
                'badge' => $badge
            ];
        }

        usort($metrics, fn($a, $b) => $b->performance_score <=> $a->performance_score);
        return array_slice($metrics, 0, 10);
    }

    public function getTeamPerformance()
    {
        $teams = DB::table('teams')->get();

        $teamMembers = DB::table('employees')
            ->select('team_id', DB::raw('COUNT(*) as members'))
            ->whereNotNull('team_id')
            ->groupBy('team_id')
            ->get()
            ->keyBy('team_id');

        $teamTasks = DB::table('tasks')
            ->join('employees', 'tasks.employee_id', '=', 'employees.id')
            ->select('employees.team_id', DB::raw('COUNT(*) as total_tasks'), DB::raw('SUM(CASE WHEN tasks.status = "completed" THEN 1 ELSE 0 END) as completed_tasks'))
            ->whereNotNull('employees.team_id')
            ->groupBy('employees.team_id')
            ->get()
            ->keyBy('team_id');

        $teamAttendances = DB::table('attendences')
            ->join('employees', 'attendences.employee_id', '=', 'employees.id')
            ->select('employees.team_id', DB::raw('COUNT(*) as total_attendances'), DB::raw('SUM(CASE WHEN attendences.present = 1 THEN 1 ELSE 0 END) as present_days'))
            ->whereNotNull('employees.team_id')
            ->groupBy('employees.team_id')
            ->get()
            ->keyBy('team_id');

        $teamCommits = DB::table('commit_logs')
            ->join('employees', 'commit_logs.employee_id', '=', 'employees.id')
            ->select('employees.team_id', DB::raw('COUNT(*) as total_commits'))
            ->whereNotNull('employees.team_id')
            ->groupBy('employees.team_id')
            ->get()
            ->keyBy('team_id');

        $teamMeetings = DB::table('employee_meeting')
            ->join('employees', 'employee_meeting.employee_id', '=', 'employees.id')
            ->select('employees.team_id', DB::raw('COUNT(*) as total_meetings'))
            ->whereNotNull('employees.team_id')
            ->groupBy('employees.team_id')
            ->get()
            ->keyBy('team_id');

        $result = [];
        foreach ($teams as $team) {
            $membersInfo = $teamMembers->get($team->id);
            $tasksInfo = $teamTasks->get($team->id);
            $attendanceInfo = $teamAttendances->get($team->id);
            $commitsInfo = $teamCommits->get($team->id);
            $meetingsInfo = $teamMeetings->get($team->id);

            $members = $membersInfo ? (int)$membersInfo->members : 0;
            $total_tasks = $tasksInfo ? (int)$tasksInfo->total_tasks : 0;
            $completed_tasks = $tasksInfo ? (int)$tasksInfo->completed_tasks : 0;
            $total_attendances = $attendanceInfo ? (int)$attendanceInfo->total_attendances : 0;
            $present_days = $attendanceInfo ? (int)$attendanceInfo->present_days : 0;
            $total_commits = $commitsInfo ? (int)$commitsInfo->total_commits : 0;
            $total_meetings = $meetingsInfo ? (int)$meetingsInfo->total_meetings : 0;

            $taskScore = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0;
            $attendanceScore = $total_attendances > 0 ? ($present_days / $total_attendances) * 100 : 100;
            $commitScore = min(100, $total_commits * 2);
            $meetingScore = min(100, $total_meetings * 5);

            $productivity_score = ($taskScore * 0.4) + ($attendanceScore * 0.2) + ($commitScore * 0.2) + ($meetingScore * 0.2);

            $result[] = [
                'team_name' => $team->name,
                'productivity_score' => round($productivity_score, 1),
                'members' => $members,
                'completed_tasks' => $completed_tasks,
                'total_commits' => $total_commits,
                'attendance_percentage' => round($attendanceScore, 1)
            ];
        }

        usort($result, fn($a, $b) => $b['productivity_score'] <=> $a['productivity_score']);
        return $result;
    }

    public function getEmployeesRequiringAttention()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

        $employees = DB::table('employees')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->select('employees.id', 'employees.name', 'teams.name as team_name')
            ->get();

        $pendingTasksStats = DB::table('tasks')
            ->where('status', '=', 'pending')
            ->select('employee_id', DB::raw('COUNT(*) as pending_tasks'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $attendanceStats = DB::table('attendences')
            ->select('employee_id', DB::raw('COUNT(*) as total_attendances'), DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_days'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $recentCommitsStats = DB::table('commit_logs')
            ->where('commit_date', '>=', $sevenDaysAgo)
            ->select('employee_id', DB::raw('COUNT(*) as recent_commits'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $meetingStats = DB::table('employee_meeting')
            ->select('employee_id', DB::raw('COUNT(*) as meetings_attended'))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $attentionList = [];
        foreach ($employees as $employee) {
            $pending = $pendingTasksStats->get($employee->id);
            $attendance = $attendanceStats->get($employee->id);
            $commits = $recentCommitsStats->get($employee->id);
            $meetings = $meetingStats->get($employee->id);

            $pending_tasks = $pending ? (int)$pending->pending_tasks : 0;
            $total_attendances = $attendance ? (int)$attendance->total_attendances : 0;
            $present_days = $attendance ? (int)$attendance->present_days : 0;
            $recent_commits = $commits ? (int)$commits->recent_commits : 0;
            $meetings_attended = $meetings ? (int)$meetings->meetings_attended : 0;

            $attendancePercentage = $total_attendances > 0 ? ($present_days / $total_attendances) * 100 : 100;

            $reasons = [];
            $riskScore = 0;

            if ($attendancePercentage < 70) {
                $reasons[] = 'Low attendance (' . round($attendancePercentage) . '%)';
                $riskScore += 3;
            }
            if ($pending_tasks > 5) {
                $reasons[] = 'High pending tasks (' . $pending_tasks . ')';
                $riskScore += 2;
            }
            if ($recent_commits == 0) {
                $reasons[] = 'No commits in last 7 days';
                $riskScore += 1;
            }
            if ($meetings_attended == 0) {
                $reasons[] = 'Low meeting participation';
                $riskScore += 1;
            }

            if (count($reasons) > 0) {
                $riskLevel = 'Low';
                if ($riskScore >= 4) $riskLevel = 'High';
                elseif ($riskScore >= 2) $riskLevel = 'Medium';

                $attentionList[] = [
                    'name' => $employee->name,
                    'team' => $employee->team_name ?? 'N/A',
                    'risk_level' => $riskLevel,
                    'reason' => implode(', ', $reasons),
                    'risk_score' => $riskScore
                ];
            }
        }

        usort($attentionList, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);
        return array_slice($attentionList, 0, 15);
    }

    public function getTeamsRequiringAttention($teamPerformance)
    {
        $attentionList = [];
        foreach ($teamPerformance as $team) {
            $reasons = [];
            $riskScore = 0;

            if ($team['productivity_score'] < 60) {
                $reasons[] = 'Low productivity (' . $team['productivity_score'] . '%)';
                $riskScore += 3;
            }
            if ($team['attendance_percentage'] < 75) {
                $reasons[] = 'Low attendance (' . $team['attendance_percentage'] . '%)';
                $riskScore += 2;
            }
            if ($team['total_commits'] < 10) {
                $reasons[] = 'Low commit activity';
                $riskScore += 1;
            }

            if (count($reasons) > 0) {
                $riskLevel = 'Low';
                if ($riskScore >= 4) $riskLevel = 'High';
                elseif ($riskScore >= 2) $riskLevel = 'Medium';
                
                $action = 'Schedule 1-on-1 with team lead';
                if ($riskScore >= 4) $action = 'Immediate performance review needed';

                $attentionList[] = [
                    'team_name' => $team['team_name'],
                    'issue' => implode(', ', $reasons),
                    'risk_level' => $riskLevel,
                    'recommended_action' => $action,
                    'risk_score' => $riskScore
                ];
            }
        }
        
        usort($attentionList, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);
        return array_slice($attentionList, 0, 5);
    }

    public function getProjectRisks()
    {
        $projects = DB::table('projects')->get();
        $risks = [];
        $today = Carbon::today();

        foreach ($projects as $project) {
            $riskScore = 0;
            $reasons = [];
            
            if ($project->status === 'Delayed') {
                $riskScore += 3;
                $reasons[] = 'Project is delayed';
            }
            
            $endDate = Carbon::parse($project->end_date);
            if ($project->status !== 'Completed' && $endDate->isPast()) {
                $riskScore += 3;
                $reasons[] = 'Past expected end date';
            } elseif ($project->status !== 'Completed' && $endDate->diffInDays($today, false) >= -7 && $endDate->diffInDays($today, false) <= 0) {
                $riskScore += 1;
                $reasons[] = 'Approaching deadline rapidly';
            }

            if (count($reasons) > 0) {
                $riskLevel = 'Low';
                if ($riskScore >= 3) $riskLevel = 'High';
                elseif ($riskScore >= 2) $riskLevel = 'Medium';

                $risks[] = [
                    'project_name' => $project->name,
                    'assigned_team' => 'N/A',
                    'risk_level' => $riskLevel,
                    'risk_reason' => implode(', ', $reasons),
                    'risk_score' => $riskScore
                ];
            }
        }

        usort($risks, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);
        return array_slice($risks, 0, 10);
    }

    public function calculateOrganizationHealth($teamPerformance, $employeesAttention)
    {
        $totalTeams = count($teamPerformance) > 0 ? count($teamPerformance) : 1;
        $avgTeamProd = array_reduce($teamPerformance, fn($carry, $item) => $carry + $item['productivity_score'], 0) / $totalTeams;
        
        $penalty = count($employeesAttention) * 0.5;
        
        $health = $avgTeamProd - $penalty;
        return min(100, max(0, round($health, 1)));
    }

    public function generateAIRecommendations(ManagerAgentService $agent, $summary, $teamPerformance, $projectRisks)
    {
        $prompt = '
You are an Executive AI Manager for Leadership Insights.
Based on the following organizational summary, provide 4-5 strategic recommendations.
Summary: ' . json_encode($summary) . '
Teams: ' . json_encode(array_slice($teamPerformance, 0, 3)) . '
Project Risks: ' . json_encode($projectRisks) . '
 
Instructions:
Provide your response STRICTLY as a JSON array of objects.
Each object must have "type" (choose from: Promotion, Resource Allocation, Restructuring, Risk Mitigation, Productivity), "title", and "description".
Do not return anything else, no markdown, no explanations, no wrapping blocks.
        ';

        $response = $agent->askNvidia($prompt);
        
        if (is_array($response)) {
            if (isset($response[0]['title']) && isset($response[0]['description'])) {
                return $response;
            }
        }

        return [
            [
                'type' => 'Risk Mitigation',
                'title' => 'Review High Risk Projects',
                'description' => 'Address the ' . $summary['high_risk_projects'] . ' high risk projects immediately to prevent further delays.'
            ]
        ];
    }
}
