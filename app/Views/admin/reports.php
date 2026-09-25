<?php
/** @var array $performance */
$period        = $period ?? 'monthly';
$periodLabel   = $periodLabel ?? '';
$revenue       = (float) ($revenue ?? 0);
$revenueChange = $revenueChange ?? null;
$compareText   = $compareText ?? '';
$chartTitle    = $chartTitle ?? 'Sales';
$chartLabels   = array_values($chartLabels ?? []);
$chartValues   = array_map('floatval', array_values($chartValues ?? []));
$chartMax      = $chartValues ? max($chartValues) : 0;
$periods = ['daily' => 'Daily', 'monthly' => 'Monthly', 'ytd' => 'YTD'];

$short = function (float $v): string {
    if ($v >= 1000000) return number_format($v / 1000000, 1) . 'M';
    if ($v >= 1000)    return number_format($v / 1000, 1) . 'K';
    return number_format($v, 0);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Reports & Analytics') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--green:#16a34a;--green-bg:#e7f8ee;--red:#dc2626;--red-bg:#fdecec;}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:230px;flex-shrink:0;background:linear-gradient(180deg,var(--navy-900),var(--navy-800));color:#cbd5e1;padding:18px 12px;display:flex;flex-direction:column;}
.brand{display:flex;align-items:center;gap:9px;padding:6px 8px 20px;}
.brand-title{color:#fff;font-weight:700;font-size:14px;}
.brand-sub{font-size:10.5px;color:#8590b3;text-transform:uppercase;letter-spacing:.04em;}
.nav-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;color:#b7c0dd;font-size:13px;font-weight:500;margin-bottom:2px;text-decoration:none;}
.nav-link.active{background:var(--indigo-500);color:#fff;}
.sidebar-footer{margin-top:auto;padding-top:12px;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:9px;}
.avatar{width:30px;height:30px;border-radius:50%;background:var(--indigo-500);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:11px;flex-shrink:0;}
.sidebar-footer .name{color:#fff;font-size:12.5px;font-weight:600;}
.sidebar-footer .role{color:#7f8bb0;font-size:11px;}
.main{flex:1;min-width:0;}
.topbar{background:#fff;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;gap:16px;padding:12px 24px;}
.search-box{flex:1;max-width:380px;display:flex;align-items:center;gap:8px;background:var(--slate-100);border-radius:9px;padding:8px 12px;color:var(--slate-500);}
.search-box input{border:none;background:transparent;outline:none;flex:1;font-size:13px;}
.topbar-icons{display:flex;align-items:center;gap:14px;margin-left:auto;color:var(--slate-500);}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;}
.period-label{margin-top:6px !important;font-weight:600;color:var(--indigo-500) !important;}
.segmented{display:inline-flex;background:var(--slate-100);border-radius:9px;padding:3px;}
.segmented a{padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;color:var(--slate-500);text-decoration:none;}
.segmented a:hover{color:var(--slate-900);}
.segmented a.active{background:var(--indigo-500);color:#fff;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px;}
.grid-2{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;}
.stat-icon{width:34px;height:34px;border-radius:9px;background:var(--indigo-50);color:var(--indigo-500);display:flex;align-items:center;justify-content:center;margin-bottom:10px;}
.stat-value{font-size:21px;font-weight:700;}
.stat-label{color:var(--slate-500);font-size:12.5px;margin:2px 0 8px;}
.stat-badge{display:inline-flex;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;}
.stat-badge.up{background:var(--green-bg);color:var(--green);}
.stat-badge.down{background:var(--red-bg);color:var(--red);}
.stat-badge.none{background:var(--slate-100);color:var(--slate-500);}
.bar-row{margin-bottom:12px;}
.bar-row:last-child{margin-bottom:0;}
.bar-label{display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px;}
.bar-label span:last-child{font-weight:700;color:#334155;}
.bar-track{height:6px;background:var(--slate-100);border-radius:6px;overflow:hidden;}
.bar-fill{height:100%;border-radius:6px;background:var(--indigo-500);}
.bars{height:220px;display:flex;align-items:stretch;gap:10px;border-bottom:1px solid var(--slate-100);}
.bar-col{flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;}
.bar-val{font-size:10.5px;color:var(--slate-500);margin-bottom:4px;white-space:nowrap;}
.bar-area{flex:1;width:100%;display:flex;align-items:flex-end;justify-content:center;}
.bar{width:100%;max-width:46px;background:var(--indigo-500);border-radius:6px 6px 0 0;min-height:2px;}
.bar-lbl{font-size:11.5px;color:var(--slate-500);margin-top:6px;}
.chart-note{margin:10px 0 0;font-size:12px;color:var(--slate-500);}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:10.5px;text-transform:uppercase;color:var(--slate-500);padding:10px 12px;border-bottom:1px solid var(--slate-100);}
td{padding:12px;border-bottom:1px solid var(--slate-100);font-size:13px;}
.table-user{display:flex;align-items:center;gap:9px;}
.badge{display:inline-flex;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;}
.badge.excellent{background:var(--green-bg);color:var(--green);}
@media (max-width:1100px){.grid-4{grid-template-columns:repeat(2,1fr);}}
@media (max-width:900px){.grid-2{grid-template-columns:1fr;}}
</style>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
            <div>
                <div class="brand-title">AutoPartFlow</div>
                <div class="brand-sub">Admin Workspace</div>
            </div>
        </div>
        <nav aria-label="Admin navigation">
<a class="nav-link" href="<?= url('admin/dashboard') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8v8H3V3Zm10 0h8v5h-8V3ZM3 13h8v8H3v-8Zm10-3h8v11h-8V10Z"/></svg>Dashboard</a>
<a class="nav-link active" aria-current="page" href="<?= url('admin/reports') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h2v16h15v2H4V3Zm5 8h3v6H9v-6Zm5-5h3v11h-3V6Z"/></svg>Reports</a>
<a class="nav-link" href="<?= url('admin/users') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Z"/></svg>User Management</a>
<a class="nav-link" href="<?= url('admin/employees') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Zm16-7h4v7h-4v-7Z"/></svg>Employees</a>
<a class="nav-link" href="<?= url('admin/notifications') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>Notifications</a>
<a class="nav-link" href="<?= url('admin/settings') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v2H3V5Zm4 6h10v2H7v-2Zm3 6h4v2h-4v-2Z"/></svg>Settings</a>
</nav>
        <div class="sidebar-footer">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div>
            <div>
                <div class="name"><?= e($_SESSION['full_name'] ?? 'Alex Rivera') ?></div>
                <div class="role">Logistics Manager</div>
            </div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="search-box"><input type="text" id="reportSearch" placeholder="Search employees in report..."></div>
            <div class="topbar-icons">
                <a href="<?= url('admin/notifications') ?>" aria-label="Notifications">Notifications</a> <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div>
            </div>
        </header>

        <div class="content">
            <div class="page-head">
                <div>
                    <h1>Reports &amp; Analytics</h1>
                    <p>High-level performance and operations dashboard.</p>
                    <?php if ($periodLabel): ?><p class="period-label">Showing: <?= e($periodLabel) ?></p><?php endif; ?>
                </div>
                <nav class="segmented" aria-label="Report period">
                    <?php foreach ($periods as $key => $label): ?>
                        <a href="?period=<?= $key ?>" class="<?= $period === $key ? 'active' : '' ?>" <?= $period === $key ? 'aria-current="page"' : '' ?>><?= $label ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="grid-4">
                <div class="card">
                    <div class="stat-icon">$</div>
                    <div class="stat-value">Rs. <?= number_format($revenue, 2) ?></div>
                    <div class="stat-label">Gross Revenue</div>
                    <?php if ($revenueChange === null): ?>
                        <span class="stat-badge none">No earlier sales to compare</span>
                    <?php elseif ($revenueChange >= 0): ?>
                        <span class="stat-badge up">↑ <?= number_format($revenueChange, 1) ?>% <?= e($compareText) ?></span>
                    <?php else: ?>
                        <span class="stat-badge down">↓ <?= number_format(abs($revenueChange), 1) ?>% <?= e($compareText) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <div class="stat-icon">%</div>
                    <div class="stat-value">18.2%</div>
                    <div class="stat-label">Net Profit Margin</div>
                    <span class="stat-badge up">↑ 2.1% vs last month</span>
                </div>
                <div class="card">
                    <div class="stat-icon">#</div>
                    <div class="stat-value">3,492</div>
                    <div class="stat-label">Active Orders</div>
                    <span class="stat-badge down">↓ 1.5% vs last month</span>
                </div>
                <div class="card">
                    <div class="stat-icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 11H7v-2h4V6h2v7Z"/></svg></div>
                    <div class="stat-value">2.4h</div>
                    <div class="stat-label">Avg Fulfillment</div>
                    <span class="stat-badge up">↓ 0.3h (improved)</span>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h3 style="margin-top:0;"><?= e($chartTitle) ?></h3>
                    <div class="bars">
                        <?php foreach ($chartValues as $i => $v): $h = $chartMax > 0 ? ($v / $chartMax) * 100 : 0; ?>
                            <div class="bar-col" title="<?= e($chartLabels[$i] ?? '') ?>: Rs. <?= number_format($v, 2) ?>">
                                <div class="bar-val"><?= $short($v) ?></div>
                                <div class="bar-area"><div class="bar" style="height:<?= round($h, 1) ?>%"></div></div>
                                <div class="bar-lbl"><?= e($chartLabels[$i] ?? '') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($chartMax <= 0): ?>
                        <p class="chart-note">No sales recorded in this period yet.</p>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">Category Profitability</h3>
                    <div class="bar-row"><div class="bar-label"><span>Engine Components</span><span>45%</span></div><div class="bar-track"><div class="bar-fill" style="width:45%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Braking Systems</span><span>30%</span></div><div class="bar-track"><div class="bar-fill" style="width:30%"></div></div></div>
                    <div class="bar-row"><div class="bar-label"><span>Electrical</span><span>15%</span></div><div class="bar-track"><div class="bar-fill" style="width:15%;background:var(--slate-300);"></div></div></div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Employee Performance</h3>
                <table>
                    <thead><tr><th>Employee</th><th>Employee ID</th><th>Sales</th><th>Revenue</th><th>Status</th></tr></thead>
                    <tbody id="perfRows">
                    <?php foreach ($performance as $p): ?>
                        <tr>
                            <td><div class="table-user"><div class="avatar"><?= strtoupper(substr($p['full_name'], 0, 1)) ?></div><strong><?= e($p['full_name']) ?></strong></div></td>
                            <td><?= e($p['employee_code']) ?></td>
                            <td><?= (int) $p['total_sales'] ?></td>
                            <td>Rs. <?= number_format((float) $p['total_revenue'], 2) ?></td>
                            <td><span class="badge excellent">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$performance): ?><tr><td colspan="5">No sales data yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('reportSearch').addEventListener('input', e => {
    const q = e.target.value.trim().toLowerCase();
    document.querySelectorAll('#perfRows tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>