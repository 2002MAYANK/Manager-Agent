<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            font-size: 14.5px;
            --app-bg: #050b16;
            --panel-bg: #0d1626;
            --panel-soft: #101b2e;
            --panel-border: rgba(148, 163, 184, .16);
            --muted: #9ca7bc;
            --text: #f7f8fb;
            --purple: #6d39f5;
            --purple-2: #9b4dff;
            --green: #20c66b;
            --blue: #3478ff;
            --orange: #ff8a14;
            --red: #ff4d5c;
        }

        * {
            letter-spacing: 0;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at 70% -10%, rgba(61, 91, 140, .18), transparent 28rem),
                linear-gradient(135deg, #060b18 0%, #081222 48%, #050914 100%);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 290px minmax(0, 1fr);
        }

        .app-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 28px 20px 24px;
            border-right: 1px solid var(--panel-border);
            background: rgba(5, 11, 22, .82);
            backdrop-filter: blur(18px);
            display: flex;
            flex-direction: column;
            gap: 28px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .brand-mark {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-weight: 800;
            background: linear-gradient(135deg, #5b35f3, #913eff);
            box-shadow: 0 14px 34px rgba(96, 56, 255, .35);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 800;
            white-space: nowrap;
        }

        .nav-section-title {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            margin: 18px 10px 10px;
        }

        .sidebar-link {
            color: #f1f5fb;
            text-decoration: none;
            height: 56px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 16px;
            border-radius: 8px;
            font-weight: 600;
        }

        .sidebar-link i {
            font-size: 21px;
            width: 24px;
            text-align: center;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: #fff;
            background: linear-gradient(135deg, #5b35f3, #8739fb);
        }

        .sidebar-user {
            margin-top: auto;
            min-height: 72px;
            padding: 14px;
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background: rgba(16, 27, 46, .95);
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, #f7c26b, #2f8dff);
            border: 2px solid rgba(255, 255, 255, .12);
        }

        .app-main {
            min-width: 0;
        }

        .topbar {
            height: 94px;
            padding: 0 28px 0 40px;
            border-bottom: 1px solid var(--panel-border);
            background: rgba(5, 10, 20, .7);
            backdrop-filter: blur(18px);
            display: flex;
            align-items: center;
            gap: 26px;
        }

        .topbar-title {
            font-size: 27px;
            font-weight: 800;
        }

        .search-box {
            width: min(220px, 30vw);
            height: 45px;
            border: 1px solid var(--panel-border);
            border-radius: 999px;
            background: var(--panel-soft);
            color: #fff;
            padding: 0 48px 0 22px;
            outline: none;
        }

        .search-wrap {
            position: relative;
        }

        .search-wrap i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #aeb8ca;
            font-size: 21px;
        }

        .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border: 0;
            color: #b8c2d4;
            background: transparent;
            font-size: 23px;
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #6d39f5;
            color: #fff;
            font-size: 12px;
            display: grid;
            place-items: center;
        }

        .content-wrap {
            padding: 34px 28px 24px 40px;
        }

        .glass-card {
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background: linear-gradient(145deg, rgba(17, 29, 48, .88), rgba(8, 15, 28, .88));
            box-shadow: 0 18px 50px rgba(0, 0, 0, .18);
        }

        .btn-ai {
            min-height: 50px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            padding: 0 24px;
            background: linear-gradient(135deg, #4d32db, #9a42f5);
            box-shadow: 0 10px 24px rgba(107, 57, 245, .25);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-ai:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(107, 57, 245, .4);
            background: linear-gradient(135deg, #5b3eff, #a54eff);
            color: #fff;
        }

        .btn-ai:active {
            transform: translateY(1px);
            box-shadow: 0 6px 14px rgba(107, 57, 245, .3);
        }

        .date-pill {
            min-height: 56px;
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background: var(--panel-soft);
            color: #fff;
            padding: 0 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            white-space: nowrap;
        }

        .muted-text {
            color: var(--muted);
        }

        .page-grid {
            display: grid;
            grid-template-columns: minmax(280px, 390px) minmax(0, 1fr);
            gap: 22px;
            align-items: start;
        }

        .page-panel {
            padding: 22px;
        }

        .panel-title {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 18px;
        }

        .form-label {
            color: #d8deeb;
            font-weight: 700;
        }

        .form-control,
        .form-select {
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background-color: #0c1524;
            color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(109, 57, 245, .72);
            background-color: #0c1524;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(109, 57, 245, .18);
        }

        .form-control::placeholder {
            color: #68758b;
        }

        .data-table {
            margin: 0;
            color: var(--text);
            background: transparent !important;
        }

        .data-table th {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            border-color: var(--panel-border);
            white-space: nowrap;
            background: transparent !important;
        }

        .data-table td {
            border-color: rgba(148, 163, 184, .11);
            vertical-align: middle;
            background: transparent !important;
            color: var(--text);
        }

        .data-table tbody tr {
            background: transparent !important;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            border-radius: 999px;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
            background: rgba(148, 163, 184, .18);
        }

        .status-pill.green {
            background: rgba(32, 198, 107, .22);
            color: #5df39a;
        }

        .status-pill.blue {
            background: rgba(52, 120, 255, .22);
            color: #78a5ff;
        }

        .status-pill.orange {
            background: rgba(255, 138, 20, .22);
            color: #ffb05f;
        }

        .status-pill.red {
            background: rgba(255, 77, 92, .2);
            color: #ff7d89;
        }

        .pagination {
            --bs-pagination-bg: transparent;
            --bs-pagination-border-color: var(--panel-border);
            --bs-pagination-color: var(--muted);
            --bs-pagination-hover-bg: var(--panel-soft);
            --bs-pagination-hover-color: #fff;
            --bs-pagination-active-bg: #6d39f5;
            --bs-pagination-active-border-color: #6d39f5;
            --bs-pagination-disabled-bg: transparent;
            --bs-pagination-disabled-border-color: var(--panel-border);
            margin-bottom: 0;
        }

        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            border-radius: 6px;
        }

        .page-link {
            border-radius: 6px;
            margin: 0 3px;
            box-shadow: none !important;
        }

        .sort-link {
            transition: color 0.2s;
        }

        .sort-link:hover,
        .sort-link.active {
            color: #fff !important;
        }

        @media (max-width: 1199.98px) {
            .app-shell {
                grid-template-columns: 88px minmax(0, 1fr);
            }

            .app-sidebar {
                padding: 22px 14px;
                align-items: center;
            }

            .brand-title,
            .nav-section-title,
            .sidebar-link span,
            .sidebar-user .user-copy,
            .sidebar-user i {
                display: none;
            }

            .sidebar-link {
                width: 56px;
                justify-content: center;
                padding: 0;
            }

            .topbar {
                padding-left: 24px;
            }
        }

        @media (max-width: 767.98px) {
            .app-shell {
                display: block;
            }

            .app-sidebar {
                position: static;
                height: auto;
                flex-direction: row;
                overflow-x: auto;
                border-right: 0;
                border-bottom: 1px solid var(--panel-border);
            }

            .topbar {
                height: auto;
                padding: 18px;
                flex-wrap: wrap;
            }

            .topbar-title {
                font-size: 23px;
            }

            .search-box {
                width: 100%;
            }

            .content-wrap {
                padding: 22px 16px;
            }

            .page-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="app-shell">
        <aside class="app-sidebar">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">PM</div>
                <div class="brand-title">AI Manager <i class="bi bi-stars text-purple"></i></div>
            </div>

            <nav class="d-flex flex-column gap-1">
                <a href="{{ url('/') }}" class="sidebar-link {{ request()->is('/') ? 'active' : '' }}"><i
                        class="bi bi-grid-1x2"></i><span>Dashboard</span></a>

                <div class="nav-section-title">Management</div>
                <a href="{{ url('/employees') }}"
                    class="sidebar-link {{ request()->is('employees') ? 'active' : '' }}"><i
                        class="bi bi-people"></i><span>Employees</span></a>
                <a href="{{ url('/tasks') }}" class="sidebar-link {{ request()->is('tasks') ? 'active' : '' }}"><i
                        class="bi bi-calendar2-check"></i><span>Tasks</span></a>
                <a href="{{ url('/attendence') }}"
                    class="sidebar-link {{ request()->is('attendence') ? 'active' : '' }}"><i
                        class="bi bi-calendar3"></i><span>Attendence</span></a>
                <a href="{{ url('/commits') }}" class="sidebar-link {{ request()->is('commits') ? 'active' : '' }}"><i
                        class="bi bi-git"></i><span>Commits</span></a>
                <a href="{{ url('/meetings') }}"
                    class="sidebar-link {{ request()->is('meetings') ? 'active' : '' }}"><i
                        class="bi bi-calendar4-week"></i><span>Meetings</span></a>

                <div class="nav-section-title">AI & Reports</div>
                {{-- <a href="#" class="sidebar-link"><i class="bi bi-robot"></i><span>AI Manager</span></a> --}}
                <a href="{{ url('/reports') }}"
                    class="sidebar-link {{ request()->is('reports*') ? 'active' : '' }}"><i
                        class="bi bi-bar-chart-line"></i><span>Reports</span></a>

                <div class="nav-section-title">System</div>
                <a href="{{ url('/developer-tools') }}"
                    class="sidebar-link {{ request()->is('developer-tools*') ? 'active' : '' }}"><i
                        class="bi bi-code-slash"></i><span>Developer Tools</span></a>
                {{-- <a href="#" class="sidebar-link"><i class="bi bi-person-gear"></i><span>Users</span></a> --}}
                {{-- <a href="#" class="sidebar-link"><i class="bi bi-gear"></i><span>Settings</span></a> --}}
            </nav>

            <div class="sidebar-user d-flex align-items-center gap-3">
                <img class="avatar" src="https://i.pravatar.cc/96?img=12" alt="Aman Verma">
                <div class="user-copy min-w-0">
                    <div class="fw-bold text-truncate">Mayank Patel</div>
                    <div class="small muted-text text-truncate">Project Manager</div>
                </div>
                <i class="bi bi-chevron-down ms-auto muted-text"></i>
            </div>
        </aside>

        <main class="app-main">
            <header class="topbar">
                {{-- <i class="bi bi-list fs-3 muted-text"></i> --}}
                <div class="topbar-title me-auto">@yield('page-title', 'Dashboard')</div>

                @if (
                    !request()->is('/') &&
                        !request()->is('developer-tools') &&
                        !request()->is('reports') &&
                        !request()->is('reports/*'))
                    <div class="search-wrap">
                        <input class="search-box" id="globalSearch" type="search" placeholder="Search...">
                        <i class="bi bi-search"></i>
                    </div>
                @endif

                <button type="button" class="notification-btn" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge">3</span>
                </button>

                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-bold">Mayank Patel <i class="bi bi-chevron-down small muted-text"></i></div>
                        <div class="small muted-text">Project Manager</div>
                    </div>
                    <img class="avatar" src="https://i.pravatar.cc/96?img=12" alt="Aman Verma">
                </div>
            </header>

            <div class="content-wrap">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/datatable.js') }}"></script>
    <script>
        document.addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.js-delete-confirm');
            if (!deleteButton) {
                return;
            }

            event.preventDefault();

            const form = deleteButton.closest('form');
            const label = deleteButton.dataset.label || 'this item';

            Swal.fire({
                title: 'Delete item?',
                text: `This will permanently delete ${label}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ff4d5c',
                cancelButtonColor: '#6c757d',
                background: '#0d1626',
                color: '#f7f8fb',
            }).then((result) => {
                if (result.isConfirmed && form) {
                    form.submit();
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
