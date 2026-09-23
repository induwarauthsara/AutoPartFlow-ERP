<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'AutoPartFlow') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@400&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0..200" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/public/public.css') ?>">
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(url(), '/') ?>'
        };
    </script>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
</head>
<body class="public-body">
<header class="public-header">
    <div class="public-header__inner">
        <a class="public-brand" href="<?= url() ?>">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
            <span>AutoPartFlow</span>
        </a>

        <nav class="public-nav" aria-label="Public navigation">
            <a class="<?= $currentPath === $base . '/' || $currentPath === '/' ? 'active' : '' ?>" href="<?= url() ?>">Home</a>
            <a class="<?= str_contains($currentPath, '/catalog') ? 'active' : '' ?>" href="<?= url('catalog') ?>">Catalog</a>
            <a class="<?= str_contains($currentPath, '/track-order') ? 'active' : '' ?>" href="<?= url('track-order') ?>">Track Order</a>
        </nav>

        <div class="public-actions">
            <form class="public-search" action="<?= url('catalog') ?>" method="get">
                <span class="material-symbols-outlined">search</span>
                <input type="search" name="q" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search parts..." aria-label="Search parts">
            </form>
            <a class="cart-button" href="<?= url('checkout') ?>" aria-label="Shopping cart">
                <span class="material-symbols-outlined">shopping_cart</span>
                <span class="cart-count">0</span>
            </a>
            <span class="header-divider"></span>
            <a class="sign-in-button" href="<?= url('login') ?>">
                <span class="material-symbols-outlined">login</span>
                Sign In
            </a>
        </div>
    </div>
</header>

<main class="public-main">
    <?= $content ?>
</main>

<footer class="public-footer">
    <div class="public-footer__bottom">
        <span>&copy; <?= date('Y') ?> AutoPartFlow ERP. All rights reserved.</span>
        <div>
            <a href="<?= url('catalog') ?>">Catalog</a>
            <a href="<?= url('track-order') ?>">Track Order</a>
        </div>
    </div>
</footer>

<script src="<?= asset('js/public/catalog.js') ?>"></script>
</body>
</html>
