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
$activePage = 'reports';
$employeeModel = new Employee();

$monthly = $db->select(
    "SELECT DATE_FORMAT(sale_date,'%b') m, SUM(amount) total FROM demo_sales
     GROUP BY MONTH(sale_date) ORDER BY MIN(sale_date) LIMIT 6"
);
$labels = $monthly ? array_column($monthly, 'm') : ['Jan','Feb','Mar','Apr','May','Jun'];
$values = $monthly ? array_map('floatval', array_column($monthly, 'total')) : [58000,64000,49000,72000,81000,93000];

// Employee performance table — reads the same `employees` table Member 3 owns
$topEmployees = $db->select(
    "SELECT u.full_name, e.designation, e.performance_score,
            COUNT(a.attendance_id) orders_processed
     FROM employees e
     JOIN users u ON u.user_id = e.user_id
     LEFT JOIN attendance_logs a ON a.employee_id = e.employee_id
     WHERE e.is_deleted = 0
     GROUP BY e.employee_id
     ORDER BY e.performance_score DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports & Analytics - <?= APP_NAME ?></title>
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
                    <h1>Reports &amp; Analytics</h1>
                    <p>High-level performance and operations dashboard.</p>
                </div>
                <div class="head-actions">
                    <div class="segmented"><button>Daily</button><button class="active">Monthly</button><button>YTD</button></div>
                </div>
            </div>

            <div class="grid grid-4" style="margin-bottom:18px;">
                <div class="card stat-card">
                    <div class="stat-top"><div class="stat-icon"><?= icon('reports') ?></div></div>
                    <div class="stat-value">$1.2M</div>
                    <div class="stat-label">Gross Revenue</div>
                    <span class="stat-badge up" style="align-self:flex-start;">↑ 14.5% vs last month</span>
                </div>
                <div class="card stat-card">
                    <div class="stat-top"><div class="stat-icon"><?= icon('dashboard') ?></div></div>
                    <div class="stat-value">18.2%</div>
                    <div class="stat-label">Net Profit Margin</div>
                    <span class="stat-badge up" style="align-self:flex-start;">↑ 2.1% vs last month</span>
                </div>
                <div class="card stat-card">
                    <div class="stat-top"><div class="stat-icon"><?= icon('sales') ?></div></div>
                    <div class="stat-value">3,492</div>
                    <div class="stat-label">Active Orders</div>
                    <span class="stat-badge down" style="align-self:flex-start;">↓ 1.5% vs last month</span>
                </div>
                <div class="card stat-card">
                    <div class="stat-top"><div class="stat-icon"><?= icon('calendar') ?></div></div>
                    <div class="stat-value">2.4h</div>
                    <div class="stat-label">Avg. Fulfillment</div>
                    <span class="stat-badge up" style="align-self:flex-start;">↓ 0.3h (improved)</span>
                </div>
            </div>

            <div class="grid grid-2" style="margin-bottom:18px;">
                <div class="card">
                    <div class="card-head"><h3>Sales vs Profit (Monthly)</h3><span class="text-muted">⋯</span></div>
                    <div class="chart-box"><canvas id="reportChart" style="width:100%;height:100%;"></canvas></div>
                </div>
                <div class="card">
                    <div class="card-head"><h3>Category Profitability</h3></div>
                    <div class="bar-row"><div class="bar-label"><span>Engine Components</span><span>45%</span></div><div class="bar-track"><div class="bar-fill" style="width:45%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Braking Systems</span><span>30%</span></div><div class="bar-track"><div class="bar-fill" style="width:30%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Electrical</span><span>15%</span></div><div class="bar-track"><div class="bar-fill" style="width:15%;background:var(--slate-300);"></div></div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h3>Employee Performance</h3>
                    <button class="btn btn-outline btn-sm"><?= icon('export') ?> Export</button>
                </div>
                <table>
                    <thead><tr><th>Employee</th><th>Role</th><th>Orders Processed</th><th>Accuracy Rate</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($topEmployees): foreach ($topEmployees as $e):
                        $score = (float) $e['performance_score'];
                        $status = $score >= 95 ? 'excellent' : ($score >= 85 ? 'good' : 'review');
                        $statusLabel = $score >= 95 ? 'Excellent' : ($score >= 85 ? 'Good' : 'Review');
                    ?>
                        <tr>
                            <td><div class="table-user"><div class="avatar" style="width:28px;height:28px;font-size:11px;"><?= strtoupper(substr($e['full_name'],0,1)) ?></div><span class="name"><?= htmlspecialchars($e['full_name']) ?></span></div></td>
                            <td><?= htmlspecialchars($e['designation'] ?: '—') ?></td>
                            <td><?= number_format((int) $e['orders_processed']) ?></td>
                            <td><?= number_format($score, 1) ?>%</td>
                            <td><span class="badge <?= $status ?>"><?= $statusLabel ?></span></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td><div class="table-user"><div class="avatar" style="width:28px;height:28px;font-size:11px;">JS</div><span class="name">John Smith</span></div></td><td>Warehouse Lead</td><td>1,245</td><td>99.8%</td><td><span class="badge excellent">Excellent</span></td></tr>
                        <tr><td><div class="table-user"><div class="avatar" style="width:28px;height:28px;font-size:11px;">AD</div><span class="name">Alice Doe</span></div></td><td>Picker</td><td>982</td><td>98.5%</td><td><span class="badge good">Good</span></td></tr>
                        <tr><td><div class="table-user"><div class="avatar" style="width:28px;height:28px;font-size:11px;">RJ</div><span class="name">Robert Jones</span></div></td><td>Packer</td><td>850</td><td>94.2%</td><td><span class="badge review">Review</span></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="/public/js/charts.js"></script>
<script>
drawLineChart('reportChart', <?= json_encode($labels) ?>, <?= json_encode($values) ?>, {color:'#4f5bd5'});
window.addEventListener('resize', () => drawLineChart('reportChart', <?= json_encode($labels) ?>, <?= json_encode($values) ?>));
</script>
</body>
</html>
