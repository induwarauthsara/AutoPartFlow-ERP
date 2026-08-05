<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Order Management') ?> | AutoPartFlow ERP</title>
    <link rel="stylesheet" href="<?= asset('css/sales/orders.css') ?>">
</head>
<body class="orders-app">
    <aside class="app-sidebar" id="app-sidebar" aria-label="Sales navigation">
        <div class="app-brand">
            <span class="app-brand__mark" aria-hidden="true">A</span>
            <div>
                <strong>AutoPartFlow</strong>
                <span>Sales ERP</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <p class="sidebar-nav__label">Workspace</p>
            <a class="sidebar-nav__link" href="#" data-route="dashboard">
                <svg class="ui-icon"><use href="#icon-dashboard"></use></svg>
                Dashboard
            </a>
            <a class="sidebar-nav__link" href="#" data-route="pos">
                <svg class="ui-icon"><use href="#icon-pos"></use></svg>
                Point of Sale
            </a>
            <a class="sidebar-nav__link sidebar-nav__link--active" href="#" data-route="orders" aria-current="page">
                <svg class="ui-icon"><use href="#icon-orders"></use></svg>
                Orders
            </a>
            <a class="sidebar-nav__link" href="#" data-route="customers">
                <svg class="ui-icon"><use href="#icon-customers"></use></svg>
                Customers
            </a>
            <a class="sidebar-nav__link" href="#" data-route="inventory">
                <svg class="ui-icon"><use href="#icon-inventory"></use></svg>
                Inventory
            </a>
        </nav>

        <div class="sidebar-profile">
            <span class="avatar">SR</span>
            <div>
                <strong>Sales Rep</strong>
                <span>Sales Operations</span>
            </div>
        </div>
    </aside>

    <header class="app-header">
        <button class="icon-button app-header__menu" type="button" id="sidebar-toggle" aria-label="Open navigation" aria-controls="app-sidebar" aria-expanded="false">
            <svg class="ui-icon"><use href="#icon-menu"></use></svg>
        </button>
        <div class="app-header__brand">AutoPartFlow</div>
        <div class="app-header__actions">
            <button class="icon-button" type="button" aria-label="Notifications">
                <svg class="ui-icon"><use href="#icon-bell"></use></svg>
                <span class="notification-dot" aria-hidden="true"></span>
            </button>
            <span class="avatar avatar--small">SR</span>
        </div>
    </header>

    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <?= $content ?>

    <nav class="mobile-nav" aria-label="Mobile navigation">
        <a href="#" class="mobile-nav__item">
            <svg class="ui-icon"><use href="#icon-pos"></use></svg>
            <span>POS</span>
        </a>
        <a href="#" class="mobile-nav__item">
            <svg class="ui-icon"><use href="#icon-inventory"></use></svg>
            <span>Inventory</span>
        </a>
        <a href="#" class="mobile-nav__item mobile-nav__item--active" aria-current="page">
            <svg class="ui-icon"><use href="#icon-orders"></use></svg>
            <span>Orders</span>
        </a>
        <a href="#" class="mobile-nav__item">
            <svg class="ui-icon"><use href="#icon-customers"></use></svg>
            <span>Customers</span>
        </a>
    </nav>

    <svg class="svg-sprite" aria-hidden="true">
        <symbol id="icon-dashboard" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3Zm10 0h8v5h-8V3ZM3 13h8v8H3v-8Zm10-3h8v11h-8V10Z"/></symbol>
        <symbol id="icon-pos" viewBox="0 0 24 24"><path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm1 3v5h10V5H7Zm0 8v2h2v-2H7Zm4 0v2h2v-2h-2Zm4 0v2h2v-2h-2Zm-8 4v2h2v-2H7Zm4 0v2h6v-2h-6Z"/></symbol>
        <symbol id="icon-orders" viewBox="0 0 24 24"><path d="M7 2h10v2h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3V2Zm2 2v2h6V4H9Zm-3 6v2h12v-2H6Zm0 4v2h8v-2H6Zm0 4v2h5v-2H6Z"/></symbol>
        <symbol id="icon-customers" viewBox="0 0 24 24"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-1a3 3 0 1 0 0-6 5.9 5.9 0 0 1 0 6ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Zm14.5 0H22v-2c0-2.3-2.1-4-4.8-4.7 1.1 1.2 1.8 2.8 1.8 4.7v2h-2.5Z"/></symbol>
        <symbol id="icon-inventory" viewBox="0 0 24 24"><path d="m12 2 9 4.5v11L12 22l-9-4.5v-11L12 2Zm0 2.2L6.2 7 12 9.8 17.8 7 12 4.2ZM5 8.6v7.7l6 3v-7.7l-6-3Zm8 10.7 6-3V8.6l-6 3v7.7Z"/></symbol>
        <symbol id="icon-menu" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/></symbol>
        <symbol id="icon-bell" viewBox="0 0 24 24"><path d="M12 22a2.5 2.5 0 0 0 2.3-1.5H9.7A2.5 2.5 0 0 0 12 22Zm7-5-2-2v-5a5 5 0 0 0-4-4.9V3h-2v2.1A5 5 0 0 0 7 10v5l-2 2v2h14v-2Z"/></symbol>
        <symbol id="icon-search" viewBox="0 0 24 24"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></symbol>
        <symbol id="icon-plus" viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></symbol>
        <symbol id="icon-download" viewBox="0 0 24 24"><path d="M11 3h2v10l3.5-3.5 1.4 1.4-5.9 5.9-5.9-5.9 1.4-1.4L11 13V3ZM4 19h16v2H4v-2Z"/></symbol>
        <symbol id="icon-filter" viewBox="0 0 24 24"><path d="M3 5h18v2H3V5Zm3 6h12v2H6v-2Zm4 6h4v2h-4v-2Z"/></symbol>
        <symbol id="icon-chevron" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7-1.4-1.4L13.2 12 7.6 6.4 9 5Z"/></symbol>
        <symbol id="icon-close" viewBox="0 0 24 24"><path d="m6.4 5 5.6 5.6L17.6 5 19 6.4 13.4 12l5.6 5.6-1.4 1.4-5.6-5.6L6.4 19 5 17.6l5.6-5.6L5 6.4 6.4 5Z"/></symbol>
        <symbol id="icon-trash" viewBox="0 0 24 24"><path d="M8 3h8l1 2h4v2H3V5h4l1-2Zm-2 6h12l-1 12H7L6 9Zm3 2v7h2v-7H9Zm4 0v7h2v-7h-2Z"/></symbol>
        <symbol id="icon-cart" viewBox="0 0 24 24"><path d="M3 3h2l2.2 10.2A2 2 0 0 0 9.1 15H18a2 2 0 0 0 1.9-1.4L22 7H7l-.4-2H3V3Zm6 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm9 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></symbol>
        <symbol id="icon-check" viewBox="0 0 24 24"><path d="m9.5 16.2-4.2-4.2 1.4-1.4 2.8 2.8 7.8-7.8L18.7 7l-9.2 9.2Z"/></symbol>
    </svg>

    <script src="<?= asset('js/sales/orders-mock-data.js') ?>"></script>
    <script src="<?= asset('js/sales/orders.js') ?>"></script>
</body>
</html>
