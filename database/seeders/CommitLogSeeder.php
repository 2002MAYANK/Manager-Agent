<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder; 
use App\Models\CommitLog;

class CommitLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CommitLog::insert([
            [
                'employee_id' => 1,
                'commit_hash' => 'abc123',
                'commit_message' => 'Fixed API bug',
                'lines_added' => 120,
                'lines_deleted' => 20,
                'commit_date' => now(),
            ],
            [
                'employee_id' => 2,
                'commit_hash' => 'xyz456',
                'commit_message' => 'Added authentication',
                'lines_added' => 150,
                'lines_deleted' => 10,
                'commit_date' => now(),
            ],
            [
                'employee_id' => 4,
                'commit_hash' => 'ui789',
                'commit_message' => 'Updated dashboard UI',
                'lines_added' => 80,
                'lines_deleted' => 5,
                'commit_date' => now(),
            ],
            [
                'employee_id' => 3,
                'commit_hash' => 'def234',
                'commit_message' => 'Refactored task service',
                'lines_added' => 95,
                'lines_deleted' => 12,
                'commit_date' => now()->subDays(1),
            ],
            [
                'employee_id' => 4,
                'commit_hash' => 'ghj012',
                'commit_message' => 'Write unit tests for Report',
                'lines_added' => 200,
                'lines_deleted' => 0,
                'commit_date' => now()->subDays(2),
            ],
            [
                'employee_id' => 2,
                'commit_hash' => 'klm345',
                'commit_message' => 'Optimize DB queries',
                'lines_added' => 60,
                'lines_deleted' => 30,
                'commit_date' => now()->subDays(3),
            ],
            [
                'employee_id' => 1,
                'commit_hash' => 'nop678',
                'commit_message' => 'Fix meeting time zone bug',
                'lines_added' => 10,
                'lines_deleted' => 2,
                'commit_date' => now()->subDays(4),
            ],
            [
                'employee_id' => 3,
                'commit_hash' => 'qrs901',
                'commit_message' => 'Add role-based permissions',
                'lines_added' => 180,
                'lines_deleted' => 20,
                'commit_date' => now()->subDays(5),
            ],
            [
                'employee_id' => 4,
                'commit_hash' => 'tuv234',
                'commit_message' => 'Improve import performance',
                'lines_added' => 130,
                'lines_deleted' => 40,
                'commit_date' => now()->subDays(6),
            ],
            [
                'employee_id' => 1,
                'commit_hash' => 'wxy567',
                'commit_message' => 'Cleanup unused assets',
                'lines_added' => 25,
                'lines_deleted' => 100,
                'commit_date' => now()->subDays(7),
            ],
        ]);
    }
}
