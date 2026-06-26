<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $taskTemplates = [
            'Integrate Stripe payment gateway',
            'Refactor JWT authentication flow',
            'Implement OAuth2 Google sign-in',
            'Fix memory leak in email worker',
            'Write integration tests for checkout flow',
            'Optimize search index query',
            'Update API documentation for API v2',
            'Configure CI/CD pipeline in GitHub Actions',
            'Design onboarding screens',
            'Set up Redis cache for dashboard metrics',
            'Implement database partition for logs',
            'Audit security in npm package dependencies',
            'Develop real-time notifications with WebSockets',
            'Refactor reporting module',
            'Create daily automated backup script',
            'Improve mobile responsiveness of navigation',
            'Integrate Sentry error tracking dashboard',
            'Build user settings wizard',
            'Optimize Eloquent queries on landing page',
            'Add theme customization support',
        ];

        $assignedDate = fake()->dateTimeBetween('2026-06-01', '2026-06-18');
        $status = fake()->randomElement(['pending', 'in_progress', 'completed']);
        
        $assignedTimestamp = $assignedDate->getTimestamp();
        $endTimestamp = strtotime('2026-06-18 23:59:59');
        $maxDiff = max(0, $endTimestamp - $assignedTimestamp);
        
        $dueOffset = rand(0, $maxDiff);
        $dueDate = (new \DateTime())->setTimestamp($assignedTimestamp + $dueOffset);
        
        $completedDate = null;
        if ($status === 'completed') {
            $completedOffset = rand(0, $dueOffset);
            $completedDate = (new \DateTime())->setTimestamp($assignedTimestamp + $completedOffset);
        }

        return [
            'employee_id' => 1, // Will be overridden during seeding
            'title' => fake()->randomElement($taskTemplates) . ' - ' . ucfirst(fake()->word()),
            'description' => fake()->paragraph(),
            'status' => $status,
            'assigned_date' => $assignedDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'completed_date' => $completedDate ? $completedDate->format('Y-m-d') : null,
        ];
    }
}
