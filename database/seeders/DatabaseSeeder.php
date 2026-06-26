<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Truncate tables to ensure a clean slate and avoid unique constraints errors
        DB::table('employee_meeting')->truncate();
        DB::table('meetings')->truncate();
        DB::table('commit_logs')->truncate();
        DB::table('attendences')->truncate();
        DB::table('tasks')->truncate();
        DB::table('employees')->truncate();
        DB::table('teams')->truncate();
        DB::table('reports')->truncate();

        Schema::enableForeignKeyConstraints();

        $this->call([
            TeamSeeder::class,
            EmployeeSeeder::class,
            TaskSeeder::class,
            AttendenceSeeder::class,
            CommitLogSeeder::class,
            MeetingSeeder::class,
            ReportSeeder::class,
        ]);
    }
}
