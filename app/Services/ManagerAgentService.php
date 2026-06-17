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

class ManagerAgentService
{
    /**
     * Create a new class instance.
     */
    public function generateReport($startDate = null, $endDate = null)
    {
        set_time_limit(300);

        $tasksQuery = Task::with('employee');
        $attendencesQuery = Attendence::with('employee');
        $commitsQuery = CommitLog::with('employee');
        $meetingsQuery = Meeting::with('employees');
        $transcriptsQuery = MeetingTranscript::with(['meeting', 'employee']);

        if ($startDate && $endDate) {
            $tasksQuery->whereBetween('assigned_date', [$startDate, $endDate]);
            $attendencesQuery->whereBetween('date', [$startDate, $endDate]);
            $commitsQuery->whereBetween('commit_date', [$startDate, $endDate]);
            $meetingsQuery->whereBetween('meeting_date', [$startDate, $endDate]);
            $meetingIds = Meeting::whereBetween('meeting_date', [$startDate, $endDate])->pluck('id');
            $transcriptsQuery->whereIn('meeting_id', $meetingIds);
        }

        $tasks = $tasksQuery->get();
        $attendences = $attendencesQuery->get();
        $commits = $commitsQuery->get();
        $meetings = $meetingsQuery->get();
        $transcripts = $transcriptsQuery->get();

        // Build per-employee meeting participation summary
        $employeeMeetingCounts = [];
        foreach ($meetings as $meeting) {
            foreach ($meeting->employees as $emp) {
                $employeeMeetingCounts[$emp->name] = ($employeeMeetingCounts[$emp->name] ?? 0) + 1;
            }
        }

        // Build per-employee transcript contribution summary
        $employeeTranscriptSummary = [];
        foreach ($transcripts as $t) {
            $name = $t->speaker_name;
            if (!isset($employeeTranscriptSummary[$name])) {
                $employeeTranscriptSummary[$name] = ['entries' => 0, 'total_words' => 0];
            }
            $employeeTranscriptSummary[$name]['entries']++;
            $employeeTranscriptSummary[$name]['total_words'] += str_word_count($t->spoken_text);
        }

        $meetingParticipationJson = json_encode($employeeMeetingCounts);
        $transcriptContributionJson = json_encode($employeeTranscriptSummary);

        $prompt = "
You are an AI Manager Agent.

Analyze the employee tasks, attendence logs, commit logs, meeting notes, meeting participation and meeting transcript contributions provided below.

TASKS:
{$tasks->toJson()}

ATTENDENCE:
{$attendences->toJson()}

COMMITS:
{$commits->toJson()}

MEETINGS:
{$meetings->toJson()}

MEETING PARTICIPATION (employee name => number of meetings attended):
{$meetingParticipationJson}

MEETING TRANSCRIPT CONTRIBUTIONS (employee name => {entries, total_words}):
{$transcriptContributionJson}

Instructions:

1. Calculate Team Productivity Percentage based on overall task completion, attendence, development activity, meeting participation and speaking contributions.
2. Identify the Top Performers based on:
   - Completed tasks (weight: 40%)
   - Commit activity (weight: 20%)
   - Attendence consistency (weight: 15%)
   - Meeting attendance (weight: 15%)
   - Speaking contribution in meetings and useful participation in discussions (weight: 10%) — employees contributing more meaningful discussion should receive higher scores.
3. Identify employees that require Attention based on low productivity, poor attendence, few commits, pending tasks, delays, low meeting participation or minimal speaking contributions.
4. Identify Risks from overdue tasks, project delays, meeting notes and team performance.
5. Provide Supporting Employee Statistics summarizing the key metrics for each employee.
6. Provide specific Meeting Analysis based on the transcript contributions, attendance, speaking frequency, duration, and discussion involvement.
7. Provide Commit Analysis outlining code contribution trends.
8. Provide Attendence Analysis summarizing attendance patterns.
9. Provide Task Analysis evaluating task completion rates and bottlenecks.

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
  \"task_analysis\": \"Summary of task completions.\"
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
            $response = Http::timeout(120)
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
