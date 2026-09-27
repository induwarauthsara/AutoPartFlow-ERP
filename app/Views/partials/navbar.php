<?php
/**
 * Reusable Navigation Bar Component
 * AutoPartFlow ERP
 *
 * Included by public layouts and views:
 *  - layouts/public.php
 *  - layouts/main.php
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = rtrim(BASE_URL, '/');

// Active route detection
$isHome = ($currentPath === $base . '/' || $currentPath === '/' || ($base !== '' && $currentPath === $base) || $currentPath === '');
$isFinder = str_contains($currentPath, '/finder');
$isCatalog = str_contains($currentPath, '/catalog');
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
            <a class="<?= $isFinder ? 'active' : '' ?>" href="<?= url('finder') ?>">Spare Parts Finder</a>
            <a class="<?= $isCatalog ? 'active' : '' ?>" href="<?= url('catalog') ?>">Catalog</a>
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
