<?php
/** @var array $settings */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Settings') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);}
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
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;margin-top:18px;max-width:600px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;}
.form-control{width:100%;padding:10px 12px;border:1px solid var(--slate-300);border-radius:8px;background:#fff;color:var(--slate-900);}
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
            <a class="nav-link" href="<?= url('admin/notifications') ?>">Notifications</a>
            <a class="nav-link active" href="<?= url('admin/settings') ?>">Settings</a>
        </nav>
    </aside>
    <div class="main">
        <div class="content">
            <div class="page-head">
                <h1>System Settings</h1>
                <p>Configure your AutoPartFlow environment and business details.</p>
            </div>
            <div class="card">
                <div class="form-group">
                    <label>Business Name</label>
                    <input class="form-control" value="<?= e($settings['business_name'] ?? '') ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Business Address</label>
                    <input class="form-control" value="<?= e($settings['business_address'] ?? '') ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Business Phone</label>
                    <input class="form-control" value="<?= e($settings['business_phone'] ?? '') ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Business Email</label>
                    <input class="form-control" value="<?= e($settings['business_email'] ?? '') ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Currency</label>
                    <input class="form-control" value="<?= e($settings['currency'] ?? '') ?> (<?= e($settings['currency_symbol'] ?? '') ?>)" readonly>
                </div>
                <div class="form-group">
                    <label>Tax Rate</label>
                    <input class="form-control" value="<?= e($settings['tax_rate'] ?? '0') ?>%" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
