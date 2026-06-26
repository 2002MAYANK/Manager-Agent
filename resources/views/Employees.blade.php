@extends('layout')

@section('title', 'Employees | AI Manager')
@section('page-title', 'Employees')

@push('styles')
    <style>
        .btn-outline-light {
            border-color: var(--panel-border);
            color: var(--muted);
            background: #fff;
        }

        .btn-outline-light:hover {
            background: var(--panel-soft);
            border-color: var(--muted);
            color: var(--text);
        }

        .btn-outline-danger {
            border-color: rgba(239, 68, 68, 0.5);
            color: var(--red);
            background: #fff;
        }

        .btn-outline-danger:hover {
            background: rgba(239, 68, 68, 0.08);
            border-color: var(--red);
            color: #b91c1c;
        }

        .modal-content.glass-card {
            background: var(--panel-bg) !important;
            color: var(--text);
        }

        .delete-warning-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.08);
            display: grid;
            place-items: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: var(--red);
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
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addEmployeeCollapse"
            aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addEmployeeCollapse">
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
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Employee name" required>
                </div>

                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}"
                        placeholder="name@company.com" required>
                </div>

                <div>
                    <label class="form-label" for="department">Department</label>
                    <input class="form-control" id="department" name="department" value="{{ old('department') }}"
                        placeholder="Engineering">
                </div>

                <div>
                    <label class="form-label" for="designation">Designation</label>
                    <input class="form-control" id="designation" name="designation" value="{{ old('designation') }}"
                        placeholder="Backend Developer">
                </div>

                <div>
                    <label class="form-label" for="team_id">Team</label>
                    <select class="form-select" id="team_id" name="team_id">
                        <option value="">Select team (optional)</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Employee
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse"
                        data-bs-target="#addEmployeeCollapse"
                        style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Employee List</h2>
            <span class="status-pill blue" id="totalCount">Employees</span>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100" id="dataTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Team</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <form id="editEmployeeForm" method="POST" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editEmployeeModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: var(--purple);"></i>Edit Employee
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
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
                        <div class="mb-3">
                            <label class="form-label" for="editTeamId">Team</label>
                            <select class="form-select" id="editTeamId" name="team_id">
                                <option value="">Select team (optional)</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Cancel</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            let dt = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ url('/employees') }}',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        visible: false
                    }, // The email is shown inside the name column HTML
                    {
                        data: 'team',
                        name: 'team',
                        orderable: false
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'designation',
                        name: 'designation'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#globalSearch').on('keyup', function() {
                dt.search(this.value).draw();
            });

            // Edit Employee Modal Population
            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-employee-btn');
                if (editBtn) {
                    const form = document.getElementById('editEmployeeForm');
                    form.action = '{{ url('/employees') }}/' + editBtn.dataset.id;
                    document.getElementById('editName').value = editBtn.dataset.name;
                    document.getElementById('editEmail').value = editBtn.dataset.email;
                    document.getElementById('editDepartment').value = editBtn.dataset.department || '';
                    document.getElementById('editDesignation').value = editBtn.dataset.designation || '';
                    document.getElementById('editTeamId').value = editBtn.dataset.teamId || '';
                }
            });
        });
    </script>
@endpush
