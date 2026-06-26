<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Task;
use App\Models\Attendence;
use App\Models\CommitLog;
use App\Models\Meeting;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    private array $headers = ['X-Requested-With' => 'XMLHttpRequest'];

    public function test_employees_search(): void
    {
        Employee::create(['name' => 'Alice Margatroid', 'email' => 'alice@marisa.com', 'department' => 'Magic', 'designation' => 'Doll Maker']);
        Employee::create(['name' => 'Bob Builder', 'email' => 'bob@builder.com', 'department' => 'Construction', 'designation' => 'Builder']);

        // Search by Name
        $response = $this->getJson('/employees?draw=1&start=0&length=10&search[value]=Alice', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Alice Margatroid', $response->getContent());
        $this->assertStringNotContainsString('Bob Builder', $response->getContent());

        // Search by Email
        $response = $this->getJson('/employees?draw=1&start=0&length=10&search[value]=bob@builder.com', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Bob Builder', $response->getContent());
        $this->assertStringNotContainsString('Alice Margatroid', $response->getContent());

        // Search by Department
        $response = $this->getJson('/employees?draw=1&start=0&length=10&search[value]=Magic', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Alice Margatroid', $response->getContent());
        $this->assertStringNotContainsString('Bob Builder', $response->getContent());

        // Search by Designation
        $response = $this->getJson('/employees?draw=1&start=0&length=10&search[value]=Builder', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Bob Builder', $response->getContent());
        $this->assertStringNotContainsString('Alice Margatroid', $response->getContent());
    }

    public function test_tasks_search_and_date_filter(): void
    {
        $employee1 = Employee::create(['name' => 'Alice Margatroid', 'email' => 'alice@marisa.com']);
        $employee2 = Employee::create(['name' => 'Bob Builder', 'email' => 'bob@builder.com']);

        $task1 = Task::create([
            'employee_id' => $employee1->id,
            'title' => 'Fix doll joints',
            'status' => 'in_progress',
            'assigned_date' => '2026-06-19',
            'due_date' => '2026-06-20'
        ]);

        $task2 = Task::create([
            'employee_id' => $employee2->id,
            'title' => 'Build a wall',
            'status' => 'pending',
            'assigned_date' => '2026-06-20',
            'due_date' => '2026-06-25'
        ]);

        // Date Filter
        $response = $this->getJson('/tasks?draw=1&start=0&length=10&date=2026-06-19', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Fix doll joints', $response->getContent());
        $this->assertStringNotContainsString('Build a wall', $response->getContent());

        // Search by Title
        $response = $this->getJson('/tasks?draw=1&start=0&length=10&search[value]=doll', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Fix doll joints', $response->getContent());
        $this->assertStringNotContainsString('Build a wall', $response->getContent());

        // Search by Status
        $response = $this->getJson('/tasks?draw=1&start=0&length=10&search[value]=pending', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Build a wall', $response->getContent());
        $this->assertStringNotContainsString('Fix doll joints', $response->getContent());

        // Search by Employee Name
        $response = $this->getJson('/tasks?draw=1&start=0&length=10&search[value]=Bob', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Build a wall', $response->getContent());
        $this->assertStringNotContainsString('Fix doll joints', $response->getContent());
    }

    public function test_attendance_search_and_date_filter(): void
    {
        $employee1 = Employee::create(['name' => 'Alice Margatroid', 'email' => 'alice@marisa.com']);
        $employee2 = Employee::create(['name' => 'Bob Builder', 'email' => 'bob@builder.com']);

        $att1 = Attendence::create([
            'employee_id' => $employee1->id,
            'date' => '2026-06-19',
            'check_in' => '09:00',
            'check_out' => '17:00',
            'present' => 1
        ]);

        $att2 = Attendence::create([
            'employee_id' => $employee2->id,
            'date' => '2026-06-20',
            'check_in' => '09:30',
            'check_out' => '17:30',
            'present' => 0
        ]);

        // Date Filter
        $response = $this->getJson('/attendence?draw=1&start=0&length=10&date=2026-06-19', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Alice Margatroid', $response->getContent());
        $this->assertStringNotContainsString('Bob Builder', $response->getContent());

        // Search by Employee Name
        $response = $this->getJson('/attendence?draw=1&start=0&length=10&search[value]=Bob', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Bob Builder', $response->getContent());
        $this->assertStringNotContainsString('Alice Margatroid', $response->getContent());

        // Search by Date (partial match)
        $response = $this->getJson('/attendence?draw=1&start=0&length=10&search[value]=2026-06-20', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Bob Builder', $response->getContent());
        $this->assertStringNotContainsString('Alice Margatroid', $response->getContent());

        // Search by Status "Present"
        $response = $this->getJson('/attendence?draw=1&start=0&length=10&search[value]=Present', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Alice Margatroid', $response->getContent());
        $this->assertStringNotContainsString('Bob Builder', $response->getContent());

        // Search by Status "Absent"
        $response = $this->getJson('/attendence?draw=1&start=0&length=10&search[value]=Absent', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Bob Builder', $response->getContent());
        $this->assertStringNotContainsString('Alice Margatroid', $response->getContent());
    }

    public function test_commits_search_and_date_filter(): void
    {
        $employee1 = Employee::create(['name' => 'Alice Margatroid', 'email' => 'alice@marisa.com']);
        $employee2 = Employee::create(['name' => 'Bob Builder', 'email' => 'bob@builder.com']);

        $c1 = CommitLog::create([
            'employee_id' => $employee1->id,
            'commit_hash' => 'abcdef123456',
            'commit_message' => 'feat: support doll speaking',
            'lines_added' => 100,
            'lines_deleted' => 10,
            'commit_date' => '2026-06-19 10:00:00',
            'repository_name' => 'doll-core'
        ]);

        $c2 = CommitLog::create([
            'employee_id' => $employee2->id,
            'commit_hash' => '987654zyxwvu',
            'commit_message' => 'fix: concrete mixer leak',
            'lines_added' => 5,
            'lines_deleted' => 20,
            'commit_date' => '2026-06-20 12:00:00',
            'repository_name' => 'mixer-driver'
        ]);

        // Date Filter
        $response = $this->getJson('/commits?draw=1&start=0&length=10&date=2026-06-19', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('feat: support doll speaking', $response->getContent());
        $this->assertStringNotContainsString('fix: concrete mixer leak', $response->getContent());

        // Search by Message
        $response = $this->getJson('/commits?draw=1&start=0&length=10&search[value]=speaking', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('feat: support doll speaking', $response->getContent());
        $this->assertStringNotContainsString('fix: concrete mixer leak', $response->getContent());

        // Search by Repo
        $response = $this->getJson('/commits?draw=1&start=0&length=10&search[value]=mixer', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('fix: concrete mixer leak', $response->getContent());
        $this->assertStringNotContainsString('feat: support doll speaking', $response->getContent());

        // Search by Hash
        $response = $this->getJson('/commits?draw=1&start=0&length=10&search[value]=abcd', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('feat: support doll speaking', $response->getContent());
        $this->assertStringNotContainsString('fix: concrete mixer leak', $response->getContent());

        // Search by Employee
        $response = $this->getJson('/commits?draw=1&start=0&length=10&search[value]=Bob', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('fix: concrete mixer leak', $response->getContent());
        $this->assertStringNotContainsString('feat: support doll speaking', $response->getContent());
    }

    public function test_meetings_search(): void
    {
        $employee1 = Employee::create(['name' => 'Alice Margatroid', 'email' => 'alice@marisa.com']);
        $employee2 = Employee::create(['name' => 'Bob Builder', 'email' => 'bob@builder.com']);

        $m1 = Meeting::create([
            'title' => 'Doll Voice Integration Sync',
            'notes' => 'Discuss speech synthesizers for doll voice boxes.',
            'meeting_date' => '2026-06-19 14:00:00'
        ]);
        $m1->employees()->attach($employee1->id);

        $m2 = Meeting::create([
            'title' => 'Concrete Drying Time Review',
            'notes' => 'Review the optimal moisture levels for quick-drying concrete.',
            'meeting_date' => '2026-06-20 15:00:00'
        ]);
        $m2->employees()->attach($employee2->id);

        // Search by Title
        $response = $this->getJson('/meetings?draw=1&start=0&length=10&search[value]=Voice', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Doll Voice Integration Sync', $response->getContent());
        $this->assertStringNotContainsString('Concrete Drying Time Review', $response->getContent());

        // Search by Notes
        $response = $this->getJson('/meetings?draw=1&start=0&length=10&search[value]=moisture', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Concrete Drying Time Review', $response->getContent());
        $this->assertStringNotContainsString('Doll Voice Integration Sync', $response->getContent());

        // Search by Participant
        $response = $this->getJson('/meetings?draw=1&start=0&length=10&search[value]=Alice', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Doll Voice Integration Sync', $response->getContent());
        $this->assertStringNotContainsString('Concrete Drying Time Review', $response->getContent());

        // Search by Date (partial match)
        $response = $this->getJson('/meetings?draw=1&start=0&length=10&search[value]=2026-06-20', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Concrete Drying Time Review', $response->getContent());
        $this->assertStringNotContainsString('Doll Voice Integration Sync', $response->getContent());
    }

    public function test_reports_search(): void
    {
        $r1 = Report::create([
            'team_productivity' => 85,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-07',
            'generated_at' => '2026-06-08 10:00:00',
            'top_performers' => [],
            'attention_required' => [],
            'risks' => [],
            'full_report' => '[]',
        ]);

        $r2 = Report::create([
            'team_productivity' => 92,
            'start_date' => '2026-06-08',
            'end_date' => '2026-06-14',
            'generated_at' => '2026-06-15 11:00:00',
            'top_performers' => [],
            'attention_required' => [],
            'risks' => [],
            'full_report' => '[]',
        ]);

        // Search by ID with Hash prefix
        $searchId = '#' . str_pad($r1->id, 4, '0', STR_PAD_LEFT);
        $response = $this->getJson('/reports?draw=1&start=0&length=10&search[value]=' . $searchId, $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString($searchId, $response->getContent());

        // Search by Date Range (start date)
        $response = $this->getJson('/reports?draw=1&start=0&length=10&search[value]=2026-06-08', $this->headers);
        $response->assertStatus(200);
        // Should match r2's start_date
        $this->assertStringContainsString('92%', $response->getContent());

        // Search by Generated Date
        $response = $this->getJson('/reports?draw=1&start=0&length=10&search[value]=2026-06-15', $this->headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('92%', $response->getContent());
    }
}
