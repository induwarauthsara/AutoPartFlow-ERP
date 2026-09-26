<?php
/**
 * Unified Admin Navigation Sidebar
 * Included across:
 *  - Dashboard (/admin/dashboard)
 *  - Inventory (/inventory)
 *  - Reports (/admin/reports)
 *  - User Management (/admin/users)
 *  - Employees (/admin/employees)
 *  - Suppliers (/admin/suppliers)
 *  - Notifications (/admin/notifications)
 *  - Settings (/admin/settings)
 */
$rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

$activeItem = match (true) {
    str_contains($rawUri, '/inventory')           => 'inventory',
    str_contains($rawUri, '/admin/purchases')     => 'purchases',
    str_contains($rawUri, '/admin/reports')       => 'reports',
    str_contains($rawUri, '/admin/users')         => 'users',
    str_contains($rawUri, '/admin/employees')     => 'employees',
    str_contains($rawUri, '/admin/suppliers')     => 'suppliers',
    str_contains($rawUri, '/admin/notifications') => 'notifications',
    str_contains($rawUri, '/admin/settings')      => 'settings',
    default => 'dashboard',
};

$userName = $_SESSION['full_name'] ?? 'System Administrator';
$userInitial = strtoupper(substr(trim($userName), 0, 1)) ?: 'A';
?>
<aside class="sidebar sales-sidebar" id="admin-sidebar" aria-label="Admin navigation">
    <div class="brand sales-brand">
        <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
        <div>
            <div class="brand-title">AutoPartFlow</div>
            <div class="brand-sub">Admin Workspace</div>
        </div>
    </div>
    <nav aria-label="Admin navigation">
        <a class="nav-link <?= $activeItem === 'dashboard' ? 'active' : '' ?>" <?= $activeItem === 'dashboard' ? 'aria-current="page"' : '' ?> href="<?= url('admin/dashboard') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8v8H3V3Zm10 0h8v5h-8V3ZM3 13h8v8H3v-8Zm10-3h8v11h-8V10Z"/></svg>
            <span>Dashboard</span>
        </a>
        <a class="nav-link <?= $activeItem === 'inventory' ? 'active' : '' ?>" <?= $activeItem === 'inventory' ? 'aria-current="page"' : '' ?> href="<?= url('inventory') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 9 4.5v11L12 22l-9-4.5v-11L12 2Zm0 2.2L6.2 7 12 9.8 17.8 7 12 4.2ZM5 8.6v7.7l6 3v-7.7l-6-3Zm8 10.7 6-3V8.6l-6 3v7.7Z"/></svg>
            <span>Inventory</span>
        </a>
        <a class="nav-link <?= $activeItem === 'purchases' ? 'active' : '' ?>" <?= $activeItem === 'purchases' ? 'aria-current="page"' : '' ?> href="<?= url('admin/purchases') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v2H4V4Zm-1 4h18v12H3V8Zm2 2v8h14v-8H5Zm2 2h10v2H7v-2Z"/></svg>
            <span>Purchases</span>
        </a>
        <a class="nav-link <?= $activeItem === 'reports' ? 'active' : '' ?>" <?= $activeItem === 'reports' ? 'aria-current="page"' : '' ?> href="<?= url('admin/reports') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h2v16h15v2H4V3Zm5 8h3v6H9v-6Zm5-5h3v11h-3V6Z"/></svg>
            <span>Reports</span>
        </a>
        <a class="nav-link <?= $activeItem === 'users' ? 'active' : '' ?>" <?= $activeItem === 'users' ? 'aria-current="page"' : '' ?> href="<?= url('admin/users') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Z"/></svg>
            <span>User Management</span>
        </a>
        <a class="nav-link <?= $activeItem === 'employees' ? 'active' : '' ?>" <?= $activeItem === 'employees' ? 'aria-current="page"' : '' ?> href="<?= url('admin/employees') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Zm16-7h4v7h-4v-7Z"/></svg>
            <span>Employees</span>
        </a>
        <a class="nav-link <?= $activeItem === 'suppliers' ? 'active' : '' ?>" <?= $activeItem === 'suppliers' ? 'aria-current="page"' : '' ?> href="<?= url('admin/suppliers') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v2H4V4Zm-1 4h18v12H3V8Zm2 2v8h14v-8H5Zm2 2h10v2H7v-2Z"/></svg>
            <span>Suppliers</span>
        </a>
        <a class="nav-link <?= $activeItem === 'notifications' ? 'active' : '' ?>" <?= $activeItem === 'notifications' ? 'aria-current="page"' : '' ?> href="<?= url('admin/notifications') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>
            <span>Notifications</span>
        </a>
        <a class="nav-link <?= $activeItem === 'settings' ? 'active' : '' ?>" <?= $activeItem === 'settings' ? 'aria-current="page"' : '' ?> href="<?= url('admin/settings') ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v2H3V5Zm4 6h10v2H7v-2Zm3 6h4v2h-4v-2Z"/></svg>
            <span>Settings</span>
        </a>
        <a class="nav-link" href="<?= url() ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span>Public Home</span>
        </a>
        <a class="nav-link" href="<?= url('logout') ?>" style="color:#fca5a5;">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            <span>Sign Out</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="avatar"><?= $userInitial ?></div>
        <div>
            <div class="name"><?= e($userName) ?></div>
            <div class="role"><?= e(ucwords(str_replace('_', ' ', $_SESSION['role_slug'] ?? 'Admin'))) ?></div>
        </div>
    </div>
</aside>

