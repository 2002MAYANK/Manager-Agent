@extends('layout')

@section('title', 'Projects - AI Manager')
@section('page-title', 'Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="text-muted">Manage your organization's projects</div>
    <button class="btn btn-ai" data-bs-toggle="modal" data-bs-target="#addProjectModal">
        <i class="bi bi-plus-lg"></i> Add Project
    </button>
</div>

@if(session('success'))
<div class="alert alert-success border-0 bg-success text-white">
    {{ session('success') }}
</div>
@endif

<div class="glass-card p-0">
    <div class="table-responsive">
        <table class="table data-table mb-0 w-100" id="projectsTable">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Team</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Progress</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- AJAX populated -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ url('/projects') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Add Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option>Planning</option>
                                <option>In Progress</option>
                                <option>Completed</option>
                                <option>On Hold</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option>Low</option>
                                <option selected>Medium</option>
                                <option>High</option>
                                <option>Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Team</label>
                        <select name="team_id" class="form-select">
                            <option value="">No Team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-ai">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form id="editProjectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Edit Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option>Planning</option>
                                <option>In Progress</option>
                                <option>Completed</option>
                                <option>On Hold</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" id="edit_priority" class="form-select">
                                <option>Low</option>
                                <option>Medium</option>
                                <option>High</option>
                                <option>Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Team</label>
                        <select name="team_id" id="edit_team_id" class="form-select">
                            <option value="">No Team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-ai">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#projectsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('/projects') }}",
        columns: [
            {data: 'name', name: 'name', render: function(data, type, row) {
                return '<div class="fw-bold text-dark">' + data + '</div>';
            }},
            {data: 'team_name', name: 'team.name'},
            {data: 'status', name: 'status', render: function(data) {
                let color = 'blue';
                if(data === 'Completed') color = 'green';
                if(data === 'On Hold') color = 'orange';
                return `<span class="status-pill ${color}">${data}</span>`;
            }},
            {data: 'priority', name: 'priority', render: function(data) {
                let color = 'blue';
                if(data === 'High') color = 'orange';
                if(data === 'Critical') color = 'red';
                return `<span class="status-pill ${color}">${data}</span>`;
            }},
            {data: 'start_date', name: 'start_date'},
            {data: 'end_date', name: 'end_date'},
            {data: 'progress', name: 'progress', orderable: false, searchable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end'}
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search projects..."
        }
    });

    $('#editProjectModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var project = button.data('project');
        var form = $('#editProjectForm');
        
        form.attr('action', '/projects/' + project.id);
        $('#edit_name').val(project.name);
        $('#edit_description').val(project.description);
        $('#edit_status').val(project.status);
        $('#edit_priority').val(project.priority);
        $('#edit_start_date').val(project.start_date);
        $('#edit_end_date').val(project.end_date);
        $('#edit_team_id').val(project.team_id);
    });
});
</script>
@endpush
