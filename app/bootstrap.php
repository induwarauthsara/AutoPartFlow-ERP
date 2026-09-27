<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    $trimmedPath = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . '/' . $trimmedPath;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = (string) ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if ($token === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals((string) $_SESSION['csrf_token'], $token);
}

function auth_check(): bool
{
    return !empty($_SESSION['user_id']);
}

function auth_role(): ?string
{
    return $_SESSION['role_slug'] ?? null;
}

function auth_dashboard_url(): string
{
    $role = auth_role();
    return match ($role) {
        'owner' => url('admin/dashboard'),
        'sales_rep' => url('sales'),
        'store_manager' => url('inventory'),
        'shop_customer' => url('customer/dashboard'),
        default => url('login'),
    };
}

function auth_dashboard_label(): string
{
    $role = auth_role();
    return match ($role) {
        'owner' => 'Admin Dashboard',
        'sales_rep' => 'Sales Dashboard',
        'store_manager' => 'Store Dashboard',
        'shop_customer' => 'Customer Dashboard',
        default => 'Dashboard',
    };
}

function navbar(array $data = []): void
{
    extract($data, EXTR_SKIP);
    $path = APP_PATH . '/Views/partials/navbar.php';
    if (file_exists($path)) {
        require $path;
        return;
    }

    $altPath = APP_PATH . '/Views/layouts/partials/navbar.php';
    if (file_exists($altPath)) {
        require $altPath;
        return;
    }

    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = rtrim(BASE_URL, '/');
    $isHome = ($currentPath === $base . '/' || $currentPath === '/' || ($base !== '' && $currentPath === $base) || $currentPath === '');
    $isCatalog = str_contains($currentPath, '/catalog') || str_contains($currentPath, '/finder');
    $isShopCustomer = (string) ($_SESSION['role_slug'] ?? '') === 'shop_customer';
    $isOrders = str_contains($currentPath, '/orders') || str_contains($currentPath, '/my-orders');
    $searchQuery = $filters['search'] ?? ($_GET['q'] ?? '');
    ?>
    <header class="public-header">
        <div class="public-header__inner">
            <a class="public-brand" href="<?= url() ?>">
                <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
                <span>AutoPartFlow</span>
            </a>
            <nav class="public-nav" aria-label="Public navigation">
                <a class="<?= $isHome ? 'active' : '' ?>" href="<?= url() ?>">Home</a>
                <a class="<?= $isCatalog ? 'active' : '' ?>" href="<?= url('catalog') ?>">Catalog & Parts Finder</a>
                <?php if ($isShopCustomer): ?>
                    <a class="<?= $isOrders ? 'active' : '' ?>" href="<?= url('orders') ?>">My Orders</a>
                <?php endif; ?>
            </nav>
            <div class="public-actions">
                <form class="public-search" action="<?= url('catalog') ?>" method="get">
                    <span class="material-symbols-outlined">search</span>
                    <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Search parts..." aria-label="Search parts">
                </form>
                <a class="cart-button" href="<?= url('cart') ?>" aria-label="Shopping cart">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span class="cart-count">0</span>
                </a>
                <span class="header-divider"></span>
                <?php if (auth_check()): ?>
                    <a class="sign-in-button sign-in-button--dashboard" href="<?= auth_dashboard_url() ?>">
                        <span class="material-symbols-outlined">dashboard</span>
                        <?= e(auth_dashboard_label()) ?>
                    </a>
                    <a class="sign-in-button sign-in-button--profile" href="<?= url('profile') ?>" title="Edit Profile">
                        <span class="material-symbols-outlined">person</span>
                        Profile
                    </a>
                    <a class="sign-in-button sign-in-button--logout" href="<?= url('logout') ?>" title="Sign Out">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="logout-text">Sign Out</span>
                    </a>
                <?php else: ?>
                    <a class="sign-in-button" href="<?= url('login') ?>">
                        <span class="material-symbols-outlined">login</span>
                        Sign In
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php
}


