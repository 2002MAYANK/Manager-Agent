<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Project::with('team')->select('projects.*');
            
            return DataTables::of($query)
                ->addColumn('team_name', function($row) {
                    return $row->team ? $row->team->name : 'N/A';
                })
                ->addColumn('progress', function($row) {
                    $totalTasks = $row->tasks()->count();
                    $completedTasks = $row->tasks()->where('status', 'completed')->count();
                    $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    return $percentage . '%';
                })
                ->addColumn('actions', function($row) {
                    return '
                        <div class="d-flex gap-2">
                            <a href="/projects/'.$row->id.'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editProjectModal" data-project=\''.json_encode($row).'\'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="/projects/'.$row->id.'" method="POST" class="d-inline">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="project">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $teams = Team::all();
        return view('Projects', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Planning,In Progress,Completed,On Hold',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        Project::create($validated);
        return back()->with('success', 'Project created successfully');
    }

    public function show($id)
    {
        $project = Project::with(['team', 'tasks.employee', 'commits.employee', 'meetings'])->findOrFail($id);
        
        $totalTasks = $project->tasks->count();
        $completedTasks = $project->tasks->where('status', 'completed')->count();
        $pendingTasks = $totalTasks - $completedTasks;
        
        $overdueTasks = $project->tasks->filter(function($task) {
            return $task->status !== 'completed' && $task->due_date && Carbon::parse($task->due_date)->isPast();
        })->count();

        $totalCommits = $project->commits->count();
        $totalMeetings = $project->meetings->count();
        
        $teamMembers = $project->team ? $project->team->employees->count() : 0;

        // Health Score Calculation
        $healthScore = 100;
        
        // Deduction for overdue tasks
        if ($totalTasks > 0) {
            $overduePenalty = ($overdueTasks / $totalTasks) * 30; // Max 30% penalty
            $healthScore -= min(30, $overduePenalty);
        }

        // Bonus for completion
        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) : 0;
        $healthScore = $healthScore * (0.5 + ($completionRate * 0.5)); // Scale score based on completion
        
        // Small boost for commit activity and meetings
        if ($totalCommits > 10) $healthScore += 5;
        if ($totalMeetings > 0) $healthScore += 5;

        // Ensure score is between 0 and 100
        $healthScore = max(0, min(100, round($healthScore)));

        $healthStatus = 'Healthy';
        if ($healthScore < 50) $healthStatus = 'At Risk';
        elseif ($healthScore < 80) $healthStatus = 'Needs Attention';

        return view('ProjectDetail', compact(
            'project', 'totalTasks', 'completedTasks', 'pendingTasks', 
            'overdueTasks', 'totalCommits', 'totalMeetings', 'teamMembers',
            'healthScore', 'healthStatus'
        ));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Planning,In Progress,Completed,On Hold',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $project->update($validated);
        return back()->with('success', 'Project updated successfully');
    }

    public function destroy($id)
    {
        Project::findOrFail($id)->delete();
        return back()->with('success', 'Project deleted successfully');
    }
}
