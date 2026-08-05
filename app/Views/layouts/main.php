<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'AutoPartFlow ERP') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="site-body">
    <header class="navbar">
        <div class="container navbar-inner">
            <a href="<?= url() ?>" class="brand-logo">
                <span class="brand-badge">A</span>
                <div class="brand-text">
                    <strong>AutoPartFlow</strong>
                    <span>Enterprise ERP</span>
                </div>
            </a>
            
            <nav class="main-nav">
                <a href="<?= url() ?>" class="nav-link">Home</a>
                <a href="<?= url('sales') ?>" class="nav-link">Sales Workspace</a>
                <a href="<?= url('sales/pos') ?>" class="nav-link">POS</a>
                <a href="<?= url('sales/orders') ?>" class="nav-link">Orders</a>
            </nav>

            <div class="header-actions">
                <a href="<?= url('login') ?>" class="btn-login">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                    </svg>
                    <span>Sign In</span>
                </a>
            </div>
        </div>
    </header>

    <main class="page-content">
        <?php if (!empty($flash)): ?>
            <div class="container">
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <span class="alert-icon">✓</span>
                    <span><?= e($flash['message']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <strong>AutoPartFlow ERP</strong>
                <p>Automobile Spare Parts Distribution & Inventory Management System</p>
            </div>
            <p class="copyright">&copy; <?= date('Y') ?> AutoPartFlow ERP. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
