<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $teams = [
            [
                'name' => 'Team Alpha',
                'description' => 'Backend Development & Architecture Team',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Team Beta',
                'description' => 'Frontend Development & UI/UX Design Team',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Team Gamma',
                'description' => 'QA & Automation Testing Team',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Team Delta',
                'description' => 'DevOps, Infrastructure & Platform Engineering Team',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Team Epsilon',
                'description' => 'Product Management, Analytics & Growth Team',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('teams')->insert($teams);
    }
}
