<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'User Management | AutoPartFlow ERP') ?></title>
    <link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="base-url" content="<?= url() ?>">
    <style>
        /* Scoped User Management Styles */
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --navy-900: #0f1f38;
            --navy-800: #162a4a;
            --indigo-500: #4f5bd5;
            --indigo-600: #4338ca;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --green-dark: #15803d;
            --red: #dc2626;
            --red-soft: #fee2e2;
            --amber: #d97706;
            --amber-soft: #fef3c7;
            --blue: #2563eb;
            --blue-soft: #dbeafe;
            --purple: #7c3aed;
            --purple-soft: #ede9fe;
            --teal: #0d9488;
            --teal-soft: #ccfbf1;
            --radius-lg: 12px;
            --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        }

        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: var(--slate-50); color: var(--slate-900); }
        button, input, select { font: inherit; }

        .app-shell { display: flex; min-height: 100vh; width: 100%; }
        .main { flex: 1; min-width: 0; background: var(--slate-50); display: flex; flex-direction: column; overflow-y: auto; }

        /* Topbar Header */
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid var(--slate-200);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 64px;
        }
        .topbar-brand {
            font-weight: 700;
            font-size: 16px;
            color: var(--navy-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge-admin {
            font-size: 11.5px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            background: #e0e7ff;
            color: #4338ca;
        }
        .search-box {
            position: relative;
            flex: 0 1 420px;
        }
        .search-box svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            fill: var(--slate-400);
            pointer-events: none;
        }
        .search-box input {
            width: 100%;
            padding: 9px 14px 9px 38px;
            border: 1px solid var(--slate-200);
            border-radius: 9px;
            background: var(--slate-50);
            color: var(--slate-800);
            font-size: 13.5px;
            outline: none;
            transition: all 0.15s ease;
        }
        .search-box input:focus {
            background: #ffffff;
            border-color: var(--indigo-500);
            box-shadow: 0 0 0 3px rgba(79, 91, 213, 0.12);
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-btn {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--slate-100);
            color: var(--slate-600);
            text-decoration: none;
            transition: background 0.15s;
        }
        .topbar-btn:hover {
            background: var(--slate-200);
        }
        .topbar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--indigo-500);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 13.5px;
            text-decoration: none;
        }

        /* Page Content Layout */
        .content {
            padding: 28px;
            flex: 1;
        }
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .page-head h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 4px;
            color: var(--navy-900);
            letter-spacing: -0.02em;
        }
        .page-head p {
            margin: 0;
            color: var(--slate-500);
            font-size: 13.5px;
        }
        .head-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background: var(--navy-900);
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #1e3a5f;
        }
        .btn-secondary {
            background: #ffffff;
            border-color: var(--slate-200);
            color: var(--slate-700);
        }
        .btn-secondary:hover {
            background: var(--slate-100);
        }
        .btn-danger {
            background: var(--red);
            color: #ffffff;
        }
        .btn-danger:hover {
            background: #b91c1c;
        }

        /* KPI Cards Grid */
        .kpi-row {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--slate-200);
            padding: 20px;
            box-shadow: var(--shadow);
        }
        .kpi-card-title {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--navy-900);
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .link-reset {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--indigo-500);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }
        .link-reset:hover {
            text-decoration: underline;
        }

        /* Donut / Stats */
        .status-overview {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .donut-wrap {
            position: relative;
            width: 100px;
            height: 100px;
            flex-shrink: 0;
        }
        .donut-wrap svg {
            transform: rotate(-90deg);
            width: 100px;
            height: 100px;
        }
        .donut-inner {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .donut-inner b {
            font-size: 20px;
            font-weight: 800;
            color: var(--navy-900);
            line-height: 1;
        }
        .donut-inner span {
            font-size: 11px;
            color: var(--slate-500);
            margin-top: 2px;
        }
        .status-pills {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }
        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            padding: 6px 10px;
            border-radius: 8px;
            background: var(--slate-50);
        }
        .status-item strong {
            font-weight: 700;
            font-size: 13.5px;
        }

        /* Roles Breakdown Cards */
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .role-filter-card {
            border: 1px solid var(--slate-200);
            background: var(--slate-50);
            border-radius: 10px;
            padding: 14px 12px;
            text-align: left;
            cursor: pointer;
            transition: all 0.15s ease;
            position: relative;
            outline: none;
        }
        .role-filter-card:hover {
            border-color: var(--indigo-500);
            background: #ffffff;
            transform: translateY(-1px);
        }
        .role-filter-card.active {
            border-color: var(--indigo-500);
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(79, 91, 213, 0.2);
        }
        .role-card-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .role-card-count {
            display: block;
            font-size: 22px;
            font-weight: 800;
            color: var(--navy-900);
            line-height: 1.1;
            margin-top: 4px;
        }
        .role-card-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-600);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Two-Column Workspace Layout */
        .main-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1.1fr);
            gap: 20px;
            align-items: start;
        }
        .card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--slate-200);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .card-head {
            padding: 16px 20px;
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .card-head h2 {
            font-size: 16px;
            font-weight: 700;
            color: var(--navy-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .count-tag {
            font-size: 11.5px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--slate-100);
            color: var(--slate-600);
        }

        /* Users Table */
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }
        th {
            background: var(--slate-50);
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 1px solid var(--slate-200);
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--slate-100);
            vertical-align: middle;
            color: var(--slate-800);
        }
        tr:hover td {
            background: #fafcff;
        }
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            flex-shrink: 0;
        }
        .user-name {
            font-weight: 700;
            color: var(--navy-900);
            line-height: 1.2;
        }
        .user-meta {
            font-size: 12px;
            color: var(--slate-500);
            margin-top: 2px;
        }

        /* Role Badges */
        .badge-role {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-role--owner { background: var(--purple-soft); color: var(--purple); }
        .badge-role--sales_rep { background: var(--blue-soft); color: var(--blue); }
        .badge-role--store_manager { background: var(--teal-soft); color: var(--teal); }
        .badge-role--shop_customer { background: var(--amber-soft); color: var(--amber); }

        /* Status Toggle Pill */
        .pill-status {
            border: none;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .pill-status.on {
            background: var(--green-soft);
            color: var(--green-dark);
        }
        .pill-status.on:hover {
            background: #bbf7d0;
        }
        .pill-status.off {
            background: var(--slate-100);
            color: var(--slate-500);
        }
        .pill-status.off:hover {
            background: var(--slate-200);
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-icon {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 1px solid var(--slate-200);
            background: #ffffff;
            color: var(--slate-600);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-icon:hover {
            background: var(--slate-100);
            color: var(--navy-900);
        }
        .btn-icon.del:hover {
            background: var(--red-soft);
            border-color: #fca5a5;
            color: var(--red);
        }
        .btn-icon svg {
            width: 15px;
            height: 15px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        /* Audit Log */
        .audit-list {
            list-style: none;
            margin: 0;
            padding: 0;
            max-height: 480px;
            overflow-y: auto;
        }
        .audit-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--slate-100);
        }
        .audit-item:last-child {
            border-bottom: none;
        }
        .audit-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .audit-icon.create { background: var(--green-soft); color: var(--green); }
        .audit-icon.update { background: var(--blue-soft); color: var(--blue); }
        .audit-icon.delete { background: var(--red-soft); color: var(--red); }
        .audit-title {
            font-size: 13px;
            color: var(--slate-800);
            line-height: 1.35;
        }
        .audit-title strong {
            color: var(--navy-900);
        }
        .audit-time {
            font-size: 11.5px;
            color: var(--slate-400);
            margin-top: 3px;
        }

        /* Dialogs / Modals */
        dialog {
            border: none;
            border-radius: 16px;
            padding: 0;
            width: min(480px, 94vw);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }
        dialog::backdrop {
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
        }
        .modal-box {
            padding: 24px;
        }
        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--navy-900);
            margin: 0;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--slate-400);
            cursor: pointer;
            padding: 4px;
            line-height: 1;
        }
        .modal-close:hover {
            color: var(--slate-700);
        }
        .form-group {
            margin-bottom: 14px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-700);
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            background: #ffffff;
            color: var(--slate-900);
            font-size: 13.5px;
            outline: none;
            transition: all 0.15s ease;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: var(--indigo-500);
            box-shadow: 0 0 0 3px rgba(79, 91, 213, 0.12);
        }
        .form-hint {
            font-size: 11.5px;
            color: var(--slate-500);
        }
        .form-error {
            font-size: 12px;
            color: var(--red);
            font-weight: 500;
            min-height: 1em;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-700);
            cursor: pointer;
            margin-top: 6px;
        }
        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--slate-100);
        }

        /* Toast notification */
        .toast-notify {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--navy-900);
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(10px);
            pointer-events: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast-notify.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 1150px) {
            .kpi-row { grid-template-columns: 1fr; }
            .roles-grid { grid-template-columns: repeat(2, 1fr); }
            .main-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .content { padding: 18px; }
            .topbar { padding: 12px 18px; }
            .search-box { flex-basis: 100%; order: 3; }
            .page-head { flex-direction: column; align-items: flex-start; }
            .roles-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

    <div class="main">
        <header class="topbar">
            <div class="topbar-brand">
                <span>AutoPartFlow</span>
                <span class="badge-admin">Admin Workspace</span>
            </div>
            <div class="search-box">
                <svg viewBox="0 0 24 24"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></svg>
                <input id="q" type="search" placeholder="Search users by name, email, role, phone..." autocomplete="off">
            </div>
            <div class="topbar-actions">
                <a href="<?= url('admin/notifications') ?>" class="topbar-btn" title="Notifications">
                    <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:currentColor;"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>
                </a>
                <a href="<?= url('profile') ?>" class="topbar-avatar" title="Edit Profile — <?= e($_SESSION['full_name'] ?? 'Admin') ?>">
                    <?= strtoupper(substr(trim((string)($_SESSION['full_name'] ?? 'A')), 0, 1)) ?>
                </a>
            </div>
        </header>

        <div class="content">
            <!-- Full Width Header -->
            <div class="page-head">
                <div>
                    <h1>User Management</h1>
                    <p>Control system access, assign user roles, and monitor security activity logs.</p>
                </div>
                <div class="head-actions">
                    <button class="btn btn-primary" type="button" id="addBtn">
                        <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        <span>Add New User</span>
                    </button>
                </div>
            </div>

            <!-- KPI Row: Status Donut & Role Breakdown -->
            <div class="kpi-row">
                <!-- Status Donut -->
                <div class="kpi-card">
                    <div class="kpi-card-title">Account Status</div>
                    <div class="status-overview">
                        <div class="donut-wrap">
                            <svg viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                                <circle id="donutArc" cx="50" cy="50" r="40" fill="none" stroke="#16a34a" stroke-width="10" stroke-dasharray="0 251.2"/>
                            </svg>
                            <div class="donut-inner">
                                <b id="totalUsersCount">0</b>
                                <span>Total</span>
                            </div>
                        </div>
                        <div class="status-pills">
                            <div class="status-item">
                                <span style="display:flex;align-items:center;gap:6px;color:#16a34a;font-weight:600;">
                                    <span style="width:8px;height:8px;border-radius:50%;background:#16a34a;"></span> Active
                                </span>
                                <strong id="activeUsersCount">0</strong>
                            </div>
                            <div class="status-item">
                                <span style="display:flex;align-items:center;gap:6px;color:#64748b;font-weight:600;">
                                    <span style="width:8px;height:8px;border-radius:50%;background:#94a3b8;"></span> Inactive
                                </span>
                                <strong id="inactiveUsersCount">0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles Grid Filter -->
                <div class="kpi-card">
                    <div class="kpi-card-title">
                        <span>Role Distribution</span>
                        <button class="link-reset" id="resetRoleFilter" type="button">Show All Users</button>
                    </div>
                    <div class="roles-grid" id="rolesCardsContainer">
                        <!-- Populated by JS from real roles -->
                    </div>
                </div>
            </div>

            <!-- Main Grid: Users Directory & Audit Log -->
            <div class="main-grid">
                <!-- System Users Directory -->
                <div class="card">
                    <div class="card-head">
                        <h2>
                            <span>System Users</span>
                            <span class="count-tag" id="visibleUsersCountTag">0 users</span>
                        </h2>
                        <span id="activeFilterBadge" style="font-size:12px;font-weight:600;color:var(--indigo-500);display:none;"></span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userRows">
                                <!-- Populated dynamically from state -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Security Audit Log -->
                <div class="card">
                    <div class="card-head">
                        <h2>Security Audit Log</h2>
                        <button class="btn btn-secondary" id="exportLogBtn" type="button" style="padding:5px 12px;font-size:12px;">
                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                            Export CSV
                        </button>
                    </div>
                    <ul class="audit-list" id="auditList">
                        <!-- Populated dynamically -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add / Edit User Modal Dialog -->
<dialog id="userDlg">
    <div class="modal-box">
        <div class="modal-head">
            <h3 class="modal-title" id="dlgTitle">Add New User</h3>
            <button class="modal-close" id="dlgCloseBtn" type="button">&times;</button>
        </div>
        <form id="userForm" novalidate>
            <input type="hidden" id="fId" value="0">

            <div class="form-group">
                <label for="fName">Full Name *</label>
                <input id="fName" type="text" placeholder="e.g. Kasun Perera" required autocomplete="off">
                <span class="form-error" id="eName"></span>
            </div>

            <div class="form-group">
                <label for="fUsername">Username *</label>
                <input id="fUsername" type="text" placeholder="e.g. kasun_p" required autocomplete="off">
                <span class="form-error" id="eUsername"></span>
            </div>

            <div class="form-group">
                <label for="fEmail">Email Address *</label>
                <input id="fEmail" type="email" placeholder="e.g. kasun@autopartflow.lk" required autocomplete="off">
                <span class="form-error" id="eEmail"></span>
            </div>

            <div class="form-group">
                <label for="fPhone">Phone Number</label>
                <input id="fPhone" type="tel" placeholder="e.g. 0712345678" autocomplete="off">
                <span class="form-error" id="ePhone"></span>
            </div>

            <div class="form-group">
                <label for="fRole">System Role *</label>
                <select id="fRole" required>
                    <!-- Populated dynamically from roles -->
                </select>
                <span class="form-error" id="eRole"></span>
            </div>

            <div class="form-group">
                <label for="fPassword" id="lblPassword">Password *</label>
                <input id="fPassword" type="password" placeholder="At least 6 characters" autocomplete="new-password">
                <span class="form-hint" id="hintPassword">Required when creating a new account.</span>
                <span class="form-error" id="ePassword"></span>
            </div>

            <label class="checkbox-label">
                <input type="checkbox" id="fActive" checked>
                <span>Account is active (can sign in)</span>
            </label>

            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" id="dlgCancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveBtn">Save User</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Deactivate / Delete User Modal Dialog -->
<dialog id="delDlg">
    <div class="modal-box">
        <div class="modal-head">
            <h3 class="modal-title" style="color:var(--red);">Deactivate User?</h3>
            <button class="modal-close" id="delCloseBtn" type="button">&times;</button>
        </div>
        <p id="delText" style="margin:0 0 20px;font-size:13.5px;color:var(--slate-600);line-height:1.5;"></p>
        <div class="modal-foot">
            <button class="btn btn-secondary" id="delCancelBtn" type="button">Cancel</button>
            <button class="btn btn-danger" id="delConfirmBtn" type="button">Confirm Deactivate</button>
        </div>
    </div>
</dialog>

<!-- Toast Notification -->
<div class="toast-notify" id="toast">
    <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#4ade80;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
    <span id="toastMsg">Action completed successfully.</span>
</div>

<script>
(function() {
    'use strict';

    // Real dataset bootstrapped from Database via AdminController
    const RAW_USERS = <?= json_encode(array_map(function($u) {
        return [
            'id' => (int) $u['id'],
            'name' => $u['full_name'],
            'username' => $u['username'],
            'email' => $u['email'],
            'phone' => $u['phone'] ?? '',
            'role' => $u['role_name'],
            'roleSlug' => $u['role_slug'],
            'roleId' => (int) $u['role_id'],
            'active' => (bool) $u['is_active'],
            'lastLogin' => $u['last_login_at'] ? strtotime($u['last_login_at']) * 1000 : null,
            'createdAt' => $u['created_at'] ? strtotime($u['created_at']) * 1000 : null,
            'code' => $u['employee_code'] ?? '',
        ];
    }, $rows), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    const RAW_ROLES = <?= json_encode(array_map(function($r) {
        return [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'slug' => $r['slug'],
            'description' => $r['description'] ?? '',
        ];
    }, $roles), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
    const BASE_URL = <?= json_encode(url()) ?>;
    const RAW_AUDIT = <?= json_encode($activityLogs ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    // Persistent audit log in client storage
    const AUDIT_STORAGE_KEY = 'apf_user_audit_log_v2';
    function loadAuditLog() {
        try {
            const raw = localStorage.getItem(AUDIT_STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }
    function saveAuditLog(log) {
        try {
            localStorage.setItem(AUDIT_STORAGE_KEY, JSON.stringify(log.slice(0, 100)));
        } catch (e) {}
    }

    let state = {
        users: RAW_USERS,
        roles: RAW_ROLES,
        selectedRoleSlug: null,
        log: RAW_AUDIT.concat(loadAuditLog()).sort((a, b) => Number(b.time || 0) - Number(a.time || 0))
    };

    let deletingId = null;

    // Helper functions
    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function timeAgo(ts) {
        if (!ts) return 'Never logged in';
        const diffMs = Date.now() - ts;
        const mins = Math.round(diffMs / 60000);
        if (mins < 2) return 'Just now';
        if (mins < 60) return `${mins} mins ago`;
        const hrs = Math.round(mins / 60);
        if (hrs < 24) return `${hrs} hr${hrs > 1 ? 's' : ''} ago`;
        const days = Math.round(hrs / 24);
        if (days < 30) return `${days} day${days > 1 ? 's' : ''} ago`;
        return new Date(ts).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function getAvatarBg(name) {
        const colors = ['#3b82f6', '#10b981', '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#06b6d4'];
        let hash = 0;
        for (let i = 0; i < name.length; i++) hash += name.charCodeAt(i);
        return colors[Math.abs(hash) % colors.length];
    }

    function addAuditEntry(type, description) {
        const entry = {
            id: Math.random().toString(36).substring(2, 9),
            type: type, // 'create', 'update', 'delete'
            text: description,
            actor: 'System Admin',
            time: Date.now()
        };
        state.log.unshift(entry);
        saveAuditLog(state.log);
    }

    // Render Role distribution cards
    function renderRoleCards() {
        const container = document.getElementById('rolesCardsContainer');
        const roleColors = {
            'owner': '#7c3aed',
            'sales_rep': '#2563eb',
            'store_manager': '#0d9488',
            'shop_customer': '#d97706'
        };

        container.innerHTML = state.roles.map(r => {
            const count = state.users.filter(u => u.roleSlug === r.slug).length;
            const isSelected = state.selectedRoleSlug === r.slug;
            const color = roleColors[r.slug] || '#4f5bd5';

            return `
                <button type="button" class="role-filter-card ${isSelected ? 'active' : ''}" data-role-slug="${esc(r.slug)}">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="role-card-label" title="${esc(r.name)}">${esc(r.name)}</span>
                        <span class="role-card-badge" style="background:${color};"></span>
                    </div>
                    <span class="role-card-count">${count}</span>
                </button>
            `;
        }).join('');
    }

    // Render Users Table & Donut
    function render() {
        const q = document.getElementById('q').value.trim().toLowerCase();

        const filtered = state.users.filter(u => {
            if (state.selectedRoleSlug && u.roleSlug !== state.selectedRoleSlug) {
                return false;
            }
            if (q) {
                const searchHaystack = [u.name, u.username, u.email, u.phone, u.role, u.code].filter(Boolean).join(' ').toLowerCase();
                return searchHaystack.includes(q);
            }
            return true;
        });

        // Donut & Status numbers
        const total = state.users.length;
        const activeCount = state.users.filter(u => u.active).length;
        const inactiveCount = total - activeCount;

        document.getElementById('totalUsersCount').textContent = total;
        document.getElementById('activeUsersCount').textContent = activeCount;
        document.getElementById('inactiveUsersCount').textContent = inactiveCount;

        const circumference = 2 * Math.PI * 40; // 251.3
        const activePct = total > 0 ? (activeCount / total) : 0;
        const dashoffset = circumference * (1 - activePct);
        const arc = document.getElementById('donutArc');
        arc.setAttribute('stroke-dasharray', `${circumference}`);
        arc.setAttribute('stroke-dashoffset', `${dashoffset}`);

        // Tags & Headers
        document.getElementById('visibleUsersCountTag').textContent = `${filtered.length} of ${total} users`;
        const filterBadge = document.getElementById('activeFilterBadge');
        if (state.selectedRoleSlug) {
            const selectedRole = state.roles.find(r => r.slug === state.selectedRoleSlug);
            filterBadge.textContent = `Filtered by: ${selectedRole ? selectedRole.name : state.selectedRoleSlug}`;
            filterBadge.style.display = 'inline';
        } else {
            filterBadge.style.display = 'none';
        }

        renderRoleCards();

        // Render Table Rows
        const tbody = document.getElementById('userRows');
        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center;padding:36px;color:var(--slate-500);">
                        <div style="font-weight:600;font-size:14px;color:var(--slate-700);">No user accounts found</div>
                        <div style="font-size:12.5px;margin-top:4px;">Try refining your search keyword or clearing the role filter.</div>
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = filtered.map(u => {
                const initial = (u.name.trim()[0] || 'U').toUpperCase();
                const avatarBg = getAvatarBg(u.name);
                const roleClass = `badge-role--${u.roleSlug || 'owner'}`;

                return `
                    <tr data-user-id="${u.id}">
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar" style="background:${avatarBg};">${initial}</div>
                                <div>
                                    <div class="user-name">${esc(u.name)}</div>
                                    <div class="user-meta">@${esc(u.username)} &bull; ${esc(u.email)}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-role ${roleClass}">${esc(u.role)}</span>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:500;">${esc(u.phone || '—')}</div>
                            ${u.code ? `<div style="font-size:11.5px;color:var(--slate-400);">${esc(u.code)}</div>` : ''}
                        </td>
                        <td>
                            <button type="button" class="pill-status ${u.active ? 'on' : 'off'}" data-action="toggle-status" data-id="${u.id}" title="Click to toggle status">
                                ${u.active ? 'Active' : 'Inactive'}
                            </button>
                        </td>
                        <td>
                            <span style="font-size:12.5px;color:var(--slate-600);">${timeAgo(u.lastLogin)}</span>
                        </td>
                        <td style="text-align:right;">
                            <div class="action-btns" style="justify-content:flex-end;">
                                <button type="button" class="btn-icon" data-action="edit" data-id="${u.id}" title="Edit User">
                                    <svg viewBox="0 0 24 24"><path d="M11 4H4v16h16v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                </button>
                                <button type="button" class="btn-icon del" data-action="delete" data-id="${u.id}" title="Deactivate User">
                                    <svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Render Audit Log
        renderAuditLog();
    }

    function renderAuditLog() {
        const list = document.getElementById('auditList');
        if (state.log.length === 0) {
            list.innerHTML = `
                <li style="text-align:center;padding:28px 16px;color:var(--slate-400);font-size:13px;">
                    No security events recorded yet.
                </li>
            `;
            return;
        }

        const icons = {
            'create': '+',
            'update': '✏',
            'delete': '✕'
        };

        list.innerHTML = state.log.slice(0, 30).map(item => `
            <li class="audit-item">
                <div class="audit-icon ${esc(item.type)}">${icons[item.type] || '•'}</div>
                <div style="flex:1;min-width:0;">
                    <div class="audit-title"><strong>${esc(item.actor)}</strong> ${esc(item.text)}</div>
                    <div class="audit-time">${timeAgo(item.time)}</div>
                </div>
            </li>
        `).join('');
    }

    // Role filter click handling
    document.getElementById('rolesCardsContainer').addEventListener('click', e => {
        const card = e.target.closest('.role-filter-card');
        if (!card) return;
        const slug = card.dataset.roleSlug;
        state.selectedRoleSlug = state.selectedRoleSlug === slug ? null : slug;
        render();
    });

    document.getElementById('resetRoleFilter').onclick = () => {
        state.selectedRoleSlug = null;
        render();
    };

    document.getElementById('q').addEventListener('input', render);

    // Modal Handling
    const userDlg = document.getElementById('userDlg');
    const delDlg = document.getElementById('delDlg');

    // Populate role options in select
    const roleSelect = document.getElementById('fRole');
    roleSelect.innerHTML = state.roles.map(r => `
        <option value="${r.id}">${esc(r.name)} (${esc(r.slug)})</option>
    `).join('');

    function openUserModal(user = null) {
        document.getElementById('fId').value = user ? user.id : 0;
        document.getElementById('dlgTitle').textContent = user ? 'Edit User Profile' : 'Add New User';
        document.getElementById('saveBtn').textContent = user ? 'Save Changes' : 'Create User';

        document.getElementById('fName').value = user ? user.name : '';
        document.getElementById('fUsername').value = user ? user.username : '';
        document.getElementById('fEmail').value = user ? user.email : '';
        document.getElementById('fPhone').value = user ? user.phone : '';
        document.getElementById('fRole').value = user ? user.roleId : (state.roles[0]?.id || 1);
        document.getElementById('fActive').checked = user ? user.active : true;
        document.getElementById('fPassword').value = '';

        if (user) {
            document.getElementById('lblPassword').textContent = 'Password (optional)';
            document.getElementById('hintPassword').textContent = 'Leave empty to keep existing password.';
            document.getElementById('fPassword').required = false;
        } else {
            document.getElementById('lblPassword').textContent = 'Password *';
            document.getElementById('hintPassword').textContent = 'At least 6 characters.';
            document.getElementById('fPassword').required = true;
        }

        // Clear errors
        ['eName', 'eUsername', 'eEmail', 'ePhone', 'eRole', 'ePassword'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '';
        });

        userDlg.showModal();
    }

    document.getElementById('addBtn').onclick = () => openUserModal(null);
    document.getElementById('dlgCloseBtn').onclick = () => userDlg.close();
    document.getElementById('dlgCancelBtn').onclick = () => userDlg.close();

    // Form submission
    document.getElementById('userForm').addEventListener('submit', async e => {
        e.preventDefault();

        const id = parseInt(document.getElementById('fId').value, 10);
        const fullName = document.getElementById('fName').value.trim();
        const username = document.getElementById('fUsername').value.trim();
        const email = document.getElementById('fEmail').value.trim().toLowerCase();
        const phone = document.getElementById('fPhone').value.trim();
        const roleId = parseInt(document.getElementById('fRole').value, 10);
        const password = document.getElementById('fPassword').value;
        const isActive = document.getElementById('fActive').checked ? 1 : 0;

        let hasError = false;
        if (!fullName) {
            document.getElementById('eName').textContent = 'Full name is required.';
            hasError = true;
        } else {
            document.getElementById('eName').textContent = '';
        }

        if (!username) {
            document.getElementById('eUsername').textContent = 'Username is required.';
            hasError = true;
        } else {
            document.getElementById('eUsername').textContent = '';
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            document.getElementById('eEmail').textContent = 'Valid email is required.';
            hasError = true;
        } else {
            document.getElementById('eEmail').textContent = '';
        }

        if (!id && (!password || password.length < 6)) {
            document.getElementById('ePassword').textContent = 'Password must be at least 6 characters.';
            hasError = true;
        } else if (password && password.length < 6) {
            document.getElementById('ePassword').textContent = 'Password must be at least 6 characters.';
            hasError = true;
        } else {
            document.getElementById('ePassword').textContent = '';
        }

        if (hasError) return;

        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
            const payload = {
                id: id,
                full_name: fullName,
                username: username,
                email: email,
                phone: phone,
                role_id: roleId,
                is_active: isActive,
                password: password,
                csrf_token: CSRF_TOKEN
            };

            const response = await fetch(`${BASE_URL}admin/users/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (!result.ok) {
                showToast(result.message || 'Error saving user account.');
                saveBtn.disabled = false;
                saveBtn.textContent = id ? 'Save Changes' : 'Create User';
                return;
            }

            // Sync updated users from server
            if (Array.isArray(result.users)) {
                state.users = result.users.map(u => ({
                    id: parseInt(u.id, 10),
                    name: u.full_name,
                    username: u.username,
                    email: u.email,
                    phone: u.phone || '',
                    role: u.role_name,
                    roleSlug: u.role_slug,
                    roleId: parseInt(u.role_id, 10),
                    active: Boolean(parseInt(u.is_active, 10)),
                    lastLogin: u.last_login_at ? new Date(u.last_login_at).getTime() : null,
                    createdAt: u.created_at ? new Date(u.created_at).getTime() : null,
                    code: u.employee_code || ''
                }));
            }

            addAuditEntry(id ? 'update' : 'create', `${id ? 'updated profile for' : 'created account for'} ${email}`);
            showToast(result.message || 'User saved successfully.');
            userDlg.close();
            render();
        } catch (err) {
            showToast('Network error while saving user. Please try again.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = id ? 'Save Changes' : 'Create User';
        }
    });

    // Table clicks: Edit, Delete, Toggle Status
    document.getElementById('userRows').addEventListener('click', async e => {
        const toggleBtn = e.target.closest('[data-action="toggle-status"]');
        if (toggleBtn) {
            const uid = parseInt(toggleBtn.dataset.id, 10);
            const user = state.users.find(u => u.id === uid);
            if (!user) return;

            const newActive = !user.active;
            try {
                const response = await fetch(`${BASE_URL}admin/users/save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        id: user.id,
                        full_name: user.name,
                        username: user.username,
                        email: user.email,
                        phone: user.phone,
                        role_id: user.roleId,
                        is_active: newActive ? 1 : 0,
                        csrf_token: CSRF_TOKEN
                    })
                });
                const res = await response.json();
                if (res.ok) {
                    user.active = newActive;
                    addAuditEntry('update', `changed status of ${user.email} to ${newActive ? 'Active' : 'Inactive'}`);
                    showToast(`Status updated to ${newActive ? 'Active' : 'Inactive'}`);
                    render();
                } else {
                    showToast(res.message || 'Could not update status.');
                }
            } catch (err) {
                showToast('Failed to update status. Please try again.');
            }
            return;
        }

        const editBtn = e.target.closest('[data-action="edit"]');
        if (editBtn) {
            const uid = parseInt(editBtn.dataset.id, 10);
            const user = state.users.find(u => u.id === uid);
            if (user) openUserModal(user);
            return;
        }

        const delBtn = e.target.closest('[data-action="delete"]');
        if (delBtn) {
            const uid = parseInt(delBtn.dataset.id, 10);
            const user = state.users.find(u => u.id === uid);
            if (!user) return;

            // Guard against deleting last Business Owner
            if (user.roleSlug === 'owner') {
                const activeOwners = state.users.filter(u => u.roleSlug === 'owner' && u.active);
                if (activeOwners.length <= 1) {
                    showToast('Cannot deactivate the only active Business Owner account.');
                    return;
                }
            }

            deletingId = uid;
            document.getElementById('delText').textContent = `Are you sure you want to deactivate ${user.name} (${user.email})? This user will lose system workspace access.`;
            delDlg.showModal();
        }
    });

    document.getElementById('delCloseBtn').onclick = () => delDlg.close();
    document.getElementById('delCancelBtn').onclick = () => delDlg.close();

    document.getElementById('delConfirmBtn').onclick = async () => {
        if (!deletingId) return;
        const confirmBtn = document.getElementById('delConfirmBtn');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deactivating...';

        const user = state.users.find(u => u.id === deletingId);

        try {
            const response = await fetch(`${BASE_URL}admin/users/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    id: deletingId,
                    csrf_token: CSRF_TOKEN
                })
            });

            const res = await response.json();
            if (res.ok) {
                state.users = state.users.filter(u => u.id !== deletingId);
                addAuditEntry('delete', `deactivated user account ${user ? user.email : deletingId}`);
                showToast(res.message || 'User account deactivated.');
                delDlg.close();
                render();
            } else {
                showToast(res.message || 'Could not deactivate user account.');
            }
        } catch (err) {
            showToast('Network error while deactivating user.');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Deactivate';
            deletingId = null;
        }
    };

    // Export CSV
    document.getElementById('exportLogBtn').onclick = () => {
        if (state.log.length === 0) {
            showToast('No audit events to export.');
            return;
        }
        const headers = ['Timestamp', 'Actor', 'Action Type', 'Description'];
        const rows = state.log.map(item => [
            new Date(item.time).toISOString(),
            item.actor,
            item.type,
            `"${String(item.text).replace(/"/g, '""')}"`
        ]);
        const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `autopartflow-security-audit-${new Date().toISOString().slice(0, 10)}.csv`;
        link.click();
        URL.revokeObjectURL(url);
    };

    // Toast helper
    let toastTimeout = null;
    function showToast(message) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMsg').textContent = message;
        toast.classList.add('show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    // Initial render
    render();
})();
</script>
</body>
</html>
