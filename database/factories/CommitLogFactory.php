<?php

namespace Database\Factories;

use App\Models\CommitLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommitLog>
 */
class CommitLogFactory extends Factory
{
    protected $model = CommitLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $commitMessages = [
            'feat: implement token-based API authentication',
            'fix: resolve race condition in database seeder',
            'refactor: simplify employee meeting attachment logic',
            'test: add model unit tests for Attendence model',
            'docs: document environment variables in README.md',
            'chore: update composer dependencies',
            'style: run Laravel Pint syntax checks',
            'fix: handle empty state in employee index view',
            'feat: add analytics export to CSV',
            'fix: correct typo in attendance status computation',
            'refactor: restructure App\\Services directory',
            'perf: index frequently queried columns in commit_logs table',
            'feat: support custom filters on dashboard reports',
            'fix: resolve null pointer exception in controller',
            'chore: delete obsolete temporary assets',
        ];

        return [
            'employee_id' => 1, // Will be overridden during seeding
            'commit_hash' => fake()->sha1(),
            'commit_message' => fake()->randomElement($commitMessages) . ' (#' . rand(100, 999) . ')',
            'lines_added' => rand(5, 450),
            'lines_deleted' => rand(0, 250),
            'commit_date' => fake()->dateTimeBetween('2026-06-01', '2026-06-18')->format('Y-m-d H:i:s'),
        ];
    }
}
