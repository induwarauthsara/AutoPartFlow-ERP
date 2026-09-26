<?php
/** @var array $rows */
$filterType   = $filterType ?? 'all';
$filterStatus = $filterStatus ?? 'all';
$unreadCount  = (int) ($unreadCount ?? 0);
$flash        = $flash ?? null;

$typeLabels = [
    'low_stock'        => 'Low stock',
    'new_order'        => 'New order',
    'pending_order'    => 'Pending order',
    'credit_due'       => 'Credit due',
    'purchase_arrival' => 'Purchase arrival',
    'delivery_update'  => 'Delivery update',
    'system'           => 'System',
];
$chips    = ['all' => 'All', 'critical' => 'Critical', 'orders' => 'Orders', 'inventory' => 'Inventory', 'system' => 'System'];
$statuses = ['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'];

$link = function (array $over) use ($filterType, $filterStatus): string {
    $q = array_merge(['type' => $filterType, 'status' => $filterStatus], $over);
    $q = array_filter($q, fn($v) => $v !== 'all');
    return url('admin/notifications') . ($q ? '?' . http_build_query($q) : '');
};
$when = function (?string $ts): string {
    if (!$ts) return '';
    $t = strtotime($ts);
    return date(date('Y-m-d', $t) === date('Y-m-d') ? 'g:i A' : 'j M, g:i A', $t);
};
$ret = '<input type="hidden" name="return_type" value="' . e($filterType) . '">'
     . '<input type="hidden" name="return_status" value="' . e($filterStatus) . '">';

