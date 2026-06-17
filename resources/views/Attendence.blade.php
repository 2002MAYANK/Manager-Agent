@extends('layout')

@section('title', 'Attendence | AI Manager')
@section('page-title', 'Attendence')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addAttendanceCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addAttendanceCollapse">
            <i class="bi bi-plus-lg me-2"></i>Add Attendance
        </button>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="addAttendanceCollapse">
        <section class="glass-card page-panel">
            <h2 class="panel-title"><i class="bi bi-calendar-plus me-2"></i>Add Attendance</h2>
            <form method="POST" action="{{ url('/attendence') }}" class="d-grid gap-3">
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
                    <label class="form-label" for="date">Date</label>
                    <input class="form-control" id="date" name="date" type="date" value="{{ old('date', now()->toDateString()) }}" required>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="check_in">Check In</label>
                        <input class="form-control" id="check_in" name="check_in" type="time" value="{{ old('check_in') }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="check_out">Check Out</label>
                        <input class="form-control" id="check_out" name="check_out" type="time" value="{{ old('check_out') }}">
                    </div>
                </div>

                <div>
                    <label class="form-label" for="present">Present</label>
                    <select class="form-select" id="present" name="present" required>
                        <option value="1" @selected(old('present', '1') === '1')>Yes</option>
                        <option value="0" @selected(old('present') === '0')>No</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Attendance
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addAttendanceCollapse" style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Attendance Records</h2>
            <div class="d-flex align-items-center gap-2">
                <input type="date" id="dateFilter" class="form-control form-control-sm" style="width: auto; background-color: var(--panel-soft); border-color: var(--panel-border); color: #fff;">
                <span class="status-pill blue" id="totalCount">{{ $attendences->total() }} total</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100">
                <thead>
                    <tr>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="employee_name">Employee <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="date">Date <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="check_in">Check In <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th><a href="#" class="sort-link text-decoration-none text-muted" data-sort="check_out">Check Out <i class="bi bi-arrow-down-up ms-1"></i></a></th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('components.attendence-table-body', ['attendences' => $attendences])
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="mt-4 d-flex justify-content-end">
            {{ $attendences->links() }}
        </div>
    </section>

    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <form id="editAttendanceForm" method="POST" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editAttendanceModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: #9b65ff;"></i>Edit Attendance
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editAttendanceEmployeeId">Employee</label>
                            <select class="form-select" id="editAttendanceEmployeeId" name="employee_id" required>
                                <option value="">Select employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editAttendanceDate">Date</label>
                            <input class="form-control" id="editAttendanceDate" name="date" type="date" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="editAttendanceCheckIn">Check In</label>
                                <input class="form-control" id="editAttendanceCheckIn" name="check_in" type="time" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="editAttendanceCheckOut">Check Out</label>
                                <input class="form-control" id="editAttendanceCheckOut" name="check_out" type="time">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="editAttendancePresent">Present</label>
                            <select class="form-select" id="editAttendancePresent" name="present" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
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
        const editBtn = e.target.closest('.edit-attendance-btn');
        if (!editBtn) {
            return;
        }

        const form = document.getElementById('editAttendanceForm');
        form.action = '{{ url("/attendence") }}/' + editBtn.dataset.id;
        document.getElementById('editAttendanceEmployeeId').value = editBtn.dataset.employeeId;
        document.getElementById('editAttendanceDate').value = editBtn.dataset.date;
        document.getElementById('editAttendanceCheckIn').value = editBtn.dataset.checkIn;
        document.getElementById('editAttendanceCheckOut').value = editBtn.dataset.checkOut || '';
        document.getElementById('editAttendancePresent').value = editBtn.dataset.present;
    });
});
</script>
@endpush
