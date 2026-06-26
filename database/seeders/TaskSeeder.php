<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeIds = DB::table('employees')->pluck('id')->toArray();

        if (empty($employeeIds)) {
            return;
        }

        $total = 180000;
        $chunkSize = 1000;
        $now = now();

        // --- DISTRIBUTION LOGIC & SPECIFICATION ---
        // Date range: 2026-06-01 to 2026-06-18 (18 days total)
        // 1. Low Activity Dates: June 10 and June 15 (only 20-50 tasks assigned)
        // 2. High Activity Dates: June 05, June 08, and June 12 (significantly more tasks)
        // 3. Weekends: Lower activity (weight = 0.1)
        // 4. Normal Weekdays: Normal activity (weight = 2.0)
        
        $tasksPerDay = [];
        $tasksPerDay['2026-06-10'] = rand(20, 50);
        $tasksPerDay['2026-06-15'] = rand(20, 50);

        $remaining = $total - array_sum($tasksPerDay);

        $weights = [
            '2026-06-01' => 2.0, // Mon
            '2026-06-02' => 2.0, // Tue
            '2026-06-03' => 2.0, // Wed
            '2026-06-04' => 2.0, // Thu
            '2026-06-05' => 6.0, // Fri (High Activity Day)
            '2026-06-06' => 0.1, // Sat (Weekend - low support activity)
            '2026-06-07' => 0.1, // Sun (Weekend - low support activity)
            '2026-06-08' => 6.0, // Mon (High Activity Day)
            '2026-06-09' => 2.0, // Tue
            // '2026-06-10' is handled via Low Activity configuration
            '2026-06-11' => 2.0, // Thu
            '2026-06-12' => 6.0, // Fri (High Activity Day)
            '2026-06-13' => 0.1, // Sat (Weekend - low support activity)
            '2026-06-14' => 0.1, // Sun (Weekend - low support activity)
            // '2026-06-15' is handled via Low Activity configuration
            '2026-06-16' => 2.0, // Tue
            '2026-06-17' => 2.0, // Wed
            '2026-06-18' => 2.0, // Thu
        ];

        $totalWeight = array_sum($weights);
        $allocated = 0;
        foreach ($weights as $date => $weight) {
            $tasksPerDay[$date] = (int) round(($weight / $totalWeight) * $remaining);
            $allocated += $tasksPerDay[$date];
        }

        // Adjust rounding differences to guarantee exactly 35,000 tasks
        $diff = $remaining - $allocated;
        if ($diff !== 0) {
            $tasksPerDay['2026-06-01'] += $diff;
        }

        // Build list of dates corresponding to tasks count for sequential processing
        $datesList = [];
        foreach ($tasksPerDay as $date => $count) {
            for ($k = 0; $k < $count; $k++) {
                $datesList[] = $date;
            }
        }
        
        shuffle($datesList);

        DB::transaction(function () use ($employeeIds, $datesList, $chunkSize, $now) {
            $totalCount = count($datesList);
            for ($i = 0; $i < $totalCount; $i += $chunkSize) {
                $chunk = [];
                $currentChunkSize = min($chunkSize, $totalCount - $i);
                
                $rawTasks = \Database\Factories\TaskFactory::new()->count($currentChunkSize)->raw();

                foreach ($rawTasks as $index => $task) {
                    $assignedDateStr = $datesList[$i + $index];
                    $assignedDate = new \DateTime($assignedDateStr);
                    
                    $assignedTimestamp = $assignedDate->getTimestamp();
                    $endTimestamp = strtotime('2026-06-18 23:59:59');
                    $maxDiff = max(0, $endTimestamp - $assignedTimestamp);

                    // due_date must fall between assigned_date and 18 June 2026
                    $dueOffset = rand(0, $maxDiff);
                    $dueDate = (new \DateTime())->setTimestamp($assignedTimestamp + $dueOffset);

                    $completedDate = null;
                    if ($task['status'] === 'completed') {
                        // Completed tasks should increase toward the end of the period.
                        // We use a power-based skew (pow(r, 0.5) shifts factor towards 1.0)
                        $factor = pow(mt_rand() / mt_getrandmax(), 0.5);
                        $completedOffset = (int) ($factor * $dueOffset);
                        $completedDate = (new \DateTime())->setTimestamp($assignedTimestamp + $completedOffset);
                    }

                    $task['employee_id'] = $employeeIds[array_rand($employeeIds)];
                    $task['assigned_date'] = $assignedDate->format('Y-m-d');
                    $task['due_date'] = $dueDate->format('Y-m-d');
                    $task['completed_date'] = $completedDate ? $completedDate->format('Y-m-d') : null;
                    $task['created_at'] = $now;
                    $task['updated_at'] = $now;
                    
                    $chunk[] = $task;
                }

                DB::table('tasks')->insert($chunk);
            }
        });
    }
}
