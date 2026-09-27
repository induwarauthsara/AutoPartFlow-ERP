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
        <header class="topbar">
            <div style="font-weight:700;font-size:16px;color:var(--navy-900, #0f1f38);display:flex;align-items:center;gap:8px;">
                <span>AutoPartFlow</span>
                <span style="font-size:11.5px;font-weight:600;padding:2px 8px;border-radius:6px;background:#e0e7ff;color:#4338ca;">Admin Workspace</span>
            </div>
            <div class="topbar-actions" style="display:flex;align-items:center;gap:12px;">
                <a href="<?= url('admin/notifications') ?>" style="display:grid;place-items:center;width:36px;height:36px;border-radius:8px;background:var(--slate-100, #f1f5f9);color:var(--slate-500, #64748b);text-decoration:none;" title="Notifications">
                    <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:currentColor;"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>
                </a>
                <a href="<?= url('profile') ?>" class="avatar" title="View Profile — <?= e($_SESSION['full_name'] ?? 'Admin') ?>" style="width:36px;height:36px;border-radius:50%;background:var(--indigo-500, #4f5bd5);color:#fff;display:grid;place-items:center;font-weight:700;font-size:13px;text-decoration:none;">
                    <?= e(strtoupper(substr(trim((string)($_SESSION['full_name'] ?? 'Admin')), 0, 1))) ?>
                </a>
            </div>
        </header>
        <div class="content">
            <?= $content ?>
        </div>
    </div>
</div>
<script src="<?= asset('js/validation.js') ?>"></script>
</body>
</html>

