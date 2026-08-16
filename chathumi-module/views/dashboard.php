<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/icons.php';
require_once __DIR__ . '/../models/Employee.php';

$auth = new Auth();
$currentUser = $auth->requireRole(ADMIN_MODULE_ROLES);
$db = Database::getInstance();
$activePage = 'dashboard';

// --- Demo widgets (Sales/Inventory data is owned by Members 1 & 2;
//     these read-only demo tables stand in for their live tables so
//     this module runs standalone during development). ---
$salesByDay = $db->select(
    "SELECT DATE_FORMAT(sale_date,'%a') d, SUM(amount) total FROM demo_sales
     WHERE sale_date >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(sale_date) ORDER BY sale_date"
);
$labels = $salesByDay ? array_column($salesByDay, 'd') : ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$values = $salesByDay ? array_map('floatval', array_column($salesByDay, 'total')) : [4200,5100,3900,6200,7300,8100,6700];

$lowStock = $db->select("SELECT * FROM demo_inventory_alerts WHERE status IN ('critical','low') ORDER BY status LIMIT 6");

$todaysSales = array_sum($values) ? end($values) : 12450;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Owner Dashboard - <?= APP_NAME ?></title>
<link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="main">
        <?php require __DIR__ . '/partials/topbar.php'; ?>

        <div class="content">
            <div class="page-head">
                <div>
                    <h1>Overview</h1>
                    <p>Welcome back. Here's what's happening today.</p>
                </div>
                <div class="head-actions">
                    <div class="segmented">
                        <button>Today</button><button>7D</button><button class="active">30D</button>
                        <button><?= icon('calendar', 'icon') ?> Custom</button>
                    </div>
                    <button class="btn btn-primary"><?= icon('export') ?> Export Report</button>
                </div>
            </div>

            <div class="grid grid-4" style="margin-bottom:18px;">
                <div class="card stat-card">
                    <div class="stat-top">
                        <div class="stat-icon"><?= icon('sales') ?></div>
                        <span class="stat-badge up">↑ 12.5%</span>
                    </div>
                    <div class="stat-value">$<?= number_format($todaysSales) ?></div>
                    <div class="stat-label">Today's Sales</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-top">
                        <div class="stat-icon"><?= icon('reports') ?></div>
                        <span class="stat-badge up">↑ 8.2%</span>
                    </div>
                    <div class="stat-value">$342,000</div>
                    <div class="stat-label">Monthly Revenue</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-top">
                        <div class="stat-icon"><?= icon('dashboard') ?></div>
                        <span class="stat-badge down">↓ 1.2%</span>
                    </div>
                    <div class="stat-value">18.5%</div>
                    <div class="stat-label">Gross Profit Margin</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-top">
                        <div class="stat-icon"><?= icon('inventory') ?></div>
                        <span class="stat-badge live">● Live</span>
                    </div>
                    <div class="stat-value">42</div>
                    <div class="stat-label">Active Orders</div>
                </div>
            </div>

            <div class="grid grid-2" style="margin-bottom:18px;">
                <div class="card">
                    <div class="card-head">
                        <h3>Sales vs Revenue</h3>
                        <span class="text-muted">⋯</span>
                    </div>
                    <div class="chart-box"><canvas id="salesChart" style="width:100%;height:100%;"></canvas></div>
                    <div class="status-dot"><span class="dot"></span> Revenue &nbsp;&nbsp;<span class="dot" style="background:var(--slate-300);"></span> Sales Volume</div>
                </div>
                <div class="card">
                    <div class="card-head"><h3>Top Categories</h3></div>
                    <div class="bar-row"><div class="bar-label"><span>Brake Systems</span><span>42%</span></div><div class="bar-track"><div class="bar-fill" style="width:42%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Engine Components</span><span>28%</span></div><div class="bar-track"><div class="bar-fill" style="width:28%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Suspension</span><span>18%</span></div><div class="bar-track"><div class="bar-fill" style="width:18%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Filters &amp; Fluids</span><span>12%</span></div><div class="bar-track"><div class="bar-fill" style="width:12%;background:var(--slate-300);"></div></div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h3><?= icon('alert-triangle') ?> Low Stock Alerts</h3>
                    <a class="link" href="#">View All</a>
                </div>
                <table>
                    <thead><tr><th>Item Details</th><th>Brand</th><th>Stock</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if ($lowStock): foreach ($lowStock as $item): ?>
                        <tr>
                            <td><div class="name" style="font-weight:600;"><?= htmlspecialchars($item['item_name']) ?></div><div class="sub text-muted" style="font-size:12px;">SKU: <?= htmlspecialchars($item['sku']) ?></div></td>
                            <td><?= htmlspecialchars($item['brand']) ?></td>
                            <td><?= (int) $item['stock_qty'] ?> units</td>
                            <td><span class="badge <?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
                            <td><span class="icon-action"><?= icon('reports') ?></span></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td><div style="font-weight:600;">Ceramic Brake Pads (Front)</div><div class="sub text-muted" style="font-size:12px;">SKU: BP-CER-1042</div></td>
                            <td>Bosch</td><td>4 units</td><td><span class="badge critical">Critical</span></td><td><span class="icon-action"><?= icon('reports') ?></span></td>
                        </tr>
                        <tr>
                            <td><div style="font-weight:600;">Synthetic Motor Oil 5W-30</div><div class="sub text-muted" style="font-size:12px;">SKU: OIL-SYN-5W30</div></td>
                            <td>Mobil 1</td><td>12 units</td><td><span class="badge low">Low</span></td><td><span class="icon-action"><?= icon('reports') ?></span></td>
                        </tr>
                        <tr>
                            <td><div style="font-weight:600;">Air Filter Element</div><div class="sub text-muted" style="font-size:12px;">SKU: AF-STD-998</div></td>
                            <td>K&amp;N</td><td>15 units</td><td><span class="badge low">Low</span></td><td><span class="icon-action"><?= icon('reports') ?></span></td>
                        </tr>
                        <tr>
                            <td><div style="font-weight:600;">Spark Plugs (Platinum)</div><div class="sub text-muted" style="font-size:12px;">SKU: SP-PLT-04</div></td>
                            <td>NGK</td><td>2 units</td><td><span class="badge critical">Critical</span></td><td><span class="icon-action"><?= icon('reports') ?></span></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="/public/js/charts.js"></script>
<script>
drawLineChart('salesChart', <?= json_encode($labels) ?>, <?= json_encode($values) ?>);
window.addEventListener('resize', () => drawLineChart('salesChart', <?= json_encode($labels) ?>, <?= json_encode($values) ?>));
</script>
</body>
</html>
