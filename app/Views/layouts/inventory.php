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
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<meta name="base-url" content="<?= url() ?>">
</head>
<body class="sales-app">
    <aside class="sales-sidebar" id="sales-sidebar" aria-label="Workspace navigation">
        <div class="sales-brand">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
            <div>
                <strong>AutoPartFlow</strong>
                <span>Stock Workspace</span>
            </div>
        </div>

        <nav class="sales-nav">
            <p class="sales-nav__label">Workspace</p>




            <a class="sales-nav__link <?= str_contains(($title ?? ''), 'Inventory') ? 'sales-nav__link--active' : '' ?>" href="<?= url('inventory') ?>" data-nav-page="inventory">
                <svg class="sales-icon"><use href="#sales-icon-inventory"></use></svg>
                Inventory
            </a>
        </nav>

        <div class="sales-profile">
            <span class="sales-avatar" aria-hidden="true">
                <svg class="sales-icon"><use href="#sales-icon-user"></use></svg>
            </span>
            <div>
                <strong>Store Manager</strong>
                <span>Stock Operations</span>
            </div>
        </div>
    </aside>

    <header class="sales-header">
        <button class="sales-icon-button sales-header__menu" type="button" id="sales-menu-toggle" aria-label="Open navigation" aria-controls="sales-sidebar" aria-expanded="false">
            <svg class="sales-icon"><use href="#sales-icon-menu"></use></svg>
        </button>
        <strong class="sales-header__brand"><img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="" width="40" height="40">AutoPartFlow</strong>
        <div class="sales-search sales-header__search">
            <svg class="sales-icon"><use href="#sales-icon-search"></use></svg>
            <input type="search" id="inventory-header-search" placeholder="Search SKU, parts..." autocomplete="off">
        </div>
        <div class="sales-header__actions">
            <button class="sales-icon-button" type="button" aria-label="Notifications">
                <svg class="sales-icon"><use href="#sales-icon-bell"></use></svg>
                <span class="sales-notification-dot" aria-hidden="true"></span>
            </button>
            <a href="<?= url('logout') ?>" class="sales-avatar sales-avatar--small" title="Sign Out" aria-label="Sign out">
                <svg class="sales-icon"><use href="#sales-icon-user"></use></svg>
            </a>
        </div>
    </header>

    <div class="sales-sidebar-backdrop" id="sales-sidebar-backdrop"></div>

    <?= $content ?>

    <nav class="sales-mobile-nav" aria-label="Mobile navigation">




        <a href="<?= url('inventory') ?>" class="sales-mobile-nav__item <?= str_contains(($title ?? ''), 'Inventory') ? 'sales-mobile-nav__item--active' : '' ?>">
            <svg class="sales-icon"><use href="#sales-icon-inventory"></use></svg>
            <span>Inventory</span>
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
