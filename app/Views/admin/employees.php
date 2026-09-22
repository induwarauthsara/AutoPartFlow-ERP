<?php
/** @var array $rows */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Employee Management') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--green:#16a34a;--green-bg:#e7f8ee;--amber:#d97706;--amber-bg:#fef3e2;}
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
.search-box{flex:1;max-width:380px;display:flex;align-items:center;gap:8px;background:var(--slate-100);border-radius:9px;padding:8px 12px;color:var(--slate-500);font-weight:400;}
.search-box input{border:none;background:transparent;outline:none;flex:1;font-size:13px;}
.topbar-icons{display:flex;align-items:center;gap:12px;margin-left:auto;color:var(--slate-500);}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;}
.btn{padding:9px 16px;border-radius:9px;font-size:12.5px;font-weight:600;border:1px solid var(--slate-300);background:#fff;cursor:pointer;}
.btn-primary{background:var(--navy-900);color:#fff;border-color:var(--navy-900);}
.head-actions{display:flex;gap:10px;}
.grid-2{display:grid;grid-template-columns:1fr 1.4fr;gap:16px;margin-bottom:16px;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;}
.stat-mini{display:flex;gap:14px;margin-top:12px;}
.stat-mini .box{background:var(--slate-100);padding:8px 14px;border-radius:9px;}
.stat-mini .num{font-size:16px;font-weight:700;}
.stat-mini .label{font-size:11px;color:var(--slate-500);}
.bar-track{height:6px;background:var(--slate-100);border-radius:6px;overflow:hidden;margin:10px 0;}
.bar-fill{height:100%;border-radius:6px;background:var(--indigo-500);}
.perf-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.perf-grid .lbl{font-size:10.5px;text-transform:uppercase;color:var(--slate-500);margin-bottom:4px;}
.perf-grid .val{font-size:17px;font-weight:700;}
.perf-grid .change{font-size:11px;font-weight:600;margin-top:2px;}
.change.up{color:var(--green);}
.change.down{color:#dc2626;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:10.5px;text-transform:uppercase;color:var(--slate-500);padding:10px 12px;border-bottom:1px solid var(--slate-100);}
td{padding:12px;border-bottom:1px solid var(--slate-100);font-size:13px;}
.table-user{display:flex;align-items:center;gap:9px;}
.badge{display:inline-flex;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;}
.badge.active{background:var(--green-bg);color:var(--green);}
.badge.on_leave{background:var(--amber-bg);color:var(--amber);}
.chip-count{background:var(--indigo-50);color:var(--indigo-500);font-weight:700;font-size:11px;padding:3px 9px;border-radius:20px;}
@media (max-width:900px){.grid-2,.perf-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand"><div class="brand-mark">AP</div><div><div class="brand-title">AutoPartFlow</div><div class="brand-sub">Logistics ERP</div></div></div>
        <nav>
            <div class="nav-group">
                <div class="nav-group-label">Main Menu</div>
                <a class="nav-link" href="<?= url('admin/dashboard') ?>">Dashboard</a>
                <a class="nav-link" href="#">Sales</a>
                <a class="nav-link" href="#">Products</a>
            </div>
            <div class="nav-group">
                <div class="nav-group-label">Operations</div>
                <a class="nav-link" href="#">Inventory</a>
                <a class="nav-link" href="<?= url('admin/users') ?>">Customers</a>
                <a class="nav-link active" href="<?= url('admin/employees') ?>">Employees</a>
            </div>
            <div class="nav-group">
                <div class="nav-group-label">Insights</div>
                <a class="nav-link" href="<?= url('admin/reports') ?>">Reports</a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div>
            <div><div class="name"><?= e($_SESSION['full_name'] ?? 'Alex Rivera') ?></div><div class="role">Admin</div></div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            AutoPartFlow
            <div class="search-box">🔍 <input type="text" placeholder="Search employees..."></div>
            <div class="topbar-icons">🔔 📅 <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div></div>
        </header>

        <div class="content">
            <div class="page-head">
                <div>
                    <h1>Employees</h1>
                    <p>Manage team performance, attendance, and assignments.</p>
                </div>
                <div class="head-actions">
                    <button class="btn">⬇ Export</button>
                    <button class="btn btn-primary">+ New Employee</button>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h3 style="margin-top:0;">Today's Attendance</h3>
                    <div style="font-size:28px;font-weight:700;">42 <span style="font-size:14px;font-weight:500;color:var(--slate-500);">/ 45 Present</span></div>
                    <div class="bar-track"><div class="bar-fill" style="width:93%"></div></div>
                    <div class="stat-mini">
                        <div class="box"><div class="num" style="color:var(--amber);">2</div><div class="label">On Leave</div></div>
                        <div class="box"><div class="num" style="color:#dc2626;">1</div><div class="label">Late</div></div>
                    </div>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">Team Performance (Q3)</h3>
                    <div class="perf-grid">
                        <div><div class="lbl">Total Sales Volume</div><div class="val">$1.2M</div><div class="change up">↑ +12% vs Q2</div></div>
                        <div><div class="lbl">Avg Order Fulfillment</div><div class="val">4.2 Hrs</div><div class="change down">↓ -15% vs Q2</div></div>
                        <div><div class="lbl">Customer Satisfaction</div><div class="val">98%</div><div class="change">— No change</div></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Personnel Directory</h3>
                <table>
                    <thead><tr><th>Employee</th><th>Role</th><th>Performance Score</th><th>Total Sales (YTD)</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $emp): $score = min(100, (float) $emp['commission_rate'] * 10); ?>
                        <tr>
                            <td><div class="table-user"><div class="avatar"><?= strtoupper(substr($emp['full_name'],0,1)) ?></div><div><strong><?= e($emp['full_name']) ?></strong><br><span style="color:var(--slate-500);font-size:11.5px;">ID: <?= e($emp['employee_code']) ?></span></div></div></td>
                            <td><?= e($emp['designation']) ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="bar-track" style="width:70px;margin:0;"><div class="bar-fill" style="width:<?= $score ?>%;background:<?= $score>=85?'var(--green)':'var(--indigo-500)' ?>;"></div></div>
                                    <span style="font-size:12px;font-weight:600;"><?= round($score) ?>/100</span>
                                </div>
                            </td>
                            <td>$<?= number_format((float) $emp['base_salary']) ?></td>
                            <td><span class="badge active">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="5">No employees found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>