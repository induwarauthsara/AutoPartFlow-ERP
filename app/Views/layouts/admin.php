<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin Workspace') ?> | AutoPartFlow ERP</title>
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
    <link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="base-url" content="<?= url() ?>">
</head>
<body>
<div class="app-shell">
    <?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>
    <div class="main">
        <?= $content ?>
    </div>
</div>
</body>
</html>
