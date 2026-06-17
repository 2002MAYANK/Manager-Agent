@forelse ($commits as $commit)
    <tr>
        <td><span class="status-pill">{{ $commit->commit_hash }}</span></td>
        <td class="fw-bold">{{ $commit->commit_message }}</td>
        <td>{{ $commit->employee?->name ?? 'Deleted employee' }}</td>
        <td>
            <span class="text-success">+{{ $commit->lines_added }}</span>
            <span class="text-danger ms-2">-{{ $commit->lines_deleted }}</span>
        </td>
        <td>{{ \Carbon\Carbon::parse($commit->commit_date)->format('M d, Y h:i A') }}</td>
        <td>
            <div class="d-flex gap-2 flex-wrap">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-light edit-commit-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editCommitModal"
                    data-id="{{ $commit->id }}"
                    data-employee-id="{{ $commit->employee_id }}"
                    data-commit-hash="{{ e($commit->commit_hash) }}"
                    data-commit-message="{{ e($commit->commit_message) }}"
                    data-lines-added="{{ $commit->lines_added }}"
                    data-lines-deleted="{{ $commit->lines_deleted }}"
                    data-commit-date="{{ $commit->commit_date }}"
                >Update</button>
                <form method="POST" action="{{ url('/commits/' . $commit->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="commit {{ $commit->commit_hash }}">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center muted-text py-5">No commits added yet.</td>
    </tr>
@endforelse
