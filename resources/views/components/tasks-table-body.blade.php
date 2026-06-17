@forelse ($tasks as $task)
    @php
        $statusClass = ['completed' => 'green', 'in_progress' => 'blue', 'pending' => 'orange'][$task->status] ?? '';
    @endphp
    <tr>
        <td>
            <div class="fw-bold">{{ $task->title }}</div>
            <div class="small muted-text text-truncate" style="max-width: 260px;">{{ $task->description ?? 'No description' }}</div>
        </td>
        <td>{{ $task->employee?->name ?? 'Deleted employee' }}</td>
        <td><span class="status-pill {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></td>
        <td>{{ \Carbon\Carbon::parse($task->assigned_date)->format('M d, Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</td>
        <td>{{ $task->completed_date ? \Carbon\Carbon::parse($task->completed_date)->format('M d, Y') : 'Not done' }}</td>
        <td>
            <div class="d-flex gap-2 flex-wrap">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-light edit-task-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editTaskModal"
                    data-id="{{ $task->id }}"
                    data-employee-id="{{ $task->employee_id }}"
                    data-title="{{ e($task->title) }}"
                    data-description="{{ e($task->description ?? '') }}"
                    data-status="{{ $task->status }}"
                    data-assigned-date="{{ $task->assigned_date }}"
                    data-due-date="{{ $task->due_date }}"
                    data-completed-date="{{ $task->completed_date }}"
                >Update</button>
                <form method="POST" action="{{ url('/tasks/' . $task->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="task {{ $task->title }}">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center muted-text py-5">No tasks added yet.</td>
    </tr>
@endforelse
