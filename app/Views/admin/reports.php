<?php
/** @var array $performance */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Reports & Analytics') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);}
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
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:11px;text-transform:uppercase;color:var(--slate-500);padding:10px 12px;border-bottom:1px solid var(--slate-100);}
td{padding:13px 12px;border-bottom:1px solid var(--slate-100);font-size:13.5px;}
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">AutoPartFlow</div>
        <nav>
            <a class="nav-link" href="<?= url('admin/dashboard') ?>">Dashboard</a>
            <a class="nav-link active" href="<?= url('admin/reports') ?>">Reports</a>
            <a class="nav-link" href="<?= url('admin/users') ?>">User Management</a>
            <a class="nav-link" href="<?= url('admin/employees') ?>">Employees</a>
            <a class="nav-link" href="<?= url('admin/notifications') ?>">Notifications</a>
            <a class="nav-link" href="<?= url('admin/settings') ?>">Settings</a>
        </nav>
    </aside>
    <div class="main">
        <div class="content">
            <div class="page-head">
                <h1>Reports & Analytics</h1>
                <p>Employee sales performance and commission overview.</p>
            </div>
            <div class="card">
                <h3 style="margin-top:0;">Employee Sales Performance</h3>
                <table>
                    <thead><tr><th>Employee</th><th>Total Sales</th><th>Total Revenue</th><th>Commission Earned</th></tr></thead>
                    <tbody>
                    <?php foreach ($performance as $p): ?>
                        <tr>
                            <td><strong><?= e($p['full_name']) ?></strong> <span style="color:var(--slate-500);font-size:12px;">(<?= e($p['employee_code']) ?>)</span></td>
                            <td><?= (int) $p['total_sales'] ?></td>
                            <td>Rs. <?= number_format((float) $p['total_revenue'], 2) ?></td>
                            <td>Rs. <?= number_format((float) $p['commission_earned'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$performance): ?><tr><td colspan="4">No sales data yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
