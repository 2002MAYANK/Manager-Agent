<?php

namespace App\Http\Controllers;

use App\Models\Attendence;
use App\Models\CommitLog;
use App\Models\Employee;
use App\Models\Meeting;
use App\Models\MeetingRecording;
use App\Models\MeetingTranscript;
use App\Models\Report;
use App\Models\Task;
use Carbon\Carbon;
use App\Services\ManagerAgentService;
use App\Services\TranscriptionService;
use Illuminate\Http\Request;

class ManagerAgentController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $rangeStart = $today->copy()->subDays(6);
        $previousRangeStart = $rangeStart->copy()->subDays(7);
        $previousRangeEnd = $rangeStart->copy()->subDay();
        $latestReport = Report::latest()->first();

        // Single query for total and status counts
        $taskStats = \DB::table('tasks')
            ->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending
            ")
            ->first();

        $totalTasks = $taskStats->total;
        $completedTasks = $taskStats->completed;
        $inProgressTasks = $taskStats->in_progress;
        $pendingTasks = $taskStats->pending;

        // Consolidate weekly task counts
        $weekStats = \DB::table('tasks')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN assigned_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as current_week_tasks,
                COALESCE(SUM(CASE WHEN assigned_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as previous_week_tasks,
                COALESCE(SUM(CASE WHEN status = 'completed' AND completed_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as current_week_completed,
                COALESCE(SUM(CASE WHEN status = 'completed' AND completed_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as previous_week_completed,
                COALESCE(SUM(CASE WHEN status = 'pending' AND assigned_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as current_week_pending,
                COALESCE(SUM(CASE WHEN status = 'pending' AND assigned_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as previous_week_pending
            ", [
                $rangeStart->toDateString(),
                $today->toDateString(),
                $previousRangeStart->toDateString(),
                $previousRangeEnd->toDateString(),
                $rangeStart->toDateString(),
                $today->toDateString(),
                $previousRangeStart->toDateString(),
                $previousRangeEnd->toDateString(),
                $rangeStart->toDateString(),
                $today->toDateString(),
                $previousRangeStart->toDateString(),
                $previousRangeEnd->toDateString(),
            ])
            ->first();

        $currentWeekTasks = $weekStats->current_week_tasks;
        $previousWeekTasks = $weekStats->previous_week_tasks;
        $currentWeekCompleted = $weekStats->current_week_completed;
        $previousWeekCompleted = $weekStats->previous_week_completed;
        $currentWeekPending = $weekStats->current_week_pending;
        $previousWeekPending = $weekStats->previous_week_pending;

        // Consolidate meetings count
        $meetingStats = \DB::table('meetings')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN meeting_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as current_meetings,
                COALESCE(SUM(CASE WHEN meeting_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as previous_meetings
            ", [
                $rangeStart->copy()->startOfDay(),
                $today->copy()->endOfDay(),
                $previousRangeStart->copy()->startOfDay(),
                $previousRangeEnd->copy()->endOfDay()
            ])
            ->first();

        $meetings = $meetingStats->current_meetings;
        $previousWeekMeetings = $meetingStats->previous_meetings;

        $teamProductivity = $latestReport?->team_productivity
            ?? $this->percentage($completedTasks, $totalTasks);

        $chart = $this->buildProductivityChart($rangeStart, $today);
        $statusDistribution = $this->buildStatusDistribution($totalTasks, $completedTasks, $inProgressTasks, $pendingTasks);

        // --- NEW DATA ADDITIONS ---
        $totalEmployeesCount = \App\Models\Employee::count();
        $totalTeamsCount = \App\Models\Team::count();
        $totalProjectsCount = \App\Models\Project::count();
        $totalCommitsCount = \App\Models\CommitLog::count();
        $orgHealthScore = $teamProductivity; // use team productivity as base

        // $topPerformer = ($latestReport && !empty($latestReport->top_performers)) ? json_decode($latestReport->top_performers)[0] ?? null : null;
        $topPerformersData = is_array($latestReport?->top_performers)
            ? $latestReport->top_performers
            : json_decode($latestReport?->top_performers ?? '[]', true);

        $topPerformer = $topPerformersData[0] ?? null;
        $topPerformerName = $topPerformer->name ?? 'N/A';
        $bestPerformingTeamObj = \App\Models\Team::withCount('employees')->orderBy('employees_count', 'desc')->first();
        $bestPerformingTeamName = $bestPerformingTeamObj->name ?? 'N/A';

        // $employeesAttentionCount = ($latestReport && !empty($latestReport->attention_required)) ? count(json_decode($latestReport->attention_required)) : 0;
        $attentionData = is_array($latestReport?->attention_required)
            ? $latestReport->attention_required
            : json_decode($latestReport?->attention_required ?? '[]', true);

        $employeesAttentionCount = count($attentionData);

        $highRiskProjectsCount = \App\Models\Project::where('priority', 'Critical')->count();

        $totalReposSynced = \App\Models\CommitLog::whereNotNull('project_id')->distinct('project_id')->count('project_id');

        $topContributorQuery = \App\Models\CommitLog::select('employee_id', \DB::raw('count(*) as total'))
            ->groupBy('employee_id')
            ->orderBy('total', 'desc')
            ->first();
        $topContributorName = $topContributorQuery ? ($topContributorQuery->employee->name ?? 'N/A') : 'N/A';

        $lastSyncTimeObj = \App\Models\CommitLog::max('created_at');
        $lastSyncTime = $lastSyncTimeObj ? \Carbon\Carbon::parse($lastSyncTimeObj)->diffForHumans() : 'Never';

        $projectStatsObj = \DB::table('projects')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'Planning' THEN 1 ELSE 0 END), 0) as planning,
                COALESCE(SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END), 0) as on_hold
            ")->first();

        $aiRecommendations = [
            'productivity' => 'Consider reallocating tasks from ' . $topPerformerName . ' to balance workload.',
            'team' => $bestPerformingTeamName . ' is showing strong velocity. Consider cross-training initiatives.',
            'project' => $highRiskProjectsCount > 0 ? 'Review ' . $highRiskProjectsCount . ' critical projects immediately.' : 'No projects are currently at high risk.'
        ];
        // --- END NEW DATA ADDITIONS ---

        return view('dashboard', [
            'managerName' => 'Mayank',
            'totalEmployeesCount' => $totalEmployeesCount,
            'totalTeamsCount' => $totalTeamsCount,
            'totalProjectsCount' => $totalProjectsCount,
            'totalCommitsCount' => $totalCommitsCount,
            'orgHealthScore' => $orgHealthScore,
            'topPerformerName' => $topPerformerName,
            'bestPerformingTeamName' => $bestPerformingTeamName,
            'employeesAttentionCount' => $employeesAttentionCount,
            'highRiskProjectsCount' => $highRiskProjectsCount,
            'totalReposSynced' => $totalReposSynced,
            'topContributorName' => $topContributorName,
            'lastSyncTime' => $lastSyncTime,
            'projectStatsObj' => $projectStatsObj,
            'aiRecommendations' => $aiRecommendations,
            'dateRangeLabel' => $rangeStart->format('M d') . ' - ' . $today->format('M d, Y'),
            'stats' => [
                'total_tasks' => [
                    'value' => $totalTasks,
                    'change' => $this->changePercent($currentWeekTasks, $previousWeekTasks),
                ],
                'completed_tasks' => [
                    'value' => $completedTasks,
                    'change' => $this->changePercent($currentWeekCompleted, $previousWeekCompleted),
                ],
                'team_productivity' => [
                    'value' => $teamProductivity,
                    'change' => $this->changePercent($teamProductivity, $this->percentage($previousWeekCompleted, $previousWeekTasks)),
                ],
                'pending_tasks' => [
                    'value' => $pendingTasks,
                    'change' => $this->changePercent($currentWeekPending, $previousWeekPending),
                ],
                'meetings' => [
                    'value' => $meetings,
                    'change' => $meetings - $previousWeekMeetings,
                ],
            ],
            'chart' => $chart,
            'statusDistribution' => $statusDistribution,
            'topPerformers' => ($latestReport && !empty($latestReport->top_performers)) ? $this->normalizePeople($latestReport->top_performers, []) : $this->topPerformers(),
            'attentionRequired' => ($latestReport && !empty($latestReport->attention_required)) ? $this->normalizePeople($latestReport->attention_required, []) : $this->attentionRequired(),
            'risks' => ($latestReport && !empty($latestReport->risks)) ? $this->normalizeRisks($latestReport->risks, []) : $this->recentRisks(),
            'latestReport' => $latestReport,
        ]);
    }

    public function previewReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Missing dates'], 400);
        }

        $employees = Employee::count();
        $tasks = Task::whereBetween('assigned_date', [$startDate, $endDate])->count();
        $attendences = Attendence::whereBetween('date', [$startDate, $endDate])->count();
        $commits = CommitLog::whereBetween('commit_date', [$startDate, $endDate])->count();
        $meetings = Meeting::whereBetween('meeting_date', [$startDate, $endDate])->count();

        return response()->json([
            'employees' => $employees,
            'tasks' => $tasks,
            'attendences' => $attendences,
            'commits' => $commits,
            'meetings' => $meetings,
        ]);
    }

    public function generate(
        Request $request,
        ManagerAgentService $agent
    ) {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $agent->generateReport($startDate, $endDate);

        $reportModel = Report::latest()->first();
        if ($reportModel) {
            return redirect('/reports/' . $reportModel->id);
        }

        return redirect('/reports');
    }

    public function reports(Request $request)
    {
        $query = Report::select('reports.*');

        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchValue = trim($request->input('search.value'));
                        $query->where(function ($q) use ($searchValue) {
                            $idVal = ltrim(str_replace('#', '', $searchValue), '0');
                            if (is_numeric($idVal)) {
                                $q->where('reports.id', $idVal);
                            }
                            $q->orWhere('reports.generated_at', 'like', "%{$searchValue}%")
                                ->orWhere('reports.start_date', 'like', "%{$searchValue}%")
                                ->orWhere('reports.end_date', 'like', "%{$searchValue}%");
                        });
                    }
                })
                ->editColumn('id', function ($report) {
                    return '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT);
                })
                ->editColumn('generated_at', function ($report) {
                    return \Carbon\Carbon::parse($report->generated_at)->format('M d, Y h:i A');
                })
                ->editColumn('start_date', function ($report) {
                    return $report->start_date ? \Carbon\Carbon::parse($report->start_date)->format('M d, Y') : '-';
                })
                ->editColumn('end_date', function ($report) {
                    return $report->end_date ? \Carbon\Carbon::parse($report->end_date)->format('M d, Y') : '-';
                })
                ->editColumn('team_productivity', function ($report) {
                    return '<span class="status-pill green">' . $report->team_productivity . '%</span>';
                })
                ->addColumn('actions', function ($report) {
                    return '<a href="' . url('/reports/' . $report->id) . '" class="btn btn-sm btn-outline-light"
                                style="border-color: var(--panel-border);">View Report</a>';
                })
                ->rawColumns(['team_productivity', 'actions'])
                ->make(true);
        }

        return view('Reports');
    }

    public function showReport($id)
    {
        $report = Report::findOrFail($id);
        $reportData = is_array($report->full_report) ? $report->full_report : json_decode($report->full_report, true);

        return view('ReportDetail', [
            'reportModel' => $report,
            'report' => is_array($reportData) ? $reportData : [],
        ]);
    }

    public function employees(Request $request)
    {
        $query = Employee::with('team')->select('employees.*');

        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('employees.name', 'like', "%{$searchValue}%")
                                ->orWhere('employees.email', 'like', "%{$searchValue}%")
                                ->orWhere('employees.department', 'like', "%{$searchValue}%")
                                ->orWhere('employees.designation', 'like', "%{$searchValue}%");
                        });
                    }
                })
                ->addColumn('actions', function ($employee) {
                    return '
                        <div class="d-flex gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-light edit-employee-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editEmployeeModal"
                                data-id="' . $employee->id . '"
                                data-name="' . e($employee->name) . '"
                                data-email="' . e($employee->email) . '"
                                data-department="' . e($employee->department) . '"
                                data-designation="' . e($employee->designation) . '"
                                data-team-id="' . ($employee->team_id ?? '') . '"
                            >Update</button>
                            <form method="POST" action="' . url('/employees/' . $employee->id) . '" class="d-inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="employee ' . e($employee->name) . '">Delete</button>
                            </form>
                        </div>
                    ';
                })
                ->editColumn('name', function ($employee) {
                    return '
                        <div class="d-flex align-items-center gap-3">
                            <img class="avatar" style="width:40px;height:40px;border-radius:50%;" src="https://ui-avatars.com/api/?name=' . urlencode($employee->name) . '&background=random&color=fff" alt="' . e($employee->name) . '">
                            <div class="min-w-0">
                                <div class="fw-bold text-truncate">' . e($employee->name) . '</div>
                                <div class="small text-muted text-truncate">' . e($employee->email) . '</div>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('team', function ($employee) {
                    return $employee->team ? '<span class="status-pill purple">' . e($employee->team->name) . '</span>' : '<span class="muted-text">—</span>';
                })
                ->editColumn('created_at', function ($employee) {
                    return $employee->created_at->format('M d, Y');
                })
                ->rawColumns(['actions', 'name', 'team'])
                ->make(true);
        }

        $teams = \App\Models\Team::orderBy('name')->get();

        return view('Employees', compact('teams'));
    }

    public function storeEmployee(Request $request)
    {
        Employee::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email'],
            'department' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ]));

        return redirect('/employees')->with('success', 'Employee added successfully.');
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email,' . $id],
            'department' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ]);

        $employee->update($data);

        return redirect('/employees')->with('success', 'Employee updated successfully.');
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);

        // Cascade delete related records
        $employee->tasks()->delete();
        $employee->attendances()->delete();
        $employee->commits()->delete();

        $employee->delete();

        return redirect('/employees')->with('success', 'Employee deleted successfully.');
    }

    public function exportData()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Write CSV Headers
            fputcsv($handle, [
                'Employee Name',
                'Employee Email',
                'Department',
                'Designation',
                'Task Details',
                'Attendence Details',
                'Commit Details',
                'Meeting Details'
            ]);

            // Chunk the employees query with all relationships to keep memory utilization low
            Employee::with(['tasks', 'attendances', 'commits', 'meetings.transcripts', 'meetings.employees'])
                ->chunk(100, function ($employees) use ($handle) {
                    foreach ($employees as $e) {
                        // Task Details Block
                        $taskDetails = $e->tasks->map(function ($t) {
                            return "Task: {$t->title} | Status: {$t->status} | Assigned: {$t->assigned_date} | Due: {$t->due_date} | Completed: " . ($t->completed_date ?? 'N/A');
                        })->implode("\n");

                        // Attendence Details Block
                        $attendenceDetails = $e->attendances->map(function ($a) {
                            $status = $a->present ? 'Present' : 'Absent';
                            return "Date: {$a->date} | In: {$a->check_in} | Out: " . ($a->check_out ?? 'N/A') . " | Status: {$status}";
                        })->implode("\n");

                        // Commit Details Block
                        $commitDetails = $e->commits->map(function ($c) {
                            return "Hash: {$c->commit_hash} | Msg: {$c->commit_message} | +{$c->lines_added} -{$c->lines_deleted} | Date: {$c->commit_date}";
                        })->implode("\n");

                        // Meeting Details Block
                        $meetingDetails = $e->meetings->map(function ($m) use ($e) {
                            $participants = $m->employees->pluck('name')->implode(', ');
                            // Calculate speaking contribution for this employee in this meeting
                            $myTranscripts = $m->transcripts->where('employee_id', $e->id);
                            $myEntries = $myTranscripts->count();
                            $myWords = 0;
                            foreach ($myTranscripts as $tr) {
                                $myWords += str_word_count($tr->spoken_text);
                            }
                            return "Title: {$m->title} | Date: {$m->meeting_date} | Participants: {$participants} | Speaking: {$myEntries} entries ({$myWords} words)";
                        })->implode("\n\n");

                        fputcsv($handle, [
                            $e->name,
                            $e->email,
                            $e->department ?? 'N/A',
                            $e->designation ?? 'N/A',
                            $taskDetails,
                            $attendenceDetails,
                            $commitDetails,
                            $meetingDetails
                        ]);
                    }
                    // Free memory between chunks
                    unset($employees);
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                });

            fclose($handle);
        }, 'employee_complete_export.csv', [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => '0',
        ]);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $csvData = file_get_contents($request->file('import_file')->getPathname());
        $rows = $this->parseCsv($csvData);

        if (empty($rows)) {
            return redirect('/')->with('error', 'CSV file is empty or invalid.');
        }

        $imported = 0;
        $created = 0;

        foreach ($rows as $row) {
            $name = $row['Employee Name'] ?? $row['employee_name'] ?? null;
            if (!$name) continue;

            $employee = Employee::where('name', $name)->first();

            if (!$employee) {
                $employee = Employee::create(['name' => $name, 'email' => strtolower(str_replace(' ', '.', $name)) . '@company.com']);
                $created++;
            }

            $imported++;
        }

        $message = "Imported {$imported} employee records.";
        if ($created > 0) {
            $message .= " ({$created} new employees created.)";
        }

        return redirect('/')->with('success', $message);
    }

    private function buildCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }

    private function parseCsv(string $csvData): array
    {
        $lines = array_filter(explode("\n", trim($csvData)));
        if (count($lines) < 2) return [];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $csvData);
        rewind($handle);

        $headers = fgetcsv($handle);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($handle);
        return $rows;
    }

    public function tasks(Request $request)
    {
        $query = Task::with('employee')->select('tasks.*');

        if ($request->ajax()) {
            if ($request->filled('status')) {
                $query->where('tasks.status', $request->status);
            }
            if ($request->filled('date')) {
                $query->whereDate('tasks.assigned_date', $request->date);
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('tasks.title', 'like', "%{$searchValue}%")
                                ->orWhere('tasks.status', 'like', "%{$searchValue}%")
                                ->orWhereHas('employee', function ($subQuery) use ($searchValue) {
                                    $subQuery->where('name', 'like', "%{$searchValue}%");
                                });
                        });
                    }
                })
                ->addColumn('employee_name', function ($task) {
                    return $task->employee ? $task->employee->name : 'Deleted employee';
                })
                ->filterColumn('employee_name', function ($query, $keyword) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('employee_name', function ($query, $order) {
                    $query->orderBy(Employee::select('name')->whereColumn('employees.id', 'tasks.employee_id'), $order);
                })
                ->addColumn('actions', function ($task) {
                    return '
                        <div class="d-flex gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-light edit-task-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editTaskModal"
                                data-id="' . $task->id . '"
                                data-employee-id="' . $task->employee_id . '"
                                data-title="' . e($task->title) . '"
                                data-description="' . e($task->description ?? '') . '"
                                data-status="' . $task->status . '"
                                data-assigned-date="' . $task->assigned_date . '"
                                data-due-date="' . $task->due_date . '"
                                data-completed-date="' . $task->completed_date . '"
                            >Update</button>
                            <form method="POST" action="' . url('/tasks/' . $task->id) . '" class="d-inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="task ' . e($task->title) . '">Delete</button>
                            </form>
                        </div>
                    ';
                })
                ->editColumn('status', function ($task) {
                    $statusClass = ['completed' => 'green', 'in_progress' => 'blue', 'pending' => 'orange'][$task->status] ?? '';
                    return '<span class="status-pill ' . $statusClass . '">' . str_replace('_', ' ', ucfirst($task->status)) . '</span>';
                })
                ->editColumn('title', function ($task) {
                    return '<div class="fw-bold">' . e($task->title) . '</div>
                            <div class="small muted-text text-truncate" style="max-width: 260px;">' . e($task->description ?? 'No description') . '</div>';
                })
                ->editColumn('assigned_date', function ($task) {
                    return \Carbon\Carbon::parse($task->assigned_date)->format('M d, Y');
                })
                ->editColumn('due_date', function ($task) {
                    return \Carbon\Carbon::parse($task->due_date)->format('M d, Y');
                })
                ->editColumn('completed_date', function ($task) {
                    return $task->completed_date ? \Carbon\Carbon::parse($task->completed_date)->format('M d, Y') : 'Not done';
                })
                ->rawColumns(['actions', 'status', 'title'])
                ->make(true);
        }

        $employeesList = Employee::orderBy('name')->take(100)->get();

        return view('Tasks', [
            'employees' => $employeesList,
        ]);
    }

    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:assigned_date'],
            'completed_date' => ['nullable', 'date', 'after_or_equal:assigned_date'],
        ], [
            'employee_id.exists' => 'Employee does not exist.',
        ]);

        Task::create($data);

        return redirect('/tasks')->with('success', 'Task added successfully.');
    }

    public function updateTask(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:assigned_date'],
            'completed_date' => ['nullable', 'date', 'after_or_equal:assigned_date'],
        ], [
            'employee_id.exists' => 'Employee does not exist.',
        ]);

        $task->update($data);

        return redirect('/tasks')->with('success', 'Task updated successfully.');
    }

    public function deleteTask($id)
    {
        Task::findOrFail($id)->delete();

        return redirect('/tasks')->with('success', 'Task deleted successfully.');
    }

    public function updateAttendence(Request $request, $id)
    {
        $attendence = Attendence::findOrFail($id);

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['required', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'present' => ['required', 'boolean'],
        ], [
            'employee_id.exists' => 'Employee does not exist.',
        ]);

        $attendence->update($data);

        return redirect('/attendence')->with('success', 'Attendence updated successfully.');
    }

    public function deleteAttendence($id)
    {
        Attendence::findOrFail($id)->delete();

        return redirect('/attendence')->with('success', 'Attendence deleted successfully.');
    }

    public function updateCommit(Request $request, $id)
    {
        $commit = CommitLog::findOrFail($id);

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'commit_hash' => ['required', 'string', 'max:255'],
            'commit_message' => ['required', 'string', 'max:255'],
            'lines_added' => ['required', 'integer', 'min:0'],
            'lines_deleted' => ['required', 'integer', 'min:0'],
            'commit_date' => ['required', 'date'],
        ], [
            'employee_id.exists' => 'Employee does not exist.',
        ]);

        $commit->update($data);

        return redirect('/commits')->with('success', 'Commit updated successfully.');
    }

    public function deleteCommit($id)
    {
        CommitLog::findOrFail($id)->delete();

        return redirect('/commits')->with('success', 'Commit deleted successfully.');
    }

    public function updateMeeting(Request $request, $id)
    {
        $meeting = Meeting::findOrFail($id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string'],
            'meeting_date' => ['required', 'date'],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['exists:employees,id'],
        ], [
            'participant_ids.required' => 'Please select at least one participant.',
            'participant_ids.*.exists' => 'Employee does not exist.',
        ]);

        $meeting->update(collect($data)->only(['title', 'notes', 'meeting_date'])->toArray());
        $meeting->employees()->sync($data['participant_ids']);

        return redirect('/meetings')->with('success', 'Meeting updated successfully.');
    }

    public function deleteMeeting($id)
    {
        Meeting::findOrFail($id)->delete();

        return redirect('/meetings')->with('success', 'Meeting deleted successfully.');
    }

    public function attendence(Request $request)
    {
        $query = Attendence::with('employee')->select('attendences.*');

        if ($request->ajax()) {
            if ($request->filled('date')) {
                $query->whereDate('attendences.date', $request->date);
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->whereHas('employee', function ($subQuery) use ($searchValue) {
                                $subQuery->where('name', 'like', "%{$searchValue}%");
                            })
                                ->orWhere('attendences.date', 'like', "%{$searchValue}%");

                            $lowerVal = strtolower($searchValue);
                            if (strpos('present', $lowerVal) !== false) {
                                $q->orWhere('attendences.present', 1);
                            }
                            if (strpos('absent', $lowerVal) !== false) {
                                $q->orWhere('attendences.present', 0);
                            }
                        });
                    }
                })
                ->addColumn('employee_name', function ($attendence) {
                    return $attendence->employee ? $attendence->employee->name : 'Deleted employee';
                })
                ->filterColumn('employee_name', function ($query, $keyword) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('employee_name', function ($query, $order) {
                    $query->orderBy(Employee::select('name')->whereColumn('employees.id', 'attendences.employee_id'), $order);
                })
                ->addColumn('actions', function ($attendence) {
                    return '
                        <div class="d-flex gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-light edit-attendence-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editAttendenceModal"
                                data-id="' . $attendence->id . '"
                                data-employee-id="' . $attendence->employee_id . '"
                                data-date="' . $attendence->date . '"
                                data-check-in="' . ($attendence->check_in ? \Carbon\Carbon::parse($attendence->check_in)->format('H:i') : '') . '"
                                data-check-out="' . ($attendence->check_out ? \Carbon\Carbon::parse($attendence->check_out)->format('H:i') : '') . '"
                                data-present="' . $attendence->present . '"
                            >Update</button>
                            <form method="POST" action="' . url('/attendence/' . $attendence->id) . '" class="d-inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="attendence record">Delete</button>
                            </form>
                        </div>
                    ';
                })
                ->editColumn('present', function ($attendence) {
                    $statusClass = $attendence->present ? 'green' : 'red';
                    $statusText = $attendence->present ? 'Present' : 'Absent';
                    return '<span class="status-pill ' . $statusClass . '">' . $statusText . '</span>';
                })
                ->editColumn('date', function ($attendence) {
                    return \Carbon\Carbon::parse($attendence->date)->format('M d, Y');
                })
                ->editColumn('check_in', function ($attendence) {
                    return $attendence->check_in ? \Carbon\Carbon::parse($attendence->check_in)->format('h:i A') : '--';
                })
                ->editColumn('check_out', function ($attendence) {
                    return $attendence->check_out ? \Carbon\Carbon::parse($attendence->check_out)->format('h:i A') : '--';
                })
                ->rawColumns(['actions', 'present'])
                ->make(true);
        }

        $employeesList = Employee::orderBy('name')->take(100)->get();

        return view('Attendence', [
            'employees' => $employeesList,
        ]);
    }

    public function storeAttendence(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['required', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'present' => ['required', 'boolean'],
        ], [
            'employee_id.exists' => 'Employee does not exist.',
        ]);

        Attendence::create($data);

        return redirect('/attendence')->with('success', 'Attendence added successfully.');
    }

    public function commits(Request $request)
    {
        $query = CommitLog::with(['employee', 'insight'])->select('commit_logs.*');

        if ($request->ajax()) {
            if ($request->filled('date')) {
                $query->whereDate('commit_logs.commit_date', $request->date);
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('commit_logs.commit_message', 'like', "%{$searchValue}%")
                                ->orWhere('commit_logs.repository_name', 'like', "%{$searchValue}%")
                                ->orWhere('commit_logs.commit_hash', 'like', "%{$searchValue}%")
                                ->orWhereHas('employee', function ($subQuery) use ($searchValue) {
                                    $subQuery->where('name', 'like', "%{$searchValue}%");
                                });
                        });
                    }
                })
                ->addColumn('employee_name', function ($commit) {
                    return $commit->employee ? $commit->employee->name : 'Deleted employee';
                })
                ->filterColumn('employee_name', function ($query, $keyword) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('employee_name', function ($query, $order) {
                    $query->orderBy(Employee::select('name')->whereColumn('employees.id', 'commit_logs.employee_id'), $order);
                })
                ->addColumn('actions', function ($commit) {
                    return '
                        <div class="d-flex gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-light edit-commit-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editCommitModal"
                                data-id="' . $commit->id . '"
                                data-employee-id="' . $commit->employee_id . '"
                                data-hash="' . e($commit->commit_hash) . '"
                                data-message="' . e($commit->commit_message) . '"
                                data-added="' . $commit->lines_added . '"
                                data-deleted="' . $commit->lines_deleted . '"
                                data-date="' . (\Carbon\Carbon::parse($commit->commit_date)->format('Y-m-d\TH:i')) . '"
                            >Update</button>
                            <form method="POST" action="' . url('/commits/' . $commit->id) . '" class="d-inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="commit ' . e(substr($commit->commit_hash, 0, 7)) . '">Delete</button>
                            </form>
                        </div>
                    ';
                })
                ->editColumn('commit_hash', function ($commit) {
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-git text-orange"></i>
                            <span class="font-monospace fw-bold text-orange">' . substr($commit->commit_hash, 0, 7) . '</span>
                        </div>
                    ';
                })
                ->editColumn('commit_message', function ($commit) {
                    return '<div class="text-truncate" style="max-width: 250px;">' . e($commit->commit_message) . '</div>';
                })
                ->addColumn('lines', function ($commit) {
                    return '
                        <div class="d-flex gap-2 font-monospace small">
                            <span class="text-success">+' . $commit->lines_added . '</span>
                            <span class="text-danger">-' . $commit->lines_deleted . '</span>
                        </div>
                    ';
                })
                ->editColumn('commit_date', function ($commit) {
                    return \Carbon\Carbon::parse($commit->commit_date)->format('M d, Y h:i A');
                })
                ->addColumn('repository_name', function ($commit) {
                    return $commit->repository_name ? e($commit->repository_name) : '<span class="muted-text">—</span>';
                })
                ->addColumn('insight', function ($commit) {
                    return '
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-light view-insight-btn"
                            data-id="' . $commit->id . '"
                            style="border-color: var(--purple); color: var(--purple-2); white-space: nowrap;"
                        ><i class="bi bi-cpu me-1"></i>View Insight</button>
                    ';
                })
                ->rawColumns(['actions', 'commit_hash', 'commit_message', 'lines', 'repository_name', 'insight'])
                ->make(true);
        }

        $employeesList = Employee::orderBy('name')->take(100)->get();

        return view('Commits', [
            'employees' => $employeesList,
        ]);
    }

    public function generateCommitInsight(Request $request, $id)
    {
        $commit = CommitLog::with('employee')->findOrFail($id);

        $insight = \App\Models\CommitInsight::where('commit_log_id', $id)->first();
        if ($insight) {
            return response()->json([
                'success' => true,
                'insight' => $insight,
                'commit' => clone $commit,
            ]);
        }

        // Generate insights lazily via LLM
        $prompt = "
Analyze this Git commit.

Commit Message:
{$commit->commit_message}

Author:
" . ($commit->employee ? $commit->employee->name : 'Unknown') . "

Date:
{$commit->commit_date}

Return ONLY JSON:

{
  \"feature_category\": \"\",
  \"business_impact\": \"\",
  \"technical_complexity\": \"\",
  \"risk_level\": \"\",
  \"summary\": \"\"
}
";

        try {
            $agentService = resolve(\App\Services\ManagerAgentService::class);
            $response = $agentService->askNvidia($prompt);

            $result = is_array($response) ? $response : json_decode($response, true);

            if (!$result || !isset($result['feature_category'])) {
                throw new \Exception("Invalid AI response.");
            }
        } catch (\Exception $e) {
            Log::error("[CommitInsight] AI failed: " . $e->getMessage());
            // Fallback content in case of AI provider failure
            $result = [
                'feature_category' => 'General Maintenance',
                'business_impact' => 'Regular repository and code updates.',
                'technical_complexity' => 'Medium',
                'risk_level' => 'Low',
                'summary' => 'This commit addresses code quality, regular maintenance, or standard code integration.'
            ];
        }

        $insight = \App\Models\CommitInsight::create([
            'commit_log_id' => $id,
            'feature_category' => $result['feature_category'] ?? 'General Maintenance',
            'business_impact' => $result['business_impact'] ?? 'N/A',
            'technical_complexity' => $result['technical_complexity'] ?? 'Medium',
            'risk_level' => $result['risk_level'] ?? 'Low',
            'summary' => $result['summary'] ?? 'No summary available.',
        ]);

        return response()->json([
            'success' => true,
            'insight' => $insight,
            'commit' => clone $commit,
        ]);
    }

    public function storeCommit(Request $request)
    {
        CommitLog::create($request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'commit_hash' => ['required', 'string', 'max:255'],
            'commit_message' => ['required', 'string', 'max:255'],
            'lines_added' => ['required', 'integer', 'min:0'],
            'lines_deleted' => ['required', 'integer', 'min:0'],
            'commit_date' => ['required', 'date'],
        ], [
            'employee_id.exists' => 'Employee does not exist.',
        ]));

        return redirect('/commits')->with('success', 'Commit added successfully.');
    }

    public function meetings(Request $request)
    {
        $query = Meeting::with(['employees', 'recordings'])->select('meetings.*');

        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('meetings.title', 'like', "%{$searchValue}%")
                                ->orWhere('meetings.notes', 'like', "%{$searchValue}%")
                                ->orWhere('meetings.meeting_date', 'like', "%{$searchValue}%")
                                ->orWhereHas('employees', function ($subQuery) use ($searchValue) {
                                    $subQuery->where('name', 'like', "%{$searchValue}%");
                                });
                        });
                    }
                })
                ->addColumn('actions', function ($meeting) {
                    return '
                        <div class="d-flex gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-light edit-meeting-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editMeetingModal"
                                data-id="' . $meeting->id . '"
                                data-title="' . e($meeting->title) . '"
                                data-notes="' . e($meeting->notes) . '"
                                data-date="' . (\Carbon\Carbon::parse($meeting->meeting_date)->format('Y-m-d\TH:i')) . '"
                                data-participants=\'' . json_encode($meeting->employees->pluck('id')) . '\'
                            >Update</button>
                            <form method="POST" action="' . url('/meetings/' . $meeting->id) . '" class="d-inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="meeting ' . e($meeting->title) . '">Delete</button>
                            </form>
                        </div>
                    ';
                })
                ->editColumn('title', function ($meeting) {
                    return '<div class="fw-bold"><a href="' . url('/meetings/' . $meeting->id) . '" class="text-decoration-none" style="color: var(--blue);">' . e($meeting->title) . '</a></div>';
                })
                ->addColumn('participants', function ($meeting) {
                    return $meeting->employees->isNotEmpty() ? e($meeting->employees->pluck('name')->implode(', ')) : '<span class="muted-text">—</span>';
                })
                ->editColumn('notes', function ($meeting) {
                    return '<div style="min-width: 280px;">' . e(\Illuminate\Support\Str::limit($meeting->notes, 100)) . '</div>';
                })
                ->addColumn('audio', function ($meeting) {
                    if ($meeting->recordings->isNotEmpty()) {
                        return '<a href="' . url('/meetings/' . $meeting->id) . '" class="btn btn-sm btn-outline-light" style="border-color: var(--panel-border); white-space: nowrap;"><i class="bi bi-play-circle me-1"></i>View Recording</a>';
                    }
                    return '<span class="muted-text">—</span>';
                })
                ->editColumn('meeting_date', function ($meeting) {
                    return \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y h:i A');
                })
                ->rawColumns(['actions', 'title', 'participants', 'notes', 'audio'])
                ->make(true);
        }

        $employeesList = Employee::orderBy('name')->take(100)->get();

        return view('Meetings', [
            'employees' => $employeesList,
        ]);
    }

    public function storeMeeting(Request $request, TranscriptionService $transcriptionService)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string'],
            'meeting_date' => ['required', 'date'],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['exists:employees,id'],
            'meeting_audio.*' => ['nullable', 'file', 'mimes:mp3,wav,m4a,mp4,webm', 'max:51200'],
            'transcript_speakers' => ['nullable', 'array'],
            'transcript_speakers.*' => ['nullable', 'string', 'max:255'],
            'transcript_texts' => ['nullable', 'array'],
            'transcript_texts.*' => ['nullable', 'string'],
            'transcript_timestamps' => ['nullable', 'array'],
            'transcript_timestamps.*' => ['nullable', 'string', 'max:50'],
        ], [
            'participant_ids.required' => 'Please select at least one participant.',
            'participant_ids.*.exists' => 'Employee does not exist.',
        ]);

        $meetingData = collect($data)->only(['title', 'notes', 'meeting_date'])->toArray();
        $meetingData['total_participants'] = count($data['participant_ids']);

        $meeting = Meeting::create($meetingData);

        // Sync participants
        $meeting->employees()->sync($data['participant_ids']);

        $hasUploadedRecordings = false;

        // Handle recordings (multiple files support)
        if ($request->hasFile('meeting_audio')) {
            $files = $request->file('meeting_audio');
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                $path = $file->store('meeting_audio', 'public');
                $ext = strtolower($file->getClientOriginalExtension());
                $type = in_array($ext, ['mp4', 'webm']) ? 'video' : 'audio';

                MeetingRecording::create([
                    'meeting_id' => $meeting->id,
                    'file_path' => $path,
                    'file_type' => $type,
                ]);

                $hasUploadedRecordings = true;

                // Simulate Transcription
                $transcriptionService->processRecording($meeting, $path);
            }
        }

        // Store manual transcripts
        $speakers = $data['transcript_speakers'] ?? [];
        $texts = $data['transcript_texts'] ?? [];
        $timestamps = $data['transcript_timestamps'] ?? [];

        $sequence = $meeting->transcripts()->max('sequence') ?? 0;

        foreach ($speakers as $index => $speaker) {
            if (!empty($speaker) && !empty($texts[$index] ?? '')) {
                $employee = Employee::where('name', $speaker)->first();
                $sequence++;
                MeetingTranscript::create([
                    'meeting_id' => $meeting->id,
                    'employee_id' => $employee?->id,
                    'speaker_name' => $speaker,
                    'spoken_text' => $texts[$index],
                    'timestamp' => $timestamps[$index] ?? null,
                    'sequence' => $sequence,
                ]);
            }
        }

        // Calculate analytics after transcripts are added
        $transcripts = $meeting->transcripts()->get();
        $meeting->total_transcript_entries = $transcripts->count();

        if ($transcripts->isNotEmpty()) {
            $speakerCounts = $transcripts->groupBy('speaker_name')->map->count();
            // Ignore "Unknown Speaker" when determining most/least active
            $validSpeakers = $speakerCounts->except(['Unknown Speaker', '']);
            if ($validSpeakers->isNotEmpty()) {
                $meeting->most_active_speaker = $validSpeakers->sortDesc()->keys()->first();
                $meeting->least_active_speaker = $validSpeakers->sort()->keys()->first();
            }

            // Estimate duration based on timestamp if present
            $lastTimestamp = $transcripts->whereNotNull('timestamp')->sortByDesc('sequence')->first()?->timestamp;
            if ($lastTimestamp) {
                $meeting->meeting_duration = $lastTimestamp;
            }
        }

        $meeting->save();

        return redirect('/meetings')->with('success', 'Meeting added successfully.');
    }

    public function showMeeting($id)
    {
        $meeting = Meeting::with(['employees', 'transcripts.employee', 'recordings'])->findOrFail($id);

        return view('MeetingDetail', [
            'meeting' => $meeting,
        ]);
    }

    public function exportReport($id)
    {
        $report = Report::findOrFail($id);
        $reportData = is_array($report->full_report) ? $report->full_report : json_decode($report->full_report, true);

        $rows = [];

        // Header info
        $rows[] = ['AI Performance Report #' . str_pad($report->id, 4, '0', STR_PAD_LEFT)];
        $rows[] = ['Generated', Carbon::parse($report->generated_at)->format('M d, Y h:i A')];
        if ($report->start_date && $report->end_date) {
            $rows[] = ['Period', Carbon::parse($report->start_date)->format('M d, Y') . ' - ' . Carbon::parse($report->end_date)->format('M d, Y')];
        }
        $rows[] = ['Team Productivity', ($report->team_productivity ?? 0) . '%'];
        $rows[] = [];

        // Top Performers
        $rows[] = ['--- Top Performers ---'];
        foreach (($reportData['top_performers'] ?? []) as $p) {
            $name = is_string($p) ? $p : ($p['name'] ?? $p['employee_name'] ?? 'Employee');
            $reason = is_array($p) ? ($p['reason'] ?? '') : '';
            $rows[] = [$name, $reason];
        }
        $rows[] = [];

        // Attention Required
        $rows[] = ['--- Attention Required ---'];
        foreach (($reportData['attention_required'] ?? []) as $p) {
            $name = is_string($p) ? $p : ($p['name'] ?? $p['employee_name'] ?? $p['reason'] ?? 'Employee');
            $reason = is_array($p) ? ($p['reason'] ?? '') : '';
            $rows[] = [$name, $reason];
        }
        $rows[] = [];

        // Top Performing Team
        $rows[] = ['--- Top Performing Team ---'];
        if (!empty($reportData['top_performing_team'])) {
            $t = $reportData['top_performing_team'];
            $rows[] = ['Team Name', $t['team_name'] ?? ''];
            $rows[] = ['Team Score', ($t['score'] ?? '') . '%'];
            $rows[] = ['Team Performance Summary', $t['reason'] ?? ''];
        } else {
            $rows[] = ['No top performing team returned.'];
        }
        $rows[] = [];

        // Risks
        $rows[] = ['--- Risks ---'];
        foreach (($reportData['risks'] ?? []) as $r) {
            $title = is_string($r) ? $r : ($r['title'] ?? $r['risk'] ?? $r['description'] ?? 'Risk');
            $impact = is_array($r) ? ($r['impact'] ?? $r['severity'] ?? '') : '';
            $rows[] = [$title, $impact];
        }
        $rows[] = [];

        // Team Analysis
        $rows[] = ['--- Team Analysis ---'];
        foreach (($reportData['team_analysis'] ?? []) as $team) {
            $teamName = $team['team_name'] ?? 'Team';
            $rows[] = ["Team: {$teamName}"];

            // Team Top Performer
            if (!empty($team['top_performer'])) {
                $tp = $team['top_performer'];
                $tpName = $tp['employee_name'] ?? 'N/A';
                $tpScore = isset($tp['score']) ? $tp['score'] . '%' : 'N/A';
                $tpReason = $tp['reason'] ?? 'N/A';
                $rows[] = ['  Top Performer', $tpName, "Score: {$tpScore}", "Reason: {$tpReason}"];
            } else {
                $rows[] = ['  Top Performer', 'N/A'];
            }

            // Team Attention Required
            if (!empty($team['attention_required'])) {
                $rows[] = ['  Attention Required:'];
                foreach ($team['attention_required'] as $ar) {
                    $arName = $ar['employee_name'] ?? 'N/A';
                    $arReason = $ar['reason'] ?? 'N/A';
                    $rows[] = ['    Employee Name', $arName, "Reason: {$arReason}"];
                }
            } else {
                $rows[] = ['  Attention Required', 'None'];
            }

            // Team Risks
            if (!empty($team['risks'])) {
                $rows[] = ['  Team Risks:'];
                foreach ($team['risks'] as $risk) {
                    $rows[] = ['    Risk', $risk];
                }
            } else {
                $rows[] = ['  Team Risks', 'None'];
            }
            $rows[] = [];
        }

        // Supporting Employee Statistics
        $rows[] = ['--- Supporting Employee Statistics ---'];
        foreach (($reportData['supporting_employee_statistics'] ?? []) as $s) {
            $name = is_array($s) ? ($s['name'] ?? '') : '';
            $summary = is_array($s) ? ($s['summary'] ?? '') : '';
            $rows[] = [$name, $summary];
        }
        $rows[] = [];

        // Analysis Sections
        $sections = [
            'Meeting Analysis' => 'meeting_analysis',
            'Commit Analysis' => 'commit_analysis',
            'Attendence Analysis' => 'attendence_analysis',
            'Task Analysis' => 'task_analysis'
        ];

        foreach ($sections as $title => $key) {
            if (!empty($reportData[$key])) {
                $rows[] = ["--- {$title} ---"];
                $rows[] = [$reportData[$key]];
                $rows[] = [];
            }
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = 'report_' . str_pad($report->id, 4, '0', STR_PAD_LEFT) . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildProductivityChart(Carbon $start, Carbon $end): array
    {
        $points = [];
        $labels = [];
        $linePoints = [];
        $areaPoints = [];
        $days = $start->diffInDays($end) + 1;

        // Base counts prior to the range start
        $runningAssigned = Task::where('assigned_date', '<', $start->toDateString())->count();
        $runningCompleted = Task::where('status', 'completed')
            ->where('completed_date', '<', $start->toDateString())
            ->count();

        // Get count of assignments within the date range grouped by date
        $assignedCounts = \DB::table('tasks')
            ->select('assigned_date', \DB::raw('COUNT(*) as count'))
            ->whereBetween('assigned_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('assigned_date')
            ->pluck('count', 'assigned_date');

        // Get count of completions within the date range grouped by date
        $completedCounts = \DB::table('tasks')
            ->select('completed_date', \DB::raw('COUNT(*) as count'))
            ->where('status', 'completed')
            ->whereBetween('completed_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('completed_date')
            ->pluck('count', 'completed_date');

        for ($index = 0; $index < $days; $index++) {
            $date = $start->copy()->addDays($index);
            $dateStr = $date->toDateString();

            $runningAssigned += $assignedCounts->get($dateStr, 0);
            $runningCompleted += $completedCounts->get($dateStr, 0);

            $value = $this->percentage($runningCompleted, $runningAssigned);
            $x = $days > 1 ? round($index * (700 / ($days - 1)), 2) : 0;
            $y = round(190 - ($value * 1.9), 2);

            $points[] = $value;
            $labels[] = $date->format('M d');
            $linePoints[] = "{$x},{$y}";
            $areaPoints[] = "{$x},{$y}";
        }

        return [
            'labels' => $labels,
            'values' => $points,
            'line_points' => implode(' ', $linePoints),
            'area_points' => implode(' ', array_merge($areaPoints, ['700,190', '0,190'])),
            'latest' => count($points) ? end($points) : 0,
        ];
    }

    private function buildStatusDistribution(int $total, int $completed, int $inProgress, int $pending): array
    {
        $completedPercent = $this->percentage($completed, $total);
        $inProgressPercent = $this->percentage($inProgress, $total);
        $pendingPercent = $this->percentage($pending, $total);
        $progressEnd = min(100, $completedPercent + $inProgressPercent);

        return [
            'total' => $total,
            'completed' => ['count' => $completed, 'percent' => $completedPercent],
            'in_progress' => ['count' => $inProgress, 'percent' => $inProgressPercent],
            'pending' => ['count' => $pending, 'percent' => $pendingPercent],
            'donut_style' => $total > 0
                ? "conic-gradient(var(--green) 0 {$completedPercent}%, var(--blue) {$completedPercent}% {$progressEnd}%, var(--orange) {$progressEnd}% 100%)"
                : 'conic-gradient(rgba(148, 163, 184, .2) 0 100%)',
        ];
    }

    private function topPerformers(): array
    {
        $since = Carbon::today()->subDays(30)->toDateString();
        $results = \DB::table('employees as e')
            ->select('e.id', 'e.name')
            ->leftJoin(\DB::raw('(
                SELECT employee_id, COUNT(*) as tasks_count, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks_count
                FROM tasks
                WHERE assigned_date >= \'' . $since . '\'
                GROUP BY employee_id
            ) as t'), 't.employee_id', '=', 'e.id')
            ->leftJoin(\DB::raw('(
                SELECT employee_id, COUNT(*) as attendances_count, SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_attendances_count
                FROM attendences
                WHERE date >= \'' . $since . '\'
                GROUP BY employee_id
            ) as a'), 'a.employee_id', '=', 'e.id')
            ->leftJoin(\DB::raw('(
                SELECT employee_id, COUNT(*) as commits_count
                FROM commit_logs
                WHERE commit_date >= \'' . $since . ' 00:00:00\'
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
            ->take(3)
            ->get();

        return $results->map(function ($row) {
            return [
                'name' => $row->name,
                'score' => (int) $row->score,
                'avatar' => $this->avatarUrl($row->id),
            ];
        })->all();
    }

    private function attentionRequired(): array
    {
        $todayStr = Carbon::today()->toDateString();
        $since = Carbon::today()->subDays(30)->toDateString();
        $results = \DB::table('employees as e')
            ->select('e.id', 'e.name')
            ->leftJoin(\DB::raw('(
                SELECT employee_id, COUNT(*) as tasks_count,
                    SUM(CASE WHEN status IN ("pending", "in_progress") THEN 1 ELSE 0 END) as incomplete_tasks_count,
                    SUM(CASE WHEN status != "completed" AND due_date < \'' . $todayStr . '\' THEN 1 ELSE 0 END) as overdue_tasks_count
                FROM tasks
                WHERE assigned_date >= \'' . $since . '\'
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
            ->take(3)
            ->get();

        return $results->map(function ($row) {
            return [
                'name' => $row->name,
                'score' => (int) $row->score,
                'avatar' => $this->avatarUrl($row->id + 20),
            ];
        })->all();
    }

    private function recentRisks(): array
    {
        $overdueTasks = Task::with('employee')
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', Carbon::today()->toDateString())
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->map(fn(Task $task) => [
                'title' => $task->title . ' is overdue',
                'impact' => $task->due_date < Carbon::today()->subDays(3)->toDateString() ? 'High Impact' : 'Medium Impact',
                'time' => Carbon::parse($task->due_date)->diffForHumans(),
            ])
            ->all();

        if ($overdueTasks) {
            return $overdueTasks;
        }

        return Meeting::latest('meeting_date')
            ->take(3)
            ->get()
            ->map(fn(Meeting $meeting) => [
                'title' => $meeting->title,
                'impact' => 'Meeting Note',
                'time' => Carbon::parse($meeting->meeting_date)->diffForHumans(),
            ])
            ->all();
    }

    private function normalizePeople(?array $source, array $fallback): array
    {
        $people = $source ?: $fallback;

        return collect($people)
            ->take(3)
            ->values()
            ->map(function ($person, int $index) {
                if (is_string($person)) {
                    return [
                        'name' => $person,
                        'score' => 0,
                        'avatar' => $this->avatarUrl($index + 1),
                    ];
                }

                if (! is_array($person)) {
                    $person = [];
                }

                return [
                    'name' => $person['name'] ?? $person['employee_name'] ?? $person[0] ?? 'Employee',
                    'score' => (int) ($person['score'] ?? $person['productivity'] ?? $person['percentage'] ?? $person[1] ?? 0),
                    'avatar' => $person['avatar'] ?? $person[2] ?? $this->avatarUrl($index + 1),
                ];
            })
            ->all();
    }

    private function normalizeRisks(?array $source, array $fallback): array
    {
        $risks = $source ?: $fallback;

        return collect($risks)
            ->take(3)
            ->values()
            ->map(function ($risk) {
                if (is_string($risk)) {
                    return [
                        'title' => $risk,
                        'impact' => 'Medium Impact',
                        'time' => 'Now',
                    ];
                }

                if (! is_array($risk)) {
                    $risk = [];
                }

                return [
                    'title' => $risk['title'] ?? $risk['risk'] ?? $risk['description'] ?? 'Project risk',
                    'impact' => $risk['impact'] ?? $risk['severity'] ?? 'Medium Impact',
                    'time' => $risk['time'] ?? $risk['created_at'] ?? 'Now',
                ];
            })
            ->all();
    }

    private function percentage(int|float $value, int|float $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    private function changePercent(int|float $current, int|float $previous): int
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    private function avatarUrl(int $seed): string
    {
        return 'https://i.pravatar.cc/80?img=' . (($seed % 60) + 1);
    }
}
