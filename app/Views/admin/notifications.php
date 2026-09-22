<?php
/** @var array $rows */
$critical = array_filter($rows, fn($n) => ($n['type'] ?? '') === 'low_stock' || str_contains(strtolower($n['title'] ?? ''), 'failed') || str_contains(strtolower($n['title'] ?? ''), 'critical'));
$updates = array_udiff($rows, $critical, fn($a,$b) => $a['id'] <=> $b['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Notification Center') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--red:#dc2626;--red-bg:#fdecec;}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:230px;flex-shrink:0;background:linear-gradient(180deg,var(--navy-900),var(--navy-800));color:#cbd5e1;padding:18px 12px;display:flex;flex-direction:column;}
.brand{display:flex;align-items:center;gap:9px;padding:6px 8px 20px;}
.brand-mark{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--indigo-500),#7c8cf0);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0;}
.brand-title{color:#fff;font-weight:700;font-size:14px;}
.brand-sub{font-size:10.5px;color:#8590b3;text-transform:uppercase;letter-spacing:.04em;}
.nav-group{margin-top:12px;}
.nav-group-label{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:#5b6689;padding:0 10px;margin-bottom:6px;font-weight:600;}
.nav-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;color:#b7c0dd;font-size:13px;font-weight:500;margin-bottom:2px;text-decoration:none;}
.nav-link.active{background:var(--indigo-500);color:#fff;}
.sidebar-footer{margin-top:auto;padding-top:12px;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:9px;}
.avatar{width:30px;height:30px;border-radius:50%;background:var(--indigo-500);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:11px;flex-shrink:0;}
.sidebar-footer .name{color:#fff;font-size:12.5px;font-weight:600;}
.sidebar-footer .role{color:#7f8bb0;font-size:11px;}
.main{flex:1;min-width:0;}
.topbar{background:#fff;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;gap:16px;padding:12px 24px;font-weight:700;}
.topbar-icons{display:flex;align-items:center;gap:14px;margin-left:auto;color:var(--slate-500);}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;}
.btn{padding:8px 14px;border-radius:9px;font-size:12.5px;font-weight:600;border:1px solid var(--slate-300);background:#fff;cursor:pointer;}
.btn-primary{background:var(--navy-900);color:#fff;border-color:var(--navy-900);}
.head-actions{display:flex;gap:10px;}
.grid-2{display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;margin-bottom:16px;}
.card h3{margin:0 0 14px;font-size:15px;display:flex;align-items:center;gap:8px;}
.badge-count{background:var(--red-bg);color:var(--red);font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;margin-left:6px;}
.notif-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--slate-100);border-left:3px solid var(--red);padding-left:12px;margin-bottom:8px;}
.notif-item.info{border-left-color:var(--indigo-500);}
.notif-item:last-child{border-bottom:none;}
.notif-icon{width:34px;height:34px;border-radius:10px;background:var(--red-bg);color:var(--red);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;}
.notif-icon.info{background:var(--indigo-50);color:var(--indigo-500);}
.notif-title{font-weight:600;font-size:13.5px;}
.notif-msg{color:var(--slate-500);font-size:12.5px;margin-top:3px;}
.notif-time{color:var(--slate-500);font-size:11px;float:right;}
.stat-row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;margin-bottom:6px;}
.stat-row .dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px;}
.stat-row .num{font-weight:700;font-size:14px;}
.bar-track{height:5px;background:var(--slate-100);border-radius:6px;overflow:hidden;margin-bottom:14px;}
.bar-fill{height:100%;border-radius:6px;}
.chip{display:inline-block;font-size:11.5px;font-weight:600;padding:5px 12px;border-radius:20px;background:var(--slate-100);color:var(--slate-500);margin:0 6px 6px 0;}
.chip.active{background:var(--indigo-500);color:#fff;}
@media (max-width:900px){.grid-2{grid-template-columns:1fr;}}
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
            <div class="nav-group">
                <div class="nav-group-label">Main Menu</div>
                <a class="nav-link active" href="<?= url('admin/dashboard') ?>">Dashboard</a>
                <a class="nav-link" href="#">Sales</a>
            </div>
            <div class="nav-group">
                <div class="nav-group-label">Operations</div>
                <a class="nav-link" href="#">Products</a>
                <a class="nav-link" href="#">Inventory</a>
            </div>
            <div class="nav-group">
                <div class="nav-group-label">Administration</div>
                <a class="nav-link" href="<?= url('admin/users') ?>">Customers</a>
                <a class="nav-link" href="<?= url('admin/employees') ?>">Employees</a>
                <a class="nav-link" href="<?= url('admin/reports') ?>">Reports</a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)) ?></div>
            <div><div class="name"><?= e($_SESSION['full_name'] ?? 'Sarah Jenkins') ?></div><div class="role">Admin</div></div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">AutoPartFlow
            <div class="topbar-icons">🔔 📅 <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)) ?></div></div>
        </header>

        <div class="content">
            <div class="page-head">
                <div>
                    <h1>Notification Center</h1>
                    <p>Manage system alerts and updates.</p>
                </div>
                <div class="head-actions">
                    <button class="btn">Mark all as read</button>
                    <button class="btn btn-primary">Filter</button>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <div class="card">
                        <h3>⚠️ Critical Alerts <span class="badge-count"><?= count($critical) ?: 0 ?> New</span></h3>
                        <?php if ($critical): foreach ($critical as $n): ?>
                            <div class="notif-item">
                                <div class="notif-icon">!</div>
                                <div style="flex:1;">
                                    <span class="notif-time"><?= date('g:i A', strtotime($n['created_at'])) ?></span>
                                    <div class="notif-title"><?= e($n['title']) ?></div>
                                    <div class="notif-msg"><?= e($n['message']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p style="color:var(--slate-500);">No critical alerts.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card">
                        <h3>ℹ️ Updates &amp; Activities</h3>
                        <?php if ($updates): foreach ($updates as $n): ?>
                            <div class="notif-item info">
                                <div class="notif-icon info">i</div>
                                <div style="flex:1;">
                                    <span class="notif-time"><?= date('g:i A', strtotime($n['created_at'])) ?></span>
                                    <div class="notif-title"><?= e($n['title']) ?></div>
                                    <div class="notif-msg"><?= e($n['message']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p style="color:var(--slate-500);">No updates yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <div class="card">
                        <h3>System Status</h3>
                        <div class="stat-row"><span><span class="dot" style="background:var(--red);"></span>Low Stock Items</span><span class="num">14</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:35%;background:var(--red);"></div></div>
                        <div class="stat-row"><span><span class="dot" style="background:var(--indigo-500);"></span>Pending Orders</span><span class="num">42</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:60%;background:var(--indigo-500);"></div></div>
                    </div>
                    <div class="card">
                        <h3>Quick Filters</h3>
                        <span class="chip active">All</span>
                        <span class="chip">Critical</span>
                        <span class="chip">Orders</span>
                        <span class="chip">Inventory</span>
                        <span class="chip">System</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>