<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\Employee;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_report(): void
    {
        // 1. Create team and employees to aggregate
        $team = Team::create(['name' => 'Team Alpha']);
        Employee::create(['name' => 'Alice Margatroid', 'email' => 'alice@marisa.com', 'team_id' => $team->id]);
        Employee::create(['name' => 'Bob Builder', 'email' => 'bob@builder.com', 'team_id' => $team->id]);

        // 2. Fake Nvidia API response
        Http::fake([
            'integrate.api.nvidia.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'team_productivity_percentage' => 88,
                                'top_performers' => [
                                    ['name' => 'Alice Margatroid', 'reason' => 'Completed all tasks on time']
                                ],
                                'top_performing_team' => [
                                    'team_name' => 'Team Alpha',
                                    'score' => 95,
                                    'reason' => 'Excellent collaboration'
                                ],
                                'team_analysis' => [
                                    [
                                        'team_name' => 'Team Alpha',
                                        'top_performer' => [
                                            'employee_name' => 'Alice Margatroid',
                                            'score' => 98,
                                            'reason' => 'Superb doll making productivity'
                                        ],
                                        'attention_required' => [
                                            [
                                                'employee_name' => 'Bob Builder',
                                                'reason' => 'Fewer commits than expected'
                                            ]
                                        ],
                                        'risks' => [
                                            'Heavy dependency on doll joints'
                                        ]
                                    ]
                                ],
                                'attention_required' => [
                                    ['name' => 'Bob Builder', 'reason' => 'Needs attention']
                                ],
                                'risks' => [
                                    ['risk' => 'Lack of bricks', 'severity' => 'High']
                                ],
                                'supporting_employee_statistics' => [],
                                'meeting_analysis' => 'Good meetings',
                                'commit_analysis' => 'High frequency',
                                'attendence_analysis' => 'Excellent attendance',
                                'task_analysis' => 'On time'
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        // 3. Post to generate-report
        $response = $this->post('/generate-report', [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-18',
        ]);

        // 4. Assert report was created and has the correct team analysis
        $report = Report::latest()->first();
        $this->assertNotNull($report);
        $this->assertEquals(88, $report->team_productivity);

        $reportData = json_decode($report->full_report, true);
        $this->assertArrayHasKey('team_analysis', $reportData);
        $this->assertEquals('Team Alpha', $reportData['team_analysis'][0]['team_name']);
        $this->assertEquals('Alice Margatroid', $reportData['team_analysis'][0]['top_performer']['employee_name']);
    }

    public function test_can_view_report_detail_with_team_analysis(): void
    {
        // 1. Create a report in database with pre-populated team analysis
        $fullReport = [
            'team_productivity_percentage' => 88,
            'top_performers' => [
                ['name' => 'Alice Margatroid', 'reason' => 'Completed tasks']
            ],
            'team_analysis' => [
                [
                    'team_name' => 'Team Alpha',
                    'top_performer' => [
                        'employee_name' => 'Alice Margatroid',
                        'score' => 98,
                        'reason' => 'Superb doll making productivity'
                    ],
                    'attention_required' => [
                        [
                            'employee_name' => 'Bob Builder',
                            'reason' => 'Fewer commits than expected'
                        ]
                    ],
                    'risks' => [
                        'Heavy dependency on doll joints'
                    ]
                ]
            ],
            'attention_required' => [
                ['name' => 'Bob Builder', 'reason' => 'Needs attention']
            ],
            'risks' => [
                ['risk' => 'Lack of bricks', 'severity' => 'High']
            ]
        ];

        $report = Report::create([
            'team_productivity' => 88,
            'top_performers' => [],
            'attention_required' => [],
            'risks' => [],
            'full_report' => json_encode($fullReport),
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-18',
            'generated_at' => now(),
        ]);

        // 2. View details
        $response = $this->get('/reports/' . $report->id);

        $response->assertStatus(200);
        $response->assertSee('Team Analysis');
        $response->assertSee('Team Alpha');
        $response->assertSee('Alice Margatroid');
        $response->assertSee('Bob Builder');
        $response->assertSee('Heavy dependency on doll joints');
    }

    public function test_can_export_report_with_team_analysis(): void
    {
        // 1. Create report
        $fullReport = [
            'team_productivity_percentage' => 88,
            'team_analysis' => [
                [
                    'team_name' => 'Team Alpha',
                    'top_performer' => [
                        'employee_name' => 'Alice Margatroid',
                        'score' => 98,
                        'reason' => 'Superb productivity'
                    ],
                    'attention_required' => [
                        [
                            'employee_name' => 'Bob Builder',
                            'reason' => 'Fewer commits'
                        ]
                    ],
                    'risks' => [
                        'Heavy dependency on doll joints'
                    ]
                ]
            ]
        ];

        $report = Report::create([
            'team_productivity' => 88,
            'top_performers' => [],
            'attention_required' => [],
            'risks' => [],
            'full_report' => json_encode($fullReport),
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-18',
            'generated_at' => now(),
        ]);

        // 2. Request CSV export
        $response = $this->get('/reports/' . $report->id . '/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $csvContent = $response->getContent();
        $this->assertStringContainsString('--- Team Analysis ---', $csvContent);
        $this->assertStringContainsString('Team: Team Alpha', $csvContent);
        $this->assertStringContainsString('Alice Margatroid', $csvContent);
        $this->assertStringContainsString('Score: 98%', $csvContent);
        $this->assertStringContainsString('Reason: Superb productivity', $csvContent);
        $this->assertStringContainsString('Bob Builder', $csvContent);
        $this->assertStringContainsString('Reason: Fewer commits', $csvContent);
        $this->assertStringContainsString('Heavy dependency on doll joints', $csvContent);
    }
}
