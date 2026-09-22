<?php
/** @var array $rows */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Notification Center') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--bg:#f5f6fb;--red:#dc2626;--red-bg:#fdecec;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:250px;flex-shrink:0;background:linear-gradient(180deg,var(--navy-900),var(--navy-800));color:#cbd5e1;padding:20px 14px;}
.brand{color:#fff;font-weight:700;padding:6px 8px 22px;}
.nav-link{display:block;padding:9px 10px;border-radius:8px;color:#b7c0dd;font-size:13.5px;font-weight:500;margin-bottom:2px;text-decoration:none;}
.nav-link.active{background:var(--indigo-500);color:#fff;}
.main{flex:1;min-width:0;}
.content{padding:26px;}
.page-head h1{font-size:24px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13.5px;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;margin-top:18px;}
.notif-item{display:flex;gap:12px;padding:14px 0;border-bottom:1px solid var(--slate-100);}
.notif-item:last-child{border-bottom:none;}
.notif-icon{width:36px;height:36px;border-radius:10px;background:var(--indigo-50);color:var(--indigo-500);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;}
.notif-title{font-weight:600;font-size:13.5px;}
.notif-msg{color:var(--slate-500);font-size:12.5px;margin-top:2px;}
.notif-time{color:var(--slate-500);font-size:11.5px;margin-top:4px;}
.unread{background:var(--red);}
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">AutoPartFlow</div>
        <nav>
            <a class="nav-link" href="<?= url('admin/dashboard') ?>">Dashboard</a>
            <a class="nav-link" href="<?= url('admin/reports') ?>">Reports</a>
            <a class="nav-link" href="<?= url('admin/users') ?>">User Management</a>
            <a class="nav-link" href="<?= url('admin/employees') ?>">Employees</a>
            <a class="nav-link active" href="<?= url('admin/notifications') ?>">Notifications</a>
            <a class="nav-link" href="<?= url('admin/settings') ?>">Settings</a>
        </nav>
    </aside>
    <div class="main">
        <div class="content">
            <div class="page-head">
                <h1>Notification Center</h1>
                <p>Manage system alerts and updates.</p>
            </div>
            <div class="card">
                <?php foreach ($rows as $n): ?>
                    <div class="notif-item">
                        <div class="notif-icon">!</div>
                        <div>
                            <div class="notif-title"><?= e($n['title']) ?></div>
                            <div class="notif-msg"><?= e($n['message']) ?></div>
                            <div class="notif-time"><?= date('M j, g:i A', strtotime($n['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$rows): ?><p style="color:var(--slate-500);">No notifications yet.</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
