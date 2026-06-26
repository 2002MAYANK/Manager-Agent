@extends('layout')

@section('title', $meeting->title . ' | AI Manager')
@section('page-title', 'Meeting Details')

@push('styles')
<style>
    .meeting-shell { max-width: 980px; margin: 0 auto; }
    .meeting-card { padding: 26px; }
    .meeting-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; margin-bottom: 24px; }
    .meeting-title { font-size: 28px; font-weight: 800; margin: 0 0 8px; color: var(--text); }
    .meeting-section { padding: 20px; border: 1px solid var(--panel-border); border-radius: 8px; background: var(--panel-soft); margin-bottom: 16px; }
    .meeting-section h2 { font-size: 16px; font-weight: 700; margin: 0 0 16px; color: var(--text); }
    .participant-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 999px; background: #fff; border: 1px solid var(--panel-border); color: var(--purple); font-size: 13px; font-weight: 600; margin: 0 6px 6px 0; transition: all 0.2s ease; }
    .participant-chip:hover { border-color: var(--purple-2); background: rgba(37, 99, 235, 0.05); }
    .transcript-entry { padding: 14px 0; border-top: 1px solid var(--panel-border); }
    .transcript-entry:first-of-type { border-top: 0; padding-top: 0; }
    .transcript-speaker { font-weight: 700; color: var(--blue); margin-bottom: 4px; }
    .transcript-text { color: var(--text); line-height: 1.6; }
    .meeting-actions { margin-top: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
    @media (max-width: 575.98px) { .meeting-header, .meeting-actions { flex-direction: column; align-items: stretch; } }
</style>
@endpush

@section('content')
    <div class="meeting-shell">
        <div class="glass-card meeting-card">
            <div class="meeting-header">
                <div>
                    <h1 class="meeting-title">{{ $meeting->title }}</h1>
                    <div class="muted-text">
                        {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('M d, Y h:i A') }}
                    </div>
                </div>
            </div>

            {{-- Participants --}}
            <div class="meeting-section">
                <h2 style="color: var(--blue);"><i class="bi bi-people me-2"></i>Participants ({{ $meeting->employees->count() }})</h2>
                @forelse ($meeting->employees as $employee)
                    <span class="participant-chip">
                        <i class="bi bi-person-fill"></i>{{ $employee->name }}
                    </span>
                @empty
                    <div class="muted-text">No participants added.</div>
                @endforelse
            </div>

            {{-- Notes --}}
            <div class="meeting-section">
                <h2><i class="bi bi-journal-text me-2"></i>Meeting Notes</h2>
                <div style="color: var(--text); line-height: 1.7; white-space: pre-wrap;">{{ $meeting->notes }}</div>
            </div>

            {{-- Audio/Video Recordings --}}
            @if ($meeting->recordings && $meeting->recordings->isNotEmpty())
                <div class="meeting-section">
                    <h2 class="text-success"><i class="bi bi-play-circle me-2"></i>Meeting Recordings</h2>
                    <div class="d-flex flex-column gap-4">
                        @foreach ($meeting->recordings as $recording)
                            <div>
                                @if ($recording->file_type === 'video')
                                    <video controls class="w-100" style="border-radius: 8px; background: #000; max-height: 400px;">
                                        <source src="{{ asset('storage/' . $recording->file_path) }}" type="video/{{ pathinfo($recording->file_path, PATHINFO_EXTENSION) }}">
                                        Your browser does not support the video element.
                                    </video>
                                @else
                                    <audio controls class="w-100" style="border-radius: 8px;">
                                        <source src="{{ asset('storage/' . $recording->file_path) }}" type="audio/{{ pathinfo($recording->file_path, PATHINFO_EXTENSION) }}">
                                        Your browser does not support the audio element.
                                    </audio>
                                @endif
                                <div class="mt-2 text-end">
                                    <a href="{{ asset('storage/' . $recording->file_path) }}" download class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download me-1"></i>Download Recording
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Analytics --}}
            <div class="meeting-section">
                <h2 style="color: var(--purple-2);"><i class="bi bi-bar-chart me-2"></i>Meeting Analytics</h2>
                <div class="row g-3">
                    <div class="col-sm-6 col-md-4">
                        <div class="muted-text small text-uppercase fw-bold mb-1">Participants</div>
                        <div class="fs-5 fw-bold">{{ $meeting->total_participants }}</div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="muted-text small text-uppercase fw-bold mb-1">Transcript Entries</div>
                        <div class="fs-5 fw-bold">{{ $meeting->total_transcript_entries }}</div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="muted-text small text-uppercase fw-bold mb-1">Duration</div>
                        <div class="fs-5 fw-bold">{{ $meeting->meeting_duration ?: 'Unknown' }}</div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="muted-text small text-uppercase fw-bold mb-1">Most Active Speaker</div>
                        <div class="fs-5 fw-bold" style="color: var(--green);">{{ $meeting->most_active_speaker ?: 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="muted-text small text-uppercase fw-bold mb-1">Least Active Speaker</div>
                        <div class="fs-5 fw-bold" style="color: var(--red);">{{ $meeting->least_active_speaker ?: 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- Transcript --}}
            @if ($meeting->transcripts->isNotEmpty())
                <div class="meeting-section">
                    <h2 style="color: var(--orange);"><i class="bi bi-chat-left-quote me-2"></i>Meeting Transcript</h2>
                    @foreach ($meeting->transcripts->sortBy('sequence') as $transcript)
                        <div class="transcript-entry">
                            <div class="transcript-speaker">
                                @if($transcript->timestamp)
                                    <span class="muted-text fw-normal me-2">[{{ $transcript->timestamp }}]</span>
                                @endif
                                <i class="bi bi-person-fill me-1"></i>{{ $transcript->speaker_name }}:
                            </div>
                            <div class="transcript-text">{{ $transcript->spoken_text }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="meeting-actions">
                <a href="{{ url('/meetings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Back to Meetings
                </a>
                <a href="{{ url('/') }}" class="btn-ai d-inline-flex align-items-center text-decoration-none">
                    <i class="bi bi-grid-1x2 me-2"></i> Go to Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection
