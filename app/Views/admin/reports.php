<?php
/**
 * Admin Reports & Analytics View
 * Supports advanced multi-dimensional filtering, Custom Seasonal Variation analysis,
 * interactive trend & comparison charts, dynamic category profitability, and sales records explorer with line item modal & CSV export.
 *
 * @var array $cards
 * @var array $categoryBreakdown
 * @var array $employeePerformance
 * @var array $records
 * @var array $categories
 * @var array $brands
 * @var array $salesReps
 * @var array $seasons
 * @var array $availableYears
 * @var array $activeFilters
 * @var array|null $seasonalIntel
 * @var array|null $seasonConfig
 */

$period             = $period ?? 'monthly';
$periodLabel        = $periodLabel ?? '';
$compareText        = $compareText ?? '';
$cards              = $cards ?? [];
$chartTitle         = $chartTitle ?? 'Sales Trend';
$chartLabels        = array_values($chartLabels ?? []);
$chartValues        = array_map('floatval', array_values($chartValues ?? []));
$chartComparison    = array_map('floatval', array_values($chartComparison ?? []));
$chartHasComparison = !empty($chartHasComparison) && !empty($chartComparison);
$chartMax           = max(array_merge([0], $chartValues, $chartComparison));

$periods = [
    'daily'      => 'Today',
    'yesterday'  => 'Yesterday',
    'weekly'     => 'This Week',
    'monthly'    => 'This Month',
    'last_month' => 'Last Month',
    'quarterly'  => 'This Quarter',
    'ytd'        => 'YTD',
    'last_year'  => 'Last Year',
    'all'        => 'All History',
    'custom'     => 'Custom Range',
    'seasonal'   => 'Seasonal Variation',
];

$short = function (float $v): string {
    if ($v >= 1000000) return number_format($v / 1000000, 1) . 'M';
    if ($v >= 1000)    return number_format($v / 1000, 1) . 'K';
    return number_format($v, 0);
};

// Build export query strings based on current GET params
$pdfParams = $_GET;
$pdfParams['export'] = 'pdf';
$pdfUrl = url('admin/reports') . '?' . http_build_query($pdfParams);

$excelParams = $_GET;
$excelParams['export'] = 'excel';
$excelUrl = url('admin/reports') . '?' . http_build_query($excelParams);

$csvParams = $_GET;
$csvParams['export'] = 'csv';
$csvUrl = url('admin/reports') . '?' . http_build_query($csvParams);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Reports & Analytics - AutoPartFlow') ?></title>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<style>
:root {
    --navy-900: #002045;
    --navy-800: #101a30;
    --indigo-500: #002045;
    --indigo-600: #1a365d;
    --indigo-50: #f0f3ff;
    --slate-900: #121c2c;
    --slate-700: #334155;
    --slate-500: #64748b;
    --slate-400: #94a3b8;
    --slate-200: #e2e8f0;
    --slate-100: #f1f5f9;
    --slate-50: #f8fafc;
    --radius-lg: 12px;
    --radius-md: 9px;
    --radius-sm: 6px;
    --shadow: 0 1px 3px rgba(15,23,42,.08), 0 1px 2px rgba(15,23,42,.04);
    --shadow-md: 0 4px 6px -1px rgba(15,23,42,.1), 0 2px 4px -2px rgba(15,23,42,.1);
    --green: #146c43;
    --green-bg: #e7f8ee;
    --red: #ba1a1a;
    --red-bg: #fdecec;
    --amber: #875500;
    --amber-bg: #fff8e1;
    --blue: #1e4f91;
    --blue-bg: #eef4ff;
    --purple: #6b21a8;
    --purple-bg: #f3e8ff;
}

* { box-sizing: border-box; }
body { margin: 0; font-family: Inter, -apple-system, Segoe UI, Roboto, sans-serif; background: #f9f9ff; color: var(--slate-900); font-size: 14px; }
.app-shell { display: flex; min-height: 100vh; }
.main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.topbar { background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px; padding: 12px 28px; }
.topbar-icons { display: flex; align-items: center; gap: 16px; margin-left: auto; }
.topbar-icons a { text-decoration: none; color: var(--slate-700); font-size: 13px; font-weight: 500; }
.avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--navy-900); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; text-decoration: none; }
.content { padding: 24px 28px 48px; max-width: 1400px; width: 100%; margin: 0 auto; }

