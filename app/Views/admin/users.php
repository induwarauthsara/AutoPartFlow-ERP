<?php
/** @var array $rows */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'User Management') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--bg:#f5f6fb;--green:#16a34a;--green-bg:#e7f8ee;--red:#dc2626;--red-bg:#fdecec;--amber:#d97706;--amber-bg:#fef3e2;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);}
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
.badge{display:inline-flex;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:20px;}
.badge.active{background:var(--green-bg);color:var(--green);}
.badge.inactive{background:var(--amber-bg);color:var(--amber);}
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">AutoPartFlow</div>
        <nav>
            <a class="nav-link" href="<?= url('admin/dashboard') ?>">Dashboard</a>
            <a class="nav-link" href="<?= url('admin/reports') ?>">Reports</a>
            <a class="nav-link active" href="<?= url('admin/users') ?>">User Management</a>
            <a class="nav-link" href="<?= url('admin/employees') ?>">Employees</a>
            <a class="nav-link" href="<?= url('admin/notifications') ?>">Notifications</a>
            <a class="nav-link" href="<?= url('admin/settings') ?>">Settings</a>
        </nav>
    </aside>
    <div class="main">
        <div class="content">
            <div class="page-head">
                <h1>User Management</h1>
                <p>Control system access, roles, and review audit logs.</p>
            </div>
            <div class="card">
                <table>
                    <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last Login</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $u): ?>
                        <tr>
                            <td><strong><?= e($u['full_name']) ?></strong><br><span style="color:var(--slate-500);font-size:12px;"><?= e($u['email']) ?></span></td>
                            <td><?= e($u['role_name']) ?></td>
                            <td><span class="badge <?= $u['is_active'] ? 'active' : 'inactive' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td><?= $u['last_login_at'] ? date('M j, g:i A', strtotime($u['last_login_at'])) : 'Never' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="4">No users found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>