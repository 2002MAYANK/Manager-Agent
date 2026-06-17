<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Task;
use App\Models\Attendence;
use App\Models\CommitLog;
use App\Models\Meeting;
use App\Models\MeetingTranscript;
use App\Services\ManagerAgentService;

class ApiController extends Controller
{
    public function getEmployees()
    {
        return response()->json(Employee::all());
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
        ]);

        Employee::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully'
        ]);
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $id,
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
        ]);

        $employee->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully'
        ]);
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);

        $employee->tasks()->delete();
        $employee->attendances()->delete();
        $employee->commits()->delete();
        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }

    public function exportData()
    {
        $employees = Employee::with(['tasks', 'attendances', 'commits', 'meetings.transcripts'])->get();

        return response()->json(
            $employees->map(function($e) {
                // Task Details Block
                $taskDetails = $e->tasks->map(function($t) {
                    return "Task: {$t->title} | Status: {$t->status} | Assigned: {$t->assigned_date} | Due: {$t->due_date} | Completed: " . ($t->completed_date ?? 'N/A');
                })->implode("\n");

                // Attendence Details Block
                $attendenceDetails = $e->attendances->map(function($a) {
                    $status = $a->present ? 'Present' : 'Absent';
                    return "Date: {$a->date} | In: {$a->check_in} | Out: " . ($a->check_out ?? 'N/A') . " | Status: {$status}";
                })->implode("\n");

                // Commit Details Block
                $commitDetails = $e->commits->map(function($c) {
                    return "Hash: {$c->commit_hash} | Msg: {$c->commit_message} | +{$c->lines_added} -{$c->lines_deleted} | Date: {$c->commit_date}";
                })->implode("\n");

                // Meeting Details Block
                $meetingDetails = $e->meetings->map(function($m) use ($e) {
                    $participants = $m->employees->pluck('name')->implode(', ');
                    $myTranscripts = $m->transcripts->where('employee_id', $e->id);
                    $myEntries = $myTranscripts->count();
                    $myWords = 0;
                    foreach ($myTranscripts as $tr) {
                        $myWords += str_word_count($tr->spoken_text);
                    }
                    return "Title: {$m->title} | Date: {$m->meeting_date} | Participants: {$participants} | Speaking: {$myEntries} entries ({$myWords} words)";
                })->implode("\n\n");

                return [
                    'Employee Name' => $e->name,
                    'Employee Email' => $e->email,
                    'Department' => $e->department ?? 'N/A',
                    'Designation' => $e->designation ?? 'N/A',
                    'Task Details' => $taskDetails,
                    'Attendence Details' => $attendenceDetails,
                    'Commit Details' => $commitDetails,
                    'Meeting Details' => $meetingDetails,
                ];
            })
        );
    }

    public function importData(Request $request)
    {
        $request->validate([
            'employees' => 'required|array',
            'employees.*.Employee Name' => 'required|string|max:255',
        ]);

        $imported = 0;
        $created = 0;

        foreach ($request->input('employees', []) as $row) {
            $name = $row['Employee Name'] ?? null;
            if (!$name) continue;

            $employee = Employee::where('name', $name)->first();
            if (!$employee) {
                Employee::create(['name' => $name, 'email' => strtolower(str_replace(' ', '.', $name)) . '@company.com']);
                $created++;
            }
            $imported++;
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} employee records. ({$created} new employees created.)",
        ]);
    }

    public function getTasks()
    {
        return response()->json(Task::with('employee')->get());
    }

    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'assigned_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:assigned_date',
            'completed_date' => 'nullable|date|after_or_equal:assigned_date',
        ]);

        Task::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully'
        ]);
    }

    public function getAttendences()
    {
        return response()->json(Attendence::with('employee')->get());
    }

    public function storeAttendence(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'present' => 'required|boolean',
        ]);

        Attendence::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Attendence created successfully'
        ]);
    }

    public function getCommits()
    {
        return response()->json(CommitLog::with('employee')->get());
    }

    public function storeCommit(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'commit_hash' => 'required|string|max:255',
            'commit_message' => 'required|string|max:255',
            'lines_added' => 'required|integer|min:0',
            'lines_deleted' => 'required|integer|min:0',
            'commit_date' => 'required|date',
        ]);

        CommitLog::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Commit created successfully'
        ]);
    }

    public function getMeetings()
    {
        return response()->json(Meeting::with('employees')->get());
    }

    public function storeMeeting(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'required|string',
            'meeting_date' => 'required|date',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:employees,id',
            'transcripts' => 'nullable|array',
            'transcripts.*.speaker_name' => 'required|string|max:255',
            'transcripts.*.spoken_text' => 'required|string',
        ], [
            'participant_ids.required' => 'Please select at least one participant.',
            'participant_ids.*.exists' => 'Employee does not exist.',
        ]);

        $meeting = Meeting::create(collect($data)->only(['title', 'notes', 'meeting_date'])->toArray());

        $meeting->employees()->sync($data['participant_ids']);

        foreach ($data['transcripts'] ?? [] as $index => $t) {
            $employee = Employee::where('name', $t['speaker_name'])->first();
            MeetingTranscript::create([
                'meeting_id' => $meeting->id,
                'employee_id' => $employee?->id,
                'speaker_name' => $t['speaker_name'],
                'spoken_text' => $t['spoken_text'],
                'sequence' => $index,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Meeting created successfully'
        ]);
    }

    public function generateReport(Request $request, ManagerAgentService $agent)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $agent->generateReport($startDate, $endDate);

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully'
        ]);
    }
    public function healthCheck()
    {
        return response()->json([
            'success' => true,
            'message' => 'API is healthy'
        ]);
    }
}
