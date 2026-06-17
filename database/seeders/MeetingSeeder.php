<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Meeting; 

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Meeting::create([
            'title' => 'Weekly Sprint Meeting',
            'notes' => 'API integration delayed by 2 days. Team agreed to prioritize authentication module.',
            'meeting_date' => now(),
        ]);
    }
}
