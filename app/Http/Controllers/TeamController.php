<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Team::query()
                ->leftJoin(DB::raw('(
                    SELECT team_id, COUNT(*) as employees_count
                    FROM employees
                    GROUP BY team_id
                ) as emp_stats'), 'emp_stats.team_id', '=', 'teams.id')
                ->leftJoin(DB::raw('(
                    SELECT e.team_id, COUNT(*) as tasks_count
                    FROM employees e
                    JOIN tasks t ON e.id = t.employee_id
                    GROUP BY e.team_id
                ) as task_stats'), 'task_stats.team_id', '=', 'teams.id')
                ->leftJoin(DB::raw('(
                    SELECT e.team_id, COUNT(*) as commits_count
                    FROM employees e
                    JOIN commit_logs c ON e.id = c.employee_id
                    GROUP BY e.team_id
                ) as commit_stats'), 'commit_stats.team_id', '=', 'teams.id')
                ->leftJoin(DB::raw('(
                    SELECT e.team_id, COUNT(DISTINCT em.meeting_id) as meetings_count
                    FROM employees e
                    JOIN employee_meeting em ON e.id = em.employee_id
                    GROUP BY e.team_id
                ) as meeting_stats'), 'meeting_stats.team_id', '=', 'teams.id')
                ->select([
                    'teams.id',
                    'teams.name',
                    'teams.description',
                    DB::raw('COALESCE(emp_stats.employees_count, 0) as employees_count'),
                    DB::raw('COALESCE(task_stats.tasks_count, 0) as tasks_count'),
                    DB::raw('COALESCE(commit_stats.commits_count, 0) as commits_count'),
                    DB::raw('COALESCE(meeting_stats.meetings_count, 0) as meetings_count'),
                ]);

            return DataTables::of($query)
                ->addColumn('actions', function ($team) {
                    return '
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="' . url('/teams/' . $team->id) . '" class="btn btn-sm btn-outline-info">View details</a>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-light edit-team-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editTeamModal"
                                data-id="' . $team->id . '"
                                data-name="' . e($team->name) . '"
                                data-description="' . e($team->description ?? '') . '"
                            >Update</button>
                            <form method="POST" action="' . url('/teams/' . $team->id) . '" class="d-inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="team ' . e($team->name) . '">Delete</button>
                            </form>
                        </div>
                    ';
                })
                ->editColumn('name', function ($team) {
                    return '<div class="fw-bold"><a href="' . url('/teams/' . $team->id) . '" class="text-decoration-none" style="color: var(--blue);">' . e($team->name) . '</a></div>';
                })
                ->editColumn('description', function ($team) {
                    return $team->description ? '<span class="muted-text">' . e($team->description) . '</span>' : '<span class="muted-text">—</span>';
                })
                ->rawColumns(['actions', 'name', 'description'])
                ->make(true);
        }

        return view('Teams');
    }

    public function show(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        if ($request->ajax()) {
            $query = Employee::where('team_id', $team->id)
                ->withCount(['tasks', 'commits'])
                ->select('employees.*');

            return DataTables::of($query)
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
                ->addColumn('actions', function ($employee) {
                    return '<a href="' . url('/employees') . '" class="btn btn-sm btn-outline-light">Manage Employees</a>';
                })
                ->rawColumns(['name', 'actions'])
                ->make(true);
        }

        // Fetch team aggregates
        $totalEmployees = Employee::where('team_id', $team->id)->count();

        $taskStats = DB::table('tasks')
            ->join('employees', 'employees.id', '=', 'tasks.employee_id')
            ->where('employees.team_id', $team->id)
            ->selectRaw('
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = "pending" OR status = "in_progress" THEN 1 ELSE 0 END) as pending_tasks
            ')
            ->first();

        $totalTasks = $taskStats->total_tasks ?? 0;
        $completedTasks = $taskStats->completed_tasks ?? 0;
        $pendingTasks = $taskStats->pending_tasks ?? 0;

        $attendanceCount = DB::table('attendences')
            ->join('employees', 'employees.id', '=', 'attendences.employee_id')
            ->where('employees.team_id', $team->id)
            ->count();

        $commitCount = DB::table('commit_logs')
            ->join('employees', 'employees.id', '=', 'commit_logs.employee_id')
            ->where('employees.team_id', $team->id)
            ->count();

        $meetingsAttended = DB::table('employee_meeting')
            ->join('employees', 'employees.id', '=', 'employee_meeting.employee_id')
            ->where('employees.team_id', $team->id)
            ->distinct('meeting_id')
            ->count('meeting_id');

        return view('TeamDetail', [
            'team' => $team,
            'stats' => [
                'total_employees' => $totalEmployees,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'attendance_count' => $attendanceCount,
                'commit_count' => $commitCount,
                'meetings_attended' => $meetingsAttended,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
        ]);

        DB::transaction(function () use ($data) {
            $team = Team::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            if (!empty($data['employee_ids'])) {
                Employee::whereIn('id', $data['employee_ids'])->update(['team_id' => $team->id]);
            }
        });

        return redirect('/teams')->with('success', 'Team created successfully.');
    }

    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
        ]);

        DB::transaction(function () use ($team, $data) {
            $team->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            // Clear previous members
            Employee::where('team_id', $team->id)->update(['team_id' => null]);

            // Assign new members
            if (!empty($data['employee_ids'])) {
                Employee::whereIn('id', $data['employee_ids'])->update(['team_id' => $team->id]);
            }
        });

        return redirect('/teams')->with('success', 'Team updated successfully.');
    }

    public function destroy($id)
    {
        $team = Team::findOrFail($id);

        DB::transaction(function () use ($team) {
            Employee::where('team_id', $team->id)->update(['team_id' => null]);
            $team->delete();
        });

        return redirect('/teams')->with('success', 'Team deleted successfully.');
    }

    public function members($id)
    {
        $employees = Employee::where('team_id', $id)->get(['id', 'name', 'email']);
        return response()->json($employees);
    }

    public function searchEmployees(Request $request)
    {
        $q = $request->input('q');
        if (empty($q)) {
            return response()->json([]);
        }

        $employees = Employee::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->take(20)
            ->get(['id', 'name', 'email']);

        return response()->json($employees);
    }
}
