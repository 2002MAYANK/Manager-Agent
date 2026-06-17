@forelse ($employees as $employee)
    <tr>
        <td class="fw-bold">{{ $employee->name }}</td>
        <td>{{ $employee->email }}</td>
        <td>{{ $employee->department ?? 'Not set' }}</td>
        <td>{{ $employee->designation ?? 'Not set' }}</td>
        <td class="muted-text">{{ $employee->created_at?->format('M d, Y') }}</td>
        <td>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-light edit-employee-btn"
                    data-id="{{ $employee->id }}"
                    data-name="{{ $employee->name }}"
                    data-email="{{ $employee->email }}"
                    data-department="{{ $employee->department }}"
                    data-designation="{{ $employee->designation }}"
                    data-bs-toggle="modal" data-bs-target="#editEmployeeModal"
                    style="border-radius: 6px; padding: 4px 10px;">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-employee-btn"
                    data-id="{{ $employee->id }}"
                    data-name="{{ $employee->name }}"
                    data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal"
                    style="border-radius: 6px; padding: 4px 10px;">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center muted-text py-5">No employees added yet.</td>
    </tr>
@endforelse
