@extends('layout')

@section('title', 'Commits | AI Manager')
@section('page-title', 'Commits')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addCommitCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addCommitCollapse">
            <i class="bi bi-plus-lg me-2"></i>Add Commit
        </button>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="addCommitCollapse">
        <section class="glass-card page-panel">
            <h2 class="panel-title"><i class="bi bi-git me-2"></i>Add Commit</h2>
            <form method="POST" action="{{ url('/commits') }}" class="d-grid gap-3">
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
                    <label class="form-label" for="commit_hash">Commit Hash</label>
                    <input class="form-control" id="commit_hash" name="commit_hash" value="{{ old('commit_hash') }}" placeholder="a1b2c3d" required>
                </div>

                <div>
                    <label class="form-label" for="commit_message">Message</label>
                    <input class="form-control" id="commit_message" name="commit_message" value="{{ old('commit_message') }}" placeholder="Implemented feature" required>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="lines_added">Lines Added</label>
                        <input class="form-control" id="lines_added" name="lines_added" type="number" min="0" value="{{ old('lines_added', 0) }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="lines_deleted">Lines Deleted</label>
                        <input class="form-control" id="lines_deleted" name="lines_deleted" type="number" min="0" value="{{ old('lines_deleted', 0) }}" required>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="commit_date">Commit Date</label>
                    <input class="form-control" id="commit_date" name="commit_date" type="datetime-local" value="{{ old('commit_date') }}" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Commit
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addCommitCollapse" style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Commit Logs</h2>
            <div class="d-flex align-items-center gap-2">
                <input type="date" id="dateFilter" class="form-control form-control-sm" style="width: auto; background-color: var(--panel-soft); border-color: var(--panel-border); color: #fff;">
                <span class="status-pill blue" id="totalCount">{{ $commits->total() }} total</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100">
                <thead>
                    <tr>
                        <th>Hash</th>
                        <th>Message</th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="employee_name">Employee <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th>
                            <a href="#" class="sort-link text-decoration-none text-muted me-2" data-sort="lines_added">Added <i class="bi bi-arrow-down-up ms-1"></i></a>
                            <a href="#" class="sort-link text-decoration-none text-muted" data-sort="lines_deleted">Deleted <i class="bi bi-arrow-down-up ms-1"></i></a>
                        </th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="commit_date">Date <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('components.commits-table-body', ['commits' => $commits])
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="mt-4 d-flex justify-content-end">
            {{ $commits->links() }}
        </div>
    </section>

    <div class="modal fade" id="editCommitModal" tabindex="-1" aria-labelledby="editCommitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <form id="editCommitForm" method="POST" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editCommitModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: #9b65ff;"></i>Edit Commit
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editCommitEmployeeId">Employee</label>
                            <select class="form-select" id="editCommitEmployeeId" name="employee_id" required>
                                <option value="">Select employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editCommitHash">Commit Hash</label>
                            <input class="form-control" id="editCommitHash" name="commit_hash" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editCommitMessage">Message</label>
                            <input class="form-control" id="editCommitMessage" name="commit_message" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="editLinesAdded">Lines Added</label>
                                <input class="form-control" id="editLinesAdded" name="lines_added" type="number" min="0" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="editLinesDeleted">Lines Deleted</label>
                                <input class="form-control" id="editLinesDeleted" name="lines_deleted" type="number" min="0" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="editCommitDate">Commit Date</label>
                            <input class="form-control" id="editCommitDate" name="commit_date" type="datetime-local" required>
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
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-commit-btn');
        if (!editBtn) {
            return;
        }

        const form = document.getElementById('editCommitForm');
        form.action = '{{ url("/commits") }}/' + editBtn.dataset.id;
        document.getElementById('editCommitEmployeeId').value = editBtn.dataset.employeeId;
        document.getElementById('editCommitHash').value = editBtn.dataset.commitHash;
        document.getElementById('editCommitMessage').value = editBtn.dataset.commitMessage;
        document.getElementById('editLinesAdded').value = editBtn.dataset.linesAdded;
        document.getElementById('editLinesDeleted').value = editBtn.dataset.linesDeleted;
        document.getElementById('editCommitDate').value = editBtn.dataset.commitDate.replace(' ', 'T').slice(0, 16);
    });
});
</script>
@endpush
