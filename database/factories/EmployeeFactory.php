<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake('en_IN')->firstName();
        $lastName = fake('en_IN')->lastName();
        $name = "$firstName $lastName";
        
        // Generate a unique email using the name and a small random string to guarantee uniqueness
        $email = strtolower($firstName . '.' . $lastName) . '_' . Str::random(5) . '@example.com';

        $departments = ['Engineering', 'QA', 'Product', 'Design', 'HR', 'Marketing', 'Sales', 'Operations'];
        
        $designations = [
            'Engineering' => ['Frontend Developer', 'Backend Developer', 'Full Stack Developer', 'Technical Lead', 'Engineering Manager'],
            'QA' => ['QA Engineer', 'Tester', 'QA Lead'],
            'Product' => ['Product Manager', 'Product Owner'],
            'Design' => ['UX/UI Designer', 'Product Designer', 'Visual Designer'],
            'HR' => ['HR Generalist', 'HR Manager', 'Talent Acquisition Specialist'],
            'Marketing' => ['Marketing Specialist', 'Marketing Manager'],
            'Sales' => ['Account Executive', 'Sales Manager'],
            'Operations' => ['Operations Analyst', 'Operations Lead'],
        ];

        $department = fake()->randomElement($departments);
        $designation = fake()->randomElement($designations[$department]);

        return [
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'designation' => $designation,
        ];
    }
}
