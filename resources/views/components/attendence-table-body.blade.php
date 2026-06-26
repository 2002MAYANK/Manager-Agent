@forelse ($attendences as $attendence)
    <tr>
        <td class="fw-bold">{{ $attendence->employee?->name ?? 'Deleted employee' }}</td>
        <td>{{ \Carbon\Carbon::parse($attendence->date)->format('M d, Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($attendence->check_in)->format('h:i A') }}</td>
        <td>{{ $attendence->check_out ? \Carbon\Carbon::parse($attendence->check_out)->format('h:i A') : 'Not set' }}</td>
        <td>
            <span class="status-pill {{ $attendence->present ? 'green' : 'red' }}">
                {{ $attendence->present ? 'Present' : 'Absent' }}
            </span>
        </td>
        <td>
            <div class="d-flex gap-2 flex-wrap">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-light edit-attendance-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editAttendenceModal"
                    data-id="{{ $attendence->id }}"
                    data-employee-id="{{ $attendence->employee_id }}"
                    data-date="{{ $attendence->date }}"
                    data-check-in="{{ $attendence->check_in }}"
                    data-check-out="{{ $attendence->check_out }}"
                    data-present="{{ $attendence->present ? '1' : '0' }}"
                >Update</button>
                <form method="POST" action="{{ url('/attendence/' . $attendence->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="attendance record for {{ $attendence->employee?->name ?? 'Deleted employee' }}">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center muted-text py-5">No attendance records added yet.</td>
    </tr>
@endforelse
