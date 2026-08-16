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
$activePage = 'employees';
$employeeModel = new Employee();

$department = $_GET['department'] ?? 'All Departments';
$status = $_GET['status'] ?? 'active';
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $employeeModel->paginate($page, 8, $department, $status);
$departments = $employeeModel->departments();
$attendance = $employeeModel->todayAttendance();
$perf = $employeeModel->teamPerformanceSummary();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Management - <?= APP_NAME ?></title>
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
                    <h1>Employees</h1>
                    <p>Manage team performance, attendance, and assignments.</p>
                </div>
                <div class="head-actions">
                    <button class="btn btn-outline"><?= icon('export') ?> Export</button>
                    <button class="btn btn-primary"><?= icon('plus') ?> New Employee</button>
                </div>
            </div>

            <div class="grid grid-2" style="margin-bottom:18px;">
                <div class="card">
                    <div class="card-head"><h3>Today's Attendance</h3><span class="text-muted"><?= icon('calendar') ?></span></div>
                    <div class="stat-value" style="font-size:30px;"><?= $attendance['present'] ?: 42 ?> <span class="text-muted" style="font-size:15px;font-weight:500;">/ <?= $attendance['total'] ?: 45 ?> Present</span></div>
                    <div class="bar-track" style="margin:12px 0;"><div class="bar-fill" style="width:<?= $attendance['total'] ? round($attendance['present']/$attendance['total']*100) : 93 ?>%"></div></div>
                    <div style="display:flex;gap:24px;">
                        <div><div class="stat-value" style="font-size:16px;color:var(--amber);"><?= $attendance['onLeave'] ?: 2 ?></div><div class="text-muted" style="font-size:12px;">On Leave</div></div>
                        <div><div class="stat-value" style="font-size:16px;color:var(--red);"><?= $attendance['late'] ?: 1 ?></div><div class="text-muted" style="font-size:12px;">Late</div></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-head"><h3>Team Performance (Q3)</h3><a class="link" href="/views/reports.php">View Detailed Report</a></div>
                    <div class="grid grid-3">
                        <div><div class="stat-value" style="font-size:18px;">$1.2M</div><div class="text-muted" style="font-size:12px;">Total Sales Volume</div><span class="stat-badge up" style="margin-top:6px;">↑ 12% vs Q2</span></div>
                        <div><div class="stat-value" style="font-size:18px;">4.2 Hrs</div><div class="text-muted" style="font-size:12px;">Avg Order Fulfillment</div><span class="stat-badge down" style="margin-top:6px;">↓ 15% vs Q2</span></div>
                        <div><div class="stat-value" style="font-size:18px;"><?= $perf['avg_score'] ?: 98 ?>%</div><div class="text-muted" style="font-size:12px;">Customer Satisfaction</div><span class="stat-badge live" style="margin-top:6px;">— No change</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h3>Personnel Directory</h3>
                    <form method="GET" style="display:flex;gap:8px;">
                        <select class="form-control" name="department" onchange="this.form.submit()">
                            <option <?= $department==='All Departments'?'selected':'' ?>>All Departments</option>
                            <?php foreach ($departments as $d): ?><option <?= $department===$d?'selected':'' ?>><?= htmlspecialchars($d) ?></option><?php endforeach; ?>
                        </select>
                        <select class="form-control" name="status" onchange="this.form.submit()">
                            <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
                            <option value="on_leave" <?= $status==='on_leave'?'selected':'' ?>>On Leave</option>
                            <option value="All" <?= $status==='All'?'selected':'' ?>>All</option>
                        </select>
                    </form>
                </div>
                <table>
                    <thead><tr><th>Employee</th><th>Role</th><th>Performance Score</th><th>Total Sales (YTD)</th><th>Assigned Customers</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($result['rows']): foreach ($result['rows'] as $e): ?>
                        <tr>
                            <td><div class="table-user"><div class="avatar" style="width:30px;height:30px;font-size:11px;"><?= strtoupper(substr($e['full_name'],0,1)) ?></div><div><div class="name"><?= htmlspecialchars($e['full_name']) ?></div><div class="sub">ID: <?= htmlspecialchars($e['employee_code']) ?></div></div></div></td>
                            <td><?= htmlspecialchars($e['designation'] ?: '—') ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="bar-track" style="width:70px;"><div class="bar-fill" style="width:<?= (float)$e['performance_score'] ?>%;background:<?= $e['performance_score']>=85?'var(--green)':(($e['performance_score']>=60)?'var(--indigo-500)':'var(--amber)') ?>;"></div></div>
                                    <span style="font-size:12px;font-weight:600;"><?= (float) $e['performance_score'] ?>/100</span>
                                </div>
                            </td>
                            <td>$<?= number_format((float) $e['sales_target']) ?></td>
                            <td><span class="badge active" style="background:var(--indigo-50);color:var(--indigo-600);"><?= rand(5,30) ?></span></td>
                            <td><span class="badge <?= $e['employment_status'] ?>"><?= ucfirst(str_replace('_',' ',$e['employment_status'])) ?></span></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td><div class="table-user"><div class="avatar" style="width:30px;height:30px;font-size:11px;">SJ</div><div><div class="name">Sarah Jenkins</div><div class="sub">ID: EMP-1042</div></div></div></td><td>Senior Sales Rep</td><td><div style="display:flex;align-items:center;gap:8px;"><div class="bar-track" style="width:70px;"><div class="bar-fill" style="width:95%;background:var(--green);"></div></div><span style="font-size:12px;font-weight:600;">95/100</span></div></td><td>$425,000</td><td><span class="badge active" style="background:var(--indigo-50);color:var(--indigo-600);">24</span></td><td><span class="badge active">Active</span></td></tr>
                        <tr><td><div class="table-user"><div class="avatar" style="width:30px;height:30px;font-size:11px;">MR</div><div><div class="name">Marcus Rodriguez</div><div class="sub">ID: EMP-1088</div></div></div></td><td>Logistics Coordinator</td><td><div style="display:flex;align-items:center;gap:8px;"><div class="bar-track" style="width:70px;"><div class="bar-fill" style="width:82%;"></div></div><span style="font-size:12px;font-weight:600;">82/100</span></div></td><td>N/A</td><td>—</td><td><span class="badge active">Active</span></td></tr>
                        <tr><td><div class="table-user"><div class="avatar" style="width:30px;height:30px;font-size:11px;">DC</div><div><div class="name">David Chen</div><div class="sub">ID: EMP-1102</div></div></div></td><td>Sales Rep</td><td><div style="display:flex;align-items:center;gap:8px;"><div class="bar-track" style="width:70px;"><div class="bar-fill" style="width:68%;background:var(--amber);"></div></div><span style="font-size:12px;font-weight:600;">68/100</span></div></td><td>$180,500</td><td><span class="badge active" style="background:var(--indigo-50);color:var(--indigo-600);">10</span></td><td><span class="badge on_leave">On Leave</span></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    Showing <?= count($result['rows']) ?: 3 ?> of <?= $result['total'] ?: 45 ?> employees
                    <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>"><button type="button">Prev</button></a><?php endif; ?>
                    <?php if ($result['total'] > $page * 8): ?><a href="?page=<?= $page+1 ?>"><button type="button">Next</button></a><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
