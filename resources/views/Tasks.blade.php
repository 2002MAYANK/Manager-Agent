@extends('layout')

@section('title', 'Tasks | AI Manager')
@section('page-title', 'Tasks')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addTaskCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addTaskCollapse">
            <i class="bi bi-plus-lg me-2"></i>Add Task
        </button>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="addTaskCollapse">
        <section class="glass-card page-panel">
            <h2 class="panel-title"><i class="bi bi-calendar2-plus me-2"></i>Add Task</h2>
            <form method="POST" action="{{ url('/tasks') }}" class="d-grid gap-3">
                @csrf

                <div>
                    <label class="form-label" for="employee_id">Employee</label>
                    <select class="form-select" id="employee_id" name="employee_id" required>
                        <option value="">Select employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" for="title">Title</label>
                    <input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="Task title" required>
                </div>

                <div>
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Task details">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                        <option value="in_progress" @selected(old('status') === 'in_progress')>In Progress</option>
                        <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="assigned_date">Assigned</label>
                        <input class="form-control" id="assigned_date" name="assigned_date" type="date" value="{{ old('assigned_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="due_date">Due</label>
                        <input class="form-control" id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" required>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="completed_date">Completed Date</label>
                    <input class="form-control" id="completed_date" name="completed_date" type="date" value="{{ old('completed_date') }}">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Task
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addTaskCollapse" style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Task List</h2>
            <div class="d-flex align-items-center gap-2">
                <input type="date" id="dateFilter" class="form-control form-control-sm" style="width: auto; background-color: var(--panel-bg); border-color: var(--panel-border); color: var(--text);">
                <select id="statusFilter" class="form-select form-select-sm" style="width: auto; background-color: var(--panel-bg); border-color: var(--panel-border); color: var(--text);">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                <span class="status-pill blue" id="totalCount">Tasks</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100" id="dataTable">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Due</th>
                        <th>Completed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <form id="editTaskForm" method="POST" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editTaskModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: var(--purple);"></i>Edit Task
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editEmployeeId">Employee</label>
                            <select class="form-select" id="editEmployeeId" name="employee_id" required>
                                <option value="">Select employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editTitle">Title</label>
                            <input class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editDescription">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editStatus">Status</label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="editAssignedDate">Assigned</label>
                                <input class="form-control" id="editAssignedDate" name="assigned_date" type="date" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="editDueDate">Due</label>
                                <input class="form-control" id="editDueDate" name="due_date" type="date" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="editCompletedDate">Completed Date</label>
                            <input class="form-control" id="editCompletedDate" name="completed_date" type="date">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-ai">
                            <i class="bi bi-check-lg me-2"></i>Save Changes
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
    let dt = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ url("/tasks") }}',
            data: function (d) {
                d.status = $('#statusFilter').val();
                d.date = $('#dateFilter').val();
            }
        },
        columns: [
            { data: 'title', name: 'title' },
            { data: 'employee_name', name: 'employee_name' },
            { data: 'status', name: 'status' },
            { data: 'assigned_date', name: 'assigned_date' },
            { data: 'due_date', name: 'due_date' },
            { data: 'completed_date', name: 'completed_date' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#statusFilter, #dateFilter').on('change', function() {
        dt.draw();
    });

    $('#globalSearch').on('keyup', function() {
        dt.search(this.value).draw();
    });

    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-task-btn');
        if (!editBtn) {
            return;
        }

        const form = document.getElementById('editTaskForm');
        form.action = '{{ url("/tasks") }}/' + editBtn.dataset.id;
        document.getElementById('editEmployeeId').value = editBtn.dataset.employeeId;
        document.getElementById('editTitle').value = editBtn.dataset.title;
        document.getElementById('editDescription').value = editBtn.dataset.description || '';
        document.getElementById('editStatus').value = editBtn.dataset.status;
        document.getElementById('editAssignedDate').value = editBtn.dataset.assignedDate;
        document.getElementById('editDueDate').value = editBtn.dataset.dueDate;
        document.getElementById('editCompletedDate').value = editBtn.dataset.completedDate || '';
    });
});
</script>
@endpush
