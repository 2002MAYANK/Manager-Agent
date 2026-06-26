<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LeadershipInsightsController;
use App\Services\ManagerAgentService;
use Illuminate\Support\Facades\Cache;

class PrecomputeLeadershipInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leadership:precompute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precompute metrics for Leadership Insights page and store in cache';

    /**
     * Execute the console command.
     */
    public function handle(ManagerAgentService $agent)
    {
        $this->info('Starting precomputation of leadership metrics...');
        
        $controller = new LeadershipInsightsController();
        
        $this->info('Precomputing Top Performers...');
        $topPerformers = $controller->getTopPerformers();
        Cache::put('leadership_top_performers', $topPerformers, now()->addHours(6));

        $this->info('Precomputing Team Performance...');
        $teamPerformance = $controller->getTeamPerformance();
        Cache::put('leadership_team_performance', $teamPerformance, now()->addHours(6));

        $this->info('Precomputing Employees Requiring Attention...');
        $employeesAttention = $controller->getEmployeesRequiringAttention();
        Cache::put('leadership_employees_attention', $employeesAttention, now()->addHours(6));

        $this->info('Precomputing Teams Requiring Attention...');
        $teamsAttention = $controller->getTeamsRequiringAttention($teamPerformance);
        Cache::put('leadership_teams_attention', $teamsAttention, now()->addHours(6));

        $this->info('Precomputing Project Risks...');
        $projectRisks = $controller->getProjectRisks();
        Cache::put('leadership_project_risks', $projectRisks, now()->addHours(6));

        $this->info('Precomputing Leadership Summary...');
        $summary = [
            'top_performer' => count($topPerformers) > 0 ? $topPerformers[0]->name : 'N/A',
            'best_team' => count($teamPerformance) > 0 ? $teamPerformance[0]['team_name'] : 'N/A',
            'employees_attention' => count($employeesAttention),
            'teams_attention' => count($teamsAttention),
            'high_risk_projects' => count(array_filter($projectRisks, fn($p) => $p['risk_level'] === 'High')),
            'health_score' => $controller->calculateOrganizationHealth($teamPerformance, $employeesAttention)
        ];
        Cache::put('leadership_summary', $summary, now()->addHours(6));

        $this->info('Precomputing AI Recommendations...');
        $aiRecommendations = $controller->generateAIRecommendations($agent, $summary, $teamPerformance, $projectRisks);
        Cache::put('leadership_insights_ai_recommendations', $aiRecommendations, now()->addHours(6));

        $this->info('Leadership Insights precomputation completed successfully!');
        return self::SUCCESS;
    }
}
