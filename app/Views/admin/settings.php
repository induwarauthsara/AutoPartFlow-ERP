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
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--green:#16a34a;}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:220px;flex-shrink:0;background:linear-gradient(180deg,var(--navy-900),var(--navy-800));color:#cbd5e1;padding:18px 12px;display:flex;flex-direction:column;}
.brand{display:flex;align-items:center;gap:9px;padding:6px 8px 20px;}
.brand-mark{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--indigo-500),#7c8cf0);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0;}
.brand-title{color:#fff;font-weight:700;font-size:14px;}
.brand-sub{font-size:10.5px;color:#8590b3;text-transform:uppercase;letter-spacing:.04em;}
.nav-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;color:#b7c0dd;font-size:13px;font-weight:500;margin-bottom:2px;text-decoration:none;}
.nav-link.active{background:var(--indigo-500);color:#fff;}
.sidebar-footer{margin-top:auto;padding-top:12px;border-top:1px solid rgba(255,255,255,.08);font-size:11.5px;color:#7f8bb0;}
.sidebar-footer .dot{width:7px;height:7px;background:var(--green);border-radius:50%;display:inline-block;margin-right:6px;}
.main{flex:1;min-width:0;}
.topbar{background:#fff;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;gap:16px;padding:12px 24px;font-weight:700;}
.search-box{flex:1;max-width:380px;display:flex;align-items:center;gap:8px;background:var(--slate-100);border-radius:9px;padding:8px 12px;color:var(--slate-500);font-weight:400;}
.search-box input{border:none;background:transparent;outline:none;flex:1;font-size:13px;}
.topbar-icons{display:flex;align-items:center;gap:12px;margin-left:auto;color:var(--slate-500);}
.avatar{width:30px;height:30px;border-radius:50%;background:var(--indigo-500);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:11px;}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;max-width:520px;}
.btn{padding:9px 16px;border-radius:9px;font-size:12.5px;font-weight:600;border:1px solid var(--slate-300);background:#fff;cursor:pointer;}
.btn-primary{background:var(--navy-900);color:#fff;border-color:var(--navy-900);}
.head-actions{display:flex;gap:10px;}
.settings-layout{display:grid;grid-template-columns:210px 1fr;gap:16px;align-items:start;}
.tab-card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:8px;border:1px solid #eef0f5;}
.tab-link{display:block;padding:9px 12px;border-radius:9px;font-size:13px;color:var(--slate-900);text-decoration:none;margin-bottom:2px;}
.tab-link.active{background:var(--navy-900);color:#fff;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:22px;border:1px solid #eef0f5;}
.card h3{margin:0 0 4px;font-size:16px;}
.card .desc{color:var(--slate-500);font-size:12.5px;margin:0 0 16px;}
.form-row{display:grid;grid-template-columns:130px 1fr 1fr;gap:16px;align-items:start;}
.logo-box{border:2px dashed var(--slate-300);border-radius:12px;height:110px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:var(--slate-500);}
.form-group{margin-bottom:14px;}
.form-group label{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px;}
.form-control{width:100%;padding:9px 11px;border:1px solid var(--slate-300);border-radius:8px;background:#fff;}
@media (max-width:900px){.settings-layout{grid-template-columns:1fr;}.form-row{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">AP</div>
            <div><div class="brand-title">AutoPartFlow</div><div class="brand-sub">Logistics ERP</div></div>
        </div>
        <nav>
            <a class="nav-link" href="<?= url('admin/dashboard') ?>">Dashboard</a>
            <a class="nav-link" href="#">Sales</a>
            <a class="nav-link" href="#">Products</a>
            <a class="nav-link" href="#">Inventory</a>
            <a class="nav-link" href="<?= url('admin/users') ?>">Customers</a>
            <a class="nav-link" href="<?= url('admin/employees') ?>">Employees</a>
            <a class="nav-link" href="<?= url('admin/reports') ?>">Reports</a>
            <a class="nav-link active" href="<?= url('admin/settings') ?>">Settings</a>
        </nav>
        <div class="sidebar-footer"><span class="dot"></span>All Systems Operational</div>
    </aside>

    <div class="main">
        <header class="topbar">
            AutoPartFlow
            <div class="search-box">🔍 <input type="text" placeholder="Search settings..."></div>
            <div class="topbar-icons">🔔 📅 <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div></div>
        </header>

        <div class="content">
            <div class="page-head">
                <div>
                    <h1>System Settings</h1>
                    <p>Configure your AutoPartFlow ERP environment, manage business details, and customize your operational preferences.</p>
                </div>
                <div class="head-actions">
                    <button class="btn">Discard Changes</button>
                    <button class="btn btn-primary">💾 Save Configuration</button>
                </div>
            </div>

            <div class="settings-layout">
                <div class="tab-card">
                    <a class="tab-link active" href="#">📇 Business Info</a>
                    <a class="tab-link" href="#">🧾 Invoice Settings</a>
                    <a class="tab-link" href="#">🏛 Tax Config</a>
                    <a class="tab-link" href="#">💾 Data Backup</a>
                    <a class="tab-link" href="#">⚙️ Preferences</a>
                </div>

                <div class="card">
                    <h3>Business Information</h3>
                    <p class="desc">Update your company details and primary branding for external communications.</p>
                    <div class="form-row">
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:5px;">Company Logo</label>
                            <div class="logo-box">🖼️<span style="font-size:11px;text-align:center;">Click to upload<br>SVG, PNG, JPG (max 2MB)</span></div>
                        </div>
                        <div>
                            <div class="form-group"><label>Company Name</label><input class="form-control" value="<?= e($settings['business_name'] ?? '') ?>" readonly></div>
                            <div class="form-group"><label>Contact Email</label><input class="form-control" value="<?= e($settings['business_email'] ?? '') ?>" readonly></div>
                        </div>
                        <div>
                            <div class="form-group"><label>&nbsp;</label></div>
                            <div class="form-group"><label>Business Phone</label><input class="form-control" value="<?= e($settings['business_phone'] ?? '') ?>" readonly></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Primary Address</label>
                        <textarea class="form-control" rows="2" readonly><?= e($settings['business_address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>