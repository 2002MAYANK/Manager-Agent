@extends('layout')

@section('title', 'Commits | AI Manager')
@section('page-title', 'Commits')

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addCommitCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addCommitCollapse">
            <i class="bi bi-plus-lg me-2"></i>Add Commit
        </button>
    </div>

    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="addCommitCollapse">
        <section class="glass-card page-panel">
            <h2 class="panel-title"><i class="bi bi-git me-2"></i>Add Commit</h2>
            <form method="POST" action="{{ url('/commits') }}" class="d-grid gap-3">
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
                    <label class="form-label" for="commit_hash">Commit Hash</label>
                    <input class="form-control" id="commit_hash" name="commit_hash" value="{{ old('commit_hash') }}" placeholder="a1b2c3d" required>
                </div>

                <div>
                    <label class="form-label" for="commit_message">Message</label>
                    <input class="form-control" id="commit_message" name="commit_message" value="{{ old('commit_message') }}" placeholder="Implemented feature" required>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="lines_added">Lines Added</label>
                        <input class="form-control" id="lines_added" name="lines_added" type="number" min="0" value="{{ old('lines_added', 0) }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="lines_deleted">Lines Deleted</label>
                        <input class="form-control" id="lines_deleted" name="lines_deleted" type="number" min="0" value="{{ old('lines_deleted', 0) }}" required>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="commit_date">Commit Date</label>
                    <input class="form-control" id="commit_date" name="commit_date" type="datetime-local" value="{{ old('commit_date') }}" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Commit
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addCommitCollapse" style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Commit Logs</h2>
            <div class="d-flex align-items-center gap-2">
                <input type="date" id="dateFilter" class="form-control form-control-sm" style="width: auto; background-color: var(--panel-bg); border-color: var(--panel-border); color: var(--text);">
                <span class="status-pill blue" id="totalCount">Commits</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100" id="dataTable">
                <thead>
                    <tr>
                        <th>Repository</th>
                        <th>Hash</th>
                        <th>Message</th>
                        <th>Employee</th>
                        <th>Lines</th>
                        <th>Date</th>
                        <th>Insight</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <div class="modal fade" id="editCommitModal" tabindex="-1" aria-labelledby="editCommitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <form id="editCommitForm" method="POST" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editCommitModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: var(--purple);"></i>Edit Commit
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editCommitEmployeeId">Employee</label>
                            <select class="form-select" id="editCommitEmployeeId" name="employee_id" required>
                                <option value="">Select employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editCommitHash">Commit Hash</label>
                            <input class="form-control" id="editCommitHash" name="commit_hash" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editCommitMessage">Message</label>
                            <input class="form-control" id="editCommitMessage" name="commit_message" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="editLinesAdded">Lines Added</label>
                                <input class="form-control" id="editLinesAdded" name="lines_added" type="number" min="0" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="editLinesDeleted">Lines Deleted</label>
                                <input class="form-control" id="editLinesDeleted" name="lines_deleted" type="number" min="0" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="editCommitDate">Commit Date</label>
                            <input class="form-control" id="editCommitDate" name="commit_date" type="datetime-local" required>
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

    <!-- View Insight Modal -->
    <div class="modal fade" id="viewInsightModal" tabindex="-1" aria-labelledby="viewInsightModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="viewInsightModalLabel">
                        <i class="bi bi-cpu me-2" style="color: var(--purple-2);"></i>Commit Analysis Insight
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="insightLoading" class="text-center p-4">
                        <div class="spinner-border text-purple" role="status" style="color: var(--purple-2) !important;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="muted-text small mt-2">Generating AI insights...</div>
                    </div>
                    <div id="insightContent" class="d-none text-dark">
                        <div class="mb-3 p-3 rounded border" style="background: var(--panel-soft);">
                            <h6 class="fw-bold mb-2">Commit Details</h6>
                            <div class="row g-2 small">
                                <div class="col-12">
                                    <span class="muted-text">Message:</span> <span class="fw-bold text-dark" id="insightMessage"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="muted-text">Author:</span> <span class="fw-bold text-dark" id="insightAuthor"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="muted-text">Repository:</span> <span class="fw-bold text-dark" id="insightRepo"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="muted-text">Date:</span> <span class="fw-bold text-dark" id="insightDate"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="muted-text">Lines:</span> <span class="fw-bold text-success" id="insightAdded"></span> / <span class="fw-bold text-danger" id="insightDeleted"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small muted-text mb-1 text-uppercase fw-bold">Feature Category</label>
                            <div class="p-2 rounded border" style="background: var(--panel-soft); color: var(--text);" id="insightCategory"></div>
                        </div>
                        <div class="row mb-3 g-2">
                            <div class="col-6">
                                <label class="form-label small muted-text mb-1 text-uppercase fw-bold">Risk Level</label>
                                <div class="p-2 rounded text-center fw-bold border" style="background: var(--panel-soft);" id="insightRisk"></div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label small muted-text mb-1 text-uppercase fw-bold">AI Summary</label>
                            <p class="p-2 rounded border" style="background: var(--panel-soft); color: var(--text); line-height: 1.5; margin: 0;" id="insightSummary"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-light btn-sm w-100" style="border-color: var(--panel-border);" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let dt = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ url("/commits") }}',
            data: function (d) {
                d.date = $('#dateFilter').val();
            }
        },
        columns: [
            { data: 'repository_name', name: 'repository_name' },
            { data: 'commit_hash', name: 'commit_hash' },
            { data: 'commit_message', name: 'commit_message' },
            { data: 'employee_name', name: 'employee_name' },
            { data: 'lines', name: 'lines', orderable: false, searchable: false },
            { data: 'commit_date', name: 'commit_date' },
            { data: 'insight', name: 'insight', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#dateFilter').on('change', function() {
        dt.draw();
    });

    $('#globalSearch').on('keyup', function() {
        dt.search(this.value).draw();
    });

    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-commit-btn');
        if (editBtn) {
            const form = document.getElementById('editCommitForm');
            form.action = '{{ url("/commits") }}/' + editBtn.dataset.id;
            document.getElementById('editCommitEmployeeId').value = editBtn.dataset.employeeId;
            document.getElementById('editCommitHash').value = editBtn.dataset.hash;
            document.getElementById('editCommitMessage').value = editBtn.dataset.message;
            document.getElementById('editLinesAdded').value = editBtn.dataset.added;
            document.getElementById('editLinesDeleted').value = editBtn.dataset.deleted;
            document.getElementById('editCommitDate').value = editBtn.dataset.date;
            return;
        }

        const insightBtn = e.target.closest('.view-insight-btn');
        if (insightBtn) {
            const commitId = insightBtn.dataset.id;
            const modalEl = document.getElementById('viewInsightModal');
            const loadingEl = document.getElementById('insightLoading');
            const contentEl = document.getElementById('insightContent');
            
            // Reset elements and show loading spinner
            loadingEl.classList.remove('d-none');
            contentEl.classList.add('d-none');
            
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            fetch('{{ url("/commits") }}/' + commitId + '/insight', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.insight && data.commit) {
                    const insight = data.insight;
                    const commit = data.commit;

                    // Populate Commit Info
                    document.getElementById('insightMessage').textContent = commit.commit_message || 'N/A';
                    document.getElementById('insightAuthor').textContent = commit.employee ? commit.employee.name : 'N/A';
                    document.getElementById('insightRepo').textContent = commit.repository_name || 'N/A';
                    
                    const dateObj = new Date(commit.commit_date);
                    document.getElementById('insightDate').textContent = isNaN(dateObj) ? 'N/A' : dateObj.toLocaleString();
                    
                    document.getElementById('insightAdded').textContent = '+' + (commit.lines_added || 0);
                    document.getElementById('insightDeleted').textContent = '-' + (commit.lines_deleted || 0);

                    // Populate Insight Info
                    document.getElementById('insightCategory').textContent = insight.feature_category || 'N/A';
                    
                    const riskEl = document.getElementById('insightRisk');
                    const risk = (insight.risk_level || 'Low').toLowerCase();
                    riskEl.textContent = insight.risk_level || 'Low';
                    riskEl.className = 'p-2 rounded text-center fw-bold'; // Reset class
                    if (risk === 'high') {
                        riskEl.classList.add('text-danger');
                    } else if (risk === 'medium') {
                        riskEl.classList.add('text-warning');
                    } else {
                        riskEl.classList.add('text-success');
                    }
                    
                    document.getElementById('insightSummary').textContent = insight.summary || 'No summary available.';
                    
                    loadingEl.classList.add('d-none');
                    contentEl.classList.remove('d-none');
                } else {
                    alert('Failed to retrieve commit insight.');
                    modal.hide();
                }
            })
            .catch(error => {
                console.error(error);
                alert('An error occurred while generating insight.');
                modal.hide();
            });
        }
    });
});
</script>
@endpush
