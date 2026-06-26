<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $total = 10000;
        $chunkSize = 1000;
        $now = now();
        $teamIds = DB::table('teams')->pluck('id')->toArray();

        DB::transaction(function () use ($total, $chunkSize, $now, $teamIds) {
            for ($i = 0; $i < $total; $i += $chunkSize) {
                $chunk = [];
                $rawEmployees = \Database\Factories\EmployeeFactory::new()->count($chunkSize)->raw();

                foreach ($rawEmployees as $employee) {
                    $employee['created_at'] = $now;
                    $employee['updated_at'] = $now;
                    if (!empty($teamIds) && rand(1, 100) <= 80) {
                        $employee['team_id'] = $teamIds[array_rand($teamIds)];
                    } else {
                        $employee['team_id'] = null;
                    }
                    $chunk[] = $employee;
                }

                DB::table('employees')->insert($chunk);
            }
        });
    }
}
