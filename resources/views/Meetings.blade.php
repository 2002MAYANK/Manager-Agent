@extends('layout')

@section('title', 'Meetings | AI Manager')
@section('page-title', 'Meetings')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addMeetingCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addMeetingCollapse">
            <i class="bi bi-plus-lg me-2"></i>Add Meeting
        </button>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="addMeetingCollapse">
        <section class="glass-card page-panel">
            <h2 class="panel-title"><i class="bi bi-calendar4-week me-2"></i>Add Meeting</h2>
            <form method="POST" action="{{ url('/meetings') }}" class="d-grid gap-3" enctype="multipart/form-data">
                @csrf

                <div>
                    <label class="form-label" for="title">Title</label>
                    <input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="Sprint planning" required>
                </div>

                <div>
                    <label class="form-label" for="meeting_date">Meeting Date</label>
                    <input class="form-control" id="meeting_date" name="meeting_date" type="datetime-local" value="{{ old('meeting_date') }}" required>
                </div>

                <div>
                    <label class="form-label" for="participant_ids">Participants</label>
                    <div class="border rounded-3 p-3" style="max-height: 220px; overflow-y: auto; background: var(--panel-soft); border-color: var(--panel-border) !important;">
                        @foreach ($employees as $employee)
                            <label class="d-flex align-items-center gap-2 mb-2" style="cursor: pointer;">
                                <input
                                    class="form-check-input m-0"
                                    type="checkbox"
                                    name="participant_ids[]"
                                    value="{{ $employee->id }}"
                                    @checked(is_array(old('participant_ids')) && in_array($employee->id, old('participant_ids')))
                                >
                                <span>{{ $employee->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="form-text muted-text">Select one or more employees</div>
                </div>

                <div>
                    <label class="form-label" for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="5" placeholder="Discussion points and action items" required>{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="form-label" for="meeting_audio">Meeting Recordings (Audio/Video)</label>
                    <input class="form-control" type="file" id="meeting_audio" name="meeting_audio[]" accept=".mp3,.wav,.m4a,.mp4,.webm" multiple>
                    <div class="form-text muted-text">Supported: MP3, WAV, M4A, MP4, WEBM (max 50MB each). Hold Ctrl/Cmd to select multiple files.</div>
                </div>

                {{-- Transcript Section --}}
                <div>
                    <label class="form-label">Meeting Transcript <span class="muted-text fw-normal">(optional)</span></label>
                    <div id="transcriptEntries">
                        <div class="transcript-entry mb-2">
                            <div class="row g-2">
                                <div class="col-sm-2">
                                    <input class="form-control" name="transcript_timestamps[]" placeholder="00:00:00" value="{{ old('transcript_timestamps.0') }}">
                                </div>
                                <div class="col-sm-3">
                                    <input class="form-control" name="transcript_speakers[]" placeholder="Speaker name" value="{{ old('transcript_speakers.0') }}">
                                </div>
                                <div class="col-sm-6">
                                    <input class="form-control" name="transcript_texts[]" placeholder="What was said..." value="{{ old('transcript_texts.0') }}">
                                </div>
                                <div class="col-sm-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle remove-transcript" style="width: 32px; height: 32px; padding: 0;" title="Remove"><i class="bi bi-x"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-light mt-1" id="addTranscriptEntry" style="border-color: var(--panel-border);">
                        <i class="bi bi-plus me-1"></i>Add transcript entry
                    </button>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Meeting
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addMeetingCollapse" style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Meeting Notes</h2>
            <div class="d-flex align-items-center gap-2">
                <input type="date" id="dateFilter" class="form-control form-control-sm" style="width: auto; background-color: var(--panel-bg); border-color: var(--panel-border); color: var(--text);">
                <span class="status-pill blue" id="totalCount">Meetings</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100" id="dataTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Participants</th>
                        <th>Notes</th>
                        <th>Audio</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <div class="modal fade" id="editMeetingModal" tabindex="-1" aria-labelledby="editMeetingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-card">
                <form id="editMeetingForm" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editMeetingModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: var(--purple);"></i>Edit Meeting
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editMeetingTitle">Title</label>
                            <input class="form-control" id="editMeetingTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editMeetingDate">Meeting Date</label>
                            <input class="form-control" id="editMeetingDate" name="meeting_date" type="datetime-local" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Participants</label>
                            <div class="border rounded-3 p-3" style="max-height: 220px; overflow-y: auto; background: var(--panel-soft); border-color: var(--panel-border) !important;">
                                @foreach ($employees as $employee)
                                    <label class="d-flex align-items-center gap-2 mb-2" style="cursor: pointer;">
                                        <input class="form-check-input m-0 edit-meeting-participant" type="checkbox" name="participant_ids[]" value="{{ $employee->id }}">
                                        <span>{{ $employee->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editMeetingNotes">Notes</label>
                            <textarea class="form-control" id="editMeetingNotes" name="notes" rows="5" required></textarea>
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
document.addEventListener('DOMContentLoaded', function() {
    let dt = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ url("/meetings") }}',
            data: function (d) {
                d.date = $('#dateFilter').val();
            }
        },
        columns: [
            { data: 'title', name: 'title' },
            { data: 'participants', name: 'participants', orderable: false, searchable: false },
            { data: 'notes', name: 'notes' },
            { data: 'audio', name: 'audio', orderable: false, searchable: false },
            { data: 'meeting_date', name: 'meeting_date' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#dateFilter').on('change', function() {
        dt.draw();
    });

    $('#globalSearch').on('keyup', function() {
        dt.search(this.value).draw();
    });

    const container = document.getElementById('transcriptEntries');
    document.getElementById('addTranscriptEntry').addEventListener('click', function() {
        const entry = document.createElement('div');
        entry.className = 'transcript-entry mb-2';
        entry.innerHTML = `
            <div class="row g-2">
                <div class="col-sm-2">
                    <input class="form-control" name="transcript_timestamps[]" placeholder="00:00:00">
                </div>
                <div class="col-sm-3">
                    <input class="form-control" name="transcript_speakers[]" placeholder="Speaker name">
                </div>
                <div class="col-sm-6">
                    <input class="form-control" name="transcript_texts[]" placeholder="What was said...">
                </div>
                <div class="col-sm-1 d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle remove-transcript" style="width: 32px; height: 32px; padding: 0;" title="Remove"><i class="bi bi-x"></i></button>
                </div>
            </div>`;
        container.appendChild(entry);
    });

    container.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-transcript');
        if (btn) {
            const entries = container.querySelectorAll('.transcript-entry');
            if (entries.length > 1) {
                btn.closest('.transcript-entry').remove();
            }
        }
    });

    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-meeting-btn');
        if (!editBtn) {
            return;
        }

        const form = document.getElementById('editMeetingForm');
        form.action = '{{ url("/meetings") }}/' + editBtn.dataset.id;
        document.getElementById('editMeetingTitle').value = editBtn.dataset.title;
        document.getElementById('editMeetingDate').value = editBtn.dataset.date;
        document.getElementById('editMeetingNotes').value = editBtn.dataset.notes;

        const selectedParticipants = JSON.parse(editBtn.dataset.participants || '[]').map(String);
        document.querySelectorAll('.edit-meeting-participant').forEach(function (checkbox) {
            checkbox.checked = selectedParticipants.includes(String(checkbox.value));
        });
    });
});
</script>
@endpush
