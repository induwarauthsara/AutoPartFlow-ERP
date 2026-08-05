<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales Representative') ?> | AutoPartFlow ERP</title>
    <link rel="stylesheet" href="<?= asset('css/sales/sales.css') ?>">
</head>
<body class="sales-app">
    <aside class="sales-sidebar" id="sales-sidebar" aria-label="Sales Representative navigation">
        <div class="sales-brand">
            <span class="sales-brand__mark" aria-hidden="true">A</span>
            <div>
                <strong>AutoPartFlow</strong>
                <span>Sales ERP</span>
            </div>
        </div>

        <nav class="sales-nav">
            <p class="sales-nav__label">Workspace</p>
            <a class="sales-nav__link" href="#" data-nav-page="dashboard">
                <svg class="sales-icon"><use href="#sales-icon-dashboard"></use></svg>
                Dashboard
            </a>
            <a class="sales-nav__link" href="#" data-nav-page="pos">
                <svg class="sales-icon"><use href="#sales-icon-pos"></use></svg>
                Point of Sale
            </a>
            <a class="sales-nav__link" href="#" data-nav-page="orders">
                <svg class="sales-icon"><use href="#sales-icon-orders"></use></svg>
                Orders
            </a>
            <a class="sales-nav__link" href="#" data-nav-page="customers">
                <svg class="sales-icon"><use href="#sales-icon-customers"></use></svg>
                Customers
            </a>
            <a class="sales-nav__link" href="#" data-nav-page="inventory">
                <svg class="sales-icon"><use href="#sales-icon-inventory"></use></svg>
                Inventory
            </a>
        </nav>

        <div class="sales-profile">
            <span class="sales-avatar">SR</span>
            <div>
                <strong>Sales Rep</strong>
                <span>Sales Operations</span>
            </div>
        </div>
    </aside>

    <header class="sales-header">
        <button class="sales-icon-button sales-header__menu" type="button" id="sales-menu-toggle" aria-label="Open navigation" aria-controls="sales-sidebar" aria-expanded="false">
            <svg class="sales-icon"><use href="#sales-icon-menu"></use></svg>
        </button>
        <strong class="sales-header__brand">AutoPartFlow</strong>
        <div class="sales-header__actions">
            <button class="sales-icon-button" type="button" aria-label="Notifications">
                <svg class="sales-icon"><use href="#sales-icon-bell"></use></svg>
                <span class="sales-notification-dot" aria-hidden="true"></span>
            </button>
            <span class="sales-avatar sales-avatar--small">SR</span>
        </div>
    </header>

    <div class="sales-sidebar-backdrop" id="sales-sidebar-backdrop"></div>

    <?= $content ?>

    <nav class="sales-mobile-nav" aria-label="Mobile navigation">
        <a href="#" class="sales-mobile-nav__item" data-mobile-page="dashboard">
            <svg class="sales-icon"><use href="#sales-icon-dashboard"></use></svg>
            <span>Home</span>
        </a>
        <a href="#" class="sales-mobile-nav__item" data-mobile-page="pos">
            <svg class="sales-icon"><use href="#sales-icon-pos"></use></svg>
            <span>POS</span>
        </a>
        <a href="#" class="sales-mobile-nav__item" data-mobile-page="orders">
            <svg class="sales-icon"><use href="#sales-icon-orders"></use></svg>
            <span>Orders</span>
        </a>
        <a href="#" class="sales-mobile-nav__item" data-mobile-page="customers">
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
        <symbol id="sales-icon-shop" viewBox="0 0 24 24"><path d="M4 3h16l2 5a4 4 0 0 1-2 3.5V21H4v-9.5A4 4 0 0 1 2 8l2-5Zm2 9v7h3v-5h6v5h3v-7a4 4 0 0 1-4-1 4 4 0 0 1-4 0 4 4 0 0 1-4 1Z"/></symbol>
        <symbol id="sales-icon-search" viewBox="0 0 24 24"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></symbol>
        <symbol id="sales-icon-plus" viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></symbol>
        <symbol id="sales-icon-phone" viewBox="0 0 24 24"><path d="M6.6 3 10 6.4 8.2 8.2a14 14 0 0 0 7.6 7.6l1.8-1.8 3.4 3.4-1.7 2.8c-.4.7-1.3 1-2.1.8C9.8 19.3 4.7 14.2 3 6.8c-.2-.8.1-1.7.8-2.1L6.6 3Z"/></symbol>
        <symbol id="sales-icon-mail" viewBox="0 0 24 24"><path d="M3 4h18v16H3V4Zm2 3v11h14V7l-7 5-7-5Zm1.2-1L12 10.1 17.8 6H6.2Z"/></symbol>
        <symbol id="sales-icon-location" viewBox="0 0 24 24"><path d="M12 22S5 15.5 5 9a7 7 0 1 1 14 0c0 6.5-7 13-7 13Zm0-17a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z"/></symbol>
        <symbol id="sales-icon-close" viewBox="0 0 24 24"><path d="m6.4 5 5.6 5.6L17.6 5 19 6.4 13.4 12l5.6 5.6-1.4 1.4-5.6-5.6L6.4 19 5 17.6l5.6-5.6L5 6.4 6.4 5Z"/></symbol>
    </svg>

    <script src="<?= asset('js/sales/sales-mock-data.js') ?>"></script>
    <script src="<?= asset('js/sales/sales.js') ?>"></script>
</body>
</html>
