<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CommitLog;

class CommitLogSeeder extends Seeder
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
        // 1. Low Activity Dates: June 10 and June 15 (only 10-20 commits logged)
        // 2. High Activity Dates: June 05, June 08, and June 12 (significantly more commits)
        // 3. Weekends: Lower commits (weight = 0.05)
        // 4. Mid-week (Wednesday & Thursday): Highest overall commit activity (weight = 5.0)
        // 5. Normal weekdays (Mon, Tue, Fri): Baseline activity (weight = 3.0)
        
        $commitsPerDay = [];
        $commitsPerDay['2026-06-10'] = rand(10, 20);
        $commitsPerDay['2026-06-15'] = rand(10, 20);

        $remaining = $total - array_sum($commitsPerDay);

        $weights = [
            '2026-06-01' => 3.0, // Mon
            '2026-06-02' => 3.0, // Tue
            '2026-06-03' => 5.0, // Wed (Mid-week peak)
            '2026-06-04' => 5.0, // Thu (Mid-week peak)
            '2026-06-05' => 6.0, // Fri (High Activity Day)
            '2026-06-06' => 0.05, // Sat (Weekend - low commits)
            '2026-06-07' => 0.05, // Sun (Weekend - low commits)
            '2026-06-08' => 6.0, // Mon (High Activity Day)
            '2026-06-09' => 3.0, // Tue
            // '2026-06-10' is handled via Low Activity configuration
            '2026-06-11' => 5.0, // Thu (Mid-week peak)
            '2026-06-12' => 6.0, // Fri (High Activity Day)
            '2026-06-13' => 0.05, // Sat (Weekend - low commits)
            '2026-06-14' => 0.05, // Sun (Weekend - low commits)
            // '2026-06-15' is handled via Low Activity configuration
            '2026-06-16' => 3.0, // Tue
            '2026-06-17' => 5.0, // Wed (Mid-week peak)
            '2026-06-18' => 5.0, // Thu (Mid-week peak)
        ];

        $totalWeight = array_sum($weights);
        $allocated = 0;
        foreach ($weights as $date => $weight) {
            $commitsPerDay[$date] = (int) round(($weight / $totalWeight) * $remaining);
            $allocated += $commitsPerDay[$date];
        }

        // Adjust rounding differences to guarantee exactly 15,000 commits
        $diff = $remaining - $allocated;
        if ($diff !== 0) {
            $commitsPerDay['2026-06-01'] += $diff;
        }

        // Build list of dates corresponding to commits count
        $datesList = [];
        foreach ($commitsPerDay as $date => $count) {
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
                
                $rawCommitLogs = \Database\Factories\CommitLogFactory::new()->count($currentChunkSize)->raw();

                foreach ($rawCommitLogs as $index => $commitLog) {
                    $dateStr = $datesList[$i + $index];
                    
                    // Generate random hours, minutes, seconds for commit date
                    $commitDateStr = $dateStr . ' ' . sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59));

                    $commitLog['employee_id'] = $employeeIds[array_rand($employeeIds)];
                    $commitLog['commit_date'] = $commitDateStr;
                    $commitLog['created_at'] = $now;
                    $commitLog['updated_at'] = $now;
                    
                    $chunk[] = $commitLog;
                }

                DB::table('commit_logs')->insert($chunk);
            }
        });
    }
}
