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
    <link rel="stylesheet" href="<?= asset('css/public/cart.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public/orders.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public/finder.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public/delivery.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public/pages.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public/customer-dashboard.css') ?>">
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(url(), '/') ?>'
        };
    </script>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<meta name="base-url" content="<?= url() ?>">
</head>
<body class="public-body">
<?php navbar(); ?>

<main class="public-main">
    <?php if (!empty($flash)): ?><div class="container" style="margin-top: 16px;"><div role="status" class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div></div><?php endif; ?>
    <?= $content ?>
</main>

<footer class="public-footer">
    <div class="public-footer__bottom">
        <span>&copy; <?= date('Y') ?> AutoPartFlow ERP. All rights reserved.</span>
        <div>
            <a href="<?= url('finder') ?>">Spare Parts Finder</a>
            <a href="<?= url('catalog') ?>">Catalog</a>
            <a href="<?= url('track-order') ?>">Track Order</a>
        </div>
    </div>
</footer>

<script src="<?= asset('js/validation.js') ?>"></script>
<script src="<?= asset('js/public/catalog.js') ?>"></script>
<script src="<?= asset('js/public/cart.js') ?>"></script>
<script src="<?= asset('js/public/finder.js') ?>"></script>
</body>
</html>

