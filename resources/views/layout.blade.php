<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            font-size: 14.5px;
            --app-bg: #F8FAFC;
            --panel-bg: #FFFFFF;
            --panel-soft: #F1F5F9;
            --panel-border: #E2E8F0;
            --muted: #64748B;
            --text: #0F172A;
            --purple: #2563EB;
            /* Primary Blue */
            --purple-2: #3B82F6;
            /* Secondary Blue */
            --green: #10B981;
            --blue: #2563EB;
            --orange: #F59E0B;
            --red: #EF4444;
        }

        * {
            letter-spacing: 0;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--app-bg);
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
            background: var(--panel-bg);
            display: flex;
            flex-direction: column;
            gap: 28px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--purple), var(--purple-2));
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
            white-space: nowrap;
        }

        .nav-section-title {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 18px 10px 10px;
        }

        .sidebar-link {
            color: var(--muted);
            text-decoration: none;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .sidebar-link i {
            font-size: 18px;
            width: 24px;
            text-align: center;
            color: var(--muted);
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            color: var(--purple);
            background: rgba(37, 99, 235, 0.05);
        }

        .sidebar-link:hover i {
            color: var(--purple);
        }

        .sidebar-link.active {
            color: #fff !important;
            background: var(--purple);
        }

        .sidebar-link.active i {
            color: #fff !important;
        }

        .sidebar-user {
            margin-top: auto;
            min-height: 72px;
            padding: 14px;
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background: var(--panel-bg);
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, #f7c26b, #2f8dff);
            border: 2px solid var(--panel-border);
        }

        .app-main {
            min-width: 0;
        }

        .topbar {
            height: 72px;
            padding: 0 40px;
            border-bottom: 1px solid var(--panel-border);
            background: var(--panel-bg);
            display: flex;
            align-items: center;
            gap: 26px;
        }

        .topbar-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
        }

        .search-box {
            width: min(220px, 30vw);
            height: 38px;
            border: 1px solid var(--panel-border);
            border-radius: 999px;
            background: var(--panel-soft);
            color: var(--text);
            padding: 0 40px 0 16px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .search-box:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            background: #fff;
        }

        .search-wrap {
            position: relative;
        }

        .search-wrap i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 16px;
        }

        .notification-btn {
            position: relative;
            width: 36px;
            height: 36px;
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            color: var(--muted);
            background: var(--panel-bg);
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            color: var(--purple);
            background: var(--panel-soft);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--red);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            display: grid;
            place-items: center;
        }

        .content-wrap {
            padding: 34px 40px;
        }

        .glass-card {
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            background: var(--panel-bg);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 10px 20px -10px rgba(0, 0, 0, 0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--purple);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .glass-card:hover::before {
            opacity: 1;
        }

        .btn-ai {
            min-height: 40px;
            border: 0;
            border-radius: 8px;
            color: #fff !important;
            font-weight: 600;
            padding: 0 20px;
            background: linear-gradient(135deg, var(--purple), var(--purple-2));
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-ai:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.25);
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff !important;
        }

        .btn-ai:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.15);
        }

        .btn-outline-light {
            border-color: var(--panel-border) !important;
            color: var(--muted) !important;
            background: #fff !important;
        }

        .btn-outline-light:hover {
            background: var(--panel-soft) !important;
            border-color: var(--muted) !important;
            color: var(--text) !important;
        }

        .date-pill {
            min-height: 40px;
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background: var(--panel-bg);
            color: var(--text);
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 18px;
            color: var(--text);
        }

        .form-label {
            color: var(--text);
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--text);
            padding: 8px 12px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--purple);
            background-color: #ffffff;
            color: var(--text);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .data-table {
            margin: 0;
            color: var(--text);
            background: var(--panel-bg) !important;
            border-collapse: collapse;
        }

        .data-table th {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--panel-border) !important;
            padding: 14px 16px;
            background: #F8FAFC !important;
        }

        .data-table td {
            border-bottom: 1px solid var(--panel-border) !important;
            vertical-align: middle;
            background: var(--panel-bg) !important;
            color: var(--text);
            padding: 14px 16px;
        }

        .data-table tbody tr {
            background: var(--panel-bg) !important;
            transition: background-color 0.15s ease;
        }

        .data-table tbody tr:hover {
            background-color: #F8FAFC !important;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            background: rgba(148, 163, 184, .12);
        }

        .status-pill.green {
            background: rgba(16, 185, 129, .12);
            color: #047857;
        }

        .status-pill.blue {
            background: rgba(59, 130, 246, .12);
            color: #1d4ed8;
        }

        .status-pill.orange {
            background: rgba(245, 158, 11, .12);
            color: #b45309;
        }

        .status-pill.red {
            background: rgba(239, 68, 68, .12);
            color: #b91c1c;
        }

        .status-pill.purple {
            background: rgba(37, 99, 235, .12);
            color: #1d4ed8;
        }

        .pagination {
            --bs-pagination-bg: var(--panel-bg);
            --bs-pagination-border-color: var(--panel-border);
            --bs-pagination-color: var(--muted);
            --bs-pagination-hover-bg: #F1F5F9;
            --bs-pagination-hover-color: var(--text);
            --bs-pagination-active-bg: var(--purple);
            --bs-pagination-active-border-color: var(--purple);
            --bs-pagination-active-color: #fff;
            --bs-pagination-disabled-bg: var(--panel-bg);
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
            color: var(--purple) !important;
        }

        .dataTables_filter input {
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background-color: var(--panel-bg);
            color: var(--text);
            padding: 6px 12px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .dataTables_filter input:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .dataTables_length select {
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            background-color: var(--panel-bg);
            color: var(--text);
            padding: 6px 12px;
            outline: none;
        }

        .dropdown-menu-item:hover {
            background-color: #F1F5F9 !important;
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
                font-size: 20px;
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


        /* --- AI Assistant Widget - Blue SaaS Theme --- */
        .ai-widget-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-family: inherit;
        }

        .ai-widget-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
            cursor: pointer;
            border: none;
            position: relative;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .ai-widget-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
        }

        .ai-notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .ai-chat-panel {
            position: absolute;
            bottom: 76px;
            right: 0;
            width: 380px;
            height: 620px;
            max-height: calc(100vh - 100px);
            max-width: calc(100vw - 40px);
            background: #f8fafc;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px) scale(0.96);
            transform-origin: bottom right;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .ai-chat-panel.open {
            opacity: 1;
            pointer-events: all;
            transform: translateY(0) scale(1);
        }

        .ai-chat-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .ai-chat-header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ai-chat-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            position: relative;
        }

        .ai-status-indicator {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: #22c55e;
            border: 2px solid #1e40af;
            border-radius: 50%;
        }

        .ai-chat-title {
            font-weight: 700;
            font-size: 16px;
            margin: 0;
            line-height: 1.2;
        }

        .ai-chat-subtitle {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .ai-header-actions {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .ai-btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            cursor: pointer;
            font-size: 14px;
        }

        .ai-btn-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .ai-chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
            background: #f8fafc;
            scroll-behavior: smooth;
        }

        .ai-message-wrapper {
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-width: 85%;
            animation: slideUp 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
        }

        .ai-message-wrapper.user {
            align-self: flex-end;
        }

        .ai-message-wrapper.assistant {
            align-self: flex-start;
        }

        .ai-message {
            padding: 12px 16px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .ai-message.user {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            border-radius: 16px 16px 4px 16px;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
        }

        .ai-message.assistant {
            background: #fff;
            color: #1e293b;
            border-radius: 16px 16px 16px 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }

        .ai-message.assistant strong {
            font-weight: 700;
            color: #0f172a;
        }

        .ai-message-time {
            font-size: 11px;
            color: #94a3b8;
            margin: 0 4px;
        }

        .ai-message-wrapper.user .ai-message-time {
            align-self: flex-end;
        }

        .ai-quick-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            margin-top: 4px;
        }

        .ai-suggested-btn {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 13.5px;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            text-align: right;
            max-width: 95%;
            white-space: normal;
        }

        .ai-suggested-btn:hover {
            transform: translateY(-1px);
            border-color: #3b82f6;
            color: #1d4ed8;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);
        }

        .ai-chat-footer {
            padding: 16px 20px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            z-index: 2;
        }

        .ai-chat-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .ai-chat-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 24px;
            background: #fff;
            padding: 14px 48px 14px 20px;
            outline: none;
            font-size: 14.5px;
            color: #1e293b;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            resize: none;
            height: 50px;
            overflow-y: hidden;
            line-height: 20px;
        }

        .ai-chat-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .ai-chat-input::placeholder {
            color: #94a3b8;
        }

        .ai-chat-send {
            position: absolute;
            right: 6px;
            bottom: 6px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: not-allowed;
            transition: all 0.2s;
            font-size: 16px;
        }

        .ai-chat-send.active {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .ai-chat-send.active:hover {
            transform: scale(1.05);
        }

        .ai-typing-indicator {
            display: flex;
            gap: 4px;
            padding: 14px 18px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px 16px 16px 4px;
            align-self: flex-start;
            align-items: center;
            width: fit-content;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            animation: slideUp 0.3s ease-out;
        }

        .ai-typing-dot {
            width: 6px;
            height: 6px;
            background: #94a3b8;
            border-radius: 50%;
            animation: ai-typing 1.4s infinite ease-in-out both;
        }

        .ai-typing-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .ai-typing-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes ai-typing {

            0%,
            80%,
            100% {
                transform: scale(0);
                opacity: 0.3;
            }

            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .ai-widget-container {
                bottom: 20px;
                right: 20px;
            }

            .ai-chat-panel {
                position: fixed;
                bottom: 0;
                right: 0;
                width: 100vw;
                height: 100vh;
                max-height: 100vh;
                max-width: 100vw;
                border-radius: 0;
                z-index: 1060;
                transform: translateY(100%);
            }

            .ai-chat-panel.open {
                transform: translateY(0);
            }

            .ai-chat-header {
                padding-top: 24px;
                /* for safe area */
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
                <div class="brand-title">AI Manager <i class="bi bi-stars text-primary"></i></div>
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
                        class="bi bi-calendar3"></i><span>Attendance</span></a>
                <a href="{{ url('/commits') }}" class="sidebar-link {{ request()->is('commits') ? 'active' : '' }}"><i
                        class="bi bi-git"></i><span>Commits</span></a>
                <a href="{{ url('/meetings') }}"
                    class="sidebar-link {{ request()->is('meetings') ? 'active' : '' }}"><i
                        class="bi bi-calendar4-week"></i><span>Meetings</span></a>
                <a href="{{ url('/teams') }}" class="sidebar-link {{ request()->is('teams*') ? 'active' : '' }}"><i
                        class="bi bi-microsoft-teams"></i><span>Teams</span></a>
                <a href="{{ url('/projects') }}"
                    class="sidebar-link {{ request()->is('projects*') ? 'active' : '' }}"><i
                        class="bi bi-briefcase"></i><span>Projects</span></a>

                <div class="nav-section-title">AI & Reports</div>
                <a href="{{ url('/leadership-insights') }}"
                    class="sidebar-link {{ request()->is('leadership-insights') ? 'active' : '' }}"><i
                        class="bi bi-graph-up-arrow"></i><span>Leadership Insights</span></a>
                <a href="{{ url('/reports') }}"
                    class="sidebar-link {{ request()->is('reports*') ? 'active' : '' }}"><i
                        class="bi bi-bar-chart-line"></i><span>Reports</span></a>

                <div class="nav-section-title">System</div>
                <a href="{{ url('/developer-tools') }}"
                    class="sidebar-link {{ request()->is('developer-tools*') ? 'active' : '' }}"><i
                        class="bi bi-code-slash"></i><span>Developer Tools</span></a>
            </nav>

            <div class="sidebar-user d-flex align-items-center gap-3">
                <img class="avatar" src="https://i.pravatar.cc/96?img=12" alt="Mayank Patel">
                <div class="user-copy min-w-0">
                    <div class="fw-bold text-truncate text-dark">Mayank Patel</div>
                    <div class="small muted-text text-truncate">Project Manager</div>
                </div>
            </div>
        </aside>

        <main class="app-main">
            <header class="topbar">
                <div class="topbar-title me-auto">@yield('page-title', 'Dashboard')</div>

                @if (
                    !request()->is('/') &&
                        !request()->is('developer-tools') &&
                        !request()->is('reports/*') &&
                        !request()->is('leadership-insights'))
                    <div class="search-wrap">
                        <input class="search-box" id="globalSearch" type="search" placeholder="Search...">
                        <i class="bi bi-search"></i>
                    </div>
                @endif

                {{-- <button type="button" class="notification-btn" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge">3</span>
                </button> --}}

                <div class="dropdown">
                    <div class="d-flex align-items-center gap-3" data-bs-toggle="dropdown" aria-expanded="false"
                        style="cursor: pointer;">
                        <div class="text-end d-none d-sm-block">
                            <div class="fw-bold text-dark">Mayank Patel <i
                                    class="bi bi-chevron-down small muted-text"></i></div>
                            <div class="small muted-text">Project Manager</div>
                        </div>
                        <img class="avatar" src="https://i.pravatar.cc/96?img=12" alt="Mayank Patel">
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        {{-- <li><a class="dropdown-item py-2 dropdown-menu-item" href="#"><i
                                    class="bi bi-person me-2"></i> Profile</a></li> --}}
                        {{-- <li><a class="dropdown-item py-2 dropdown-menu-item" href="#"><i
                                    class="bi bi-gear me-2"></i> Settings</a></li>
                        <li> --}}
                        <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 dropdown-menu-item text-danger"
                                    style="border:none; background:none; width:100%; text-align:left;">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <div class="content-wrap">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- AI Assistant Widget - SaaS Blue Theme -->
    <div class="ai-widget-container">
        <!-- Chat Popup Window -->
        <div class="ai-chat-panel" id="aiChatPanel">
            <div class="ai-chat-header">
                <div class="ai-chat-header-info">
                    <div class="ai-chat-avatar">
                        <i class="bi bi-robot"></i>
                        <div class="ai-status-indicator"></div>
                    </div>
                    <div>
                        <h2 class="ai-chat-title">AI Manager Assistant</h2>
                        <div class="ai-chat-subtitle"><i class="bi bi-dot"
                                style="color:#22c55e; font-size: 20px; margin-left: -6px; margin-right: -4px;"></i>
                            Online</div>
                    </div>
                </div>
                <div class="ai-header-actions">
                    <button class="ai-btn-icon" id="aiClearChatBtn" title="New Chat">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button class="ai-btn-icon" id="aiMinimizeBtn" title="Minimize">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button class="ai-btn-icon" id="aiCloseBtn" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="ai-chat-body" id="aiChatBody">
                <!-- Welcome Message -->
                <div class="ai-message-wrapper assistant" id="aiWelcomeMsg">
                    <div class="ai-message assistant">
                        <strong>👋 Welcome to AI Manager Assistant</strong><br><br>
                        I can help you analyze:<br><br>
                        • Employees<br>
                        • Teams<br>
                        • Projects<br>
                        • Tasks<br>
                        • Meetings<br>
                        • GitLab Activity<br>
                        • Reports
                    </div>
                    <div class="ai-message-time" id="aiWelcomeTime"></div>
                </div>

                <!-- Quick Action Chips -->
                <div class="ai-quick-actions" id="aiSuggestedContainer">
                    <button class="ai-suggested-btn">Who is the top performer?</button>
                    <button class="ai-suggested-btn">Show employee productivity.</button>
                    <button class="ai-suggested-btn">Show team performance.</button>
                    <button class="ai-suggested-btn">Show attendance insights.</button>
                    <button class="ai-suggested-btn">Show GitLab commit analysis.</button>
                    <button class="ai-suggested-btn">Show project risks.</button>
                    <button class="ai-suggested-btn">Show delayed tasks.</button>
                </div>
            </div>

            <div class="ai-chat-footer">
                <div class="ai-chat-input-wrapper">
                    <textarea class="ai-chat-input" id="aiChatInput" placeholder="Write your message..." rows="1"></textarea>
                    <button class="ai-chat-send" id="aiChatSendBtn" disabled>
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Action Button -->
        <button class="ai-widget-btn" id="aiWidgetBtn" aria-label="Open AI Assistant">
            <i class="bi bi-chat-dots-fill"></i>
            <div class="ai-notification-badge" id="aiNotificationBadge">1</div>
        </button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('click', function(event) {
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
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#64748B',
                background: '#FFFFFF',
                color: '#0F172A',
            }).then((result) => {
                if (result.isConfirmed && form) {
                    form.submit();
                }
            });
        });
    </script>
    <script>
        // AI Assistant Widget Logic
        const aiWidgetBtn = document.getElementById('aiWidgetBtn');
        const aiChatPanel = document.getElementById('aiChatPanel');
        const aiCloseBtn = document.getElementById('aiCloseBtn');
        const aiMinimizeBtn = document.getElementById('aiMinimizeBtn');
        const aiChatInput = document.getElementById('aiChatInput');
        const aiChatSendBtn = document.getElementById('aiChatSendBtn');
        const aiChatBody = document.getElementById('aiChatBody');
        const aiClearChatBtn = document.getElementById('aiClearChatBtn');
        const aiNotificationBadge = document.getElementById('aiNotificationBadge');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Extract context from URL
        const currentPath = window.location.pathname;
        let moduleContext = 'Dashboard';
        if (currentPath.includes('projects')) moduleContext = 'Projects';
        else if (currentPath.includes('employees')) moduleContext = 'Employees';
        else if (currentPath.includes('tasks')) moduleContext = 'Tasks';
        else if (currentPath.includes('teams')) moduleContext = 'Teams';
        else if (currentPath.includes('commits')) moduleContext = 'Commits';
        else if (currentPath.includes('meetings')) moduleContext = 'Meetings';

        // Set welcome time
        const welcomeTimeEl = document.getElementById('aiWelcomeTime');
        if (welcomeTimeEl) {
            welcomeTimeEl.textContent = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function toggleChat() {
            aiChatPanel.classList.toggle('open');
            if (aiChatPanel.classList.contains('open')) {
                aiChatInput.focus();
                scrollToBottom();
                if (aiNotificationBadge) aiNotificationBadge.style.display = 'none';
            }
        }

        aiWidgetBtn.addEventListener('click', toggleChat);
        aiCloseBtn.addEventListener('click', () => aiChatPanel.classList.remove('open'));
        if (aiMinimizeBtn) aiMinimizeBtn.addEventListener('click', () => aiChatPanel.classList.remove('open'));

        // Auto-resize textarea
        aiChatInput.addEventListener('input', function() {
            this.style.height = '50px';
            this.style.height = (this.scrollHeight < 120 ? this.scrollHeight : 120) + 'px';

            if (this.value.trim().length > 0) {
                aiChatSendBtn.disabled = false;
                aiChatSendBtn.classList.add('active');
            } else {
                aiChatSendBtn.disabled = true;
                aiChatSendBtn.classList.remove('active');
            }
        });

        // Enter to send, Shift+Enter for newline
        aiChatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!this.disabled && this.value.trim().length > 0) {
                    sendMessage();
                }
            }
        });

        aiChatSendBtn.addEventListener('click', sendMessage);

        document.querySelectorAll('.ai-suggested-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                aiChatInput.value = this.textContent;
                aiChatSendBtn.disabled = false;
                sendMessage();
            });
        });

        aiClearChatBtn.addEventListener('click', function() {
            if (confirm('Start a new chat and clear history?')) {
                $.ajax({
                    url: '{{ url("/ai-chat/clear") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function() {
                        const welcomeMsg = document.getElementById('aiWelcomeMsg');
                        const suggestedContainer = document.getElementById('aiSuggestedContainer');

                        aiChatBody.innerHTML = '';
                        if (welcomeMsg) {
                            welcomeMsg.style.display = 'flex';
                            aiChatBody.appendChild(welcomeMsg);
                        }
                        if (suggestedContainer) {
                            aiChatBody.appendChild(suggestedContainer);
                        }
                    }
                });
            }
        });

        function appendMessage(text, role) {
            const time = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

            const wrapperDiv = document.createElement('div');
            wrapperDiv.className = `ai-message-wrapper ${role}`;

            const msgDiv = document.createElement('div');
            msgDiv.className = `ai-message ${role}`;

            // Format basic Markdown
            let formattedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            formattedText = formattedText.replace(/\n/g, '<br>');
            msgDiv.innerHTML = formattedText;

            const timeDiv = document.createElement('div');
            timeDiv.className = 'ai-message-time';
            timeDiv.textContent = time;

            wrapperDiv.appendChild(msgDiv);
            wrapperDiv.appendChild(timeDiv);

            const suggestedContainer = document.getElementById('aiSuggestedContainer');

            if (suggestedContainer && suggestedContainer.parentNode === aiChatBody) {
                aiChatBody.insertBefore(wrapperDiv, suggestedContainer);
            } else {
                aiChatBody.appendChild(wrapperDiv);
            }
            scrollToBottom();
        }

        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'ai-typing-indicator';
            typingDiv.id = 'aiTypingIndicator';
            typingDiv.innerHTML =
                '<div class="ai-typing-dot"></div><div class="ai-typing-dot"></div><div class="ai-typing-dot"></div>';

            const suggestedContainer = document.getElementById('aiSuggestedContainer');
            if (suggestedContainer && suggestedContainer.parentNode === aiChatBody) {
                aiChatBody.insertBefore(typingDiv, suggestedContainer);
            } else {
                aiChatBody.appendChild(typingDiv);
            }
            scrollToBottom();
        }

        function hideTyping() {
            const typingDiv = document.getElementById('aiTypingIndicator');
            if (typingDiv) typingDiv.remove();
        }

        function scrollToBottom() {
            setTimeout(() => {
                aiChatBody.scrollTop = aiChatBody.scrollHeight;
            }, 50);
        }

        function loadHistory() {
            $.get('{{ url("/ai-chat/history") }}', function(data) {
                if (data && data.history && data.history.length > 0) {
                    const welcomeMsg = document.getElementById('aiWelcomeMsg');
                    if (welcomeMsg) welcomeMsg.style.display = 'none';

                    data.history.forEach(msg => {
                        appendMessage(msg.content, msg.role);
                    });
                }
            });
        }

        function sendMessage() {
            const text = aiChatInput.value.trim();
            if (!text) return;

            const welcomeMsg = document.getElementById('aiWelcomeMsg');
            if (welcomeMsg) welcomeMsg.style.display = 'none';

            appendMessage(text, 'user');
            aiChatInput.value = '';
            aiChatInput.style.height = '50px';
            aiChatInput.disabled = true;
            aiChatSendBtn.disabled = true;
            aiChatSendBtn.classList.remove('active');
            showTyping();

            $.ajax({
                url: '{{ url("/ai-chat/send") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    message: text,
                    context: moduleContext
                },
                success: function(response) {
                    hideTyping();
                    aiChatInput.disabled = false;
                    aiChatInput.focus();
                    if (response && response.reply) {
                        appendMessage(response.reply, 'assistant');
                    } else {
                        appendMessage("Sorry, I couldn't process that request.", 'assistant');
                    }
                },
                error: function() {
                    hideTyping();
                    aiChatInput.disabled = false;
                    appendMessage("Connection error. Please try again.", 'assistant');
                }
            });
        }

        // Initialize history
        loadHistory();
    </script>
    @stack('scripts')
</body>

</html>
