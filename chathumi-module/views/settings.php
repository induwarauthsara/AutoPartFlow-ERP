<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/icons.php';
require_once __DIR__ . '/../models/Setting.php';

$auth = new Auth();
$currentUser = $auth->requireRole(ADMIN_MODULE_ROLES);
$db = Database::getInstance();
$activePage = 'settings';
$settingModel = new Setting();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Session::verifyCsrf($_POST['csrf'] ?? '')) {
        foreach (['company_name', 'contact_email', 'business_phone', 'primary_address', 'currency', 'tax_rate', 'invoice_prefix'] as $key) {
            if (isset($_POST[$key])) {
                $settingModel->set($key, trim($_POST[$key]));
            }
        }
        $auth->logActivity($currentUser['user_id'], 'Updated business settings', 'settings', 'success');
        Session::flash('success', 'Settings saved successfully.');
        header('Location: /views/settings.php');
        exit;
    }
}

$settings = $settingModel->all();
$tab = $_GET['tab'] ?? 'business_info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - <?= APP_NAME ?></title>
<link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <div class="main">
        <?php require __DIR__ . '/partials/topbar.php'; ?>
        <div class="content">
            <?php if ($flash = Session::flash('success')): ?>
                <div class="alert alert-success"><?= icon('check-circle') ?><span><?= htmlspecialchars($flash) ?></span></div>
            <?php endif; ?>

            <div class="page-head">
                <div>
                    <h1>System Settings</h1>
                    <p>Configure your <?= APP_NAME ?> environment, manage business details, and customize your operational preferences.</p>
                </div>
                <div class="head-actions">
                    <button class="btn btn-outline" type="button" onclick="location.reload()">Discard Changes</button>
                    <button class="btn btn-primary" form="settingsForm" type="submit"><?= icon('check-circle') ?> Save Configuration</button>
                </div>
            </div>

            <div class="grid" style="grid-template-columns:220px 1fr;gap:18px;align-items:start;">
                <div class="card" style="padding:10px;">
                    <?php
                    $tabs = ['business_info' => ['building', 'Business Info'], 'invoice_settings' => ['reports', 'Invoice Settings'], 'tax_config' => ['dashboard', 'Tax Config'], 'data_backup' => ['upload', 'Data Backup'], 'preferences' => ['settings', 'Preferences']];
                    foreach ($tabs as $key => [$ic, $label]):
                        $active = $tab === $key;
                    ?>
                        <a href="?tab=<?= $key ?>" class="nav-link" style="color:<?= $active ? '' : 'var(--slate-700)' ?>;<?= $active ? 'background:var(--indigo-50);color:var(--indigo-600);' : '' ?>">
                            <?= icon($ic) ?><span><?= $label ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <form id="settingsForm" method="POST" action="/views/settings.php">
                        <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">

                        <?php if ($tab === 'business_info'): ?>
                            <div class="card-head"><h3>Business Information</h3></div>
                            <p class="text-muted" style="margin-top:-8px;">Update your company details and primary branding for external communications.</p>
                            <div class="form-row" style="align-items:flex-start;margin-top:16px;">
                                <div class="form-group" style="max-width:150px;">
                                    <label>Company Logo</label>
                                    <div style="border:2px dashed var(--slate-300);border-radius:12px;height:110px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:var(--slate-500);cursor:pointer;">
                                        <?= icon('upload') ?>
                                        <span style="font-size:11px;">Click to upload<br>SVG, PNG, JPG (max 2MB)</span>
                                    </div>
                                </div>
                                <div style="flex:1;">
                                    <div class="form-row">
                                        <div class="form-group"><label>Company Name</label><input class="form-control" name="company_name" value="<?= htmlspecialchars($settings['company_name'] ?? 'AutoPartFlow Inc.') ?>"></div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group"><label>Contact Email</label><input class="form-control" type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? 'admin@autopartflow.com') ?>"></div>
                                        <div class="form-group"><label>Business Phone</label><input class="form-control" name="business_phone" value="<?= htmlspecialchars($settings['business_phone'] ?? '+1 (555) 123-4567') ?>"></div>
                                    </div>
                                    <div class="form-group"><label>Primary Address</label><textarea class="form-control" name="primary_address" rows="3"><?= htmlspecialchars($settings['primary_address'] ?? "1234 Logistics Way, Suite 100\nDetroit, MI 48201\nUnited States") ?></textarea></div>
                                </div>
                            </div>

                        <?php elseif ($tab === 'invoice_settings'): ?>
                            <div class="card-head"><h3>Invoice Settings</h3></div>
                            <div class="form-group"><label>Invoice Number Prefix</label><input class="form-control" name="invoice_prefix" value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'INV-') ?>"></div>
                            <p class="field-hint">Applies to every new invoice generated by the Mobile POS and Sales module.</p>

                        <?php elseif ($tab === 'tax_config'): ?>
                            <div class="card-head"><h3>Tax Configuration</h3></div>
                            <div class="form-group"><label>Default Tax Rate (%)</label><input class="form-control" type="number" step="0.1" name="tax_rate" value="<?= htmlspecialchars($settings['tax_rate'] ?? '8.5') ?>"></div>

                        <?php elseif ($tab === 'data_backup'): ?>
                            <div class="card-head"><h3>Data Backup</h3></div>
                            <p class="text-muted">Download a full database backup or configure automatic backup schedule.</p>
                            <button class="btn btn-outline" type="button"><?= icon('download') ?> Download Backup Now</button>

                        <?php else: ?>
                            <div class="card-head"><h3>Preferences</h3></div>
                            <div class="form-group">
                                <label>Currency</label>
                                <select class="form-control" name="currency">
                                    <?php foreach (['USD','LKR','EUR','GBP'] as $c): ?>
                                        <option <?= ($settings['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