/* Page Head */
.page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
.page-head h1 { font-size: 26px; font-weight: 700; margin: 0 0 4px; color: var(--navy-900); letter-spacing: -0.02em; }
.page-head p { margin: 0; color: var(--slate-500); font-size: 13.5px; }
.period-badge-sub { display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; font-weight: 600; font-size: 12.5px; color: #1e3a5f; background: #eef4ff; padding: 4px 10px; border-radius: 6px; border: 1px solid #d0e0fc; }
.head-actions { display: flex; align-items: center; gap: 10px; }
.btn-action { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all .15s ease; border: 1px solid transparent; min-height: 38px; }
.btn-action--primary { background: var(--navy-900); color: #fff; }
.btn-action--primary:hover { background: #1a365d; }
.btn-action--outline { background: #fff; color: var(--navy-900); border-color: #cbd5e1; }
.btn-action--outline:hover { background: #f8fafc; border-color: #94a3b8; }
.btn-action--pdf { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
.btn-action--pdf:hover { background: #fee2e2; border-color: #f87171; color: #7f1d1d; }
.btn-action--excel { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
.btn-action--excel:hover { background: #dcfce7; border-color: #86efac; color: #14532d; }

/* Segmented Period Tabs */
.period-bar { background: #fff; border-radius: var(--radius-lg); padding: 6px; border: 1px solid #e2e8f0; box-shadow: var(--shadow); margin-bottom: 16px; overflow-x: auto; }
.segmented { display: inline-flex; gap: 4px; flex-wrap: wrap; }
.segmented a { padding: 7px 13px; border-radius: var(--radius-md); font-size: 12.5px; font-weight: 600; color: var(--slate-700); text-decoration: none; transition: all .15s; white-space: nowrap; }
.segmented a:hover { color: var(--navy-900); background: #f1f5f9; }
.segmented a.active { background: var(--navy-900); color: #fff; }
.segmented a.seasonal-tab { background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }
.segmented a.seasonal-tab:hover { background: #e9d5ff; color: #581c87; }
.segmented a.seasonal-tab.active { background: #6b21a8; color: #fff; border-color: #6b21a8; }

/* Filter Console */
.filter-panel { background: #fff; border-radius: var(--radius-lg); border: 1px solid #e2e8f0; box-shadow: var(--shadow); padding: 18px 20px; margin-bottom: 20px; }
.filter-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
.filter-panel-title { font-size: 14px; font-weight: 700; color: var(--slate-900); display: flex; align-items: center; gap: 8px; }
.filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 5px; }
.filter-group label { font-size: 11.5px; font-weight: 700; color: var(--slate-700); text-transform: uppercase; letter-spacing: 0.03em; }
.filter-control { width: 100%; min-height: 38px; padding: 7px 10px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: var(--radius-md); background: #fff; color: var(--slate-900); transition: border-color .15s; outline: none; }
.filter-control:focus { border-color: var(--navy-900); box-shadow: 0 0 0 2px rgba(0,32,69,.12); }

/* Seasonal Variation Deck */
.seasonal-deck { background: linear-gradient(135deg, #fdf4ff 0%, #f3e8ff 100%); border: 1px solid #e9d5ff; border-radius: var(--radius-lg); padding: 18px; margin-bottom: 16px; position: relative; }
.seasonal-deck-badge { display: inline-flex; align-items: center; gap: 6px; background: #6b21a8; color: #fff; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.04em; }
.seasonal-deck h3 { margin: 0 0 4px; font-size: 17px; color: #3b0764; font-weight: 700; }
.seasonal-deck p { margin: 0 0 14px; font-size: 13px; color: #6b21a8; max-width: 800px; }
.seasonal-grid { display: grid; grid-template-columns: 2fr 1fr 1.2fr; gap: 12px; align-items: flex-end; }
.custom-season-inputs { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 10px; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #d8b4fe; }

/* Custom Date Inputs */
.custom-date-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 14px; display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: flex-end; }

/* Active Filter Chips */
.filter-chips { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 16px; }
.chip-label { font-size: 12px; font-weight: 600; color: var(--slate-500); margin-right: 4px; }
.chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #fff; border: 1px solid #cbd5e1; color: var(--slate-700); }
.chip-remove { text-decoration: none; color: #94a3b8; font-weight: bold; margin-left: 2px; font-size: 14px; line-height: 1; }
.chip-remove:hover { color: var(--red); }
.chip--clear { background: transparent; border-color: transparent; color: var(--red); font-size: 12px; text-decoration: underline; cursor: pointer; padding: 4px 6px; }

/* Metric Cards */
.grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
.card { background: #fff; border-radius: var(--radius-lg); box-shadow: var(--shadow); padding: 20px; border: 1px solid #e2e8f0; }
.stat-icon { width: 36px; height: 36px; border-radius: var(--radius-md); background: var(--indigo-50); color: var(--navy-900); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; font-weight: 800; font-size: 13px; border: 1px solid #dbeafe; }
.stat-value { font-size: 23px; font-weight: 800; color: var(--slate-900); letter-spacing: -0.02em; }
.stat-label { color: var(--slate-500); font-size: 12.5px; font-weight: 600; margin: 4px 0 10px; }
.stat-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 20px; }
.stat-badge.up { background: var(--green-bg); color: var(--green); }
.stat-badge.down { background: var(--red-bg); color: var(--red); }
.stat-badge.none { background: var(--slate-100); color: var(--slate-500); }

/* Seasonal Intelligence Card */
.seasonal-intel-box { background: #fff; border-radius: var(--radius-lg); border: 1px solid #d8b4fe; box-shadow: var(--shadow); padding: 20px; margin-bottom: 20px; position: relative; overflow: hidden; }
.seasonal-intel-box::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: #6b21a8; }
.seasonal-intel-head { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.seasonal-intel-title { font-size: 16px; font-weight: 700; color: #3b0764; margin: 0; display: flex; align-items: center; gap: 8px; }
.seasonal-intel-sub { font-size: 13px; color: var(--slate-500); margin: 3px 0 0; }
.seasonal-metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 16px; }
.seasonal-metric-tile { background: #fdf4ff; border: 1px solid #f3e8ff; border-radius: var(--radius-md); padding: 12px 14px; }
.seasonal-metric-val { font-size: 20px; font-weight: 800; color: #6b21a8; margin-top: 2px; }
.seasonal-metric-lbl { font-size: 11.5px; font-weight: 600; color: #7e22ce; text-transform: uppercase; letter-spacing: 0.03em; }
.seasonal-metric-desc { font-size: 11.5px; color: var(--slate-500); margin-top: 4px; }
.seasonal-parts-strip { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; padding-top: 14px; border-top: 1px solid #f3e8ff; }
.seasonal-parts-lbl { font-size: 12px; font-weight: 700; color: #6b21a8; margin-right: 6px; }
.part-pill { display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #d8b4fe; border-radius: var(--radius-sm); padding: 4px 10px; font-size: 12px; color: var(--slate-700); }
.part-pill strong { color: #581c87; font-family: monospace; }

/* Grid 2 Charts */
.grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px; }
.bars-container { height: 230px; display: flex; align-items: stretch; gap: 8px; border-bottom: 1px solid #e2e8f0; padding-top: 20px; }
.bar-col { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; height: 100%; }
.bar-val { font-size: 10px; font-weight: 600; color: var(--slate-500); margin-bottom: 4px; white-space: nowrap; }
.bar-area { flex: 1; width: 100%; display: flex; align-items: flex-end; justify-content: center; gap: 4px; }
.bar { width: 100%; max-width: 32px; background: var(--navy-900); border-radius: 4px 4px 0 0; min-height: 2px; transition: height .2s; }
.bar.bar--comparison { background: #94a3b8; max-width: 24px; }
.bar.bar--seasonal { background: #6b21a8; }
.bar-lbl { font-size: 11px; font-weight: 600; color: var(--slate-500); margin-top: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
.chart-legend { display: flex; gap: 14px; align-items: center; font-size: 12px; font-weight: 600; margin-bottom: 12px; }
.legend-item { display: inline-flex; align-items: center; gap: 6px; color: var(--slate-700); }
.legend-color { width: 12px; height: 12px; border-radius: 3px; }

/* Category Profitability */
.bar-row { margin-bottom: 12px; }
.bar-row:last-child { margin-bottom: 0; }
.bar-label { display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px; }
.bar-label span:first-child { font-weight: 600; color: var(--slate-900); }
.bar-label span:last-child { font-weight: 700; color: var(--navy-900); }
.bar-track { height: 7px; background: var(--slate-100); border-radius: 6px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 6px; background: var(--navy-900); transition: width .3s; }
.bar-fill.seasonal-fill { background: #7e22ce; }

/* Tables */
table { width: 100%; border-collapse: collapse; text-align: left; }
th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--slate-500); padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: var(--slate-900); vertical-align: middle; }
tr:hover td { background: #fcfdff; }
.table-user { display: flex; align-items: center; gap: 10px; }
.badge { display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 20px; white-space: nowrap; }
.badge--paid { background: var(--green-bg); color: var(--green); }
.badge--partial { background: var(--amber-bg); color: var(--amber); }
.badge--unpaid { background: var(--red-bg); color: var(--red); }
.badge--channel-shop { background: var(--blue-bg); color: var(--blue); }
.badge--channel-walk { background: var(--slate-100); color: var(--slate-700); }
.mono { font-family: "JetBrains Mono", Consolas, Menlo, monospace; font-size: 12.5px; font-weight: 600; }
.invoice-link { color: var(--navy-900); text-decoration: none; cursor: pointer; }
.invoice-link:hover { text-decoration: underline; color: #1a365d; }

/* Filtered Records Card */
.records-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.records-title { font-size: 16px; font-weight: 700; margin: 0; color: var(--navy-900); }
.records-counter { font-size: 12.5px; color: var(--slate-500); }
.pagination { display: flex; align-items: center; justify-content: space-between; padding-top: 16px; margin-top: 10px; border-top: 1px solid #e2e8f0; }
.page-btn { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: var(--radius-sm); border: 1px solid #cbd5e1; background: #fff; font-size: 12.5px; font-weight: 600; color: var(--slate-700); text-decoration: none; }
.page-btn:hover:not(.disabled) { background: #f8fafc; color: var(--navy-900); }
.page-btn.disabled { opacity: 0.5; pointer-events: none; }
.btn-sm-action { padding: 4px 9px; font-size: 11.5px; font-weight: 600; border-radius: var(--radius-sm); background: #f1f5f9; color: var(--navy-900); border: 1px solid #cbd5e1; cursor: pointer; text-decoration: none; }
.btn-sm-action:hover { background: #e2e8f0; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(2px); }
.modal-overlay.active { display: flex; }
.modal-card { background: #fff; border-radius: var(--radius-lg); max-width: 720px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-md); border: 1px solid #e2e8f0; }
.modal-head { padding: 18px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
.modal-title { font-size: 17px; font-weight: 700; margin: 0; color: var(--navy-900); }
.modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--slate-500); padding: 4px; line-height: 1; }
.modal-body { padding: 22px; }

@media (max-width: 1100px) {
    .grid-4 { grid-template-columns: repeat(2, 1fr); }
    .seasonal-metrics-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .grid-2 { grid-template-columns: 1fr; }
    .seasonal-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .grid-4 { grid-template-columns: 1fr; }
    .seasonal-metrics-grid { grid-template-columns: 1fr; }
    .content { padding: 16px; }
}
</style>
</head>
<body>
<div class="app-shell">
    <?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

    <div class="main">
        <header class="topbar">
            <div style="font-weight:700;font-size:15px;color:var(--navy-900);">Admin &amp; Business Intelligence</div>
            <div class="topbar-icons">
                <a href="<?= url('admin/notifications') ?>" aria-label="Notifications">Notifications</a>
                <a href="<?= url('profile') ?>" class="avatar" title="Edit Profile — <?= e($_SESSION['full_name'] ?? 'Admin') ?>"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></a>
            </div>
        </header>

        <div class="content">
            <!-- Page Header -->
            <div class="page-head">
                <div>
                    <h1>Reports &amp; Analytics</h1>
                    <p>Financial intelligence, custom Seasonal Variation analytics, and sales records.</p>
                    <?php if ($periodLabel): ?>
                        <div class="period-badge-sub">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Showing: <?= e($periodLabel) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="head-actions">
                    <a href="<?= e($pdfUrl) ?>" class="btn-action btn-action--pdf" title="Download Full Report as PDF Document">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Download PDF
                    </a>
                    <a href="<?= e($excelUrl) ?>" class="btn-action btn-action--excel" title="Download Report as Excel Spreadsheet (.xls)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Download XL
                    </a>
                    <a href="<?= e($csvUrl) ?>" class="btn-action btn-action--outline" title="Download Filtered Transactions as CSV">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Download CSV
                    </a>
                    <button type="button" onclick="window.print()" class="btn-action btn-action--primary" title="Print Current View">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        Print
                    </button>
                </div>
            </div>

            <!-- Segmented Period Switcher -->
            <div class="period-bar">
                <nav class="segmented" aria-label="Report period">
                    <?php foreach ($periods as $key => $label): 
                        $isActive = ($period === $key);
                        $isSeasonalTab = ($key === 'seasonal');
                        $classes = ($isActive ? 'active ' : '') . ($isSeasonalTab ? 'seasonal-tab' : '');
                        // Preserve existing dimensional filters when switching periods
                        $pParams = $_GET;
                        $pParams['period'] = $key;
                        $tabUrl = url('admin/reports') . '?' . http_build_query($pParams);
                    ?>
                        <a href="<?= e($tabUrl) ?>" class="<?= trim($classes) ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
                            <?php if ($isSeasonalTab): ?>
                                🍂 <?= $label ?>
                            <?php else: ?>
                                <?= $label ?>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Comprehensive Filter Console Form -->
            <form id="filterForm" method="GET" action="<?= url('admin/reports') ?>">
                <input type="hidden" name="period" value="<?= e($period) ?>">

                <!-- Custom Seasonal Variation Deck (visible when period === 'seasonal') -->
                <?php if ($period === 'seasonal'): ?>
                    <div class="seasonal-deck">
                        <div class="seasonal-deck-badge">🍂 Seasonal Variation Dimension</div>
                        <h3>Automotive Seasonal Cycle &amp; Weather-Demand Analysis</h3>
                        <p>Analyze how weather variations, monsoons, summer heatwaves, and seasonal travel surges alter parts replacement velocity and category profitability.</p>
                        
                        <div class="seasonal-grid">
                            <div class="filter-group">
                                <label for="season_preset">Seasonal Variation Model</label>
                                <select name="season_preset" id="season_preset" class="filter-control" onchange="toggleCustomSeason(this.value)">
                                    <?php foreach ($seasons as $sKey => $sDef): ?>
                                        <option value="<?= e($sKey) ?>" <?= ($seasonPreset === $sKey) ? 'selected' : '' ?>>
                                            <?= e($sDef['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="custom" <?= ($seasonPreset === 'custom') ? 'selected' : '' ?>>
                                        ⚙️ Custom Seasonal Window (User Defined)
                                    </option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="season_year">Seasonal Cycle Year</label>
                                <select name="season_year" id="season_year" class="filter-control">
                                    <?php foreach ($availableYears as $y): ?>
                                        <option value="<?= $y ?>" <?= ((string)$seasonYear === (string)$y) ? 'selected' : '' ?>>
                                            Year <?= $y ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="all" <?= ($seasonYear === 'all') ? 'selected' : '' ?>>
                                        All Recorded Years (Multi-Year Season)
                                    </option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="compare_baseline">Comparison Baseline</label>
                                <select name="compare_baseline" id="compare_baseline" class="filter-control">
                                    <option value="prev_season" <?= ($seasonBaseline === 'prev_season') ? 'selected' : '' ?>>vs Same Season in Prior Year</option>
                                    <option value="annual_avg" <?= ($seasonBaseline === 'annual_avg') ? 'selected' : '' ?>>vs Annual Off-Season Monthly Avg</option>
                                    <option value="prev_period" <?= ($seasonBaseline === 'prev_period') ? 'selected' : '' ?>>vs Preceding Period</option>
                                </select>
                            </div>
                        </div>

                        <!-- Custom Season definition inputs -->
                        <div id="customSeasonFields" class="custom-season-inputs" style="<?= ($seasonPreset === 'custom') ? '' : 'display:none;' ?>">
                            <div class="filter-group">
                                <label for="season_title">Custom Season Name</label>
                                <input type="text" name="season_title" id="season_title" class="filter-control" placeholder="e.g. Commercial Fleet Harvest Run" value="<?= e($_GET['season_title'] ?? '') ?>">
                            </div>
                            <div class="filter-group">
                                <label for="season_start_month">Start Month</label>
                                <select name="season_start_month" id="season_start_month" class="filter-control">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= ((int)($_GET['season_start_month'] ?? 5) === $m) ? 'selected' : '' ?>>
                                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="season_end_month">End Month</label>
                                <select name="season_end_month" id="season_end_month" class="filter-control">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= ((int)($_GET['season_end_month'] ?? 9) === $m) ? 'selected' : '' ?>>
                                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Custom Date Range Picker (visible when period === 'custom') -->
                <?php if ($period === 'custom'): ?>
                    <div class="custom-date-box">
                        <div class="filter-group">
                            <label for="date_from">Start Date</label>
                            <input type="date" name="date_from" id="date_from" class="filter-control" value="<?= e($from ?? date('Y-m-d', strtotime('-30 days'))) ?>">
                        </div>
                        <div class="filter-group">
                            <label for="date_to">End Date</label>
                            <input type="date" name="date_to" id="date_to" class="filter-control" value="<?= e($to ?? date('Y-m-d')) ?>">
                        </div>
                        <div>
                            <button type="submit" class="btn-action btn-action--primary" style="min-height:38px;">Set Date Window</button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Multi-Criteria Dimensional Filter Bar -->
                <div class="filter-panel">
                    <div class="filter-panel-header">
                        <div class="filter-panel-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                            Filter Sales &amp; Records Criteria
                        </div>
                        <div style="font-size:12px;color:var(--slate-500);">Combine multiple categories, brands, sales reps, and payment methods</div>
                    </div>

                    <div class="filter-grid">
                        <!-- Category -->
                        <div class="filter-group">
                            <label for="category_id">Part Category</label>
                            <select name="category_id" id="category_id" class="filter-control">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($activeFilters['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="filter-group">
                            <label for="brand_id">Brand</label>
                            <select name="brand_id" id="brand_id" class="filter-control">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= ($activeFilters['brand_id'] === (int)$b['id']) ? 'selected' : '' ?>>
                                        <?= e($b['name']) ?> (<?= e($b['country']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sales Representative -->
                        <div class="filter-group">
                            <label for="sales_rep_id">Sales Representative</label>
                            <select name="sales_rep_id" id="sales_rep_id" class="filter-control">
                                <option value="">All Staff</option>
                                <?php foreach ($salesReps as $rep): ?>
                                    <option value="<?= $rep['id'] ?>" <?= ($activeFilters['sales_rep_id'] === (int)$rep['id']) ? 'selected' : '' ?>>
                                        <?= e($rep['full_name']) ?> (<?= e($rep['employee_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Customer Channel -->
                        <div class="filter-group">
                            <label for="customer_type">Customer Channel</label>
                            <select name="customer_type" id="customer_type" class="filter-control">
                                <option value="">All Channels</option>
                                <option value="shop" <?= ($activeFilters['customer_type'] === 'shop') ? 'selected' : '' ?>>Wholesale / Garage Shop</option>
                                <option value="walking" <?= ($activeFilters['customer_type'] === 'walking') ? 'selected' : '' ?>>Retail Walk-In</option>
                            </select>
                        </div>

                        <!-- Payment Method -->
                        <div class="filter-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="filter-control">
                                <option value="">All Methods</option>
                                <option value="cash" <?= ($activeFilters['payment_method'] === 'cash') ? 'selected' : '' ?>>Cash</option>
                                <option value="card" <?= ($activeFilters['payment_method'] === 'card') ? 'selected' : '' ?>>Card</option>
                                <option value="bank_transfer" <?= ($activeFilters['payment_method'] === 'bank_transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="credit" <?= ($activeFilters['payment_method'] === 'credit') ? 'selected' : '' ?>>Credit</option>
                            </select>
                        </div>

                        <!-- Payment Status -->
                        <div class="filter-group">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="filter-control">
                                <option value="">All Statuses</option>
                                <option value="paid" <?= ($activeFilters['payment_status'] === 'paid') ? 'selected' : '' ?>>Paid</option>
                                <option value="partial" <?= ($activeFilters['payment_status'] === 'partial') ? 'selected' : '' ?>>Partial</option>
                                <option value="unpaid" <?= ($activeFilters['payment_status'] === 'unpaid') ? 'selected' : '' ?>>Unpaid / Credit</option>
                            </select>
                        </div>

                        <!-- Search Box -->
                        <div class="filter-group" style="grid-column: span 2;">
                            <label for="q">Search Records</label>
                            <input type="text" name="q" id="q" class="filter-control" placeholder="Search Invoice #, customer name, phone, part title..." value="<?= e($activeFilters['search']) ?>">
                        </div>

                        <!-- Submit / Reset Actions -->
                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn-action btn-action--primary" style="flex:1;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Apply Filters
                            </button>
                            <a href="<?= url('admin/reports?period=' . urlencode($period)) ?>" class="btn-action btn-action--outline" title="Reset all dimension filters">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Active Filter Chips / Tags Display -->
            <?php
            $hasActiveFilters = false;
            $chipList = [];
            if ($activeFilters['category_id']) {
                $cName = 'Category ID ' . $activeFilters['category_id'];
                foreach ($categories as $c) if ($c['id'] == $activeFilters['category_id']) $cName = $c['name'];
                $chipList[] = ['label' => 'Category: ' . $cName, 'param' => 'category_id'];
                $hasActiveFilters = true;
            }
            if ($activeFilters['brand_id']) {
                $bName = 'Brand ID ' . $activeFilters['brand_id'];
                foreach ($brands as $b) if ($b['id'] == $activeFilters['brand_id']) $bName = $b['name'];
                $chipList[] = ['label' => 'Brand: ' . $bName, 'param' => 'brand_id'];
                $hasActiveFilters = true;
            }
            if ($activeFilters['sales_rep_id']) {
                $rName = 'Rep ID ' . $activeFilters['sales_rep_id'];
                foreach ($salesReps as $r) if ($r['id'] == $activeFilters['sales_rep_id']) $rName = $r['full_name'];
                $chipList[] = ['label' => 'Rep: ' . $rName, 'param' => 'sales_rep_id'];
                $hasActiveFilters = true;
            }
            if ($activeFilters['customer_type']) {
                $chipList[] = ['label' => 'Channel: ' . ($activeFilters['customer_type'] === 'shop' ? 'Wholesale' : 'Retail'), 'param' => 'customer_type'];
                $hasActiveFilters = true;
            }
            if ($activeFilters['payment_method']) {
                $chipList[] = ['label' => 'Payment: ' . ucfirst($activeFilters['payment_method']), 'param' => 'payment_method'];
                $hasActiveFilters = true;
            }
            if ($activeFilters['payment_status']) {
                $chipList[] = ['label' => 'Status: ' . ucfirst($activeFilters['payment_status']), 'param' => 'payment_status'];
                $hasActiveFilters = true;
            }
            if ($activeFilters['search']) {
                $chipList[] = ['label' => 'Query: "' . $activeFilters['search'] . '"', 'param' => 'q'];
                $hasActiveFilters = true;
            }
            ?>
            <?php if ($hasActiveFilters): ?>
                <div class="filter-chips">
                    <span class="chip-label">Active Filters:</span>
                    <?php foreach ($chipList as $cp): 
                        $removeParams = $_GET;
                        unset($removeParams[$cp['param']]);
                        $removeUrl = url('admin/reports') . '?' . http_build_query($removeParams);
                    ?>
                        <span class="chip">
                            <?= e($cp['label']) ?>
                            <a href="<?= e($removeUrl) ?>" class="chip-remove" title="Remove filter">&times;</a>
                        </span>
                    <?php endforeach; ?>
                    <a href="<?= url('admin/reports?period=' . urlencode($period)) ?>" class="chip--clear">Clear All Filters</a>
                </div>
            <?php endif; ?>

            <!-- 4 Metric KPI Cards -->
            <div class="grid-4">
                <?php foreach ($cards as $card):
                    $chg = $card['change'];
                    if ($chg === null) {
                        $cls = 'none';
                    } else {
                        $isGood = ($chg >= 0) === $card['goodWhenUp'];
                        $cls = $isGood ? 'up' : 'down';
                    }
                ?>
                    <div class="card">
                        <div class="stat-icon"><?= e($card['icon']) ?></div>
                        <div class="stat-value"><?= e($card['value']) ?></div>
                        <div class="stat-label"><?= e($card['label']) ?></div>
                        <?php if ($chg === null): ?>
                            <span class="stat-badge none">Baseline established</span>
                        <?php else: ?>
                            <span class="stat-badge <?= $cls ?>">
                                <?= $chg >= 0 ? '↑' : '↓' ?> <?= number_format(abs($chg), 1) ?>% <?= e($compareText) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Featured Seasonal Variation Intelligence Deck (rendered when period === 'seasonal') -->
            <?php if ($period === 'seasonal' && !empty($seasonalIntel)): ?>
                <div class="seasonal-intel-box">
                    <div class="seasonal-intel-head">
                        <div>
                            <h3 class="seasonal-intel-title">
                                <span>🍂</span>
                                Seasonal Variation Intelligence: <?= e($seasonalIntel['season_name']) ?> (<?= e($seasonalIntel['season_year']) ?>)
                            </h3>
                            <p class="seasonal-intel-sub"><?= e($seasonalIntel['description']) ?></p>
                        </div>
                        <?php if (!empty($seasonalIntel['focus_categories'])): ?>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <span style="font-size:11px;font-weight:700;color:#6b21a8;text-transform:uppercase;">Seasonal Focus:</span>
                                <?php foreach ($seasonalIntel['focus_categories'] as $fc): ?>
                                    <span class="part-pill"><strong><?= e($fc) ?></strong></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="seasonal-metrics-grid">
                        <div class="seasonal-metric-tile">
                            <div class="seasonal-metric-lbl">Seasonal Lift Index</div>
                            <div class="seasonal-metric-val">
                                <?php if ($seasonalIntel['seasonal_lift_pct'] !== null): ?>
                                    <?= ($seasonalIntel['seasonal_lift_pct'] >= 0 ? '+' : '') . number_format($seasonalIntel['seasonal_lift_pct'], 1) ?>%
                                <?php else: ?>
                                    0.0%
                                <?php endif; ?>
                            </div>
                            <div class="seasonal-metric-desc">Monthly revenue vs off-season baseline</div>
                        </div>

                        <div class="seasonal-metric-tile">
                            <div class="seasonal-metric-lbl">Annual Revenue Share</div>
                            <div class="seasonal-metric-val"><?= number_format($seasonalIntel['annual_share_pct'], 1) ?>%</div>
                            <div class="seasonal-metric-desc">Contribution to full year revenue</div>
                        </div>

                        <div class="seasonal-metric-tile">
                            <div class="seasonal-metric-lbl">Top Surging Category</div>
                            <div class="seasonal-metric-val" style="font-size:17px;"><?= e($seasonalIntel['top_surging_category']) ?></div>
                            <div class="seasonal-metric-desc">Rs. <?= number_format($seasonalIntel['top_category_revenue'], 2) ?> in this season</div>
                        </div>

                        <div class="seasonal-metric-tile">
                            <div class="seasonal-metric-lbl">Season-over-Season Growth</div>
                            <div class="seasonal-metric-val">
                                <?php if ($seasonalIntel['sos_growth_pct'] !== null): ?>
                                    <?= ($seasonalIntel['sos_growth_pct'] >= 0 ? '+' : '') . number_format($seasonalIntel['sos_growth_pct'], 1) ?>%
                                <?php else: ?>
                                    New cycle
                                <?php endif; ?>
                            </div>
                            <div class="seasonal-metric-desc">Growth vs prior year's identical season</div>
                        </div>
                    </div>

                    <!-- Top moving parts during this seasonal window -->
                    <?php if (!empty($seasonalIntel['top_parts'])): ?>
                        <div class="seasonal-parts-strip">
                            <span class="seasonal-parts-lbl">Peak Seasonal Velocity Parts:</span>
                            <?php foreach ($seasonalIntel['top_parts'] as $tp): ?>
                                <span class="part-pill" title="<?= e($tp['name']) ?>: <?= (int)$tp['total_qty'] ?> units, Rs. <?= number_format((float)$tp['revenue'], 2) ?>">
                                    <strong><?= e($tp['product_code']) ?></strong> <?= e($tp['name']) ?> (<?= (int)$tp['total_qty'] ?> sold)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Chart & Category Breakdown Grid -->
            <div class="grid-2">
                <!-- Trend & Comparison Chart -->
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <h3 style="margin:0;font-size:16px;color:var(--navy-900);"><?= e($chartTitle) ?></h3>
                        <?php if ($chartHasComparison): ?>
                            <div class="chart-legend">
                                <span class="legend-item"><span class="legend-color" style="background:#6b21a8;"></span> Season <?= e($seasonYear) ?></span>
                                <span class="legend-item"><span class="legend-color" style="background:#94a3b8;"></span> Season <?= (int)$seasonYear - 1 ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bars-container">
                        <?php foreach ($chartValues as $i => $v): 
                            $h = $chartMax > 0 ? ($v / $chartMax) * 100 : 0;
                            $compV = $chartComparison[$i] ?? 0;
                            $compH = $chartMax > 0 ? ($compV / $chartMax) * 100 : 0;
                            $barCls = ($period === 'seasonal') ? 'bar--seasonal' : '';
                        ?>
                            <div class="bar-col" title="<?= e($chartLabels[$i] ?? '') ?>: Rs. <?= number_format($v, 2) ?><?= $chartHasComparison ? ' (Prior: Rs. ' . number_format($compV, 2) . ')' : '' ?>">
                                <div class="bar-val"><?= $short($v) ?></div>
                                <div class="bar-area">
                                    <?php if ($chartHasComparison): ?>
                                        <div class="bar bar--comparison" style="height:<?= round($compH, 1) ?>%" title="Prior Season: Rs. <?= number_format($compV, 2) ?>"></div>
                                    <?php endif; ?>
                                    <div class="bar <?= $barCls ?>" style="height:<?= round($h, 1) ?>%" title="Current: Rs. <?= number_format($v, 2) ?>"></div>
                                </div>
                                <div class="bar-lbl"><?= e($chartLabels[$i] ?? '') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($chartMax <= 0): ?>
                        <p style="margin:10px 0 0;font-size:12px;color:var(--slate-500);text-align:center;">No sales recorded matching current filter window.</p>
                    <?php endif; ?>
                </div>

                <!-- Dynamic Category Profitability & Volume -->
                <div class="card">
                    <h3 style="margin-top:0;font-size:16px;color:var(--navy-900);">Category Revenue Breakdown</h3>
                    <?php if (!empty($categoryBreakdown)): ?>
                        <?php foreach (array_slice($categoryBreakdown, 0, 6) as $cat): ?>
                            <div class="bar-row">
                                <div class="bar-label">
                                    <span><?= e($cat['name']) ?> (<?= (int)$cat['total_units'] ?> units)</span>
                                    <span>Rs. <?= number_format((float)$cat['revenue'], 2) ?> (<?= number_format((float)$cat['pct'], 1) ?>%)</span>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill <?= ($period === 'seasonal') ? 'seasonal-fill' : '' ?>" style="width:<?= round($cat['pct'], 1) ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size:12.5px;color:var(--slate-500);margin-top:20px;">No category transaction data in this period.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Employee Performance Ranking Table -->
            <div class="card" style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h3 style="margin:0;font-size:16px;color:var(--navy-900);">Sales Representative Performance</h3>
                    <div style="font-size:12px;color:var(--slate-500);">Filtered for current date &amp; criteria window</div>
                </div>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Sales Representative</th>
                                <th>Employee Code</th>
                                <th>Designation</th>
                                <th>Transactions</th>
                                <th>Revenue Generated</th>
                                <th>Commission Earned</th>
                            </tr>
                        </thead>
                        <tbody id="perfRows">
                            <?php foreach ($employeePerformance as $p): ?>
                                <tr>
                                    <td>
                                        <div class="table-user">
                                            <div class="avatar" style="width:28px;height:28px;font-size:11px;"><?= strtoupper(substr($p['full_name'], 0, 1)) ?></div>
                                            <strong><?= e($p['full_name']) ?></strong>
                                        </div>
                                    </td>
                                    <td><span class="mono"><?= e($p['employee_code']) ?></span></td>
                                    <td><?= e($p['designation'] ?? 'Representative') ?></td>
                                    <td><strong><?= (int) $p['total_sales'] ?></strong></td>
                                    <td style="font-weight:700;color:var(--navy-900);">Rs. <?= number_format((float) $p['total_revenue'], 2) ?></td>
                                    <td style="color:var(--green);font-weight:600;">Rs. <?= number_format((float) $p['commission_earned'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$employeePerformance): ?>
                                <tr><td colspan="6" style="text-align:center;color:var(--slate-500);padding:24px;">No employee sales recorded in this period.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Filtered Sales & Records Explorer -->
            <div class="card" id="salesRecordsSection">
                <div class="records-head">
                    <div>
                        <h3 class="records-title">Sales Records &amp; Filtered Transactions</h3>
                        <div class="records-counter">
                            Showing <?= count($records) ?> of <?= $totalRecords ?> total matching transactions (Page <?= $page ?> of <?= $totalPages ?>)
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <span style="font-size:12px;font-weight:600;color:var(--slate-500);margin-right:2px;">Export:</span>
                        <a href="<?= e($pdfUrl) ?>" class="btn-action btn-action--pdf" style="min-height:32px;padding:5px 11px;font-size:12px;" title="Export filtered records to PDF">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            PDF
                        </a>
                        <a href="<?= e($excelUrl) ?>" class="btn-action btn-action--excel" style="min-height:32px;padding:5px 11px;font-size:12px;" title="Export filtered records to Excel (XL)">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                            Excel (XL)
                        </a>
                        <a href="<?= e($csvUrl) ?>" class="btn-action btn-action--outline" style="min-height:32px;padding:5px 11px;font-size:12px;" title="Export filtered records to CSV">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            CSV
                        </a>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date &amp; Time</th>
                                <th>Customer &amp; Channel</th>
                                <th>Handled By</th>
                                <th>Items Summary</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th style="text-align:right;">Amount (Rs.)</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): 
                                $payStatus = $r['payment_status'] ?? 'paid';
                                $statusBadgeClass = match($payStatus) {
                                    'paid' => 'badge--paid',
                                    'partial' => 'badge--partial',
                                    'unpaid' => 'badge--unpaid',
                                    default => 'badge--paid',
                                };
                                $channelBadgeClass = ($r['customer_type'] === 'shop') ? 'badge--channel-shop' : 'badge--channel-walk';
                            ?>
                                <tr>
                                    <td>
                                        <a href="javascript:void(0)" onclick="openSaleModal(<?= (int)$r['id'] ?>)" class="mono invoice-link" title="Click to view line items">
                                            <?= e($r['invoice_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('d M Y, H:i', strtotime($r['sale_date'])) ?></td>
                                    <td>
                                        <div>
                                            <strong><?= e($r['customer_name']) ?></strong>
                                            <span class="badge <?= $channelBadgeClass ?>" style="margin-left:4px;font-size:10px;">
                                                <?= ($r['customer_type'] === 'shop') ? 'Wholesale' : 'Walk-in' ?>
                                            </span>
                                        </div>
                                        <div style="font-size:11.5px;color:var(--slate-500);"><?= e($r['customer_phone'] ?? 'No contact phone') ?></div>
                                    </td>
                                    <td><?= e($r['sales_rep_name'] ?? 'Direct System') ?></td>
                                    <td style="max-width:260px;">
                                        <div style="font-weight:600;font-size:12px;"><?= (int)$r['item_count'] ?> part line(s)</div>
                                        <div style="font-size:11.5px;color:var(--slate-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= e($r['items_summary'] ?? '') ?>">
                                            <?= e($r['items_summary'] ?: 'No items description') ?>
                                        </div>
                                    </td>
                                    <td><span style="font-size:12px;text-transform:capitalize;font-weight:600;"><?= e(str_replace('_', ' ', $r['payment_method'])) ?></span></td>
                                    <td><span class="badge <?= $statusBadgeClass ?>"><?= strtoupper(e($r['payment_status'])) ?></span></td>
                                    <td style="text-align:right;font-weight:700;font-size:13.5px;color:var(--navy-900);">
                                        Rs. <?= number_format((float)$r['total_amount'], 2) ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" class="btn-sm-action" onclick="openSaleModal(<?= (int)$r['id'] ?>)">View Items</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="9" style="text-align:center;padding:36px;color:var(--slate-500);">
                                        <div style="font-size:24px;margin-bottom:8px;">🔍</div>
                                        <strong>No sales records found matching the active criteria.</strong>
                                        <div style="font-size:12px;margin-top:4px;">Try loosening filters, changing the date range, or selecting another season.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <div>
                            <?php
                            $prevPageParams = $_GET;
                            $prevPageParams['page'] = max(1, $page - 1);
                            $prevUrl = url('admin/reports') . '?' . http_build_query($prevPageParams) . '#salesRecordsSection';
                            ?>
                            <a href="<?= e($prevUrl) ?>" class="page-btn <?= ($page <= 1) ? 'disabled' : '' ?>">← Previous</a>
                        </div>
                        <div style="font-size:12.5px;color:var(--slate-700);font-weight:600;">
                            Page <?= $page ?> of <?= $totalPages ?>
                        </div>
                        <div>
                            <?php
                            $nextPageParams = $_GET;
                            $nextPageParams['page'] = min($totalPages, $page + 1);
                            $nextUrl = url('admin/reports') . '?' . http_build_query($nextPageParams) . '#salesRecordsSection';
                            ?>
                            <a href="<?= e($nextUrl) ?>" class="page-btn <?= ($page >= $totalPages) ? 'disabled' : '' ?>">Next →</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Sale Details & Line Items Modal -->
<div id="saleDetailsModal" class="modal-overlay" onclick="closeModalOnOverlay(event)">
    <div class="modal-card">
        <div class="modal-head">
            <h3 class="modal-title" id="modalInvoiceTitle">Sale Record Details</h3>
            <button type="button" class="modal-close" onclick="closeSaleModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center;padding:30px;color:var(--slate-500);">Loading line items breakdown...</div>
        </div>
    </div>
</div>

<script>
function toggleCustomSeason(val) {
    const el = document.getElementById('customSeasonFields');
    if (el) {
        el.style.display = (val === 'custom') ? 'grid' : 'none';
    }
}

async function openSaleModal(saleId) {
    const modal = document.getElementById('saleDetailsModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalInvoiceTitle');
    
    modal.classList.add('active');
    modalBody.innerHTML = '<div style="text-align:center;padding:30px;color:var(--slate-500);">Loading line items breakdown...</div>';

    try {
        const res = await fetch('<?= url('admin/reports/sale-items') ?>?id=' + encodeURIComponent(saleId));
        const data = await res.json();
        if (!data.ok || !data.sale) {
            modalBody.innerHTML = '<div style="color:var(--red);padding:20px;">Could not load sale record. Please try again.</div>';
            return;
        }

        const s = data.sale;
        modalTitle.textContent = 'Invoice #' + s.invoice_number;

        let itemsHtml = '';
        (s.items || []).forEach(it => {
            const price = parseFloat(it.unit_price || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
            const total = parseFloat(it.line_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
            itemsHtml += `
                <tr>
                    <td><strong class="mono">${escapeHtml(it.product_code || '')}</strong></td>
                    <td>
                        <strong>${escapeHtml(it.product_name || '')}</strong>
                        <div style="font-size:11.5px;color:var(--slate-500);">${escapeHtml(it.category_name || '')} · ${escapeHtml(it.brand_name || '')}</div>
                    </td>
                    <td style="text-align:right;">Rs. ${price}</td>
                    <td style="text-align:center;font-weight:700;">${it.quantity}</td>
                    <td style="text-align:right;font-weight:700;color:var(--navy-900);">Rs. ${total}</td>
                </tr>
            `;
        });

        const subtotal = parseFloat(s.subtotal || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
        const discount = parseFloat(s.discount_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
        const tax = parseFloat(s.tax_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
        const total = parseFloat(s.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
        const paid = parseFloat(s.amount_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

        modalBody.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;background:#f8fafc;padding:14px;border-radius:8px;margin-bottom:16px;font-size:13px;">
                <div>
                    <div><strong>Customer:</strong> ${escapeHtml(s.customer_name || 'Walk-in')}</div>
                    <div style="color:var(--slate-500);font-size:12px;">Code: ${escapeHtml(s.customer_code || 'N/A')} · Phone: ${escapeHtml(s.customer_phone || 'N/A')}</div>
                    <div style="margin-top:4px;"><strong>Sales Rep:</strong> ${escapeHtml(s.sales_rep_name || 'Direct POS')}</div>
                </div>
                <div>
                    <div><strong>Date:</strong> ${escapeHtml(s.sale_date)}</div>
                    <div><strong>Payment Method:</strong> ${escapeHtml(s.payment_method.toUpperCase())}</div>
                    <div><strong>Payment Status:</strong> <span class="badge ${s.payment_status === 'paid' ? 'badge--paid' : 'badge--unpaid'}">${escapeHtml(s.payment_status.toUpperCase())}</span></div>
                </div>
            </div>

            <h4 style="margin:0 0 10px;font-size:14px;color:var(--navy-900);">Purchased Items</h4>
            <div style="overflow-x:auto;margin-bottom:16px;">
                <table>
                    <thead>
                        <tr>
                            <th>Part Code</th>
                            <th>Item Description</th>
                            <th style="text-align:right;">Unit Price</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
            </div>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;display:flex;flex-direction:column;gap:6px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;"><span>Subtotal:</span><span>Rs. ${subtotal}</span></div>
                <div style="display:flex;justify-content:space-between;color:var(--red);"><span>Discounts:</span><span>- Rs. ${discount}</span></div>
                <div style="display:flex;justify-content:space-between;"><span>Tax (VAT):</span><span>Rs. ${tax}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;border-top:1px solid #cbd5e1;padding-top:6px;color:var(--navy-900);">
                    <span>Total Amount:</span><span>Rs. ${total}</span>
                </div>
                <div style="display:flex;justify-content:space-between;color:var(--green);font-weight:600;">
                    <span>Amount Paid:</span><span>Rs. ${paid}</span>
                </div>
            </div>
        `;
    } catch (err) {
        modalBody.innerHTML = '<div style="color:var(--red);padding:20px;">An error occurred while loading record details.</div>';
    }
}

function closeSaleModal() {
    const modal = document.getElementById('saleDetailsModal');
    if (modal) modal.classList.remove('active');
}

function closeModalOnOverlay(e) {
    if (e.target && e.target.id === 'saleDetailsModal') {
        closeSaleModal();
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeSaleModal();
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}
</script>
</body>
</html>