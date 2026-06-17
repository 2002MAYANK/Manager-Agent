<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder; 
use App\Models\Attendence;

class AttendenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([1,2,3,4] as $employeeId)
        {
            Attendence::insert([
                'employee_id' => $employeeId,
                'date' => now()->toDateString(),
                'check_in' => '09:00:00',
                'check_out' => '18:00:00',
                'present' => true,
                
            ]);
        }
    }
}
