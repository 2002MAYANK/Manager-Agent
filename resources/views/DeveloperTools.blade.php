@extends('layout')

@section('title', 'Developer Tools | AI Manager')
@section('page-title', 'Developer Tools')

@push('styles')
<style>
    .dev-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
        align-items: start;
    }

    @media (max-width: 991.98px) {
        .dev-grid {
            grid-template-columns: 1fr;
        }
    }

    .token-card {
        padding: 18px;
        margin-bottom: 16px;
        border: 1px solid var(--panel-border);
        border-radius: 8px;
        background: var(--panel-bg);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .token-hash {
        font-family: monospace;
        font-size: 15px;
        color: var(--text);
        background: var(--panel-soft);
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid var(--panel-border);
        display: inline-block;
        margin: 8px 0;
    }

    .code-block {
        background: #f8fafc;
        border: 1px solid var(--panel-border);
        border-radius: 8px;
        padding: 16px;
        font-family: monospace;
        font-size: 13px;
        color: #0f172a;
        overflow-x: auto;
        white-space: pre;
        margin-bottom: 24px;
    }

    .nav-tabs {
        border-bottom-color: var(--panel-border);
        margin-bottom: 20px;
    }

    .nav-tabs .nav-link {
        color: var(--muted);
        border: none;
        border-bottom: 2px solid transparent;
        padding: 10px 16px;
        font-weight: 600;
        cursor: pointer;
    }

    .nav-tabs .nav-link:hover {
        color: var(--text);
        border-color: transparent;
    }

    .nav-tabs .nav-link.active {
        color: var(--purple);
        background: transparent;
        border-color: transparent;
        border-bottom-color: var(--purple);
    }
    
    .api-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .api-nav-btn {
        background: var(--panel-bg);
        border: 1px solid var(--panel-border);
        color: var(--muted);
        padding: 8px 16px;
        border-radius: 99px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .api-nav-btn:hover {
        color: var(--text);
        border-color: var(--muted);
        background: var(--panel-soft);
    }
    
    .api-nav-btn.active {
        background: var(--purple);
        color: #fff;
        border-color: var(--purple);
    }

    .text-purple {
        color: var(--purple) !important;
    }

    /* Toast Notification */
    .toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1050;
    }

    .custom-toast {
        background: var(--panel-bg);
        color: var(--text);
        border: 1px solid var(--green);
        border-radius: 8px;
        padding: 14px 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.3s, transform 0.3s;
        pointer-events: none;
    }

    .custom-toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .custom-toast i {
        color: var(--green);
        font-size: 18px;
    }
</style>
@endpush

@section('content')
    <div class="dev-grid">
        <!-- Left Panel: Access Tokens -->
        <section class="glass-card page-panel">
            <h2 class="panel-title mb-4">Access Tokens</h2>
            
            <form action="{{ url('/developer-tools/tokens') }}" method="POST" class="mb-4">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col">
                        <label class="form-label small">Token Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Production Server API" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn-ai" style="min-height: 45px; border-radius: 8px; padding: 0 18px;">
                            <i class="bi bi-plus-lg me-2"></i> Generate Token
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-4">
                <div class="small fw-bold muted-text mb-3 text-uppercase">Active Tokens</div>
                
                @forelse($tokens as $token)
                    <div class="token-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold fs-5">{{ $token->name }}</div>
                                <div class="small muted-text">Created: {{ $token->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                            <div>
                                @if($token->is_active)
                                    <span class="status-pill green">Active</span>
                                @else
                                    <span class="status-pill red">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="token-hash w-100 mb-0">
                                ************************{{ substr($token->token, -4) }}
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" style="border-color: var(--panel-border); height: 38px; color: var(--text);" onclick="copyToClipboard('{{ $token->token }}')" title="Copy Token">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        
                        <div class="d-flex gap-4 mb-3 small">
                            <div>
                                <span class="muted-text">Total Requests:</span> <span class="fw-bold">{{ $token->request_count }}</span>
                            </div>
                            <div>
                                <span class="muted-text">Last Used:</span> <span class="fw-bold">{{ $token->last_used_at ? $token->last_used_at->format('d M Y h:i A') : 'Never' }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <form action="{{ url('/developer-tools/tokens/' . $token->id . '/toggle') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-color: var(--panel-border); color: var(--text);">
                                    @if($token->is_active)
                                        <i class="bi bi-pause-circle me-1"></i> Deactivate
                                    @else
                                        <i class="bi bi-play-circle me-1"></i> Activate
                                    @endif
                                </button>
                            </form>
                            <form action="{{ url('/developer-tools/tokens/' . $token->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this token?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm text-danger border border-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-4 muted-text border border-dashed rounded" style="border-color: var(--panel-border) !important;">
                        No API tokens generated yet.
                    </div>
                @endforelse
            </div>
        </section>

        <!-- GitLab Integration Panel -->
        <section class="glass-card page-panel mt-4">
            <h2 class="panel-title mb-4"><i class="bi bi-gitlab me-2" style="color: #fc6d26;"></i>GitLab Integration</h2>
            
            <div class="mb-4">
                <div class="small fw-bold muted-text mb-2 text-uppercase">Connection status</div>
                <div id="gitlabConnectionStatus" class="p-3 rounded border border-dashed text-center muted-text mb-3" style="border-color: var(--panel-border) !important; background: var(--panel-soft);">
                    Not tested
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="btnTestGitLab" class="btn btn-sm btn-outline-secondary w-100" style="border-color: var(--panel-border); color: var(--text);">
                        <i class="bi bi-shield-check me-1"></i> Test Connection
                    </button>
                    <button type="button" id="btnLoadRepos" class="btn btn-sm btn-outline-secondary w-100" style="border-color: var(--panel-border); color: var(--text);">
                        <i class="bi bi-cloud-arrow-down me-1"></i> Load Repositories
                    </button>
                </div>
            </div>

            <div class="mb-4 d-none" id="gitlabReposContainer">
                <label class="form-label small fw-bold text-uppercase muted-text" for="gitlabRepoSelect">Select Repository</label>
                <select id="gitlabRepoSelect" class="form-select mb-3">
                    <option value="">Choose repository...</option>
                </select>

                <button type="button" id="btnSyncCommits" class="btn-ai w-100" style="min-height: 45px; border-radius: 8px;">
                    <i class="bi bi-arrow-repeat me-1"></i> Sync Commits
                </button>

                <div id="syncStatsContainer" class="mt-4 d-none">
                    <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Sync Summary & Statistics</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded" style="background: var(--panel-bg); border-color: var(--panel-border);">
                                <div class="small muted-text text-uppercase fw-bold mb-1">Repository Name</div>
                                <div class="fw-bold" id="statRepoName">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded" style="background: var(--panel-bg); border-color: var(--panel-border);">
                                <div class="small muted-text text-uppercase fw-bold mb-1">Total Commits Synced</div>
                                <div class="fw-bold" id="statTotalCommits">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded" style="background: var(--panel-bg); border-color: var(--panel-border);">
                                <div class="small muted-text text-uppercase fw-bold mb-1">Total Contributors</div>
                                <div class="fw-bold" id="statContributors">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded" style="background: var(--panel-bg); border-color: var(--panel-border);">
                                <div class="small muted-text text-uppercase fw-bold mb-1">Most Active Contributor</div>
                                <div class="fw-bold" id="statActiveContributor">-</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 border rounded" style="background: var(--panel-bg); border-color: var(--panel-border);">
                                <div class="small muted-text text-uppercase fw-bold mb-1">Last Sync Time / Last Commit Date</div>
                                <div class="fw-bold" id="statLastCommit">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Panel: API Documentation -->
        <section class="glass-card page-panel">
            <h2 class="panel-title mb-4">API Documentation</h2>
            
            <div class="api-nav">
                <button type="button" class="api-nav-btn active" onclick="showApiDoc('employees', this)">Employees API</button>
                <button type="button" class="api-nav-btn" onclick="showApiDoc('tasks', this)">Tasks API</button>
                <button type="button" class="api-nav-btn" onclick="showApiDoc('attendences', this)">Attendences API</button>
                <button type="button" class="api-nav-btn" onclick="showApiDoc('commits', this)">Commits API</button>
                <button type="button" class="api-nav-btn" onclick="showApiDoc('meetings', this)">Meetings API</button>
                <button type="button" class="api-nav-btn" onclick="showApiDoc('reports', this)">Reports API</button>
                <button type="button" class="api-nav-btn" onclick="showApiDoc('health', this)">Health Check</button>
            </div>

            <!-- Dynamic Doc Container -->
            <div id="apiDocContent">
                <h5 class="fw-bold mb-3 text-purple" id="apiTitle">1. Employees API</h5>
                <div class="muted-text small mb-2" id="apiDesc">Create Employee</div>
                <div class="code-block" id="apiCurl">curl -X POST {{ url('/api/employees') }} \
-H "Authorization: Bearer YOUR_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "name": "Rahul Sharma",
    "email": "rahul@example.com",
    "department": "Development",
    "designation": "Laravel Developer"
}'</div>
                <div class="muted-text small mb-2">Success Response:</div>
                <div class="code-block" id="apiResp">{
    "success": true,
    "message": "Employee created successfully"
}</div>
            </div>
        </section>
    </div>

    <!-- Toast Notification Container -->
    <div class="toast-container">
        <div id="copyToast" class="custom-toast">
            <i class="bi bi-check-circle-fill"></i>
            <span>Token copied successfully</span>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Copy To Clipboard & Toast Logic
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const toast = document.getElementById('copyToast');
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            alert('Failed to copy token');
        });
    }

    // API Documentation Data
    const apiDocs = {
        'employees': {
            title: '1. Employees API',
            desc: 'Create Employee',
            curl: `curl -X POST {{ url('/api/employees') }} \\
-H "Authorization: Bearer YOUR_TOKEN" \\
-H "Content-Type: application/json" \\
-d '{
    "name": "Rahul Sharma",
    "email": "rahul@example.com",
    "department": "Development",
    "designation": "Laravel Developer"
}'`,
            resp: `{\n    "success": true,\n    "message": "Employee created successfully"\n}`
        },
        'tasks': {
            title: '2. Tasks API',
            desc: 'Create Task',
            curl: `curl -X POST {{ url('/api/tasks') }} \\
-H "Authorization: Bearer YOUR_TOKEN" \\
-H "Content-Type: application/json" \\
-d '{
    "employee_id": 1,
    "title": "Build Developer Tools",
    "status": "pending",
    "assigned_date": "2026-06-11",
    "due_date": "2026-06-15"
}'`,
            resp: `{\n    "success": true,\n    "message": "Task created successfully"\n}`
        },
        'attendences': {
            title: '3. Attendence API',
            desc: 'Create Attendence',
            curl: `curl -X POST {{ url('/api/attendences') }} \\
-H "Authorization: Bearer YOUR_TOKEN" \\
-H "Content-Type: application/json" \\
-d '{
    "employee_id": 1,
    "date": "2026-06-11",
    "check_in": "09:00",
    "check_out": "18:00",
    "present": true
}'`,
            resp: `{\n    "success": true,\n    "message": "Attendence created successfully"\n}`
        },
        'commits': {
            title: '4. Commits API',
            desc: 'Create Commit Log',
            curl: `curl -X POST {{ url('/api/commits') }} \\
-H "Authorization: Bearer YOUR_TOKEN" \\
-H "Content-Type: application/json" \\
-d '{
    "employee_id": 1,
    "commit_hash": "a1b2c3d4",
    "commit_message": "Added Token Authentication",
    "lines_added": 120,
    "lines_deleted": 15,
    "commit_date": "2026-06-11"
}'`,
            resp: `{\n    "success": true,\n    "message": "Commit created successfully"\n}`
        },
        'meetings': {
            title: '5. Meetings API',
            desc: 'Create Meeting',
            curl: `curl -X POST {{ url('/api/meetings') }} \\
-H "Authorization: Bearer YOUR_TOKEN" \\
-H "Content-Type: application/json" \\
-d '{
    "title": "Daily Standup",
    "notes": "Discussed API integration.",
    "meeting_date": "2026-06-11"
}'`,
            resp: `{\n    "success": true,\n    "message": "Meeting created successfully"\n}`
        },
        'reports': {
            title: '6. Reports API',
            desc: 'Generate AI Report',
            curl: `curl -X POST {{ url('/api/reports/generate') }} \\
-H "Authorization: Bearer YOUR_TOKEN" \\
-H "Content-Type: application/json" \\
-d '{
    "start_date": "2026-06-01",
    "end_date": "2026-06-10"
}'`,
            resp: `{\n    "success": true,\n    "message": "Report generated successfully"\n}`
        },
        'health': {
            title: '7. Health Check',
            desc: 'Check API Status',
            curl: `curl -X GET {{ url('/api/health') }} \\
-H "Authorization: Bearer YOUR_TOKEN"`,
            resp: `{\n    "success": true,\n    "message": "API is healthy"\n}`
        }
    };

    function showApiDoc(key, btn) {
        // Update active button state
        document.querySelectorAll('.api-nav-btn').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');

        // Update content
        const data = apiDocs[key];
        document.getElementById('apiTitle').textContent = data.title;
        document.getElementById('apiDesc').textContent = data.desc;
        document.getElementById('apiCurl').textContent = data.curl;
        document.getElementById('apiResp').textContent = data.resp;
    }

    // GitLab Integration scripts
    const btnTestGitLab = document.getElementById('btnTestGitLab');
    const btnLoadRepos = document.getElementById('btnLoadRepos');
    const btnSyncCommits = document.getElementById('btnSyncCommits');
    const gitlabRepoSelect = document.getElementById('gitlabRepoSelect');
    const gitlabReposContainer = document.getElementById('gitlabReposContainer');
    const gitlabConnectionStatus = document.getElementById('gitlabConnectionStatus');

    if (btnTestGitLab) {
        btnTestGitLab.addEventListener('click', function () {
            gitlabConnectionStatus.textContent = 'Testing connection...';
            gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-warning border-warning';
            
            fetch('{{ url("/developer-tools/gitlab/test") }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        gitlabConnectionStatus.textContent = data.message;
                        gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-success border-success';
                    } else {
                        gitlabConnectionStatus.textContent = data.message;
                        gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-danger border-danger';
                    }
                })
                .catch(err => {
                    console.error(err);
                    gitlabConnectionStatus.textContent = 'Network error testing connection.';
                    gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-danger border-danger';
                });
        });
    }

    if (btnLoadRepos) {
        btnLoadRepos.addEventListener('click', function () {
            gitlabConnectionStatus.textContent = 'Loading repositories...';
            gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-warning border-warning';
            
            fetch('{{ url("/developer-tools/gitlab/projects") }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        gitlabConnectionStatus.textContent = 'Repositories loaded successfully.';
                        gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-success border-success';
                        
                        // Clear previous options except placeholder
                        gitlabRepoSelect.innerHTML = '<option value="">Choose repository...</option>';
                        data.projects.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = `${p.name} (ID: ${p.id})`;
                            gitlabRepoSelect.appendChild(opt);
                        });
                        
                        gitlabReposContainer.classList.remove('d-none');
                    } else {
                        gitlabConnectionStatus.textContent = data.message;
                        gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-danger border-danger';
                    }
                })
                .catch(err => {
                    console.error(err);
                    gitlabConnectionStatus.textContent = 'Network error loading repositories.';
                    gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-danger border-danger';
                });
        });
    }

    if (btnSyncCommits) {
        btnSyncCommits.addEventListener('click', function () {
            const projId = gitlabRepoSelect.value;
            if (!projId) {
                alert('Please select a repository first.');
                return;
            }

            gitlabConnectionStatus.textContent = 'Syncing commits in progress...';
            gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-warning border-warning';
            btnSyncCommits.disabled = true;

            fetch('{{ url("/developer-tools/gitlab/sync") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ project_id: projId })
            })
            .then(res => res.json())
            .then(data => {
                btnSyncCommits.disabled = false;
                if (data.success) {
                    gitlabConnectionStatus.textContent = data.message;
                    gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-success border-success';
                    
                    // Show stats
                    document.getElementById('syncStatsContainer').classList.remove('d-none');
                    document.getElementById('statRepoName').textContent = data.repository_name || 'N/A';
                    document.getElementById('statTotalCommits').textContent = data.count || '0';
                    document.getElementById('statContributors').textContent = data.total_contributors || '0';
                    document.getElementById('statActiveContributor').textContent = data.most_active_contributor || 'N/A';
                    
                    const now = new Date().toLocaleString();
                    const lastCommit = data.last_commit_date || 'N/A';
                    document.getElementById('statLastCommit').textContent = `Sync: ${now} | Commit: ${lastCommit}`;
                    
                    alert(data.message);
                } else {
                    gitlabConnectionStatus.textContent = data.message;
                    gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-danger border-danger';
                }
            })
            .catch(err => {
                btnSyncCommits.disabled = false;
                console.error(err);
                gitlabConnectionStatus.textContent = 'Network error syncing commits.';
                gitlabConnectionStatus.className = 'p-3 rounded border border-dashed text-center mb-3 text-danger border-danger';
            });
        });
    }
</script>
@endpush
