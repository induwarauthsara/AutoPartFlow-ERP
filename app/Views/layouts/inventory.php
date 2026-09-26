<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Inventory') ?> | AutoPartFlow ERP</title>
    <link rel="stylesheet" href="<?= asset('css/sales-rep/tokens.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/shell.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sales-rep/customers.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/inventory/inventory.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="base-url" content="<?= url() ?>">
</head>
<body class="sales-app">
    <?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

    <header class="sales-header">
        <button class="sales-icon-button sales-header__menu" type="button" id="sales-menu-toggle" aria-label="Open navigation" aria-controls="admin-sidebar" aria-expanded="false">
            <svg class="sales-icon"><use href="#sales-icon-menu"></use></svg>
        </button>
        <strong class="sales-header__brand"><img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="" width="40" height="40">AutoPartFlow</strong>
        <div class="sales-search sales-header__search">
            <svg class="sales-icon"><use href="#sales-icon-search"></use></svg>
            <input type="search" id="inventory-header-search" placeholder="Search SKU, parts..." autocomplete="off">
        </div>
        <div class="sales-header__actions">
            <a href="<?= url('admin/notifications') ?>" class="sales-icon-button" aria-label="Notifications" style="text-decoration:none;">
                <svg class="sales-icon"><use href="#sales-icon-bell"></use></svg>
                <span class="sales-notification-dot" aria-hidden="true"></span>
            </a>
            <a href="<?= url('profile') ?>" class="sales-avatar sales-avatar--small" title="Edit Profile — <?= e($_SESSION['full_name'] ?? 'Store Manager') ?>" aria-label="Edit Profile" style="text-decoration:none;cursor:pointer;">
                <?= e(strtoupper(substr(trim((string)($_SESSION['full_name'] ?? 'SM')), 0, 2)) ?: 'SM') ?>
            </a>
        </div>
    </header>

    <div class="sales-sidebar-backdrop" id="sales-sidebar-backdrop"></div>

    <?= $content ?>

    <nav class="sales-mobile-nav" aria-label="Mobile navigation">
        <?php if (($_SESSION['role_slug'] ?? '') === 'owner' || ($_SESSION['role_id'] ?? 0) === 1): ?>
        <a href="<?= url('admin/dashboard') ?>" class="sales-mobile-nav__item">
            <svg class="sales-icon"><use href="#sales-icon-dashboard"></use></svg>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>
        <a href="<?= url('inventory') ?>" class="sales-mobile-nav__item sales-mobile-nav__item--active">
            <svg class="sales-icon"><use href="#sales-icon-inventory"></use></svg>
            <span>Inventory</span>
        </a>
        <a href="<?= url('admin/reports') ?>" class="sales-mobile-nav__item">
            <svg class="sales-icon" viewBox="0 0 24 24"><path d="M4 3h2v16h15v2H4V3Zm5 8h3v6H9v-6Zm5-5h3v11h-3V6Z"/></svg>
            <span>Reports</span>
        </a>
        <a href="<?= url('admin/users') ?>" class="sales-mobile-nav__item">
            <svg class="sales-icon" viewBox="0 0 24 24"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Z"/></svg>
            <span>Users</span>
        </a>
        <a href="<?= url('admin/settings') ?>" class="sales-mobile-nav__item">
            <svg class="sales-icon" viewBox="0 0 24 24"><path d="M3 5h18v2H3V5Zm4 6h10v2H7v-2Zm3 6h4v2h-4v-2Z"/></svg>
            <span>Settings</span>
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
        <symbol id="sales-icon-search" viewBox="0 0 24 24"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></symbol>
        <symbol id="sales-icon-plus" viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></symbol>
        <symbol id="sales-icon-money" viewBox="0 0 24 24"><path d="M3 5h18v14H3V5Zm2 2v10h14V7H5Zm7 1a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm-6 1h2v2H6V9Zm10 4h2v2h-2v-2Z"/></symbol>
        <symbol id="sales-icon-trend" viewBox="0 0 24 24"><path d="m4 17 5-5 4 4 7-8v4h2V5h-7v2h3.6L13 13.4l-4-4-6.4 6.2L4 17Z"/></symbol>
        <symbol id="sales-icon-user" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></symbol>
        <symbol id="sales-icon-logout" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></symbol>
        <symbol id="sales-icon-store" viewBox="0 0 24 24"><path d="M4 4h16l1 4H3L4 4zm0 6h16v10H4V10zm2 2v6h12v-6H6z"/></symbol>
    </svg>

    <script src="<?= asset('js/sales-rep/utils.js') ?>"></script>
    <script src="<?= asset('js/sales-rep/shell.js') ?>"></script>
    <script src="<?= asset('js/inventory/inventory-mock-data.js') ?>"></script>
    <script src="<?= asset('js/inventory/inventory.js') ?>"></script>
</body>
</html>
