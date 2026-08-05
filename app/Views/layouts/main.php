<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'AutoPartFlow ERP') ?> | AutoPartFlow ERP</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <a href="<?= url() ?>" class="logo">AutoPartFlow ERP</a>
            <nav class="nav">
                <a href="<?= url() ?>" class="nav-link">Dashboard</a>
                <a href="<?= url('parts') ?>" class="nav-link">Parts</a>
            </nav>
        </div>
    </header>

    <main class="main container">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> AutoPartFlow ERP. Pure PHP MVC.</p>
        </div>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
