@extends('layout')

@section('title', $project->name . ' - AI Manager')
@section('page-title')
    <a href="{{ url('/projects') }}" class="text-decoration-none text-muted me-2"><i class="bi bi-arrow-left"></i></a>
    {{ $project->name }}
@endsection

@section('content')

<div class="row mb-4 g-3">
    <!-- Project Overview -->
    <div class="col-md-8">
        <div class="glass-card h-100 p-4">
            <h5 class="fw-bold mb-4">Project Overview</h5>
            <div class="row g-4">
                <div class="col-sm-6">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Team</div>
                    <div class="fs-5">{{ $project->team ? $project->team->name : 'Unassigned' }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Status</div>
                    <div>
                        @php
                            $sColor = 'blue';
                            if($project->status === 'Completed') $sColor = 'green';
                            if($project->status === 'On Hold') $sColor = 'orange';
                        @endphp
                        <span class="status-pill {{ $sColor }}">{{ $project->status }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Priority</div>
                    <div>
                        @php
                            $pColor = 'blue';
                            if($project->priority === 'High') $pColor = 'orange';
                            if($project->priority === 'Critical') $pColor = 'red';
                        @endphp
                        <span class="status-pill {{ $pColor }}">{{ $project->priority }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Duration</div>
                    <div class="fs-6">
                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : 'TBD' }} 
                        - 
                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : 'TBD' }}
                    </div>
                </div>
            </div>
            
            @if($project->description)
            <div class="mt-4">
                <div class="text-muted small text-uppercase fw-bold mb-2">Description</div>
                <p class="mb-0">{{ $project->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Health Score -->
    <div class="col-md-4">
        <div class="glass-card h-100 p-4 d-flex flex-column align-items-center justify-content-center text-center">
            <h5 class="fw-bold mb-4 w-100 text-start">Project Health</h5>
            
            <div class="position-relative d-inline-flex align-items-center justify-content-center mb-3" style="width: 140px; height: 140px;">
                <!-- Circular progress placeholder (simplified for styling) -->
                <svg width="140" height="140" viewBox="0 0 140 140" class="position-absolute" style="transform: rotate(-90deg);">
                    <circle cx="70" cy="70" r="60" fill="none" stroke="#E2E8F0" stroke-width="12"></circle>
                    @php
                        $strokeColor = '#10B981'; // Green
                        if($healthScore < 50) $strokeColor = '#EF4444'; // Red
                        elseif($healthScore < 80) $strokeColor = '#F59E0B'; // Orange
                        $dashArray = 2 * pi() * 60;
                        $dashOffset = $dashArray - ($dashArray * $healthScore / 100);
                    @endphp
                    <circle cx="70" cy="70" r="60" fill="none" stroke="{{ $strokeColor }}" stroke-width="12" 
                        stroke-dasharray="{{ $dashArray }}" stroke-dashoffset="{{ $dashOffset }}" stroke-linecap="round"></circle>
                </svg>
                <div class="position-absolute d-flex flex-column align-items-center">
                    <span class="fs-2 fw-bold" style="color: {{ $strokeColor }}">{{ $healthScore }}%</span>
                </div>
            </div>
            
            <div class="mt-2">
                <div class="fs-5 fw-bold" style="color: {{ $strokeColor }}">{{ $healthStatus }}</div>
                <div class="text-muted small mt-1">Based on tasks, commits & activity</div>
            </div>
        </div>
    </div>
</div>

<!-- Project Analytics -->
<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-card p-3 text-center">
            <div class="text-muted small fw-bold mb-2">Total Tasks</div>
            <div class="fs-3 fw-bold">{{ $totalTasks }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-card p-3 text-center">
            <div class="text-muted small fw-bold mb-2">Completed</div>
            <div class="fs-3 fw-bold text-success">{{ $completedTasks }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-card p-3 text-center">
            <div class="text-muted small fw-bold mb-2">Pending</div>
            <div class="fs-3 fw-bold text-warning">{{ $pendingTasks }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-card p-3 text-center">
            <div class="text-muted small fw-bold mb-2">Overdue</div>
            <div class="fs-3 fw-bold text-danger">{{ $overdueTasks }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-card p-3 text-center">
            <div class="text-muted small fw-bold mb-2">Commits</div>
            <div class="fs-3 fw-bold text-primary">{{ $totalCommits }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-card p-3 text-center">
            <div class="text-muted small fw-bold mb-2">Meetings</div>
            <div class="fs-3 fw-bold text-info">{{ $totalMeetings }}</div>
        </div>
    </div>
</div>

@endsection
