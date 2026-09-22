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
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:18px;}
.grid-2{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:18px;}
.stat-value{font-size:22px;font-weight:700;}
.stat-label{color:var(--slate-500);font-size:12.5px;}
.stat-badge{display:inline-flex;font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:20px;margin-top:6px;}
.stat-badge.up{background:#e7f8ee;color:#16a34a;}
.stat-badge.down{background:#fdecec;color:#dc2626;}
.bar-row{margin-bottom:12px;}
.bar-row:last-child{margin-bottom:0;}
.bar-label{display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px;}
.bar-label span:last-child{font-weight:700;color:#334155;}
.bar-track{height:6px;background:var(--slate-100);border-radius:6px;overflow:hidden;}
.bar-fill{height:100%;border-radius:6px;background:var(--indigo-500);}
@media (max-width:1100px){.grid-4{grid-template-columns:repeat(2,1fr);}}
@media (max-width:900px){.grid-2{grid-template-columns:1fr;}}

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
            <div class="grid-4">
                <div class="card">
                    <div class="stat-value">$1.2M</div>
                    <div class="stat-label">Gross Revenue</div>
                    <span class="stat-badge up">↑ 14.5% vs last month</span>
                </div>
                <div class="card">
                    <div class="stat-value">18.2%</div>
                    <div class="stat-label">Net Profit Margin</div>
                    <span class="stat-badge up">↑ 2.1% vs last month</span>
                </div>
                <div class="card">
                    <div class="stat-value">3,492</div>
                    <div class="stat-label">Active Orders</div>
                    <span class="stat-badge down">↓ 1.5% vs last month</span>
                </div>
                <div class="card">
                    <div class="stat-value">2.4h</div>
                    <div class="stat-label">Avg Fulfillment</div>
                    <span class="stat-badge up">↓ 0.3h (improved)</span>
                </div>
            </div>
            <div class="grid-2">
                <div class="card">
                    <h3 style="margin-top:0;">Sales vs Profit (Monthly)</h3>
                    <div style="height:220px;"><canvas id="reportChart" style="width:100%;height:100%;"></canvas></div>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">Category Profitability</h3>
                    <div class="bar-row"><div class="bar-label"><span>Engine Components</span><span>45%</span></div><div class="bar-track"><div class="bar-fill" style="width:45%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Braking Systems</span><span>30%</span></div><div class="bar-track"><div class="bar-fill" style="width:30%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Electrical</span><span>15%</span></div><div class="bar-track"><div class="bar-fill" style="width:15%;background:var(--slate-300);"></div></div></div>
                </div>
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
<script src="<?= asset('js/admin-charts.js') ?>"></script>
<script>
drawLineChart('reportChart', ['Jan','Feb','Mar','Apr','May','Jun'], [58000,64000,49000,72000,81000,93000]);
</script>
</html>
