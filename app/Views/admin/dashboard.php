<?php
/** @var array $labels */
/** @var array $values */
/** @var array $lowStock */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Owner Dashboard') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--bg:#f5f6fb;--green:#16a34a;--green-bg:#e7f8ee;--red:#dc2626;--red-bg:#fdecec;--amber:#d97706;--amber-bg:#fef3e2;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:250px;flex-shrink:0;background:linear-gradient(180deg,var(--navy-900),var(--navy-800));color:#cbd5e1;padding:20px 14px;position:sticky;top:0;height:100vh;}
.brand{display:flex;align-items:center;gap:10px;padding:6px 8px 22px;color:#fff;font-weight:700;}
.nav-link{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;color:#b7c0dd;font-size:13.5px;font-weight:500;margin-bottom:2px;}
.nav-link.active{background:var(--indigo-500);color:#fff;}
.main{flex:1;min-width:0;}
.content{padding:26px;}
.page-head h1{font-size:24px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13.5px;}
.grid{display:grid;gap:18px;}
.grid-4{grid-template-columns:repeat(4,1fr);}
.grid-2{grid-template-columns:2fr 1fr;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;}
.stat-value{font-size:22px;font-weight:700;}
.stat-label{color:var(--slate-500);font-size:12.5px;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:11px;text-transform:uppercase;color:var(--slate-500);padding:10px 12px;border-bottom:1px solid var(--slate-100);}
td{padding:13px 12px;border-bottom:1px solid var(--slate-100);font-size:13.5px;}
.badge{display:inline-flex;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:20px;}
.badge.critical{background:var(--red-bg);color:var(--red);}
.badge.low{background:var(--amber-bg);color:var(--amber);}
.chart-box{height:220px;}
.grid-2{display:grid;grid-template-columns:2fr 1fr;gap:18px;}
.bar-row{margin-bottom:12px;}
.bar-row:last-child{margin-bottom:0;}
.bar-label{display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px;}
.bar-label span:last-child{font-weight:700;color:#334155;}
.bar-track{height:6px;background:var(--slate-100);border-radius:6px;overflow:hidden;}
.bar-fill{height:100%;border-radius:6px;background:var(--indigo-500);}
@media (max-width:900px){.grid-2{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">AutoPartFlow</div>
        <nav>
            <a class="nav-link active" href="<?= url('admin/dashboard') ?>">Dashboard</a>
            <a class="nav-link" href="<?= url('admin/reports') ?>">Reports</a>
            <a class="nav-link" href="<?= url('admin/users') ?>">User Management</a>
            <a class="nav-link" href="<?= url('admin/employees') ?>">Employees</a>
            <a class="nav-link" href="<?= url('admin/notifications') ?>">Notifications</a>
            <a class="nav-link" href="<?= url('admin/settings') ?>">Settings</a>
        </nav>
    </aside>
    <div class="main">
        <div class="content">
            <div class="page-head">
                <h1>Overview</h1>
                <p>Welcome back. Here's what's happening today.</p>
            </div>
            <div class="grid grid-4" style="margin:18px 0;">
                <div class="card"><div class="stat-value">$<?= number_format(end($values) ?: 0) ?></div><div class="stat-label">Today's Sales</div></div>
                <div class="card"><div class="stat-value">$342,000</div><div class="stat-label">Monthly Revenue</div></div>
                <div class="card"><div class="stat-value">18.5%</div><div class="stat-label">Gross Profit Margin</div></div>
                <div class="card"><div class="stat-value">42</div><div class="stat-label">Active Orders</div></div>
            </div>
                <div class="grid-2" style="margin:18px 0;">
                <div class="card">
                    <h3 style="margin-top:0;">Sales vs Revenue</h3>
                    <div class="chart-box"><canvas id="salesChart" style="width:100%;height:100%;"></canvas></div>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">Top Categories</h3>
                    <div class="bar-row"><div class="bar-label"><span>Brake Systems</span><span>42%</span></div><div class="bar-track"><div class="bar-fill" style="width:42%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Engine Components</span><span>28%</span></div><div class="bar-track"><div class="bar-fill" style="width:28%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Suspension</span><span>18%</span></div><div class="bar-track"><div class="bar-fill" style="width:18%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Filters &amp; Fluids</span><span>12%</span></div><div class="bar-track"><div class="bar-fill" style="width:12%;background:var(--slate-300);"></div></div></div>
                </div>
            </div>
            </div>
            <div class="card">
                <h3>Low Stock Alerts</h3>
                <table>
                    <thead><tr><th>Item</th><th>Brand</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($lowStock as $item): ?>
                      <tr>
                         <td><?= e($item['name']) ?></td>
                         <td><?= e($item['category']) ?></td>
                         <td><?= (int) $item['quantity_on_hand'] ?> units</td>
                         <td><span class="badge critical">Reorder ≤ <?= (int) $item['reorder_level'] ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (!$lowStock): ?><tr><td colspan="4">No alerts.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
<script src="<?= asset('js/admin-charts.js') ?>"></script>
<script>
drawLineChart('salesChart', <?= json_encode($labels) ?>, <?= json_encode($values) ?>);
window.addEventListener('resize', () => drawLineChart('salesChart', <?= json_encode($labels) ?>, <?= json_encode($values) ?>));
</script>
</html>
