<?php
/** @var array $settings */
$flash = $_SESSION['settings_flash'] ?? null;
unset($_SESSION['settings_flash']);

$tabs = [
    'business'    => 'Business Info',
    'invoice'     => 'Invoice Settings',
    'backup'      => 'Data Backup',
    'preferences' => 'Preferences',
];
$activeTab = $_GET['tab'] ?? 'business';
if (!isset($tabs[$activeTab])) $activeTab = 'business';

$s = fn(string $key, string $default = '') => e((string) ($settings[$key] ?? $default));
$sel = fn(string $key, string $value, string $default = '') => ((string) ($settings[$key] ?? $default)) === $value ? 'selected' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Settings') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--green:#16a34a;--green-bg:#e7f8ee;--red:#dc2626;--red-bg:#fdecec;}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:220px;flex-shrink:0;background:linear-gradient(180deg,var(--navy-900),var(--navy-800));color:#cbd5e1;padding:18px 12px;display:flex;flex-direction:column;}
.brand{display:flex;align-items:center;gap:9px;padding:6px 8px 20px;}
.brand-title{color:#fff;font-weight:700;font-size:14px;}
.brand-sub{font-size:10.5px;color:#8590b3;text-transform:uppercase;letter-spacing:.04em;}
.nav-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;color:#b7c0dd;font-size:13px;font-weight:500;margin-bottom:2px;text-decoration:none;}
.nav-link.active{background:var(--indigo-500);color:#fff;}
.sidebar-footer{margin-top:auto;padding-top:12px;border-top:1px solid rgba(255,255,255,.08);font-size:11.5px;color:#7f8bb0;}
.sidebar-footer .dot{width:7px;height:7px;background:var(--green);border-radius:50%;display:inline-block;margin-right:6px;}
.main{flex:1;min-width:0;}
.topbar{background:#fff;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;gap:16px;padding:12px 24px;font-weight:700;}
.search-box{flex:1;max-width:380px;display:flex;align-items:center;gap:8px;background:var(--slate-100);border-radius:9px;padding:8px 12px;color:var(--slate-500);font-weight:400;}
.search-box input{border:none;background:transparent;outline:none;flex:1;font-size:13px;}
.topbar-icons{display:flex;align-items:center;gap:12px;margin-left:auto;color:var(--slate-500);}
.avatar{width:30px;height:30px;border-radius:50%;background:var(--indigo-500);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:11px;}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;max-width:520px;}
.btn{padding:9px 16px;border-radius:9px;font-size:12.5px;font-weight:600;border:1px solid var(--slate-300);background:#fff;cursor:pointer;}
.btn-primary{background:var(--navy-900);color:#fff;border-color:var(--navy-900);}
.head-actions{display:flex;gap:10px;}
.settings-layout{display:grid;grid-template-columns:210px 1fr;gap:16px;align-items:start;}
.tab-card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:8px;border:1px solid #eef0f5;}
.tab-link{display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:9px;font-size:13px;color:var(--slate-900);text-decoration:none;margin-bottom:2px;}
.tab-link:hover{background:var(--slate-100);}
.tab-link.active{background:var(--navy-900);color:#fff;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:22px;border:1px solid #eef0f5;}
.card h3{margin:0 0 4px;font-size:16px;}
.card .desc{color:var(--slate-500);font-size:12.5px;margin:0 0 16px;}
.form-row{display:grid;grid-template-columns:130px 1fr 1fr;gap:16px;align-items:start;}
.grid-2f{display:grid;grid-template-columns:1fr 1fr;gap:0 16px;}
.logo-box{border:2px dashed var(--slate-300);border-radius:12px;height:110px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:var(--slate-500);}
.form-group{margin-bottom:14px;}
.form-group label{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px;}
.form-group small{display:block;color:var(--slate-500);font-size:11.5px;margin-top:4px;}
.form-control{width:100%;padding:9px 11px;border:1px solid var(--slate-300);border-radius:8px;background:#fff;font:inherit;}
.check{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;margin-bottom:14px;}
.flash{padding:11px 14px;border-radius:9px;margin-bottom:16px;font-weight:600;font-size:13px;}
.flash.success{background:var(--green-bg);color:var(--green);}
.flash.error{background:var(--red-bg);color:var(--red);}
.found{outline:2px solid var(--indigo-500);outline-offset:4px;border-radius:6px;}
@media (max-width:900px){.settings-layout{grid-template-columns:1fr;}.form-row,.grid-2f{grid-template-columns:1fr;}}
</style>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<div class="app-shell">
    <?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

    <div class="main">
        <header class="topbar">
            AutoPartFlow
            <div class="search-box"><input type="text" id="settingSearch" placeholder="Search settings..."></div>
            <div class="topbar-icons"><a href="<?= url('admin/notifications') ?>" aria-label="Notifications">Notifications</a> <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div></div>
        </header>

        <div class="content">
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <div class="page-head">
                <div>
                    <h1>System Settings</h1>
                    <p>Configure your AutoPartFlow ERP environment, manage business details, and customize your operational preferences.</p>
                </div>
                <div class="head-actions">
                    <button class="btn" type="button" id="discardBtn">Discard Changes</button>
                    <button class="btn btn-primary" type="submit" form="settingsForm">Save Configuration</button>
                </div>
            </div>

            <form id="settingsForm" method="post" action="<?= url('admin/settings/save') ?>">
            <input type="hidden" name="active_tab" id="activeTab" value="<?= e($activeTab) ?>">
            <div class="settings-layout">
                <div class="tab-card">
                    <a class="tab-link" data-tab="business" href="?tab=business"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h18v16H3V4Zm3 3v4h4V7H6Zm7 0v2h5V7h-5ZM6 14v2h12v-2H6Z"/></svg> Business Info</a>
                    <a class="tab-link" data-tab="invoice" href="?tab=invoice"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 2h14v20l-3-2-4 2-4-2-3 2V2Zm3 4v2h8V6H8Zm0 5v2h8v-2H8Z"/></svg> Invoice Settings</a>
                    <a class="tab-link" data-tab="backup" href="?tab=backup"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h15l3 3v15H3V3Zm4 1v6h10V4H7Zm0 10v6h10v-6H7Z"/></svg> Data Backup</a>
                    <a class="tab-link" data-tab="preferences" href="?tab=preferences"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v2H3V5Zm4 6h10v2H7v-2Zm3 6h4v2h-4v-2Z"/></svg> Preferences</a>
                </div>

                <div>
                    <section class="card panel" data-panel="business">
                        <h3>Business Information</h3>
                        <p class="desc">Update your company details and primary branding for external communications.</p>
                        <div class="form-row">
                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:5px;">Company Logo</label>
                                <div class="logo-box"><img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40"></div>
                            </div>
                            <div>
                                <div class="form-group"><label for="business_name">Company Name</label><input class="form-control" id="business_name" name="business_name" value="<?= $s('business_name') ?>" required maxlength="150"></div>
                                <div class="form-group"><label for="business_email">Contact Email</label><input class="form-control" id="business_email" name="business_email" type="email" value="<?= $s('business_email') ?>" maxlength="150"></div>
                            </div>
                            <div>
                                <div class="form-group"><label>&nbsp;</label></div>
                                <div class="form-group"><label for="business_phone">Business Phone</label><input class="form-control" id="business_phone" name="business_phone" value="<?= $s('business_phone') ?>" maxlength="30"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="business_address">Primary Address</label>
                            <textarea class="form-control" id="business_address" name="business_address" rows="2" maxlength="500"><?= $s('business_address') ?></textarea>
                        </div>
                    </section>

                    <section class="card panel" data-panel="invoice">
                        <h3>Invoice Settings</h3>
                        <p class="desc">Control how invoice numbers are created and what customers see on each invoice.</p>
                        <div class="grid-2f">
                            <div class="form-group"><label for="invoice_prefix">Invoice Prefix</label><input class="form-control" id="invoice_prefix" name="invoice_prefix" value="<?= $s('invoice_prefix', 'INV-') ?>" maxlength="10"><small>Example: INV-00125</small></div>
                            <div class="form-group"><label for="invoice_next_number">Next Invoice Number</label><input class="form-control" id="invoice_next_number" name="invoice_next_number" type="number" min="1" step="1" value="<?= $s('invoice_next_number', '1') ?>" required></div>
                            <div class="form-group"><label for="invoice_due_days">Payment Due (days)</label><input class="form-control" id="invoice_due_days" name="invoice_due_days" type="number" min="0" step="1" value="<?= $s('invoice_due_days', '30') ?>" required></div>
                        </div>
                        <div class="form-group"><label for="invoice_footer_note">Invoice Footer Note</label><textarea class="form-control" id="invoice_footer_note" name="invoice_footer_note" rows="2" maxlength="500"><?= $s('invoice_footer_note', 'Thank you for your business.') ?></textarea></div>
                    </section>

                    <section class="card panel" data-panel="backup">
                        <h3>Data Backup</h3>
                        <p class="desc">Choose how often the system should back up your data and how long to keep old backups.</p>
                        <div class="grid-2f">
                            <div class="form-group"><label for="backup_frequency">Backup Frequency</label>
                                <select class="form-control" id="backup_frequency" name="backup_frequency">
                                    <option value="off" <?= $sel('backup_frequency', 'off', 'daily') ?>>Off</option>
                                    <option value="daily" <?= $sel('backup_frequency', 'daily', 'daily') ?>>Daily</option>
                                    <option value="weekly" <?= $sel('backup_frequency', 'weekly', 'daily') ?>>Weekly</option>
                                    <option value="monthly" <?= $sel('backup_frequency', 'monthly', 'daily') ?>>Monthly</option>
                                </select>
                            </div>
                            <div class="form-group"><label for="backup_retention_days">Keep Backups For (days)</label><input class="form-control" id="backup_retention_days" name="backup_retention_days" type="number" min="1" step="1" value="<?= $s('backup_retention_days', '30') ?>" required></div>
                        </div>
                    </section>

                    <section class="card panel" data-panel="preferences">
                        <h3>Preferences</h3>
                        <p class="desc">Set how money, dates and stock alerts appear across the system.</p>
                        <div class="grid-2f">
                            <div class="form-group"><label for="currency">Currency</label>
                                <select class="form-control" id="currency" name="currency">
                                    <option value="LKR" <?= $sel('currency', 'LKR', 'LKR') ?>>LKR (Rs.)</option>
                                    <option value="USD" <?= $sel('currency', 'USD', 'LKR') ?>>USD ($)</option>
                                </select>
                            </div>
                            <div class="form-group"><label for="date_format">Date Format</label>
                                <select class="form-control" id="date_format" name="date_format">
                                    <option value="Y-m-d" <?= $sel('date_format', 'Y-m-d', 'Y-m-d') ?>>2026-09-25</option>
                                    <option value="d/m/Y" <?= $sel('date_format', 'd/m/Y', 'Y-m-d') ?>>25/09/2026</option>
                                    <option value="m/d/Y" <?= $sel('date_format', 'm/d/Y', 'Y-m-d') ?>>09/25/2026</option>
                                </select>
                            </div>
                            <div class="form-group"><label for="low_stock_threshold">Low Stock Alert Level</label><input class="form-control" id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" step="1" value="<?= $s('low_stock_threshold', '10') ?>" required><small>Products at or below this quantity show as low stock.</small></div>
                        </div>
                    </section>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('settingsForm');
const tabInput = document.getElementById('activeTab');

function showTab(name) {
    document.querySelectorAll('.panel').forEach(p => p.hidden = p.dataset.panel !== name);
    document.querySelectorAll('.tab-link').forEach(a => a.classList.toggle('active', a.dataset.tab === name));
    tabInput.value = name;
    history.replaceState(null, '', '?tab=' + name);
}

document.querySelectorAll('.tab-link').forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    showTab(a.dataset.tab);
}));

// Discard: save karanna kalin karapu wenas ain karanawa
document.getElementById('discardBtn').onclick = () => {
    form.reset();
};

// Search: label eka match wena setting eka thiyena tab ekata yanawa
document.getElementById('settingSearch').addEventListener('input', e => {
    const q = e.target.value.trim().toLowerCase();
    document.querySelectorAll('.found').forEach(el => el.classList.remove('found'));
    if (!q) return;
    const label = [...document.querySelectorAll('.panel .form-group label, .panel .check')]
        .find(l => l.textContent.toLowerCase().includes(q));
    if (!label) return;
    const panel = label.closest('.panel');
    showTab(panel.dataset.panel);
    const box = label.closest('.form-group') || label;
    box.classList.add('found');
    box.scrollIntoView({block: 'center', behavior: 'smooth'});
});

showTab(tabInput.value);
</script>
</body>
</html>