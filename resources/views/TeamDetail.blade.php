@extends('layout')

@section('title', 'Team Details | AI Manager')
@section('page-title', 'Team Detail')

@push('styles')
    <style>
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 24px;
        }

        .team-title {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 8px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 20px;
            text-align: center;
        }

        .stat-card strong {
            display: block;
            font-size: 32px;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
        }

        .purple-color {
            color: var(--purple-2) !important;
        }

        .blue-color {
            color: var(--blue) !important;
        }

        .green-color {
            color: var(--green) !important;
        }

        .orange-color {
            color: var(--orange) !important;
        }

        .pink-color {
            color: #EC4899 !important;
        }

        .yellow-color {
            color: #D97706 !important;
        }
        
        .cyan-color {
            color: #0891B2 !important;
        }
    </style>
@endpush

@section('content')
    <div class="glass-card page-panel mb-4">
        <div class="detail-header">
            <div>
                <h1 class="team-title">{{ $team->name }}</h1>
                <p class="muted-text mb-0">{{ $team->description ?? 'No description provided for this team.' }}</p>
            </div>
            <a href="{{ url('/teams') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Teams
            </a>
        </div>

        <div class="stat-grid">
            <div class="glass-card stat-card">
                <strong class="blue-color">{{ $stats['total_employees'] }}</strong>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="glass-card stat-card">
                <strong class="purple-color">{{ $stats['total_tasks'] }}</strong>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="glass-card stat-card">
                <strong class="green-color">{{ $stats['completed_tasks'] }}</strong>
                <div class="stat-label">Completed Tasks</div>
            </div>
            <div class="glass-card stat-card">
                <strong class="orange-color">{{ $stats['pending_tasks'] }}</strong>
                <div class="stat-label">Pending Tasks</div>
            </div>
            <div class="glass-card stat-card">
                <strong class="cyan-color">{{ $stats['attendance_count'] }}</strong>
                <div class="stat-label">Attendance Logs</div>
            </div>
            <div class="glass-card stat-card">
                <strong class="pink-color">{{ $stats['commit_count'] }}</strong>
                <div class="stat-label">Commit Logs</div>
            </div>
            <div class="glass-card stat-card">
                <strong class="yellow-color">{{ $stats['meetings_attended'] }}</strong>
                <div class="stat-label">Meetings Attended</div>
            </div>
        </div>
    </div>

    <!-- Members Table Section -->
    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Team Members</h2>
            <span class="status-pill blue">{{ $team->name }} members</span>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100" id="membersTable">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Email</th>
                        <th>Designation</th>
                        <th>Total Tasks</th>
                        <th>Total Commits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let dt = $('#membersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url("/teams/" . $team->id) }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email', visible: false }, // Shown inline inside name HTML
            { data: 'designation', name: 'designation' },
            { data: 'tasks_count', name: 'tasks_count', searchable: false },
            { data: 'commits_count', name: 'commits_count', searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#globalSearch').on('keyup', function() {
        dt.search(this.value).draw();
    });
});
</script>
@endpush