$critical = [];
$updates  = [];
foreach ($rows as $n) {
    $t = strtolower($n['title'] ?? '');
    $isCritical = in_array($n['type'] ?? '', ['low_stock', 'credit_due'], true)
        || str_contains($t, 'failed') || str_contains($t, 'critical');
    if ($isCritical) { $critical[] = $n; } else { $updates[] = $n; }
}
$criticalUnread = count(array_filter($critical, fn($n) => (int) $n['is_read'] === 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Notification Center') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--red:#dc2626;--red-bg:#fdecec;--green:#16a34a;--green-bg:#e7f8ee;}
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
.topbar{background:#fff;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;gap:16px;padding:12px 24px;font-weight:700;}
.topbar-icons{display:flex;align-items:center;gap:14px;margin-left:auto;color:var(--slate-500);}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;}
.btn{padding:8px 14px;border-radius:9px;font-size:12.5px;font-weight:600;border:1px solid var(--slate-300);background:#fff;cursor:pointer;color:var(--slate-900);text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.btn:disabled{opacity:.5;cursor:not-allowed;}
.btn-primary{background:var(--navy-900);color:#fff;border-color:var(--navy-900);}
.head-actions{display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap;}
.head-actions form{margin:0;}
.filter-menu{position:relative;}
.filter-menu summary{list-style:none;}
.filter-menu summary::-webkit-details-marker{display:none;}
.filter-menu .menu{position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #eef0f5;border-radius:12px;box-shadow:0 8px 24px rgba(15,23,42,.12);padding:6px;min-width:160px;z-index:10;}
.filter-menu .menu small{display:block;padding:6px 10px 4px;color:var(--slate-500);font-size:11px;}
.filter-menu .menu a{display:block;padding:8px 10px;border-radius:8px;color:var(--slate-900);text-decoration:none;font-size:13px;}
.filter-menu .menu a:hover{background:var(--slate-100);}
.filter-menu .menu a.active{background:var(--indigo-50);color:var(--indigo-500);font-weight:600;}
.grid-2{display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;margin-bottom:16px;}
.card h3{margin:0 0 14px;font-size:15px;display:flex;align-items:center;gap:8px;}
.badge-count{background:var(--red-bg);color:var(--red);font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;margin-left:6px;}
.notif-item{display:flex;gap:12px;padding:12px 0 12px 12px;border-bottom:1px solid var(--slate-100);border-left:3px solid var(--red);margin-bottom:8px;}
.notif-item.info{border-left-color:var(--indigo-500);}
.notif-item.read{opacity:.65;}
.notif-item:last-child{border-bottom:none;}
.notif-icon{width:34px;height:34px;border-radius:10px;background:var(--red-bg);color:var(--red);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;}
.notif-icon.info{background:var(--indigo-50);color:var(--indigo-500);}
.notif-top{display:flex;justify-content:space-between;gap:10px;align-items:baseline;}
.notif-title{font-weight:500;font-size:13.5px;}
.notif-item.unread .notif-title{font-weight:700;}
.notif-item.unread .notif-title::before{content:"";display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--indigo-500);margin-right:7px;vertical-align:middle;}
.notif-msg{color:var(--slate-500);font-size:12.5px;margin-top:3px;}
.notif-time{color:var(--slate-500);font-size:11px;white-space:nowrap;}
.notif-meta{display:flex;align-items:center;gap:6px;margin-top:8px;flex-wrap:wrap;}
.notif-meta form{margin:0;}
.type-tag{font-size:10.5px;font-weight:600;color:var(--slate-500);background:var(--slate-100);padding:2px 8px;border-radius:20px;}
.act{border:0;background:none;color:var(--indigo-500);font-size:12px;font-weight:600;cursor:pointer;padding:3px 6px;border-radius:6px;text-decoration:none;}
.act:hover{background:var(--slate-100);}
.act.del{color:var(--red);}
.stat-row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;margin-bottom:6px;}
.stat-row .dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px;}
.stat-row .num{font-weight:700;font-size:14px;}
.bar-track{height:5px;background:var(--slate-100);border-radius:6px;overflow:hidden;margin-bottom:14px;}
.bar-fill{height:100%;border-radius:6px;}
.chip{display:inline-block;font-size:11.5px;font-weight:600;padding:5px 12px;border-radius:20px;background:var(--slate-100);color:var(--slate-500);margin:0 6px 6px 0;text-decoration:none;}
.chip:hover{color:var(--slate-900);}
.chip.active{background:var(--indigo-500);color:#fff;}
.showing{font-size:12px;color:var(--slate-500);margin:4px 0 0;}
.flash{padding:11px 14px;border-radius:9px;margin-bottom:16px;font-weight:600;font-size:13px;}
.flash.success{background:var(--green-bg);color:var(--green);}
.flash.error{background:var(--red-bg);color:var(--red);}
dialog{border:0;border-radius:16px;padding:0;width:min(480px,94vw);}
dialog::backdrop{background:rgba(15,23,42,.45);}
.dlg{padding:22px;}
.dlg h3{margin:0 0 16px;font-size:18px;}
.field{display:grid;gap:5px;margin-bottom:12px;}
.field label{font-size:12.5px;font-weight:600;}
.field input,.field select,.field textarea{border:1px solid var(--slate-300);border-radius:8px;padding:9px 11px;font:inherit;background:#fff;width:100%;}
.field small{color:var(--slate-500);font-size:11.5px;}
.dlg-foot{display:flex;justify-content:flex-end;gap:10px;margin-top:6px;}
@media (max-width:900px){.grid-2{grid-template-columns:1fr;}}
</style>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<div class="app-shell">
    <?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>

    <div class="main">
        <header class="topbar">AutoPartFlow
            <div class="topbar-icons"><a href="<?= url('admin/notifications') ?>" aria-label="Notifications">Notifications<?= $unreadCount ? ' (' . $unreadCount . ')' : '' ?></a> <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)) ?></div></div>
        </header>

        <div class="content">
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <div class="page-head">
                <div>
                    <h1>Notification Center</h1>
                    <p>Manage system alerts and updates.</p>
                    <p class="showing">Showing: <?= e($chips[$filterType]) ?> notifications, <?= e(strtolower($statuses[$filterStatus])) ?> (<?= count($rows) ?>)</p>
                </div>
                <div class="head-actions">
                    <form method="post" action="<?= url('admin/notifications/read-all') ?>">
                        <?= $ret ?>
                        <button class="btn" type="submit" <?= $unreadCount ? '' : 'disabled' ?>>Mark all as read<?= $unreadCount ? ' (' . $unreadCount . ')' : '' ?></button>
                    </form>
                    <details class="filter-menu">
                        <summary class="btn">Filter: <?= e($statuses[$filterStatus]) ?></summary>
                        <div class="menu">
                            <small>Show</small>
                            <?php foreach ($statuses as $key => $label): ?>
                                <a href="<?= e($link(['status' => $key])) ?>" class="<?= $filterStatus === $key ? 'active' : '' ?>"><?= e($label) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <button class="btn btn-primary" type="button" id="newBtn">+ New Notification</button>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <div class="card">
                        <h3><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 21 12 2l11 19H1Zm10-4h2v2h-2v-2Zm0-7h2v5h-2v-5Z"/></svg> Critical Alerts <?php if ($criticalUnread): ?><span class="badge-count"><?= $criticalUnread ?> New</span><?php endif; ?></h3>
                        <?php if ($critical): foreach ($critical as $n): $read = (int) $n['is_read'] === 1; ?>
                            <div class="notif-item <?= $read ? 'read' : 'unread' ?>">
                                <div class="notif-icon">!</div>
                                <div style="flex:1;min-width:0;">
                                    <div class="notif-top">
                                        <div class="notif-title"><?= e($n['title']) ?></div>
                                        <span class="notif-time"><?= $when($n['created_at']) ?></span>
                                    </div>
                                    <div class="notif-msg"><?= e($n['message']) ?></div>
                                    <div class="notif-meta">
                                        <span class="type-tag"><?= e($typeLabels[$n['type']] ?? $n['type']) ?></span>
                                        <?php if (!empty($n['link_url'])): ?><a class="act" href="<?= e($n['link_url']) ?>">Open</a><?php endif; ?>
                                        <form method="post" action="<?= url('admin/notifications/read') ?>"><?= $ret ?><input type="hidden" name="id" value="<?= (int) $n['id'] ?>"><button class="act" type="submit"><?= $read ? 'Mark as unread' : 'Mark as read' ?></button></form>
                                        <form method="post" action="<?= url('admin/notifications/delete') ?>" onsubmit="return confirm('Delete this notification? This cannot be undone.');"><?= $ret ?><input type="hidden" name="id" value="<?= (int) $n['id'] ?>"><button class="act del" type="submit">Delete</button></form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p style="color:var(--slate-500);">No critical alerts for this filter.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card">
                        <h3><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 4h2v2h-2V6Zm0 4h2v8h-2v-8Z"/></svg> Updates &amp; Activities</h3>
                        <?php if ($updates): foreach ($updates as $n): $read = (int) $n['is_read'] === 1; ?>
                            <div class="notif-item info <?= $read ? 'read' : 'unread' ?>">
                                <div class="notif-icon info">i</div>
                                <div style="flex:1;min-width:0;">
                                    <div class="notif-top">
                                        <div class="notif-title"><?= e($n['title']) ?></div>
                                        <span class="notif-time"><?= $when($n['created_at']) ?></span>
                                    </div>
                                    <div class="notif-msg"><?= e($n['message']) ?></div>
                                    <div class="notif-meta">
                                        <span class="type-tag"><?= e($typeLabels[$n['type']] ?? $n['type']) ?></span>
                                        <?php if (!empty($n['link_url'])): ?><a class="act" href="<?= e($n['link_url']) ?>">Open</a><?php endif; ?>
                                        <form method="post" action="<?= url('admin/notifications/read') ?>"><?= $ret ?><input type="hidden" name="id" value="<?= (int) $n['id'] ?>"><button class="act" type="submit"><?= $read ? 'Mark as unread' : 'Mark as read' ?></button></form>
                                        <form method="post" action="<?= url('admin/notifications/delete') ?>" onsubmit="return confirm('Delete this notification? This cannot be undone.');"><?= $ret ?><input type="hidden" name="id" value="<?= (int) $n['id'] ?>"><button class="act del" type="submit">Delete</button></form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p style="color:var(--slate-500);">No updates for this filter.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <div class="card">
                        <h3>System Status</h3>
                        <div class="stat-row"><span><span class="dot" style="background:var(--red);"></span>Low Stock Items</span><span class="num">14</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:35%;background:var(--red);"></div></div>
                        <div class="stat-row"><span><span class="dot" style="background:var(--indigo-500);"></span>Pending Orders</span><span class="num">42</span></div>
                        <div class="bar-track"><div class="bar-fill" style="width:60%;background:var(--indigo-500);"></div></div>
                    </div>
                    <div class="card">
                        <h3>Quick Filters</h3>
                        <?php foreach ($chips as $key => $label): ?>
                            <a class="chip <?= $filterType === $key ? 'active' : '' ?>" href="<?= e($link(['type' => $key])) ?>"><?= e($label) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<dialog id="newDlg">
    <form method="post" class="dlg" action="<?= url('admin/notifications/store') ?>">
        <?= $ret ?>
        <h3>New notification</h3>
        <div class="field">
            <label for="nType">Type</label>
            <select id="nType" name="type">
                <?php foreach ($typeLabels as $val => $label): ?>
                    <option value="<?= e($val) ?>" <?= $val === 'system' ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="nTitle">Title</label>
            <input id="nTitle" name="title" required maxlength="200">
        </div>
        <div class="field">
            <label for="nMsg">Message</label>
            <textarea id="nMsg" name="message" rows="3" required></textarea>
        </div>
        <div class="field">
            <label for="nLink">Link (optional)</label>
            <input id="nLink" name="link_url" maxlength="255" placeholder="/admin/reports">
            <small>Page to open when someone clicks "Open".</small>
        </div>
        <div class="dlg-foot">
            <button type="button" class="btn" id="newCancel">Cancel</button>
            <button type="submit" class="btn btn-primary">Create notification</button>
        </div>
    </form>
</dialog>

<script>
const newDlg = document.getElementById('newDlg');
document.getElementById('newBtn').onclick = () => newDlg.showModal();
document.getElementById('newCancel').onclick = () => newDlg.close();
document.addEventListener('click', e => {
    const menu = document.querySelector('.filter-menu');
    if (menu && menu.open && !menu.contains(e.target)) menu.open = false;
});
</script>
</body>
</html>