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
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--bg:#f5f6fb;--green:#16a34a;--green-bg:#e7f8ee;--amber:#d97706;--amber-bg:#fef3e2;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);}
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
.badge{display:inline-flex;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:20px;background:var(--green-bg);color:var(--green);}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;}
.bar-track{height:6px;background:var(--slate-100);border-radius:6px;overflow:hidden;}
.bar-fill{height:100%;border-radius:6px;background:var(--indigo-500);}
.stat-mini{display:flex;gap:24px;margin-top:14px;}
.stat-mini .num{font-size:16px;font-weight:700;}
.stat-mini .label{font-size:12px;color:var(--slate-500);}
@media (max-width:900px){.grid-2{grid-template-columns:1fr;}}
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
            <a class="nav-link active" href="<?= url('admin/employees') ?>">Employees</a>
            <a class="nav-link" href="<?= url('admin/notifications') ?>">Notifications</a>
            <a class="nav-link" href="<?= url('admin/settings') ?>">Settings</a>
        </nav>
    </aside>
    <div class="main">
        <div class="content">
            <div class="page-head">
                <h1>Employees</h1>
                <p>Manage team performance, attendance, and assignments.</p>
            </div>
                   <div class="grid-2">
                <div class="card">
                    <h3 style="margin-top:0;">Today's Attendance</h3>
                    <div style="font-size:30px;font-weight:700;">42 <span style="font-size:15px;font-weight:500;color:var(--slate-500);">/ 45 Present</span></div>
                    <div class="bar-track" style="margin:12px 0;"><div class="bar-fill" style="width:93%"></div></div>
                    <div class="stat-mini">
                        <div><div class="num" style="color:#d97706;">2</div><div class="label">On Leave</div></div>
                        <div><div class="num" style="color:#dc2626;">1</div><div class="label">Late</div></div>
                    </div>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">Team Performance (Q3)</h3>
                    <div class="stat-mini" style="gap:32px;">
                        <div><div class="num">$1.2M</div><div class="label">Total Sales Volume</div></div>
                        <div><div class="num">4.2 Hrs</div><div class="label">Avg Fulfillment</div></div>
                        <div><div class="num">98%</div><div class="label">Satisfaction</div></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <table>
                    <thead><tr><th>Employee</th><th>Designation</th><th>Department</th><th>Hire Date</th><th>Performance</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $emp): ?>
                        <tr>
                            <td><strong><?= e($emp['full_name']) ?></strong><br><span style="color:var(--slate-500);font-size:12px;">ID: <?= e($emp['employee_code']) ?></span></td>
                            <td><?= e($emp['designation']) ?></td>
                            <td><span class="badge"><?= ucfirst($emp['department']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($emp['hire_date'])) ?></td>
                                                        <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="bar-track" style="width:70px;"><div class="bar-fill" style="width:<?= min(100, (float) $emp['commission_rate'] * 10) ?>%"></div></div>
                                    <span style="font-size:12px;font-weight:600;"><?= number_format((float) $emp['commission_rate'], 1) ?>%</span>
                                </div>
                            </td>
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
