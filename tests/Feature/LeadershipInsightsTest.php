<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadershipInsightsTest extends TestCase
{
    use RefreshDatabase;
    public function test_leadership_insights_caching_and_command(): void
    {
        // Clear cache
        Cache::forget('leadership_top_performers');
        Cache::forget('leadership_team_performance');
        Cache::forget('leadership_employees_attention');
        Cache::forget('leadership_teams_attention');
        Cache::forget('leadership_project_risks');
        Cache::forget('leadership_summary');

        // Run the precomputation command
        $exitCode = Artisan::call('leadership:precompute');
        $this->assertEquals(0, $exitCode);

        // Verify metrics are cached
        $this->assertTrue(Cache::has('leadership_top_performers'));
        $this->assertTrue(Cache::has('leadership_team_performance'));
        $this->assertTrue(Cache::has('leadership_employees_attention'));
        $this->assertTrue(Cache::has('leadership_teams_attention'));
        $this->assertTrue(Cache::has('leadership_project_risks'));
        $this->assertTrue(Cache::has('leadership_summary'));
    }
}
