<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Meeting;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = DB::table('employees')->select('id', 'name')->get();
        $employeeIds = $employees->pluck('id')->toArray();
        $employeeNames = $employees->pluck('name', 'id')->toArray();

        if (empty($employeeIds)) {
            return;
        }

        $totalMeetings = 1000;
        $now = now();

        // --- DISTRIBUTION LOGIC & SPECIFICATION ---
        // Date range: 2026-06-01 to 2026-06-18 (18 days total)
        // 1. Low Activity Dates: June 10 and June 15 (only 2-5 meetings)
        // 2. High Activity Dates: June 05, June 08, and June 12 (significantly more meetings)
        // 3. Weekends: Zero meetings (weight = 0.0)
        // 4. Mondays (June 1 and June 8): Mondays contain more meetings (weight = 6.0 / 8.0)
        // 5. Other Weekdays: Normal meetings baseline (weight = 3.0)
        
        $meetingsPerDay = [];
        $meetingsPerDay['2026-06-10'] = rand(2, 5);
        $meetingsPerDay['2026-06-15'] = rand(2, 5);

        $remaining = $totalMeetings - array_sum($meetingsPerDay);

        $weights = [
            '2026-06-01' => 6.0, // Mon (Monday meeting peak)
            '2026-06-02' => 3.0, // Tue
            '2026-06-03' => 3.0, // Wed
            '2026-06-04' => 3.0, // Thu
            '2026-06-05' => 8.0, // Fri (High Activity Day)
            '2026-06-06' => 0.0, // Sat (Weekend - no meetings)
            '2026-06-07' => 0.0, // Sun (Weekend - no meetings)
            '2026-06-08' => 8.0, // Mon (High Activity Day & Monday meeting peak)
            '2026-06-09' => 3.0, // Tue
            // '2026-06-10' is handled via Low Activity configuration
            '2026-06-11' => 3.0, // Thu
            '2026-06-12' => 8.0, // Fri (High Activity Day)
            '2026-06-13' => 0.0, // Sat (Weekend - no meetings)
            '2026-06-14' => 0.0, // Sun (Weekend - no meetings)
            // '2026-06-15' is handled via Low Activity configuration
            '2026-06-16' => 3.0, // Tue
            '2026-06-17' => 3.0, // Wed
            '2026-06-18' => 3.0, // Thu
        ];

        $totalWeight = array_sum($weights);
        $allocated = 0;
        foreach ($weights as $date => $weight) {
            $meetingsPerDay[$date] = (int) round(($weight / $totalWeight) * $remaining);
            $allocated += $meetingsPerDay[$date];
        }

        // Adjust rounding differences to guarantee exactly 5,000 meetings
        $diff = $remaining - $allocated;
        if ($diff !== 0) {
            $meetingsPerDay['2026-06-01'] += $diff;
        }

        // Build list of dates corresponding to meetings count
        $datesList = [];
        foreach ($meetingsPerDay as $date => $count) {
            for ($k = 0; $k < $count; $k++) {
                $datesList[] = $date;
            }
        }
        
        shuffle($datesList);

        $meetingsToInsert = [];
        $meetingParticipants = []; // maps meeting index to array of employee IDs

        DB::transaction(function () use ($employeeIds, $employeeNames, $datesList, $totalMeetings, $now, &$meetingsToInsert, &$meetingParticipants) {
            for ($i = 0; $i < $totalMeetings; $i++) {
                $dateStr = $datesList[$i];
                
                // Meetings happen during work hours: 09:00 to 16:50 on 10 min increments
                $meetingDateStr = $dateStr . ' ' . sprintf('%02d:%02d:00', rand(9, 16), rand(0, 5) * 10);
                
                // Determine random participants (between 2 and 8 employees)
                $numParticipants = rand(2, 8);
                
                // Select random participant IDs from employeeIds
                $participantIds = (array) array_rand(array_flip($employeeIds), $numParticipants);
                if (!is_array($participantIds)) {
                    $participantIds = [$participantIds];
                }
                
                // Select speakers
                $mostActiveId = $participantIds[array_rand($participantIds)];
                $mostActiveName = $employeeNames[$mostActiveId];
                
                $leastActiveName = null;
                if (count($participantIds) > 1) {
                    $remainingIds = array_diff($participantIds, [$mostActiveId]);
                    $leastActiveId = $remainingIds[array_rand($remainingIds)];
                    $leastActiveName = $employeeNames[$leastActiveId];
                }
                
                // Generate meeting attributes using factory
                $meetingData = \Database\Factories\MeetingFactory::new()->raw([
                    'meeting_date' => $meetingDateStr,
                    'total_participants' => count($participantIds),
                    'most_active_speaker' => $mostActiveName,
                    'least_active_speaker' => $leastActiveName,
                ]);
                
                $meetingData['created_at'] = $now;
                $meetingData['updated_at'] = $now;
                
                $meetingsToInsert[] = $meetingData;
                $meetingParticipants[$i] = $participantIds;
            }

            // Bulk insert meetings
            $meetingChunkSize = 1000;
            foreach (array_chunk($meetingsToInsert, $meetingChunkSize) as $meetingsChunk) {
                DB::table('meetings')->insert($meetingsChunk);
            }

            // Retrieve the meeting IDs in sequential order
            $meetingIds = DB::table('meetings')->orderBy('id')->pluck('id')->toArray();

            // Prepare pivot data
            $pivotData = [];
            foreach ($meetingIds as $idx => $meetingId) {
                $participantIds = $meetingParticipants[$idx] ?? [];
                foreach ($participantIds as $empId) {
                    $pivotData[] = [
                        'meeting_id' => $meetingId,
                        'employee_id' => $empId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Bulk insert pivot records in smaller chunks of 1000
            $pivotChunkSize = 1000;
            foreach (array_chunk($pivotData, $pivotChunkSize) as $pivotChunk) {
                DB::table('employee_meeting')->insert($pivotChunk);
            }
        });
    }
}
