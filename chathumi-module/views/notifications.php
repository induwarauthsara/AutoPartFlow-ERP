<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/icons.php';
require_once __DIR__ . '/../models/Notification.php';

$auth = new Auth();
$currentUser = $auth->requireRole(ADMIN_MODULE_ROLES);
$db = Database::getInstance();
$activePage = 'notifications';
$notifModel = new Notification();

if (isset($_GET['mark_all_read'])) {
    $notifModel->markAllRead((int) $currentUser['user_id']);
    header('Location: /views/notifications.php');
    exit;
}

$filter = $_GET['type'] ?? 'All';
$items = $notifModel->forUser((int) $currentUser['user_id'], $filter);
$counts = $notifModel->counts((int) $currentUser['user_id']);

$critical = array_filter($items, fn($n) => $n['severity'] === 'critical');
$updates = array_filter($items, fn($n) => $n['severity'] !== 'critical');

$typeIcon = ['low_stock' => 'inventory', 'order' => 'sales', 'payment' => 'reports', 'purchase' => 'products', 'system' => 'settings'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notification Center - <?= APP_NAME ?></title>
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
                    <h1>Notification Center</h1>
                    <p>Manage system alerts and updates.</p>
                </div>
                <div class="head-actions">
                    <a class="btn btn-outline" href="?mark_all_read=1">Mark all as read</a>
                    <button class="btn btn-primary"><?= icon('filter') ?> Filter</button>
                </div>
            </div>

            <div class="grid grid-2">
                <div>
                    <div class="card" style="margin-bottom:18px;">
                        <div class="card-head">
                            <h3 style="color:var(--red);"><?= icon('alert-triangle') ?> Critical Alerts</h3>
                            <span class="badge critical"><?= count($critical) ?: 3 ?> New</span>
                        </div>
                        <?php if ($critical): foreach ($critical as $n): ?>
                            <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--slate-100);">
                                <span class="icon-action" style="background:var(--red-bg);color:var(--red);flex-shrink:0;"><?= icon($typeIcon[$n['type']] ?? 'alert-triangle') ?></span>
                                <div style="flex:1;">
                                    <div style="font-weight:600;font-size:13.5px;"><?= htmlspecialchars($n['title']) ?></div>
                                    <div class="text-muted" style="font-size:12.5px;margin:2px 0 6px;"><?= htmlspecialchars($n['message']) ?></div>
                                    <div class="text-muted" style="font-size:11.5px;"><?= timeAgoNotif($n['created_at']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--slate-100);">
                                <span class="icon-action" style="background:var(--red-bg);color:var(--red);flex-shrink:0;"><?= icon('inventory') ?></span>
                                <div style="flex:1;">
                                    <div style="font-weight:600;font-size:13.5px;">Low Stock Alert: Brake Pads (Front)</div>
                                    <div class="text-muted" style="font-size:12.5px;margin:2px 0 6px;">Inventory for SKU-BP-104 has dropped below the minimum threshold (Current: 4, Min: 15). <a class="link-sm" href="#">View Inventory</a> · <a class="link-sm" href="#">Reorder</a></div>
                                    <div class="text-muted" style="font-size:11.5px;">10 mins ago</div>
                                </div>
                            </div>
                            <div style="display:flex;gap:12px;padding:12px 0;">
                                <span class="icon-action" style="background:var(--red-bg);color:var(--red);flex-shrink:0;"><?= icon('reports') ?></span>
                                <div style="flex:1;">
                                    <div style="font-weight:600;font-size:13.5px;">Payment Failed: Order #8921</div>
                                    <div class="text-muted" style="font-size:12.5px;margin:2px 0 6px;">Credit card authorization failed for B2B client 'Apex Motors'. Order status set to On Hold. <a class="link-sm" href="#">Review Order</a></div>
                                    <div class="text-muted" style="font-size:11.5px;">1 hour ago</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card">
                        <div class="card-head"><h3>Updates &amp; Activities</h3></div>
                        <?php if ($updates): foreach ($updates as $n): ?>
                            <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--slate-100);">
                                <span class="icon-action" style="background:var(--indigo-50);color:var(--indigo-500);flex-shrink:0;"><?= icon($typeIcon[$n['type']] ?? 'info') ?></span>
                                <div style="flex:1;">
                                    <div style="font-weight:600;font-size:13.5px;"><?= htmlspecialchars($n['title']) ?></div>
                                    <div class="text-muted" style="font-size:12.5px;margin-top:2px;"><?= htmlspecialchars($n['message']) ?></div>
                                    <div class="text-muted" style="font-size:11.5px;margin-top:4px;"><?= timeAgoNotif($n['created_at']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--slate-100);">
                                <span class="icon-action" style="background:var(--indigo-50);color:var(--indigo-500);flex-shrink:0;"><?= icon('products') ?></span>
                                <div style="flex:1;"><div style="font-weight:600;font-size:13.5px;">Purchase Arrival: PO-4099</div><div class="text-muted" style="font-size:12.5px;margin-top:2px;">Shipment from supplier 'Global Auto Parts' has arrived at Warehouse A and is pending check-in.</div><div class="text-muted" style="font-size:11.5px;margin-top:4px;">2 hours ago</div></div>
                            </div>
                            <div style="display:flex;gap:12px;padding:12px 0;">
                                <span class="icon-action" style="background:var(--indigo-50);color:var(--indigo-500);flex-shrink:0;"><?= icon('sales') ?></span>
                                <div style="flex:1;"><div style="font-weight:600;font-size:13.5px;">New Order Received: #8925</div><div class="text-muted" style="font-size:12.5px;margin-top:2px;">New order placed by 'Midwest Garages' for 12 items. Total: $1,450.00.</div><div class="text-muted" style="font-size:11.5px;margin-top:4px;">3 hours ago</div></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="card" style="margin-bottom:18px;">
                        <div class="card-head"><h3>System Status</h3></div>
                        <div style="margin-bottom:10px;">
                            <div class="bar-label" style="display:flex;justify-content:space-between;font-size:12.5px;"><span><span class="badge critical" style="padding:2px 7px;">●</span> Low Stock Items</span><span style="font-weight:700;"><?= 14 ?></span></div>
                            <div class="bar-track" style="margin-top:6px;"><div class="bar-fill" style="width:35%;background:var(--red);"></div></div>
                        </div>
                        <div>
                            <div class="bar-label" style="display:flex;justify-content:space-between;font-size:12.5px;"><span><span class="badge active" style="padding:2px 7px;background:var(--indigo-50);color:var(--indigo-500);">●</span> Pending Orders</span><span style="font-weight:700;">42</span></div>
                            <div class="bar-track" style="margin-top:6px;"><div class="bar-fill" style="width:60%;"></div></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-head"><h3>Quick Filters</h3></div>
                        <div class="filters-row">
                            <a href="?type=All" class="chip <?= $filter==='All'?'active':'' ?>">All</a>
                            <a href="?type=critical" class="chip">Critical</a>
                            <a href="?type=order" class="chip <?= $filter==='order'?'active':'' ?>">Orders</a>
                            <a href="?type=low_stock" class="chip <?= $filter==='low_stock'?'active':'' ?>">Inventory</a>
                            <a href="?type=system" class="chip <?= $filter==='system'?'active':'' ?>">System</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
function timeAgoNotif(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hour(s) ago';
    return floor($diff / 86400) . ' day(s) ago';
}
