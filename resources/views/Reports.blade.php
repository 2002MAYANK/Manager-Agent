@extends('layout')

@section('title', 'Reports | AI Manager')
@section('page-title', 'Report History')

@section('content')
    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Historical Reports</h2>
            <span class="status-pill blue">{{ $reports->total() }} total</span>
        </div>

        <div class="table-responsive">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Generated Date</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Team Productivity</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td class="fw-bold">#{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->generated_at)->format('M d, Y h:i A') }}</td>
                            <td>{{ $report->start_date ? \Carbon\Carbon::parse($report->start_date)->format('M d, Y') : '-' }}
                            </td>
                            <td>{{ $report->end_date ? \Carbon\Carbon::parse($report->end_date)->format('M d, Y') : '-' }}
                            </td>
                            <td>
                                <span class="status-pill green">{{ $report->team_productivity }}%</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ url('/reports/' . $report->id) }}" class="btn btn-sm btn-outline-light"
                                    style="border-color: var(--panel-border);">View Report</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center muted-text py-5">No reports generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-end">
            {{ $reports->links() }}
        </div>
    </section>
@endsection
