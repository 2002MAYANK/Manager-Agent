@extends('layout')

@section('title', 'Dashboard | AI Manager')
@section('page-title', 'Dashboard')

@push('styles')
    <style>
        .text-purple {
            color: var(--purple);
        }

        .dashboard-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-bottom: 28px;
        }

        .dashboard-hero h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 6px;
            color: var(--text);
        }

        .stat-card {
            min-height: 120px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--purple);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--purple-2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon.purple {
            color: var(--purple);
            background: rgba(37, 99, 235, .1);
        }

        .stat-icon.green {
            color: var(--green);
            background: rgba(16, 185, 129, .1);
        }

        .stat-icon.blue {
            color: var(--purple-2);
            background: rgba(59, 130, 246, .1);
        }

        .stat-icon.orange {
            color: var(--orange);
            background: rgba(245, 158, 11, .1);
        }

        .stat-icon.pink {
            color: #EC4899;
            background: rgba(236, 72, 153, .1);
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        .stat-number {
            font-size: 26px;
            line-height: 1.1;
            font-weight: 800;
            margin: 4px 0 6px;
            color: var(--text);
        }

        .trend {
            font-size: 12px;
            color: var(--muted);
        }

        .trend.up strong {
            color: #047857;
        }

        .trend.down strong {
            color: var(--red);
        }

        .chart-card {
            min-height: 340px;
            padding: 24px 28px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: var(--text);
        }

        .chart-select {
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background: var(--panel-bg);
            color: var(--text);
            height: 38px;
            padding: 0 15px;
            font-size: 13px;
        }

        .line-chart {
            position: relative;
            height: 230px;
            margin-top: 24px;
            padding-left: 50px;
            background-image: linear-gradient(to bottom, rgba(148, 163, 184, .08) 1px, transparent 1px);
            background-size: 100% 25%;
        }

        .y-axis {
            position: absolute;
            left: 0;
            top: -8px;
            bottom: 0;
            width: 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: var(--muted);
            font-size: 12px;
        }

        .chart-area {
            position: absolute;
            left: 50px;
            right: 0;
            top: 0;
            bottom: 28px;
        }

        .chart-area svg {
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .chart-labels {
            position: absolute;
            left: 50px;
            right: 0;
            bottom: 0;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        .chart-tooltip {
            position: absolute;
            right: 14px;
            top: 5px;
            padding: 6px 12px;
            border-radius: 6px;
            background: linear-gradient(135deg, var(--purple), var(--purple-2));
            color: #fff;
            font-weight: 700;
            font-size: 12px;
        }

        .donut-wrap {
            min-height: 230px;
            display: flex;
            align-items: center;
            gap: 32px;
            margin-top: 24px;
        }

        .donut {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
        }

        .donut::before {
            content: "";
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--panel-bg);
            box-shadow: inset 0 0 12px rgba(0, 0, 0, .05);
        }

        .donut-center {
            position: absolute;
            text-align: center;
        }

        .legend-row {
            display: grid;
            grid-template-columns: 12px 1fr auto;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text);
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .list-card {
            padding: 22px 26px 24px;
            min-height: 304px;
        }

        .list-card.success {
            border-top: 4px solid var(--green) !important;
        }

        .list-card.warning {
            border-top: 4px solid var(--orange) !important;
        }

        .list-card.danger {
            border-top: 4px solid var(--red) !important;
        }

        .mini-btn {
            height: 32px;
            border: 1px solid var(--panel-border);
            color: var(--muted);
            background: var(--panel-bg);
            border-radius: 8px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .mini-btn:hover {
            color: var(--text);
            background: var(--panel-soft);
            border-color: var(--muted);
        }

        .person-row {
            display: grid;
            grid-template-columns: 24px 42px 1fr 46px;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid var(--panel-border);
            transition: all 0.2s ease;
            color: var(--text);
        }

        .person-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .progress {
            height: 6px;
            background: var(--panel-soft);
        }

        .progress-bar.bg-success {
            background: var(--green) !important;
        }

        .progress-bar.bg-warning {
            background: var(--orange) !important;
        }

        .risk-row {
            display: grid;
            grid-template-columns: 12px 1fr auto;
            gap: 16px;
            align-items: start;
            padding: 12px 0;
            border-bottom: 1px solid var(--panel-border);
            transition: all 0.2s ease;
            color: var(--text);
        }

        .risk-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .risk-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 6px;
            background: var(--red);
        }

        .empty-state {
            min-height: 166px;
            display: grid;
            place-items: center;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .dashboard-hero {
                align-items: flex-start;
                flex-direction: column;
            }

            .donut-wrap {
                flex-direction: column;
                align-items: flex-start;
                gap: 24px;
            }
        }

        @media (max-width: 575.98px) {
            .dashboard-hero h1 {
                font-size: 24px;
            }

            .stat-card {
                min-height: auto;
                padding: 18px;
            }

            .date-pill,
            .btn-ai {
                width: 100%;
                justify-content: center;
            }

            .person-row {
                grid-template-columns: 20px 36px 1fr 42px;
                gap: 10px;
            }
        }

        /* Executive Metrics & Insights Custom Styles */
        .exec-stat-card {
            background: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .exec-stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--purple-2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .exec-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .exec-stat-icon.purple {
            color: var(--purple);
            background: rgba(37, 99, 235, .08);
        }

        .exec-stat-icon.green {
            color: var(--green);
            background: rgba(16, 185, 129, .08);
        }

        .exec-stat-icon.blue {
            color: var(--purple-2);
            background: rgba(59, 130, 246, .08);
        }

        .exec-stat-icon.orange {
            color: var(--orange);
            background: rgba(245, 158, 11, .08);
        }

        .exec-stat-icon.pink {
            color: #EC4899;
            background: rgba(236, 72, 153, .08);
        }

        .exec-card-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--panel-soft);
        }

        .exec-card-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .exec-card-item:first-child {
            padding-top: 0;
        }

        .exec-card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
@endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="dashboard-hero">
        <div>
            <h1>Welcome back, {{ $managerName }}!</h1>
            <div class="muted-text">Here's what's happening with your projects today.</div>
        </div>

        <div class="d-flex flex-wrap gap-3">
            <a href="{{ url('/export-data') }}" class="btn btn-success d-inline-flex align-items-center px-4" style="border-radius: 24px; min-height: 40px;">
                <i class="bi bi-download me-2"></i> Export Data
            </a>
            <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center px-4" style="border-radius: 24px; min-height: 40px;" data-bs-toggle="modal" data-bs-target="#importDataModal">
                <i class="bi bi-upload me-2"></i> Import Data
            </button>
            <button type="button" class="btn-ai d-inline-flex align-items-center px-4" style="border-radius: 24px;" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                <i class="bi bi-stars me-2"></i> Generate Report
            </button>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-sm-6 col-xl">
            <div class="glass-card stat-card">
                <div class="stat-icon purple"><i class="bi bi-list-check"></i></div>
                <div>
                    <div class="stat-label">Total Tasks</div>
                    <div class="stat-number">{{ $stats['total_tasks']['value'] }}</div>
                    <x-dashboard-trend :change="$stats['total_tasks']['change']" />
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="glass-card stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="stat-label">Completed Tasks</div>
                    <div class="stat-number">{{ $stats['completed_tasks']['value'] }}</div>
                    <x-dashboard-trend :change="$stats['completed_tasks']['change']" />
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="glass-card stat-card">
                <div class="stat-icon blue"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Team Productivity</div>
                    <div class="stat-number">{{ $stats['team_productivity']['value'] }}%</div>
                    <x-dashboard-trend :change="$stats['team_productivity']['change']" />
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="glass-card stat-card">
                <div class="stat-icon orange"><i class="bi bi-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Tasks</div>
                    <div class="stat-number">{{ $stats['pending_tasks']['value'] }}</div>
                    <x-dashboard-trend :change="$stats['pending_tasks']['change']" good-when-negative />
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="glass-card stat-card">
                <div class="stat-icon pink"><i class="bi bi-calendar2"></i></div>
                <div>
                    <div class="stat-label">Meetings</div>
                    <div class="stat-number">{{ $stats['meetings']['value'] }}</div>
                    <x-dashboard-trend :change="$stats['meetings']['change']" suffix="" />
                </div>
            </div>
        </div>
    </div>

    <!-- Executive Insights Section -->
    <div class="mt-5 mb-3">
        <h2 class="section-title"><i class="bi bi-shield-check text-purple me-2"></i>Executive Insights</h2>
    </div>

    <!-- Executive KPI Cards -->
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="exec-stat-card">
                <div class="exec-stat-icon purple"><i class="bi bi-person-badge"></i></div>
                <div>
                    <div class="stat-label">Employees</div>
                    <div class="stat-number fs-5 mt-1 mb-0 fw-bold">{{ $totalEmployeesCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="exec-stat-card">
                <div class="exec-stat-icon blue"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Teams</div>
                    <div class="stat-number fs-5 mt-1 mb-0 fw-bold">{{ $totalTeamsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="exec-stat-card">
                <div class="exec-stat-icon green"><i class="bi bi-briefcase"></i></div>
                <div>
                    <div class="stat-label">Projects</div>
                    <div class="stat-number fs-5 mt-1 mb-0 fw-bold">{{ $totalProjectsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="exec-stat-card">
                <div class="exec-stat-icon orange"><i class="bi bi-list-task"></i></div>
                <div>
                    <div class="stat-label">Tasks (All)</div>
                    <div class="stat-number fs-5 mt-1 mb-0 fw-bold">{{ $stats['total_tasks']['value'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="exec-stat-card">
                <div class="exec-stat-icon pink"><i class="bi bi-github"></i></div>
                <div>
                    <div class="stat-label">Commits</div>
                    <div class="stat-number fs-5 mt-1 mb-0 fw-bold">{{ $totalCommitsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="exec-stat-card">
                <div class="exec-stat-icon purple"><i class="bi bi-heart-pulse"></i></div>
                <div>
                    <div class="stat-label">Health Score</div>
                    <div class="stat-number fs-5 mt-1 mb-0 fw-bold">{{ $orgHealthScore }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Dashboard Sections -->
    <div class="row g-3 g-xl-4 mb-4">
        <!-- Leadership Snapshot -->
        <div class="col-md-6 col-xl-3">
            <div class="glass-card p-4" style="min-height: 250px;">
                <h3 class="exec-card-title"><i class="bi bi-award text-purple"></i> Leadership Snapshot</h3>
                <div class="d-flex flex-column">
                    <div class="exec-card-item">
                        <span class="muted-text small">Top Performer</span>
                        <span class="fw-semibold text-dark">{{ $topPerformerName }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">Best Team</span>
                        <span class="fw-semibold text-dark">{{ $bestPerformingTeamName }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">Requires Attention</span>
                        <span class="fw-semibold text-warning">{{ $employeesAttentionCount }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">High Risk Projects</span>
                        <span class="fw-semibold text-danger">{{ $highRiskProjectsCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- GitLab Overview -->
        <div class="col-md-6 col-xl-3">
            <div class="glass-card p-4" style="min-height: 250px;">
                <h3 class="exec-card-title"><i class="bi bi-git text-danger"></i> GitLab Overview</h3>
                <div class="d-flex flex-column">
                    <div class="exec-card-item">
                        <span class="muted-text small">Repos Synced</span>
                        <span class="fw-semibold text-dark">{{ $totalReposSynced }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">Total Commits</span>
                        <span class="fw-semibold text-dark">{{ $totalCommitsCount }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">Top Contributor</span>
                        <span class="fw-semibold text-dark text-truncate" style="max-width: 120px;" title="{{ $topContributorName }}">{{ $topContributorName }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">Last Sync</span>
                        <span class="fw-semibold text-dark small text-end">{{ $lastSyncTime }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Overview -->
        <div class="col-md-6 col-xl-3">
            <div class="glass-card p-4" style="min-height: 250px;">
                <h3 class="exec-card-title"><i class="bi bi-kanban text-success"></i> Project Overview</h3>
                <div class="d-flex flex-column">
                    <div class="exec-card-item">
                        <span class="muted-text small">Planning</span>
                        <span class="fw-semibold text-dark">{{ $projectStatsObj->planning ?? 0 }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">In Progress</span>
                        <span class="fw-semibold text-primary">{{ $projectStatsObj->in_progress ?? 0 }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">Completed</span>
                        <span class="fw-semibold text-success">{{ $projectStatsObj->completed ?? 0 }}</span>
                    </div>
                    <div class="exec-card-item">
                        <span class="muted-text small">On Hold</span>
                        <span class="fw-semibold text-warning">{{ $projectStatsObj->on_hold ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Recommendations -->
        <div class="col-md-6 col-xl-3">
            <div class="glass-card p-4" style="min-height: 250px; border-top: 4px solid var(--purple);">
                <h3 class="exec-card-title"><i class="bi bi-stars text-warning"></i> AI Recommendations</h3>
                <div class="d-flex flex-column gap-2">
                    <div class="p-2 rounded-2" style="background: rgba(37, 99, 235, 0.03); border-left: 3px solid var(--purple);">
                        <div class="fw-semibold text-dark small" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Productivity</div>
                        <div class="text-muted mt-1" style="font-size: 12px; line-height: 1.3;">{{ $aiRecommendations['productivity'] }}</div>
                    </div>
                    <div class="p-2 rounded-2" style="background: rgba(16, 185, 129, 0.03); border-left: 3px solid var(--green);">
                        <div class="fw-semibold text-dark small" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Team</div>
                        <div class="text-muted mt-1" style="font-size: 12px; line-height: 1.3;">{{ $aiRecommendations['team'] }}</div>
                    </div>
                    <div class="p-2 rounded-2" style="background: rgba(245, 158, 11, 0.03); border-left: 3px solid var(--orange);">
                        <div class="fw-semibold text-dark small" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Project Risk</div>
                        <div class="text-muted mt-1" style="font-size: 12px; line-height: 1.3;">{{ $aiRecommendations['project'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-xl-7">
            <div class="glass-card chart-card">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <h2 class="section-title">Team Productivity Overview</h2>
                    <select class="chart-select" aria-label="Chart range">
                        <option>Last 7 Days</option>
                    </select>
                </div>

                <div class="line-chart">
                    <div class="y-axis">
                        <span>100%</span>
                        <span>75%</span>
                        <span>50%</span>
                        <span>25%</span>
                        <span>0%</span>
                    </div>
                    <div class="chart-area">
                        <svg viewBox="0 0 700 190" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="lineFill" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="var(--purple)" stop-opacity=".2" />
                                    <stop offset="100%" stop-color="var(--purple)" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <polygon points="{{ $chart['area_points'] }}" fill="url(#lineFill)" />
                            <polyline points="{{ $chart['line_points'] }}" fill="none" stroke="var(--purple)"
                                stroke-width="4" />
                            <g fill="#fff" stroke="var(--purple)" stroke-width="4">
                                @foreach (explode(' ', $chart['line_points']) as $point)
                                    @php
                                        $coordinates = explode(',', $point);
                                    @endphp
                                    <circle cx="{{ $coordinates[0] }}" cy="{{ $coordinates[1] }}" r="6" />
                                @endforeach
                            </g>
                        </svg>
                        <div class="chart-tooltip">{{ $chart['latest'] }}%</div>
                    </div>
                    <div class="chart-labels">
                        @foreach ($chart['labels'] as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="glass-card chart-card">
                <h2 class="section-title mb-4">Task Status Distribution</h2>
                <div class="donut-wrap">
                    <div class="position-relative">
                        <div class="donut" style="background: {{ $statusDistribution['donut_style'] }}"></div>
                        <div class="donut-center top-50 start-50 translate-middle">
                            <div class="fs-2 fw-bold lh-1">{{ $statusDistribution['total'] }}</div>
                            <div class="muted-text">Total</div>
                        </div>
                    </div>

                    <div class="flex-grow-1 w-100">
                        <div class="legend-row">
                            <span class="legend-dot" style="background: var(--green);"></span>
                            <span>Completed</span>
                            <span>{{ $statusDistribution['completed']['count'] }}
                                ({{ $statusDistribution['completed']['percent'] }}%)</span>
                        </div>
                        <div class="legend-row">
                            <span class="legend-dot" style="background: var(--blue);"></span>
                            <span>In Progress</span>
                            <span>{{ $statusDistribution['in_progress']['count'] }}
                                ({{ $statusDistribution['in_progress']['percent'] }}%)</span>
                        </div>
                        <div class="legend-row mb-0">
                            <span class="legend-dot" style="background: var(--orange);"></span>
                            <span>Pending</span>
                            <span>{{ $statusDistribution['pending']['count'] }}
                                ({{ $statusDistribution['pending']['percent'] }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-xl-4">
        <div class="col-xl-4">
            <div class="glass-card list-card success">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="section-title text-success"><i class="bi bi-trophy me-3"></i>Top Performers</h2>
                    <button type="button" class="mini-btn">View all</button>
                </div>

                @forelse ($topPerformers as $index => $person)
                    <div class="person-row">
                        <div>{{ $index + 1 }}</div>
                        <img class="avatar" src="{{ $person['avatar'] }}" alt="{{ $person['name'] }}">
                        <div>
                            <div>{{ $person['name'] }}</div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-success" style="width: {{ $person['score'] }}%"></div>
                            </div>
                        </div>
                        <div class="text-end">{{ $person['score'] }}%</div>
                    </div>
                @empty
                    <div class="empty-state">No employee performance data yet.</div>
                @endforelse
            </div>
        </div>

        <div class="col-xl-4">
            <div class="glass-card list-card warning">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="section-title" style="color: var(--orange);"><i
                            class="bi bi-exclamation-triangle me-3"></i>Attention Required</h2>
                    <button type="button" class="mini-btn">View all</button>
                </div>

                @forelse ($attentionRequired as $index => $person)
                    <div class="person-row">
                        <div>{{ $index + 1 }}</div>
                        <img class="avatar" src="{{ $person['avatar'] }}" alt="{{ $person['name'] }}">
                        <div>
                            <div>{{ $person['name'] }}</div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-warning" style="width: {{ $person['score'] }}%"></div>
                            </div>
                        </div>
                        <div class="text-end">{{ $person['score'] }}%</div>
                    </div>
                @empty
                    <div class="empty-state">No attention items found.</div>
                @endforelse
            </div>
        </div>

        <div class="col-xl-4">
            <div class="glass-card list-card danger">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="section-title text-danger"><i class="bi bi-shield-exclamation me-3"></i>Recent Risks</h2>
                    <button type="button" class="mini-btn">View all</button>
                </div>

                @forelse ($risks as $risk)
                    @php
                        $impact = strtolower($risk['impact']);
                        $impactStyle = str_contains($impact, 'high')
                            ? 'text-danger'
                            : (str_contains($impact, 'low')
                                ? 'text-success'
                                : '');
                    @endphp
                    <div class="risk-row">
                        <span class="risk-dot"></span>
                        <div>
                            <div>{{ $risk['title'] }}</div>
                            <div class="{{ $impactStyle }}"
                                @if (!$impactStyle) style="color: var(--orange);" @endif>
                                {{ $risk['impact'] }}
                            </div>
                        </div>
                        <div class="muted-text small">{{ $risk['time'] }}</div>
                    </div>
                @empty
                    <div class="empty-state">No current risks found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Import Data Modal -->
    <div class="modal fade" id="importDataModal" tabindex="-1" aria-labelledby="importDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card" style="background: var(--panel-bg);">
                <form action="{{ url('/import-data') }}" method="POST" enctype="multipart/form-data" id="importDataForm">
                     @csrf
                     <div class="modal-header border-bottom-0 pb-0">
                         <h5 class="modal-title fw-bold" id="importDataModalLabel">
                             <i class="bi bi-upload me-2" style="color: var(--purple);"></i>Import Data
                         </h5>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                     </div>
                     <div class="modal-body text-dark">
                         <div class="p-3 rounded mb-3" style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.15);">
                             <div class="fw-bold mb-2 text-primary"><i class="bi bi-info-circle me-2"></i>Import Format</div>
                             <div class="small muted-text">Upload a <strong class="text-dark">CSV file</strong> with the following columns:</div>
                             <ul class="small muted-text mt-2 mb-0">
                                 <li><span class="text-dark fw-bold">Employee Name</span> — Full name of the employee</li>
                                 <li><span class="text-dark fw-bold">Tasks</span> — Number of tasks</li>
                                 <li><span class="text-dark fw-bold">Meetings Attended</span> — Number of meetings</li>
                                 <li><span class="text-dark fw-bold">Attendence Count</span> — Number of attendance records</li>
                                 <li><span class="text-dark fw-bold">Commits Count</span> — Number of commits</li>
                             </ul>
                         </div>

                         <div class="p-3 rounded mb-3" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.15);">
                             <div class="small text-warning" style="color: #b45309 !important;"><i class="bi bi-lightbulb me-2"></i><strong>Tip:</strong> Use the <strong>Export Data</strong> button first to get the exact CSV format, then modify and re-import.</div>
                         </div>

                         <div class="mb-3">
                             <label class="form-label" for="importFile">CSV File</label>
                             <input class="form-control" type="file" id="importFile" name="import_file" accept=".csv" required>
                             <div class="form-text muted-text">Maximum file size: 10 MB</div>
                         </div>
                     </div>
                     <div class="modal-footer border-top-0 pt-0">
                         <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                         <button type="submit" class="btn-ai" id="btnImportData">
                             <span id="btnImportText"><i class="bi bi-upload me-2"></i>Import Data</span>
                             <div class="spinner-border spinner-border-sm d-none ms-2" id="btnImportSpinner" role="status"></div>
                         </button>
                     </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card" style="background: var(--panel-bg);">
                <form action="{{ url('/generate-report') }}" method="POST" id="generateReportForm">
                    @csrf
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="generateReportModalLabel">Generate AI Performance Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-dark">
                        <div class="mb-3">
                            <label class="form-label">Report Period</label>
                            <select class="form-select mb-3" id="quickPeriodSelect">
                                <option value="7">Last 7 Days</option>
                                <option value="30">Last 30 Days</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        
                        <div class="row g-3 mb-4 d-none" id="customDateRange">
                            <div class="col-6">
                                <label class="form-label small">Start Date</label>
                                <input type="date" class="form-control form-control-sm" name="start_date" id="startDate">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">End Date</label>
                                <input type="date" class="form-control form-control-sm" name="end_date" id="endDate">
                            </div>
                        </div>

                        <div class="p-3 rounded mb-2" style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.15);">
                            <div class="fw-bold mb-2 text-primary">Report Preview</div>
                            <div class="small muted-text mb-3">Selected Period:<br><span id="previewPeriod" class="text-dark fw-bold"></span></div>
                            
                            <div class="small fw-bold mb-2">Records Found:</div>
                            <ul class="list-unstyled small muted-text mb-0" id="previewCounts">
                                <li>Employees: <span id="countEmployees" class="text-dark fw-bold">...</span></li>
                                <li>Tasks: <span id="countTasks" class="text-dark fw-bold">...</span></li>
                                <li>Attendance Records: <span id="countAttendence" class="text-dark fw-bold">...</span></li>
                                <li>Commit Logs: <span id="countCommits" class="text-dark fw-bold">...</span></li>
                                <li>Meetings: <span id="countMeetings" class="text-dark fw-bold">...</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-ai" id="btnGenerateReport">
                            <span id="btnGenerateText">Generate AI Report</span>
                            <div class="spinner-border spinner-border-sm d-none ms-2" id="btnGenerateSpinner" role="status"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('quickPeriodSelect');
    const customDateRange = document.getElementById('customDateRange');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const previewPeriod = document.getElementById('previewPeriod');
    const form = document.getElementById('generateReportForm');
    
    const counts = {
        emp: document.getElementById('countEmployees'),
        tasks: document.getElementById('countTasks'),
        att: document.getElementById('countAttendence'),
        com: document.getElementById('countCommits'),
        meet: document.getElementById('countMeetings')
    };

    function formatDate(date) {
        // Adjust for timezone offset to prevent picking the wrong local date
        const d = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
        return d.toISOString().split('T')[0];
    }

    function formatDisplayDate(dateStr) {
        if (!dateStr) return '';
        // Create date ensuring local interpretation
        const d = new Date(dateStr + 'T12:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }

    function updateDates() {
        const val = periodSelect.value;
        const today = new Date();
        let start = new Date();
        let end = new Date();

        if (val === 'custom') {
            customDateRange.classList.remove('d-none');
        } else {
            customDateRange.classList.add('d-none');
            
            if (val === '7') {
                start.setDate(today.getDate() - 6);
            } else if (val === '30') {
                start.setDate(today.getDate() - 29);
            } else if (val === 'this_month') {
                start = new Date(today.getFullYear(), today.getMonth(), 1);
            } else if (val === 'last_month') {
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
            }
            
            startDateInput.value = formatDate(start);
            endDateInput.value = formatDate(end);
        }
        
        fetchPreview();
    }

    function fetchPreview() {
        const start = startDateInput.value;
        const end = endDateInput.value;
        
        if (!start || !end) return;

        previewPeriod.textContent = `${formatDisplayDate(start)} - ${formatDisplayDate(end)}`;
        
        Object.values(counts).forEach(el => el.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>');

        fetch('{{ url('/reports/preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ start_date: start, end_date: end })
        })
        .then(res => res.json())
        .then(data => {
            counts.emp.textContent = data.employees || 0;
            counts.tasks.textContent = data.tasks || 0;
            counts.att.textContent = data.attendences || 0;
            counts.com.textContent = data.commits || 0;
            counts.meet.textContent = data.meetings || 0;
        })
        .catch(err => {
            console.error(err);
            Object.values(counts).forEach(el => el.textContent = 'Error');
        });
    }

    periodSelect.addEventListener('change', updateDates);
    startDateInput.addEventListener('change', fetchPreview);
    endDateInput.addEventListener('change', fetchPreview);

    document.getElementById('generateReportModal').addEventListener('shown.bs.modal', function () {
        updateDates();
    });

    form.addEventListener('submit', function() {
        document.getElementById('btnGenerateText').textContent = 'Generating...';
        document.getElementById('btnGenerateSpinner').classList.remove('d-none');
        document.getElementById('btnGenerateReport').disabled = true;
    });

    // Import Data form submit
    const importForm = document.getElementById('importDataForm');
    if (importForm) {
        importForm.addEventListener('submit', function() {
            document.getElementById('btnImportText').innerHTML = 'Importing...';
            document.getElementById('btnImportSpinner').classList.remove('d-none');
            document.getElementById('btnImportData').disabled = true;
        });
    }
});
</script>
@endpush
