@extends('layout')

@section('title', 'Leadership Insights | AI Manager')
@section('page-title', 'Leadership Insights')

@push('styles')
<style>
    .kpi-card {
        padding: 24px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid var(--panel-border);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }
    .kpi-value {
        font-size: 32px;
        font-weight: 800;
        margin: 16px 0 4px 0;
        color: var(--text);
    }
    .kpi-label {
        color: var(--muted);
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .kpi-icon.primary { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
    .kpi-icon.success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
    .kpi-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .kpi-icon.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    
    .section-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .table-container {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--panel-border);
        overflow: hidden;
    }
    
    .recommendation-card {
        padding: 24px;
        border-radius: 16px;
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border: 1px solid var(--panel-border);
        margin-bottom: 16px;
        border-left: 4px solid var(--purple);
    }
    .recommendation-title {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 8px;
        color: var(--text);
    }
    .recommendation-type {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--purple);
        font-weight: 600;
        margin-bottom: 4px;
        display: inline-block;
    }
    
    .badge-excellent { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
    .badge-good { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .badge-average { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .badge-needs-improvement { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
</style>
@endpush

@section('content')

<!-- Section 6: Leadership Summary -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <h2 class="section-title"><i class="bi bi-graph-up-arrow text-primary"></i> Executive Summary</h2>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-icon success"><i class="bi bi-heart-pulse-fill"></i></div>
            </div>
            <div>
                <div class="kpi-value">{{ $summary['health_score'] }}%</div>
                <div class="kpi-label">Org Health Score</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-icon primary"><i class="bi bi-trophy-fill"></i></div>
            </div>
            <div>
                <div class="kpi-value" style="font-size: 24px; line-height: 38px;">{{ $summary['top_performer'] }}</div>
                <div class="kpi-label">Top Performer</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-icon warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
            </div>
            <div>
                <div class="kpi-value">{{ $summary['employees_attention'] }}</div>
                <div class="kpi-label">Employees Need Attention</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-icon danger"><i class="bi bi-shield-fill-x"></i></div>
            </div>
            <div>
                <div class="kpi-value">{{ $summary['high_risk_projects'] }}</div>
                <div class="kpi-label">High Risk Projects</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Section 1: Top Performers -->
    <div class="col-lg-6">
        <h2 class="section-title"><i class="bi bi-star-fill text-warning"></i> Top 10 Employees</h2>
        <div class="table-container p-3">
            <table class="table table-hover align-middle mb-0" id="topPerformersTable">
                <thead>
                    <tr>
                        <th class="text-muted small text-uppercase">Rank</th>
                        <th class="text-muted small text-uppercase">Employee</th>
                        <th class="text-muted small text-uppercase">Score</th>
                        <th class="text-muted small text-uppercase">Badge</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topPerformers as $index => $employee)
                    <tr>
                        <td class="fw-bold text-muted">#{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $employee->name }}</div>
                            <div class="small text-muted">{{ $employee->team_name ?? 'No Team' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $employee->performance_score }}%</div>
                            <div class="small text-muted">{{ $employee->completed_tasks }} tasks, {{ $employee->attendance_percentage }}% att.</div>
                        </td>
                        <td>
                            @php
                                $badgeClass = 'badge-average';
                                if($employee->badge == 'Excellent') $badgeClass = 'badge-excellent';
                                elseif($employee->badge == 'Good') $badgeClass = 'badge-good';
                                elseif($employee->badge == 'Needs Improvement') $badgeClass = 'badge-needs-improvement';
                            @endphp
                            <span class="badge rounded-pill {{ $badgeClass }} border-0 px-3 py-2">{{ $employee->badge }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Best Performing Teams -->
    <div class="col-lg-6">
        <h2 class="section-title"><i class="bi bi-people-fill text-primary"></i> Team Productivity</h2>
        <div class="table-container p-3">
            <table class="table table-hover align-middle mb-0" id="teamsTable">
                <thead>
                    <tr>
                        <th class="text-muted small text-uppercase">Team</th>
                        <th class="text-muted small text-uppercase">Score</th>
                        <th class="text-muted small text-uppercase">Metrics</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teamPerformance as $index => $team)
                    <tr class="{{ $index === 0 ? 'bg-light' : '' }}">
                        <td>
                            <div class="fw-bold">
                                {{ $team['team_name'] }}
                                @if($index === 0)
                                    <i class="bi bi-trophy-fill text-warning ms-1" title="Best Performing Team"></i>
                                @endif
                            </div>
                            <div class="small text-muted">{{ $team['members'] }} Members</div>
                        </td>
                        <td>
                            <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">{{ $team['productivity_score'] }}%</span>
                        </td>
                        <td>
                            <div class="small text-muted">
                                {{ $team['completed_tasks'] }} tasks, {{ $team['total_commits'] }} commits<br>
                                {{ $team['attendance_percentage'] }}% attendance
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Section 3: Employees Requiring Attention -->
    <div class="col-lg-6">
        <h2 class="section-title"><i class="bi bi-person-exclamation text-danger"></i> Employees Requiring Attention</h2>
        <div class="table-container p-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-muted small text-uppercase">Employee</th>
                        <th class="text-muted small text-uppercase">Risk</th>
                        <th class="text-muted small text-uppercase">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeesAttention as $emp)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $emp['name'] }}</div>
                            <div class="small text-muted">{{ $emp['team'] }}</div>
                        </td>
                        <td>
                            @php
                                $riskClass = 'bg-secondary';
                                if($emp['risk_level'] == 'High') $riskClass = 'bg-danger';
                                elseif($emp['risk_level'] == 'Medium') $riskClass = 'bg-warning text-dark';
                                elseif($emp['risk_level'] == 'Low') $riskClass = 'bg-info text-dark';
                            @endphp
                            <span class="badge {{ $riskClass }} rounded-pill px-3">{{ $emp['risk_level'] }}</span>
                        </td>
                        <td><div class="small text-muted">{{ $emp['reason'] }}</div></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No employees require immediate attention. Great!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 4: Teams Requiring Attention -->
    <div class="col-lg-6">
        <h2 class="section-title"><i class="bi bi-exclamation-octagon text-danger"></i> Teams Requiring Attention</h2>
        <div class="table-container p-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-muted small text-uppercase">Team</th>
                        <th class="text-muted small text-uppercase">Risk</th>
                        <th class="text-muted small text-uppercase">Recommended Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teamsAttention as $team)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $team['team_name'] }}</div>
                            <div class="small text-muted">{{ $team['issue'] }}</div>
                        </td>
                        <td>
                            @php
                                $riskClass = 'bg-secondary';
                                if($team['risk_level'] == 'High') $riskClass = 'bg-danger';
                                elseif($team['risk_level'] == 'Medium') $riskClass = 'bg-warning text-dark';
                                elseif($team['risk_level'] == 'Low') $riskClass = 'bg-info text-dark';
                            @endphp
                            <span class="badge {{ $riskClass }} rounded-pill px-3">{{ $team['risk_level'] }}</span>
                        </td>
                        <td><div class="small text-muted fw-bold">{{ $team['recommended_action'] }}</div></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No teams require immediate attention. Excellent!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Section 5: Project Risks -->
    <div class="col-lg-6">
        <h2 class="section-title"><i class="bi bi-kanban text-purple"></i> Project Risks</h2>
        <div class="table-container p-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-muted small text-uppercase">Project</th>
                        <th class="text-muted small text-uppercase">Risk</th>
                        <th class="text-muted small text-uppercase">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectRisks as $project)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $project['project_name'] }}</div>
                            <div class="small text-muted">{{ $project['assigned_team'] }}</div>
                        </td>
                        <td>
                            @php
                                $riskClass = 'bg-secondary';
                                if($project['risk_level'] == 'High') $riskClass = 'bg-danger';
                                elseif($project['risk_level'] == 'Medium') $riskClass = 'bg-warning text-dark';
                                elseif($project['risk_level'] == 'Low') $riskClass = 'bg-info text-dark';
                            @endphp
                            <span class="badge {{ $riskClass }} rounded-pill px-3">{{ $project['risk_level'] }}</span>
                        </td>
                        <td><div class="small text-muted">{{ $project['risk_reason'] }}</div></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No high-risk projects identified.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 7: AI Recommendations -->
    <div class="col-lg-6">
        <h2 class="section-title">
            <i class="bi bi-magic" style="background: -webkit-linear-gradient(45deg, #8b5cf6, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i> 
            AI Recommendations
        </h2>
        <div>
            @forelse($aiRecommendations as $rec)
                <div class="recommendation-card shadow-sm">
                    <span class="recommendation-type">{{ $rec['type'] ?? 'Recommendation' }}</span>
                    <h4 class="recommendation-title">{{ $rec['title'] ?? '' }}</h4>
                    <p class="mb-0 text-muted small">{{ $rec['description'] ?? '' }}</p>
                </div>
            @empty
                <div class="text-muted">AI is currently analyzing the data... Please check back shortly.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Section 8: GitLab Commit Insights -->
    <div class="col-12">
        <h2 class="section-title"><i class="bi bi-git" style="color: #fc6d26;"></i> GitLab Commit Insights</h2>
    </div>

    <div class="col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-icon primary"><i class="bi bi-person-lines-fill"></i></div>
            </div>
            <div>
                <div class="kpi-value" style="font-size: 24px; line-height: 38px;">{{ $gitLabInsights['top_contributor'] }}</div>
                <div class="kpi-label">Top Contributor ({{ $gitLabInsights['top_contributor_commits'] }} Commits)</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-icon warning"><i class="bi bi-clock-history"></i></div>
            </div>
            <div>
                <div class="kpi-value">{{ $gitLabInsights['no_recent_commits_count'] }}</div>
                <div class="kpi-label">Employees With No Commits (7 Days)</div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-icon success"><i class="bi bi-people-fill"></i></div>
            </div>
            <div>
                <div class="kpi-value" style="font-size: 24px; line-height: 38px;">{{ $gitLabInsights['most_active_team'] }}</div>
                <div class="kpi-label">Most Active Team ({{ $gitLabInsights['most_active_team_commits'] }} Commits)</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#topPerformersTable').DataTable({
        pageLength: 5,
        lengthChange: false,
        searching: false,
        ordering: false,
        info: false
    });
    $('#teamsTable').DataTable({
        pageLength: 5,
        lengthChange: false,
        searching: false,
        ordering: false,
        info: false
    });
});
</script>
@endpush
