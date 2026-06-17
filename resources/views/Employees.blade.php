@extends('layout')

@section('title', 'Employees | AI Manager')
@section('page-title', 'Employees')

@push('styles')
    <style>
        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-outline-danger {
            border-color: rgba(255, 77, 92, 0.5);
            color: #ff4d5c;
        }

        .btn-outline-danger:hover {
            background: rgba(255, 77, 92, 0.15);
            border-color: #ff4d5c;
            color: #ff7d89;
        }

        .modal-content.glass-card {
            background: linear-gradient(145deg, #111d30, #080f1c) !important;
        }

        .delete-warning-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(255, 77, 92, 0.15);
            display: grid;
            place-items: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: #ff4d5c;
        }
    </style>
@endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addEmployeeCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addEmployeeCollapse">
            <i class="bi bi-plus-lg me-2"></i>Add Employee
        </button>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="addEmployeeCollapse">
        <section class="glass-card page-panel">
            <h2 class="panel-title"><i class="bi bi-person-plus me-2"></i>Add Employee</h2>
            <form method="POST" action="{{ url('/employees') }}" class="d-grid gap-3">
                @csrf

                <div>
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Employee name" required>
                </div>

                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@company.com" required>
                </div>

                <div>
                    <label class="form-label" for="department">Department</label>
                    <input class="form-control" id="department" name="department" value="{{ old('department') }}" placeholder="Engineering">
                </div>

                <div>
                    <label class="form-label" for="designation">Designation</label>
                    <input class="form-control" id="designation" name="designation" value="{{ old('designation') }}" placeholder="Backend Developer">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Employee
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addEmployeeCollapse" style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Employee List</h2>
            <span class="status-pill blue" id="totalCount">{{ $employees->total() }} total</span>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100">
                <thead>
                    <tr>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="name">Name <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th>Email</th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="department">Department <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="designation">Designation <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="created_at">Added <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('components.employees-table-body', ['employees' => $employees])
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="mt-4 d-flex justify-content-end">
            {{ $employees->links() }}
        </div>
    </section>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <form id="editEmployeeForm" method="POST" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editEmployeeModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: #9b65ff;"></i>Edit Employee
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editName">Name</label>
                            <input class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editEmail">Email</label>
                            <input class="form-control" id="editEmail" name="email" type="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editDepartment">Department</label>
                            <input class="form-control" id="editDepartment" name="department">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editDesignation">Designation</label>
                            <input class="form-control" id="editDesignation" name="designation">
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

    <!-- Delete Employee Modal -->
    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content glass-card">
                <form id="deleteEmployeeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="deleteEmployeeModalLabel">Delete Employee</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="delete-warning-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <p class="mb-1">Are you sure you want to delete</p>
                        <p class="fw-bold fs-5 mb-2" id="deleteEmployeeName"></p>
                        <p class="small muted-text mb-0">This will also remove all their tasks, attendance records, and commits. This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 justify-content-center">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" style="border-radius: 8px; font-weight: 700; padding: 8px 24px;">
                            <i class="bi bi-trash3 me-2"></i>Delete
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
    // Edit Employee Modal Population
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-employee-btn');
        if (editBtn) {
            const form = document.getElementById('editEmployeeForm');
            form.action = '{{ url("/employees") }}/' + editBtn.dataset.id;
            document.getElementById('editName').value = editBtn.dataset.name;
            document.getElementById('editEmail').value = editBtn.dataset.email;
            document.getElementById('editDepartment').value = editBtn.dataset.department || '';
            document.getElementById('editDesignation').value = editBtn.dataset.designation || '';
        }
    });

    // Delete Employee Modal Population
    document.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('.delete-employee-btn');
        if (deleteBtn) {
            const form = document.getElementById('deleteEmployeeForm');
            form.action = '{{ url("/employees") }}/' + deleteBtn.dataset.id;
            document.getElementById('deleteEmployeeName').textContent = deleteBtn.dataset.name + '?';
        }
    });
});
</script>
@endpush
