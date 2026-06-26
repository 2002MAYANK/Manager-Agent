<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_teams_index_page_loads(): void
    {
        $response = $this->get('/teams');
        $response->assertStatus(200);
    }

    public function test_can_create_team(): void
    {
        $employee1 = Employee::create(['name' => 'John Doe', 'email' => 'john@company.com']);
        $employee2 = Employee::create(['name' => 'Jane Smith', 'email' => 'jane@company.com']);

        $response = $this->post('/teams', [
            'name' => 'Development Team',
            'description' => 'Software engineering team',
            'employee_ids' => [$employee1->id, $employee2->id],
        ]);

        $response->assertRedirect('/teams');
        $this->assertDatabaseHas('teams', [
            'name' => 'Development Team',
            'description' => 'Software engineering team',
        ]);

        $this->assertEquals(Team::first()->id, $employee1->fresh()->team_id);
        $this->assertEquals(Team::first()->id, $employee2->fresh()->team_id);
    }

    public function test_can_update_team(): void
    {
        $team = Team::create(['name' => 'Old Team']);
        $employee1 = Employee::create(['name' => 'John Doe', 'email' => 'john@company.com', 'team_id' => $team->id]);
        $employee2 = Employee::create(['name' => 'Jane Smith', 'email' => 'jane@company.com']);

        $response = $this->put('/teams/' . $team->id, [
            'name' => 'New Team',
            'description' => 'Updated desc',
            'employee_ids' => [$employee2->id], // employee1 should be removed
        ]);

        $response->assertRedirect('/teams');
        $this->assertDatabaseHas('teams', [
            'name' => 'New Team',
            'description' => 'Updated desc',
        ]);

        $this->assertNull($employee1->fresh()->team_id);
        $this->assertEquals($team->id, $employee2->fresh()->team_id);
    }

    public function test_team_detail_page_loads_and_shows_stats(): void
    {
        $team = Team::create(['name' => 'Alpha Team']);
        $response = $this->get('/teams/' . $team->id);
        $response->assertStatus(200);
    }

    public function test_can_delete_team(): void
    {
        $team = Team::create(['name' => 'Alpha Team']);
        $employee = Employee::create(['name' => 'John Doe', 'email' => 'john@company.com', 'team_id' => $team->id]);

        $response = $this->delete('/teams/' . $team->id);

        $response->assertRedirect('/teams');
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
        $this->assertNull($employee->fresh()->team_id);
    }

    public function test_can_search_employees(): void
    {
        Employee::create(['name' => 'Amit Sharma', 'email' => 'amit@company.com']);
        Employee::create(['name' => 'Rahul Verma', 'email' => 'rahul@company.com']);

        $response = $this->get('/employees/search?q=Amit');
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Amit Sharma']);
        $response->assertJsonMissing(['name' => 'Rahul Verma']);
    }
}
