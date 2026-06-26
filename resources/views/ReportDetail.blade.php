@extends('layout')

@section('title', 'Generated Report | AI Manager')
@section('page-title', 'Report Details')

@push('styles')
    <style>
        .report-shell {
            max-width: 980px;
            margin: 0 auto;
        }

        .report-card {
            padding: 26px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 24px;
        }

        .report-title {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 8px;
        }

        .report-score {
            min-width: 142px;
            padding: 18px;
            border: 1px solid rgba(16, 185, 129, .28);
            border-radius: 8px;
            background: rgba(16, 185, 129, .05);
            text-align: center;
        }

        .report-score strong {
            display: block;
            font-size: 34px;
            line-height: 1;
            color: var(--green);
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .report-section {
            min-height: 220px;
            padding: 20px;
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background: var(--panel-soft);
            color: var(--text);
        }

        .report-section h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 16px;
        }

        .report-item {
            padding: 12px 0;
            border-top: 1px solid var(--panel-border);
        }

        .report-item:first-of-type {
            border-top: 0;
            padding-top: 0;
        }

        .report-actions {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        @media (max-width: 991.98px) {
            .report-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {

            .report-header,
            .report-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .report-score {
                text-align: left;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $productivity = $reportModel->team_productivity ?? 0;
        $topPerformers = $report['top_performers'] ?? [];
        $attentionRequired = $report['attention_required'] ?? [];
        $risks = $report['risks'] ?? [];

        $displayText = function ($item, array $keys, string $fallback) {
            if (is_string($item)) {
                return $item;
            }

            if (!is_array($item)) {
                return $fallback;
            }

            foreach ($keys as $key) {
                if (!empty($item[$key])) {
                    return $item[$key];
                }
            }

            return $fallback;
        };
    @endphp

    <div class="report-shell">
        <div class="glass-card report-card">
            <div class="report-header">
                <div>
                    <h1 class="report-title">AI Performance Report #{{ str_pad($reportModel->id, 4, '0', STR_PAD_LEFT) }}
                    </h1>
                    <div class="muted-text">
                        Generated on {{ \Carbon\Carbon::parse($reportModel->generated_at)->format('M d, Y h:i A') }}<br>
                        Period:
                        @if ($reportModel->start_date && $reportModel->end_date)
                            {{ \Carbon\Carbon::parse($reportModel->start_date)->format('M d, Y') }} -
                            {{ \Carbon\Carbon::parse($reportModel->end_date)->format('M d, Y') }}
                        @else
                            All Time
                        @endif
                    </div>
                </div>

                <div class="report-score">
                    <strong>{{ (int) $productivity }}%</strong>
                    <span class="muted-text">Productivity</span>
                </div>
            </div>

            <div class="report-grid">
                <section class="report-section">
                    <h2 class="text-success"><i class="bi bi-trophy me-2"></i>Top Performers</h2>
                    @forelse ($topPerformers as $person)
                        <div class="report-item">
                            {{ $displayText($person, ['name', 'employee_name'], 'Employee') }}
                        </div>
                    @empty
                        <div class="muted-text">No top performers returned.</div>
                    @endforelse
                </section>

                <section class="report-section">
                    <h2 style="color: var(--orange);"><i class="bi bi-exclamation-triangle me-2"></i>Attention Required</h2>
                    @forelse ($attentionRequired as $person)
                        <div class="report-item">
                            {{ $displayText($person, ['name', 'employee_name', 'reason'], 'Attention item') }}
                        </div>
                    @empty
                        <div class="muted-text">No attention items returned.</div>
                    @endforelse
                </section>

                <section class="report-section">
                    <h2 class="text-danger"><i class="bi bi-shield-exclamation me-2"></i>Risks</h2>
                    @forelse ($risks as $risk)
                        <div class="report-item">
                            {{ $displayText($risk, ['title', 'risk', 'description'], 'Project risk') }}
                        </div>
                    @empty
                        <div class="muted-text">No risks returned.</div>
                    @endforelse
                </section>
            </div>

            @if(!empty($report['top_performing_team']))
                <div class="report-section" style="margin-top: 16px; min-height: auto;">
                    <h2 style="color: var(--purple-2);"><i class="bi bi-people-fill me-2"></i>Top Performing Team</h2>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px; margin-bottom: 12px; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <span class="muted-text">Team Name:</span>
                            <strong style="font-size: 18px; color: var(--text); margin-left: 8px;">{{ $report['top_performing_team']['team_name'] ?? 'N/A' }}</strong>
                        </div>
                        <div>
                            <span class="muted-text">Score:</span>
                            <span class="badge bg-primary" style="font-size: 16px; padding: 6px 12px; color: #fff; border-radius: 6px; font-weight: bold; margin-left: 8px;">
                                {{ isset($report['top_performing_team']['score']) ? $report['top_performing_team']['score'] . '%' : 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="muted-text" style="display: block; margin-bottom: 4px;">Reason:</span>
                        <p style="margin: 0; line-height: 1.6; color: var(--text);">
                            {{ $report['top_performing_team']['reason'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            @endif

            @if(!empty($report['project_analysis']))
                <div class="report-section" style="margin-top: 16px; min-height: auto;">
                    <h2 style="color: var(--blue);"><i class="bi bi-briefcase-fill me-2"></i>Project Analysis</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded border" style="background: rgba(37, 99, 235, 0.05); height: 100%;">
                                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--purple);">
                                    <i class="bi bi-star-fill me-2"></i>Top Performing Project
                                </h4>
                                <p class="mb-0 text-dark">{{ is_string($report['project_analysis']['top_performing_project']) ? $report['project_analysis']['top_performing_project'] : ($report['project_analysis']['top_performing_project']['name'] ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded border" style="background: rgba(239, 68, 68, 0.05); height: 100%;">
                                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--red);">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Project Risks & Delays
                                </h4>
                                @if(!empty($report['project_analysis']['delayed_projects']) && is_array($report['project_analysis']['delayed_projects']))
                                    <div class="mb-2"><strong>Delayed:</strong> {{ implode(', ', $report['project_analysis']['delayed_projects']) }}</div>
                                @endif
                                @if(!empty($report['project_analysis']['project_risks']))
                                    <div><strong>Risks:</strong> <span class="muted-text small">{{ is_string($report['project_analysis']['project_risks']) ? $report['project_analysis']['project_risks'] : json_encode($report['project_analysis']['project_risks']) }}</span></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($report['team_analysis']))
                <div class="report-section" style="margin-top: 16px; min-height: auto;">
                    <h2 style="color: var(--blue);"><i class="bi bi-microsoft-teams me-2"></i>Team Analysis</h2>
                    
                    @foreach($report['team_analysis'] as $index => $team)
                        @if($index > 0)
                            <hr style="border-top: 1px solid var(--panel-border); margin: 24px 0;">
                        @endif
                        
                        <div class="mb-4">
                            <h3 style="font-size: 18px; color: var(--text); font-weight: 700; margin-bottom: 16px;">
                                {{ $team['team_name'] ?? 'N/A' }}
                            </h3>
                            
                            <div class="row g-3">
                                <!-- Top Performer -->
                                <div class="col-md-4">
                                    <div class="p-3 rounded border" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.15) !important; height: 100%;">
                                        <h4 style="font-size: 14px; color: var(--green); font-weight: 700; margin-bottom: 12px;">
                                            <i class="bi bi-trophy-fill me-2"></i>Top Performer
                                        </h4>
                                        @if(!empty($team['top_performer']))
                                            <div class="mb-2">
                                                <strong>Name:</strong> <span class="text-dark fw-bold">{{ $team['top_performer']['employee_name'] ?? 'N/A' }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Score:</strong> <span class="badge bg-success">{{ isset($team['top_performer']['score']) ? $team['top_performer']['score'] . '%' : 'N/A' }}</span>
                                            </div>
                                            <div>
                                                <strong>Reason:</strong> <span class="muted-text small">{{ $team['top_performer']['reason'] ?? 'N/A' }}</span>
                                            </div>
                                        @else
                                            <span class="muted-text">N/A</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Attention Required -->
                                <div class="col-md-4">
                                    <div class="p-3 rounded border" style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.15) !important; height: 100%;">
                                        <h4 style="font-size: 14px; color: var(--orange); font-weight: 700; margin-bottom: 12px;">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Attention Required
                                        </h4>
                                        @if(!empty($team['attention_required']))
                                            @foreach($team['attention_required'] as $emp)
                                                <div class="mb-2">
                                                    <div class="fw-bold text-dark">{{ $emp['employee_name'] ?? 'N/A' }}</div>
                                                    <div class="muted-text small">{{ $emp['reason'] ?? 'N/A' }}</div>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="muted-text">No attention required.</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Team Risks -->
                                <div class="col-md-4">
                                    <div class="p-3 rounded border" style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.15) !important; height: 100%;">
                                        <h4 style="font-size: 14px; color: var(--red); font-weight: 700; margin-bottom: 12px;">
                                            <i class="bi bi-shield-fill-x me-2"></i>Team Risks
                                        </h4>
                                        @if(!empty($team['risks']))
                                            <ul class="ps-3 mb-0 muted-text small" style="list-style-type: disc;">
                                                @foreach($team['risks'] as $risk)
                                                    <li class="mb-1">{{ $risk }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="muted-text">No risks identified.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(!empty($report['leadership_summary']))
                <div class="report-section" style="margin-top: 16px; min-height: auto;">
                    <h2 style="color: var(--purple-2);"><i class="bi bi-graph-up-arrow me-2"></i>Leadership Summary</h2>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <div class="p-3 rounded border text-center" style="background: rgba(37, 99, 235, 0.05);">
                                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--text);">Top Performer</h4>
                                <p class="mb-0 text-primary fw-bold" style="font-size: 18px;">{{ $report['leadership_summary']['top_performer'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border text-center" style="background: rgba(16, 185, 129, 0.05);">
                                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--text);">Org Health</h4>
                                <p class="mb-0 text-success fw-bold" style="font-size: 18px;">{{ $report['leadership_summary']['organization_health_score'] ?? 'N/A' }}%</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border text-center" style="background: rgba(245, 158, 11, 0.05);">
                                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--text);">Attention Needed</h4>
                                <p class="mb-0 text-warning fw-bold" style="font-size: 18px;">
                                    {{ ($report['leadership_summary']['employees_attention_count'] ?? 0) + ($report['leadership_summary']['teams_attention_count'] ?? 0) }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded border text-center" style="background: rgba(239, 68, 68, 0.05);">
                                <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--text);">High Risk Projects</h4>
                                <p class="mb-0 text-danger fw-bold" style="font-size: 18px;">{{ $report['leadership_summary']['high_risk_projects_count'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($report['ai_recommendations']))
                <div class="report-section" style="margin-top: 16px; min-height: auto;">
                    <h2 style="color: var(--blue);"><i class="bi bi-magic me-2"></i>AI Recommendations</h2>
                    <div class="row g-3 mt-2">
                        @foreach($report['ai_recommendations'] as $rec)
                            <div class="col-md-6">
                                <div class="p-3 rounded border" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border-left: 4px solid var(--purple) !important; height: 100%;">
                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--purple); font-weight: 600; display: block; margin-bottom: 4px;">{{ $rec['type'] ?? 'Recommendation' }}</span>
                                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 8px; color: var(--text);">{{ $rec['title'] ?? '' }}</h4>
                                    <p class="mb-0 text-muted small" style="line-height: 1.5;">{{ $rec['description'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="report-actions">
                <a href="{{ url('/reports') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Back to History
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ url('/reports/' . $reportModel->id . '/export') }}"
                        class="btn btn-success d-inline-flex align-items-center text-decoration-none px-3"
                        style="border-radius: 8px; min-height: 40px;">
                        <i class="bi bi-download me-2"></i> Export Report
                    </a>
                    <a href="{{ url('/') }}" class="btn-ai d-inline-flex align-items-center text-decoration-none">
                        <i class="bi bi-grid-1x2 me-2"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
