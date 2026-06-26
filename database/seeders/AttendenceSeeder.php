<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendence;

class AttendenceSeeder extends Seeder
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

        $total = 1000;
        $chunkSize = 1000;
        $now = now();

        // --- DISTRIBUTION LOGIC & SPECIFICATION ---
        // Date range: 2026-06-01 to 2026-06-18 (18 days total)
        // 1. Low Activity Dates: June 10 and June 15 (only 30-60 attendance logs)
        // 2. High Activity Dates: June 05, June 08, and June 12 (significantly more attendance logs)
        // 3. Weekends: Lower overall attendance records logged (weight = 0.05)
        // 4. Weekday vs Weekend Presence: 92% present rate on weekdays, 20% on weekends.
        
        $attPerDay = [];
        $attPerDay['2026-06-10'] = rand(30, 60);
        $attPerDay['2026-06-15'] = rand(30, 60);

        $remaining = $total - array_sum($attPerDay);

        $weights = [
            '2026-06-01' => 3.5, // Mon
            '2026-06-02' => 3.5, // Tue
            '2026-06-03' => 3.5, // Wed
            '2026-06-04' => 3.5, // Thu
            '2026-06-05' => 6.0, // Fri (High Activity Day)
            '2026-06-06' => 0.05, // Sat (Weekend - low attendance)
            '2026-06-07' => 0.05, // Sun (Weekend - low attendance)
            '2026-06-08' => 6.0, // Mon (High Activity Day)
            '2026-06-09' => 3.5, // Tue
            // '2026-06-10' is handled via Low Activity configuration
            '2026-06-11' => 3.5, // Thu
            '2026-06-12' => 6.0, // Fri (High Activity Day)
            '2026-06-13' => 0.05, // Sat (Weekend - low attendance)
            '2026-06-14' => 0.05, // Sun (Weekend - low attendance)
            // '2026-06-15' is handled via Low Activity configuration
            '2026-06-16' => 3.5, // Tue
            '2026-06-17' => 3.5, // Wed
            '2026-06-18' => 3.5, // Thu
        ];

        $totalWeight = array_sum($weights);
        $allocated = 0;
        foreach ($weights as $date => $weight) {
            $attPerDay[$date] = (int) round(($weight / $totalWeight) * $remaining);
            $allocated += $attPerDay[$date];
        }

        // Adjust rounding differences to guarantee exactly 40,000 logs
        $diff = $remaining - $allocated;
        if ($diff !== 0) {
            $attPerDay['2026-06-01'] += $diff;
        }

        // Build list of dates corresponding to attendance count
        $datesList = [];
        foreach ($attPerDay as $date => $count) {
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
                
                $rawAttendences = \Database\Factories\AttendenceFactory::new()->count($currentChunkSize)->raw();

                foreach ($rawAttendences as $index => $attendence) {
                    $dateStr = $datesList[$i + $index];
                    
                    // Determine weekend presence rule
                    $dayOfWeek = date('N', strtotime($dateStr));
                    $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                    
                    // 20% present rate on weekends, 92% on weekdays
                    $present = $isWeekend ? (rand(1, 100) <= 20) : (rand(1, 100) <= 92);
                    
                    // Generate check_in and check_out
                    $checkInHour = rand(8, 10);
                    $checkInMinute = rand(0, 59);
                    $checkInSecond = rand(0, 59);
                    $checkIn = sprintf('%02d:%02d:%02d', $checkInHour, $checkInMinute, $checkInSecond);

                    $checkOut = null;
                    if ($present) {
                        $workSeconds = rand(28800, 36000); 
                        $checkInTimestamp = strtotime("2000-01-01 $checkIn");
                        $checkOut = date('H:i:s', $checkInTimestamp + $workSeconds);
                    }

                    $attendence['employee_id'] = $employeeIds[array_rand($employeeIds)];
                    $attendence['date'] = $dateStr;
                    $attendence['check_in'] = $checkIn;
                    $attendence['check_out'] = $checkOut;
                    $attendence['present'] = $present;
                    $attendence['created_at'] = $now;
                    $attendence['updated_at'] = $now;
                    
                    $chunk[] = $attendence;
                }

                DB::table('attendences')->insert($chunk);
            }
        });
    }
}
