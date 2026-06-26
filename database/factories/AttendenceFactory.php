<?php

namespace Database\Factories;

use App\Models\Attendence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendence>
 */
class AttendenceFactory extends Factory
{
    protected $model = Attendence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $present = fake()->boolean(92); // 92% attendance rate
        
        $checkInHour = rand(8, 10);
        $checkInMinute = rand(0, 59);
        $checkInSecond = rand(0, 59);
        $checkIn = sprintf('%02d:%02d:%02d', $checkInHour, $checkInMinute, $checkInSecond);

        $checkOut = null;
        if ($present) {
            // Work 8 to 10 hours
            $workSeconds = rand(28800, 36000); 
            $checkInTimestamp = strtotime("2000-01-01 $checkIn");
            $checkOut = date('H:i:s', $checkInTimestamp + $workSeconds);
        }

        return [
            'employee_id' => 1, // Will be overridden during seeding
            'date' => fake()->dateTimeBetween('2026-06-01', '2026-06-18')->format('Y-m-d'),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'present' => $present,
        ];
    }
}
