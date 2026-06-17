@forelse ($meetings as $meeting)
    <tr>
        <td class="fw-bold"><a href="{{ url('/meetings/' . $meeting->id) }}" class="text-decoration-none" style="color: var(--blue);">{{ $meeting->title }}</a></td>
        <td>
            @if ($meeting->employees->isNotEmpty())
                {{ $meeting->employees->pluck('name')->implode(', ') }}
            @else
                <span class="muted-text">—</span>
            @endif
        </td>
        <td style="min-width: 280px;">{{ Str::limit($meeting->notes, 100) }}</td>
        <td>
            @if ($meeting->recordings->isNotEmpty())
                <a href="{{ url('/meetings/' . $meeting->id) }}" class="btn btn-sm btn-outline-light" style="border-color: var(--panel-border); white-space: nowrap;">
                    <i class="bi bi-play-circle me-1"></i>View Recording
                </a>
            @else
                <span class="muted-text">—</span>
            @endif
        </td>
        <td>{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y h:i A') }}</td>
        <td>
            <div class="d-flex gap-2 flex-wrap">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-light edit-meeting-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#editMeetingModal"
                    data-id="{{ $meeting->id }}"
                    data-title="{{ e($meeting->title) }}"
                    data-notes="{{ e($meeting->notes) }}"
                    data-meeting-date="{{ $meeting->meeting_date }}"
                    data-participants='@json($meeting->employees->pluck("id")->values())'
                >Update</button>
                <form method="POST" action="{{ url('/meetings/' . $meeting->id) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger js-delete-confirm" data-label="meeting {{ $meeting->title }}">Delete</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center muted-text py-5">No meetings added yet.</td>
    </tr>
@endforelse
