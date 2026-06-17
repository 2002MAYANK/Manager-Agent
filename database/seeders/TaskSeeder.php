<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::insert([
            [
                'employee_id' => 1,
                'title' => 'API Integration',
                'status' => 'completed',
                'assigned_date' => now(),
                'due_date' => now()->addDays(2),
                'completed_date' => now(),
            ],
            [
                'employee_id' => 2,
                'title' => 'Authentication Module',
                'status' => 'completed',
                'assigned_date' => now(),
                'due_date' => now()->addDays(3),
                'completed_date' => now(),
            ],
            [
                'employee_id' => 3,
                'title' => 'Testing Dashboard',
                'status' => 'pending',
                'assigned_date' => now(),
                'due_date' => now()->addDays(1),
                'completed_date' => null,
            ],
            [
                'employee_id' => 4,
                'title' => 'UI Improvements',
                'status' => 'in_progress',
                'assigned_date' => now(),
                'due_date' => now()->addDays(4),
                'completed_date' => null,
            ],
            [
                'employee_id' => 5,
                'title' => 'Implement Reporting API',
                'status' => 'in_progress',
                'assigned_date' => now()->subDays(2),
                'due_date' => now()->addDays(5),
                'completed_date' => null,
            ],
            [
                'employee_id' => 6,
                'title' => 'Setup Role Permissions',
                'status' => 'pending',
                'assigned_date' => now()->subDays(1),
                'due_date' => now()->addDays(7),
                'completed_date' => null,
            ],
            [
                'employee_id' => 7,
                'title' => 'Import Data Script',
                'status' => 'completed',
                'assigned_date' => now()->subDays(10),
                'due_date' => now()->subDays(5),
                'completed_date' => now()->subDays(6),
            ],
            [
                'employee_id' => 8,
                'title' => 'Design System Audit',
                'status' => 'pending',
                'assigned_date' => now(),
                'due_date' => now()->addDays(14),
                'completed_date' => null,
            ],
        ]);
    }
}
