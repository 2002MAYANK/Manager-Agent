<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::insert([
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul@example.com',
                'department' => 'Development',
                'designation' => 'Laravel Developer',
            ],
            [
                'name' => 'Arjun Singh',
                'email' => 'arjun@example.com',
                'department' => 'Development',
                'designation' => 'Backend Developer',
            ],
            [
                'name' => 'Shipra Gupta',
                'email' => 'shipra@example.com',
                'department' => 'QA',
                'designation' => 'Tester',
            ],
            [
                'name' => 'Anushka Verma',
                'email' => 'anushka@example.com',
                'department' => 'Frontend',
                'designation' => 'Frontend Developer',
            ],
            [
                'name' => 'Vikram Patel',
                'email' => 'vikram@example.com',
                'department' => 'Development',
                'designation' => 'Full Stack Developer',
            ],
            [
                'name' => 'Meera Nair',
                'email' => 'meera@example.com',
                'department' => 'HR',
                'designation' => 'HR Manager',
            ],
            [
                'name' => 'Sandeep Kumar',
                'email' => 'sandeep@example.com',
                'department' => 'Operations',
                'designation' => 'Ops Lead',
            ],
            [
                'name' => 'Priya Desai',
                'email' => 'priya@example.com',
                'department' => 'Design',
                'designation' => 'Product Designer',
            ],
        ]);
    }
}
