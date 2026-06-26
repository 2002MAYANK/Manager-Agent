@extends('layout')

@section('title', 'Teams | AI Manager')
@section('page-title', 'Teams')

@push('styles')
    <style>
        .btn-outline-light {
            border-color: var(--panel-border);
            color: var(--muted);
            background: #fff;
        }

        .btn-outline-light:hover {
            background: var(--panel-soft);
            border-color: var(--muted);
            color: var(--text);
        }

        .btn-outline-danger {
            border-color: rgba(239, 68, 68, 0.5);
            color: var(--red);
            background: #fff;
        }

        .btn-outline-danger:hover {
            background: rgba(239, 68, 68, 0.08);
            border-color: var(--red);
            color: #b91c1c;
        }

        .modal-content.glass-card {
            background: var(--panel-bg) !important;
            color: var(--text);
        }

        /* AJAX search dropdown styling */
        .search-results-list {
            position: absolute;
            z-index: 1050;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            list-style: none;
            padding: 0;
            margin: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .search-results-list li {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid var(--panel-border);
            color: var(--text);
            font-size: 14px;
        }

        .search-results-list li:last-child {
            border-bottom: none;
        }

        .search-results-list li:hover {
            background: var(--panel-soft);
            color: var(--purple);
        }

        .member-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.15);
            color: var(--purple);
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }

        .member-badge .remove-btn {
            cursor: pointer;
            color: var(--red);
            font-weight: 800;
            font-size: 15px;
            transition: color 0.15s;
        }

        .member-badge .remove-btn:hover {
            color: #b91c1c;
        }
    </style>
@endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn-ai" type="button" data-bs-toggle="collapse" data-bs-target="#addTeamCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addTeamCollapse">
            <i class="bi bi-plus-lg me-2"></i>Add Team
        </button>
    </div>

    <!-- Add Team Section -->
    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="addTeamCollapse">
        <section class="glass-card page-panel">
            <h2 class="panel-title"><i class="bi bi-microsoft-teams me-2" style="color: var(--blue);"></i>Add Team</h2>
            <form method="POST" action="{{ url('/teams') }}" class="d-grid gap-3">
                @csrf

                <div>
                    <label class="form-label" for="name">Team Name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Team Name (e.g. Backend Team)" required>
                </div>

                <div>
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" placeholder="Backend services development and API support" rows="2">{{ old('description') }}</textarea>
                </div>

                <!-- AJAX Member selection -->
                <div class="position-relative">
                    <label class="form-label">Assign Members</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--panel-bg); border-color: var(--panel-border); color: var(--muted);"><i class="bi bi-search"></i></span>
                        <input class="form-control employee-search-input" placeholder="Type employee name or email to search..." autocomplete="off">
                    </div>
                    <ul class="search-results-list d-none"></ul>

                    <div class="selected-members-container d-flex flex-wrap gap-2 mt-3">
                        <!-- Badges will render here -->
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-ai">
                        <i class="bi bi-plus-lg me-2"></i>Save Team
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addTeamCollapse" style="border-radius: 8px; font-weight: 700; padding: 0 24px; min-height: 50px;">
                        Cancel
                    </button>
                </div>
            </form>
        </section>
    </div>

    <!-- Teams Listing Section -->
    <section class="glass-card page-panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="panel-title mb-0">Teams List</h2>
            <span class="status-pill blue" id="totalCount">Teams</span>
        </div>

        <div class="table-responsive">
            <table class="table data-table w-100" id="dataTable">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Description</th>
                        <th>Total Employees</th>
                        <th>Total Tasks</th>
                        <th>Total Commits</th>
                        <th>Total Meetings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <!-- Edit Team Modal -->
    <div class="modal fade" id="editTeamModal" tabindex="-1" aria-labelledby="editTeamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <form id="editTeamForm" method="POST" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editTeamModalLabel">
                            <i class="bi bi-pencil-square me-2" style="color: var(--purple);"></i>Edit Team
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editName">Team Name</label>
                            <input class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editDescription">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="form-label">Assign Members</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--panel-bg); border-color: var(--panel-border); color: var(--muted);"><i class="bi bi-search"></i></span>
                                <input class="form-control employee-search-input" placeholder="Type employee name or email to search..." autocomplete="off">
                            </div>
                            <ul class="search-results-list d-none"></ul>

                            <div class="selected-members-container d-flex flex-wrap gap-2 mt-3">
                                <!-- Prepopulated members will load here -->
                            </div>
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
document.addEventListener('DOMContentLoaded', function () {
    let dt = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url("/teams") }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description' },
            { data: 'employees_count', name: 'emp_stats.employees_count', searchable: false },
            { data: 'tasks_count', name: 'task_stats.tasks_count', searchable: false },
            { data: 'commits_count', name: 'commit_stats.commits_count', searchable: false },
            { data: 'meetings_count', name: 'meeting_stats.meetings_count', searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#globalSearch').on('keyup', function() {
        dt.search(this.value).draw();
    });

    // Helper functions for dynamic AJAX employee search & selection
    function initEmployeeSearch(container) {
        const input = container.querySelector('.employee-search-input');
        const resultsList = container.querySelector('.search-results-list');
        const badgesContainer = container.querySelector('.selected-members-container');

        let debounceTimeout = null;

        input.addEventListener('input', function () {
            clearTimeout(debounceTimeout);
            const query = input.value.trim();

            if (query.length < 2) {
                resultsList.classList.add('d-none');
                return;
            }

            debounceTimeout = setTimeout(() => {
                fetch(`{{ url("/employees/search") }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsList.innerHTML = '';
                        if (data.length === 0) {
                            const li = document.createElement('li');
                            li.textContent = 'No matching employees found';
                            li.style.cursor = 'default';
                            resultsList.appendChild(li);
                        } else {
                            data.forEach(emp => {
                                // Skip if already selected
                                if (badgesContainer.querySelector(`[data-emp-id="${emp.id}"]`)) {
                                    return;
                                }
                                const li = document.createElement('li');
                                li.textContent = `${emp.name} (${emp.email})`;
                                li.addEventListener('click', () => {
                                    addMemberBadge(emp.id, emp.name, badgesContainer);
                                    input.value = '';
                                    resultsList.classList.add('d-none');
                                });
                                resultsList.appendChild(li);
                            });
                        }
                        resultsList.classList.remove('d-none');
                    });
            }, 300);
        });

        // Hide results list when clicking outside
        document.addEventListener('click', function (e) {
            if (!container.contains(e.target)) {
                resultsList.classList.add('d-none');
            }
        });
    }

    function addMemberBadge(id, name, badgesContainer) {
        // Prevent duplicate addition
        if (badgesContainer.querySelector(`[data-emp-id="${id}"]`)) {
            return;
        }

        const badge = document.createElement('div');
        badge.className = 'member-badge';
        badge.setAttribute('data-emp-id', id);
        badge.innerHTML = `
            <span>${escapeHtml(name)}</span>
            <input type="hidden" name="employee_ids[]" value="${id}">
            <span class="remove-btn">&times;</span>
        `;

        badge.querySelector('.remove-btn').addEventListener('click', () => {
            badge.remove();
        });

        badgesContainer.appendChild(badge);
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Initialize searches
    const collapseDiv = document.getElementById('addTeamCollapse');
    const editModalDiv = document.getElementById('editTeamModal');

    if (collapseDiv) {
        initEmployeeSearch(collapseDiv);
    }
    if (editModalDiv) {
        initEmployeeSearch(editModalDiv);
    }

    // Handle Edit Team modal population
    $(document).on('click', '.edit-team-btn', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');

        $('#editTeamForm').attr('action', `{{ url("/teams") }}/${id}`);
        $('#editName').val(name);
        $('#editDescription').val(description);

        const badgesContainer = editModalDiv.querySelector('.selected-members-container');
        badgesContainer.innerHTML = '<span class="muted-text small">Loading members...</span>';

        // Fetch current members to populate badges
        fetch(`{{ url("/teams") }}/${id}/members`)
            .then(response => response.json())
            .then(members => {
                badgesContainer.innerHTML = '';
                members.forEach(emp => {
                    addMemberBadge(emp.id, emp.name, badgesContainer);
                });
            })
            .catch(() => {
                badgesContainer.innerHTML = '<span class="text-danger small">Failed to load members.</span>';
            });
    });
});
</script>
@endpush
