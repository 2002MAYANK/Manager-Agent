<?php

namespace Database\Factories;

use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $meetingTitles = [
            'Sprint Planning Sync',
            'Daily Standup Meeting',
            'Weekly Retrospective',
            '1-on-1 Sync Session',
            'System Architecture Review',
            'Client Review Meeting',
            'Product Roadmap Brainstorming',
            'Monthly Engineering Sync',
            'Security Incident Post-mortem',
        ];

        return [
            'title' => fake()->randomElement($meetingTitles) . ' - ' . ucfirst(fake()->word()),
            'notes' => fake()->paragraphs(3, true),
            'meeting_date' => fake()->dateTimeBetween('2026-06-01', '2026-06-18')->format('Y-m-d H:i:s'),
            'total_participants' => 0, // Will be overridden in seeder
            'total_transcript_entries' => rand(10, 80),
            'most_active_speaker' => null, // Will be overridden in seeder
            'least_active_speaker' => null, // Will be overridden in seeder
            'meeting_duration' => fake()->randomElement(['15m', '30m', '45m', '1h', '1h 15m', '1h 30m']),
        ];
    }
}
