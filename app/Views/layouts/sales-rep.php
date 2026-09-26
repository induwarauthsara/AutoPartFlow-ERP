<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales Representative') ?> | AutoPartFlow ERP</title>
    <!-- Inter is the Kinetic Enterprise typeface. Safe to swap weights here. -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Split CSS: tokens → chrome → shared widgets → one file per screen. -->
    <link rel="stylesheet" href="<?= asset('css/sales-rep/tokens.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/shell.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/pos.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/pos-print.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/orders.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/orders-create.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/customers.css') ?>">
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<meta name="base-url" content="<?= url() ?>">
</head>
<body class="sales-app">
    <a class="sales-skip" href="#sales-main">Skip to content</a>

    <aside class="sales-sidebar" id="sales-sidebar" aria-label="Sales Representative navigation">
        <div class="sales-brand">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
            <div>
                <strong>AutoPartFlow</strong>
                <span>Sales ERP</span>
            </div>
        </div>

        <nav class="sales-nav">
            <p class="sales-nav__label">Workspace</p>
            <a class="sales-nav__link" href="<?= url('sales') ?>" data-nav-page="dashboard">
                <svg class="sales-icon"><use href="#sales-icon-dashboard"></use></svg>
                Dashboard
            </a>
            <a class="sales-nav__link" href="<?= url('sales/pos') ?>" data-nav-page="pos">
                <svg class="sales-icon"><use href="#sales-icon-pos"></use></svg>
                Point of Sale
            </a>
            <a class="sales-nav__link" href="<?= url('sales/orders') ?>" data-nav-page="orders">
                <svg class="sales-icon"><use href="#sales-icon-orders"></use></svg>
                Orders
            </a>
            <a class="sales-nav__link" href="<?= url('sales/customers') ?>" data-nav-page="customers">
                <svg class="sales-icon"><use href="#sales-icon-customers"></use></svg>
                Customers
            </a>
        </nav>

        <div class="sales-sidebar__footer" style="margin-top:auto;padding:12px;display:flex;flex-direction:column;gap:8px;border-top:1px solid rgba(255,255,255,0.08);">
            <a href="<?= url('profile') ?>" class="sales-profile" style="margin:0;padding:10px 12px;border-radius:10px;text-decoration:none;color:inherit;display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.05);transition:background 0.15s;" title="View & Edit Profile">
                <span class="sales-avatar" aria-hidden="true" style="width:34px;height:34px;border-radius:50%;background:#3b82f6;color:#fff;display:grid;place-items:center;font-size:13px;font-weight:700;flex-shrink:0;">
                    <?= e(strtoupper(substr(trim((string)($_SESSION['full_name'] ?? 'SR')), 0, 2)) ?: 'SR') ?>
                </span>
                <div style="flex:1;min-width:0;">
                    <strong style="color:#fff;font-size:13px;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($_SESSION['full_name'] ?? 'Sales Rep') ?></strong>
                    <span style="color:#94a3b8;font-size:11px;display:block;">Sales Representative</span>
                </div>
                <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:#94a3b8;flex-shrink:0;"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
            </a>
            <a href="<?= url('logout') ?>" class="sales-nav__link sales-nav__link--logout" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;color:#fca5a5;text-decoration:none;font-size:13px;font-weight:600;transition:background 0.15s;" title="Sign Out">
                <svg class="sales-icon" viewBox="0 0 24 24" style="width:18px;height:18px;fill:currentColor;"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <header class="sales-header">
        <button class="sales-icon-button sales-header__menu" type="button" id="sales-menu-toggle" aria-label="Open navigation" aria-controls="sales-sidebar" aria-expanded="false">
            <svg class="sales-icon"><use href="#sales-icon-menu"></use></svg>
        </button>
        <strong class="sales-header__brand"><img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="" width="40" height="40">AutoPartFlow</strong>
        <div class="sales-header__actions">
            <button class="sales-icon-button" type="button" id="sales-notify-toggle" aria-label="Notifications" aria-expanded="false">
                <svg class="sales-icon"><use href="#sales-icon-bell"></use></svg>
                <span class="sales-notification-dot" aria-hidden="true"></span>
            </button>
            <a href="<?= url('profile') ?>" class="sales-avatar sales-avatar--small" title="Edit Profile — <?= e($_SESSION['full_name'] ?? 'Sales Rep') ?>" aria-label="Edit Profile" style="text-decoration:none;cursor:pointer;">
                <?= e(strtoupper(substr(trim((string)($_SESSION['full_name'] ?? 'SR')), 0, 2)) ?: 'SR') ?>
            </a>
        </div>
        <div class="sales-notify-panel hidden" id="sales-notify-panel" role="status">
            <p>No new notifications.</p>
        </div>
    </header>

    <div class="sales-sidebar-backdrop" id="sales-sidebar-backdrop"></div>

    <div id="sales-main">
        <?= $content ?>
    </div>

    <nav class="sales-mobile-nav" aria-label="Mobile navigation">
        <a href="<?= url('sales') ?>" class="sales-mobile-nav__item" data-mobile-page="dashboard">
            <svg class="sales-icon"><use href="#sales-icon-dashboard"></use></svg>
            <span>Home</span>
        </a>
        <a href="<?= url('sales/pos') ?>" class="sales-mobile-nav__item" data-mobile-page="pos">
            <svg class="sales-icon"><use href="#sales-icon-pos"></use></svg>
            <span>POS</span>
        </a>
        <a href="<?= url('sales/orders') ?>" class="sales-mobile-nav__item" data-mobile-page="orders">
            <svg class="sales-icon"><use href="#sales-icon-orders"></use></svg>
            <span>Orders</span>
        </a>
        <a href="<?= url('sales/customers') ?>" class="sales-mobile-nav__item" data-mobile-page="customers">
            <svg class="sales-icon"><use href="#sales-icon-customers"></use></svg>
            <span>Customers</span>
        </a>
    </nav>

    <svg class="sales-svg-sprite" aria-hidden="true">
        <symbol id="sales-icon-dashboard" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3Zm10 0h8v5h-8V3ZM3 13h8v8H3v-8Zm10-3h8v11h-8V10Z"/></symbol>
        <symbol id="sales-icon-pos" viewBox="0 0 24 24"><path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm1 3v5h10V5H7Zm0 8v2h2v-2H7Zm4 0v2h2v-2h-2Zm4 0v2h2v-2h-2Zm-8 4v2h2v-2H7Zm4 0v2h6v-2h-6Z"/></symbol>
        <symbol id="sales-icon-orders" viewBox="0 0 24 24"><path d="M7 2h10v2h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3V2Zm2 2v2h6V4H9Zm-3 6v2h12v-2H6Zm0 4v2h8v-2H6Zm0 4v2h5v-2H6Z"/></symbol>
        <symbol id="sales-icon-customers" viewBox="0 0 24 24"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-1a3 3 0 1 0 0-6 5.9 5.9 0 0 1 0 6ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Zm14.5 0H22v-2c0-2.3-2.1-4-4.8-4.7 1.1 1.2 1.8 2.8 1.8 4.7v2h-2.5Z"/></symbol>
        <symbol id="sales-icon-inventory" viewBox="0 0 24 24"><path d="m12 2 9 4.5v11L12 22l-9-4.5v-11L12 2Zm0 2.2L6.2 7 12 9.8 17.8 7 12 4.2ZM5 8.6v7.7l6 3v-7.7l-6-3Zm8 10.7 6-3V8.6l-6 3v7.7Z"/></symbol>
        <symbol id="sales-icon-menu" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/></symbol>
        <symbol id="sales-icon-bell" viewBox="0 0 24 24"><path d="M12 22a2.5 2.5 0 0 0 2.3-1.5H9.7A2.5 2.5 0 0 0 12 22Zm7-5-2-2v-5a5 5 0 0 0-4-4.9V3h-2v2.1A5 5 0 0 0 7 10v5l-2 2v2h14v-2Z"/></symbol>
        <symbol id="sales-icon-arrow" viewBox="0 0 24 24"><path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5Z"/></symbol>
        <symbol id="sales-icon-money" viewBox="0 0 24 24"><path d="M3 5h18v14H3V5Zm2 2v10h14V7H5Zm7 1a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm-6 1h2v2H6V9Zm10 4h2v2h-2v-2Z"/></symbol>
        <symbol id="sales-icon-trend" viewBox="0 0 24 24"><path d="m4 17 5-5 4 4 7-8v4h2V5h-7v2h3.6L13 13.4l-4-4-6.4 6.2L4 17Z"/></symbol>
        <symbol id="sales-icon-truck" viewBox="0 0 24 24"><path d="M3 4h11v4h4l3 4v6h-2a3 3 0 0 1-6 0H9a3 3 0 0 1-6 0H2V6a2 2 0 0 1 1-2Zm1 2v8.8A3 3 0 0 1 8.8 16H14V6H4Zm12 4v4h3v-1.4L17 10h-1ZM6 17a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm10 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/></symbol>
        <symbol id="sales-icon-search" viewBox="0 0 24 24"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></symbol>
        <symbol id="sales-icon-plus" viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></symbol>
        <symbol id="sales-icon-phone" viewBox="0 0 24 24"><path d="M6.6 3 10 6.4 8.2 8.2a14 14 0 0 0 7.6 7.6l1.8-1.8 3.4 3.4-1.7 2.8c-.4.7-1.3 1-2.1.8C9.8 19.3 4.7 14.2 3 6.8c-.2-.8.1-1.7.8-2.1L6.6 3Z"/></symbol>
        <symbol id="sales-icon-mail" viewBox="0 0 24 24"><path d="M3 4h18v16H3V4Zm2 3v11h14V7l-7 5-7-5Zm1.2-1L12 10.1 17.8 6H6.2Z"/></symbol>
        <symbol id="sales-icon-location" viewBox="0 0 24 24"><path d="M12 22S5 15.5 5 9a7 7 0 1 1 14 0c0 6.5-7 13-7 13Zm0-17a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z"/></symbol>
        <symbol id="sales-icon-close" viewBox="0 0 24 24"><path d="m6.4 5 5.6 5.6L17.6 5 19 6.4 13.4 12l5.6 5.6-1.4 1.4-5.6-5.6L6.4 19 5 17.6l5.6-5.6L5 6.4 6.4 5Z"/></symbol>
        <symbol id="sales-icon-download" viewBox="0 0 24 24"><path d="M11 3h2v10l3.5-3.5 1.4 1.4-5.9 5.9-5.9-5.9 1.4-1.4L11 13V3ZM4 19h16v2H4v-2Z"/></symbol>
        <symbol id="sales-icon-filter" viewBox="0 0 24 24"><path d="M3 5h18v2H3V5Zm3 6h12v2H6v-2Zm4 6h4v2h-4v-2Z"/></symbol>
        <symbol id="sales-icon-chevron" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7-1.4-1.4L13.2 12 7.6 6.4 9 5Z"/></symbol>
        <symbol id="sales-icon-check" viewBox="0 0 24 24"><path d="m9.5 16.2-4.2-4.2 1.4-1.4 2.8 2.8 7.8-7.8L18.7 7l-9.2 9.2Z"/></symbol>
        <symbol id="sales-icon-cart" viewBox="0 0 24 24"><path d="M3 3h2l2.2 10.2A2 2 0 0 0 9.1 15H18a2 2 0 0 0 1.9-1.4L22 7H7l-.4-2H3V3Zm6 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm9 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></symbol>
        <symbol id="icon-close" viewBox="0 0 24 24"><path d="m6.4 5 5.6 5.6L17.6 5 19 6.4 13.4 12l5.6 5.6-1.4 1.4-5.6-5.6L6.4 19 5 17.6l5.6-5.6L5 6.4 6.4 5Z"/></symbol>
        <symbol id="icon-brake" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="currentColor"/></symbol>
        <symbol id="icon-filter" viewBox="0 0 24 24"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></symbol>
        <symbol id="icon-battery" viewBox="0 0 24 24"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z"/></symbol>
        <symbol id="icon-spark" viewBox="0 0 24 24"><path d="M12 2l1.09 3.26L16 6l-2.91 1.09L12 10.5 10.91 7.09 8 6l2.91-1.74L12 2z"/></symbol>
        <symbol id="sales-icon-trash" viewBox="0 0 24 24"><path d="M8 3h8l1 2h4v2H3V5h4l1-2Zm-2 6h12l-1 12H7L6 9Zm3 2v7h2v-7H9Zm4 0v7h2v-7h-2Z"/></symbol>
        <symbol id="sales-icon-user" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></symbol>
        <symbol id="sales-icon-logout" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></symbol>
        <symbol id="sales-icon-store" viewBox="0 0 24 24"><path d="M4 4h16l1 4H3L4 4zm0 6h16v10H4V10zm2 2v6h12v-6H6z"/></symbol>
    </svg>

    <dialog class="sales-dialog" id="sales-confirm-dialog" aria-labelledby="sales-confirm-title">
        <div class="sales-confirm__body">
            <h2 id="sales-confirm-title">Delete?</h2>
            <p id="sales-confirm-message">This cannot be undone.</p>
        </div>
        <div class="sales-confirm__actions">
            <button class="sales-button sales-button--secondary" type="button" id="sales-confirm-cancel">Cancel</button>
            <button class="sales-button sales-button--danger" type="button" id="sales-confirm-ok">Delete</button>
        </div>
    </dialog>

    <script src="<?= asset('js/sales-rep/utils.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/mock-data.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/shell.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/dashboard.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/customers.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/orders.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/orders-create.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/pos.js') ?>"></script>
</body>
</html>
