@extends('layout')

@section('title', 'Reports | AI Manager')
@section('page-title', 'Report History')

@section('content')
    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Historical Reports</h2>
            <span class="status-pill blue" id="totalCount">Reports</span>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100" id="dataTable">
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
            </table>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let dt = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url("/reports") }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'generated_at', name: 'generated_at' },
            { data: 'start_date', name: 'start_date' },
            { data: 'end_date', name: 'end_date' },
            { data: 'team_productivity', name: 'team_productivity' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ]
    });

    dt.on('xhr', function () {
        let json = dt.ajax.json();
        if (json && json.recordsTotal !== undefined) {
            $('#totalCount').text(json.recordsTotal + ' total');
        }
    });

    $('#globalSearch').on('keyup', function() {
        dt.search(this.value).draw();
    });
});
</script>
@endpush
