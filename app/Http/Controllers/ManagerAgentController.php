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

        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $meetings = Meeting::whereBetween('meeting_date', [
            $rangeStart->copy()->startOfDay(),
            $today->copy()->endOfDay(),
        ])->count();

        $teamProductivity = $latestReport?->team_productivity
            ?? $this->percentage($completedTasks, $totalTasks);

        $currentWeekTasks = Task::whereBetween('assigned_date', [$rangeStart, $today])->count();
        $previousWeekTasks = Task::whereBetween('assigned_date', [$previousRangeStart, $previousRangeEnd])->count();
        $currentWeekCompleted = Task::where('status', 'completed')
            ->whereBetween('completed_date', [$rangeStart, $today])
            ->count();
        $previousWeekCompleted = Task::where('status', 'completed')
            ->whereBetween('completed_date', [$previousRangeStart, $previousRangeEnd])
            ->count();
        $currentWeekPending = Task::where('status', 'pending')
            ->whereBetween('assigned_date', [$rangeStart, $today])
            ->count();
        $previousWeekPending = Task::where('status', 'pending')
            ->whereBetween('assigned_date', [$previousRangeStart, $previousRangeEnd])
            ->count();
        $previousWeekMeetings = Meeting::whereBetween('meeting_date', [
            $previousRangeStart->copy()->startOfDay(),
            $previousRangeEnd->copy()->endOfDay(),
        ])->count();

        $chart = $this->buildProductivityChart($rangeStart, $today);
        $statusDistribution = $this->buildStatusDistribution($totalTasks, $completedTasks, $inProgressTasks, $pendingTasks);

        return view('dashboard', [
            'managerName' => 'Mayank',
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
            'topPerformers' => $this->normalizePeople($latestReport?->top_performers, $this->topPerformers()),
            'attentionRequired' => $this->normalizePeople($latestReport?->attention_required, $this->attentionRequired()),
            'risks' => $this->normalizeRisks($latestReport?->risks, $this->recentRisks()),
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

    public function reports()
    {
        $reports = Report::orderBy('generated_at', 'desc')->paginate(10);
        return view('Reports', compact('reports'));
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
        $query = Employee::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $allowedSorts = ['name', 'department', 'designation', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $employees = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('components.employees-table-body', compact('employees'))->render(),
                'pagination' => $employees->links()->render(),
                'total' => $employees->total(),
            ]);
        }

        return view('Employees', [
            'employees' => $employees,
        ]);
    }

    public function storeEmployee(Request $request)
    {
        Employee::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email'],
            'department' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
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
        $employees = Employee::with(['tasks', 'attendances', 'commits', 'meetings.transcripts'])->get();

        $rows = [];
        foreach ($employees as $e) {
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
                // Calculate speaking contribution for this employee in this meeting
                $myTranscripts = $m->transcripts->where('employee_id', $e->id);
                $myEntries = $myTranscripts->count();
                $myWords = 0;
                foreach ($myTranscripts as $tr) {
                    $myWords += str_word_count($tr->spoken_text);
                }
                return "Title: {$m->title} | Date: {$m->meeting_date} | Participants: {$participants} | Speaking: {$myEntries} entries ({$myWords} words)";
            })->implode("\n\n");

            $rows[] = [
                $e->name,
                $e->email,
                $e->department ?? 'N/A',
                $e->designation ?? 'N/A',
                $taskDetails,
                $attendenceDetails,
                $commitDetails,
                $meetingDetails
            ];
        }

        $csv = $this->buildCsv(
            ['Employee Name', 'Employee Email', 'Department', 'Designation', 'Task Details', 'Attendence Details', 'Commit Details', 'Meeting Details'],
            $rows
        );

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_complete_export.csv"',
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

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tasks.title', 'like', "%{$search}%")
                  ->orWhereHas('employee', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->where('tasks.status', $status);
        }

        $sortBy = $request->query('sort_by', 'assigned_date');
        $sortDir = $request->query('sort_dir', 'desc');

        if ($sortBy === 'employee_name') {
            $query->join('employees', 'tasks.employee_id', '=', 'employees.id')
                  ->orderBy('employees.name', $sortDir);
        } else {
            $allowedSorts = ['title', 'status', 'assigned_date', 'due_date', 'completed_date'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy('tasks.' . $sortBy, $sortDir);
            }
        }

        $tasks = $query->paginate(10)->withQueryString();
        $employeesList = Employee::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('components.tasks-table-body', compact('tasks'))->render(),
                'pagination' => $tasks->links()->render(),
                'total' => $tasks->total(),
            ]);
        }

        return view('Tasks', [
            'tasks' => $tasks,
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

        if ($search = $request->query('search')) {
            $query->whereHas('employee', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($date = $request->query('date')) {
            $query->whereDate('attendences.date', $date);
        }

        $sortBy = $request->query('sort_by', 'date');
        $sortDir = $request->query('sort_dir', 'desc');

        if ($sortBy === 'employee_name') {
            $query->join('employees', 'attendences.employee_id', '=', 'employees.id')
                  ->orderBy('employees.name', $sortDir);
        } else {
            $allowedSorts = ['date', 'check_in', 'check_out'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy('attendences.' . $sortBy, $sortDir);
            }
        }

        $attendences = $query->paginate(10)->withQueryString();
        $employeesList = Employee::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('components.attendence-table-body', compact('attendences'))->render(),
                'pagination' => $attendences->links()->render(),
                'total' => $attendences->total(),
            ]);
        }

        return view('Attendence', [
            'attendences' => $attendences,
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
        $query = CommitLog::with('employee')->select('commit_logs.*');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('commit_logs.commit_message', 'like', "%{$search}%")
                  ->orWhereHas('employee', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($date = $request->query('date')) {
            $query->whereDate('commit_logs.commit_date', $date);
        }

        $sortBy = $request->query('sort_by', 'commit_date');
        $sortDir = $request->query('sort_dir', 'desc');

        if ($sortBy === 'employee_name') {
            $query->join('employees', 'commit_logs.employee_id', '=', 'employees.id')
                  ->orderBy('employees.name', $sortDir);
        } else {
            $allowedSorts = ['commit_date', 'lines_added', 'lines_deleted'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy('commit_logs.' . $sortBy, $sortDir);
            }
        }

        $commits = $query->paginate(10)->withQueryString();
        $employeesList = Employee::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('components.commits-table-body', compact('commits'))->render(),
                'pagination' => $commits->links()->render(),
                'total' => $commits->total(),
            ]);
        }

        return view('Commits', [
            'commits' => $commits,
            'employees' => $employeesList,
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
        $query = Meeting::with(['employees', 'recordings']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($date = $request->query('date')) {
            $query->whereDate('meeting_date', $date);
        }

        $sortBy = $request->query('sort_by', 'meeting_date');
        $sortDir = $request->query('sort_dir', 'desc');

        $allowedSorts = ['title', 'meeting_date'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $meetings = $query->paginate(10)->withQueryString();
        $employeesList = Employee::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('components.meetings-table-body', compact('meetings'))->render(),
                'pagination' => $meetings->links()->render(),
                'total' => $meetings->total(),
            ]);
        }

        return view('Meetings', [
            'meetings' => $meetings,
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

        // Risks
        $rows[] = ['--- Risks ---'];
        foreach (($reportData['risks'] ?? []) as $r) {
            $title = is_string($r) ? $r : ($r['title'] ?? $r['risk'] ?? $r['description'] ?? 'Risk');
            $impact = is_array($r) ? ($r['impact'] ?? $r['severity'] ?? '') : '';
            $rows[] = [$title, $impact];
        }
        $rows[] = [];
        
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

        for ($index = 0; $index < $days; $index++) {
            $date = $start->copy()->addDays($index);
            $assigned = Task::whereDate('assigned_date', '<=', $date)->count();
            $completed = Task::where('status', 'completed')
                ->whereDate('completed_date', '<=', $date)
                ->count();
            $value = $this->percentage($completed, $assigned);
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
        return Employee::withCount([
            'tasks',
            'tasks as completed_tasks_count' => fn($query) => $query->where('status', 'completed'),
            'attendances',
            'attendances as present_attendances_count' => fn($query) => $query->where('present', true),
            'commits',
        ])
            ->get()
            ->map(function (Employee $employee, int $index) {
                $taskScore = $this->percentage($employee->completed_tasks_count, max(1, $employee->tasks_count));
                $attendanceScore = $this->percentage($employee->present_attendances_count, max(1, $employee->attendances_count));
                $commitScore = min(100, $employee->commits_count * 10);
                $score = round(($taskScore * .6) + ($attendanceScore * .25) + ($commitScore * .15));

                return [
                    'name' => $employee->name,
                    'score' => $score,
                    'avatar' => $this->avatarUrl($employee->id),
                ];
            })
            ->sortByDesc('score')
            ->take(3)
            ->values()
            ->all();
    }

    private function attentionRequired(): array
    {
        return Employee::withCount([
            'tasks',
            'tasks as incomplete_tasks_count' => fn($query) => $query->whereIn('status', ['pending', 'in_progress']),
            'tasks as overdue_tasks_count' => fn($query) => $query->where('status', '!=', 'completed')->whereDate('due_date', '<', Carbon::today()),
        ])
            ->get()
            ->map(function (Employee $employee) {
                $incompleteScore = $this->percentage($employee->incomplete_tasks_count, max(1, $employee->tasks_count));
                $score = min(100, $incompleteScore + ($employee->overdue_tasks_count * 10));

                return [
                    'name' => $employee->name,
                    'score' => $score,
                    'avatar' => $this->avatarUrl($employee->id + 20),
                ];
            })
            ->sortByDesc('score')
            ->take(3)
            ->values()
            ->all();
    }

    private function recentRisks(): array
    {
        $overdueTasks = Task::with('employee')
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '<', Carbon::today())
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
