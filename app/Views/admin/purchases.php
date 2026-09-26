<?php
/** @var array $summary */
/** @var array $orders */
/** @var bool $isMock */
/** @var string $csrfToken */
$statusLabel = static fn(string $status): string => ucwords(str_replace('_', ' ', $status));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Purchase Management') ?></title>
    <link rel="stylesheet" href="<?= asset('css/sales-rep/tokens.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin-purchases.css') ?>?v=5">
    <link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
</head>
<body>
<?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>
<div class="purchase-main">
    <header class="purchase-topbar">
        <div class="purchase-search-wrap">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></svg>
            <input class="purchase-search" type="search" placeholder="Search orders, suppliers..." aria-label="Search orders and suppliers" data-purchase-search>
        </div>
        <div class="purchase-topbar__spacer"></div>
        <a class="purchase-topbar__action" href="<?= url('admin/notifications') ?>" aria-label="Notifications">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>
        </a>
        <a class="purchase-topbar__action" href="<?= url('logout') ?>" aria-label="Sign out">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 7l-1.4 1.4 2.6 2.6H8v2h10.2l-2.6 2.6L17 17l5-5-5-5ZM4 5h8V3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8v-2H4V5Z"/></svg>
        </a>
    </header>

    <main class="purchase-content">
        <section class="purchase-page-head">
            <div>
                <h1>Purchase Management</h1>
                <p>Manage supplier orders, track shipments, and receive inventory.</p>
            </div>
            <div class="purchase-actions">
                <button class="purchase-button" type="button" aria-label="Filter purchase orders">
                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v2H3V5Zm3 6h12v2H6v-2Zm4 6h4v2h-4v-2Z"/></svg>
                    Filter
                </button>
                <button class="purchase-button purchase-button--primary" type="button" aria-label="Create purchase order">
                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></svg>
                    New P.O.
                </button>
            </div>
        </section>

        <section class="purchase-summary" aria-label="Purchase summary">
            <article class="purchase-stat"><div class="purchase-stat__top"><span class="purchase-stat__label">Pending Approval</span><span class="purchase-stat__icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 5h-2v6l5 3 1-1.7-4-2.3V7Z"/></svg></span></div><strong class="purchase-stat__value"><?= (int) ($summary['pendingApproval'] ?? 0) ?></strong><span class="purchase-stat__note">Purchase orders awaiting review</span></article>
            <article class="purchase-stat"><div class="purchase-stat__top"><span class="purchase-stat__label">In Transit</span><span class="purchase-stat__icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h11v4h4l3 4v6h-2a3 3 0 0 1-6 0H9a3 3 0 0 1-6 0H2V6a2 2 0 0 1 1-2Zm1 2v8.8A3 3 0 0 1 8.8 16H14V6H4Zm12 4v4h3v-1.4L17 10h-1Z"/></svg></span></div><strong class="purchase-stat__value"><?= (int) ($summary['inTransit'] ?? 0) ?></strong><span class="purchase-stat__note">Partially received shipments</span></article>
            <article class="purchase-stat"><div class="purchase-stat__top"><span class="purchase-stat__label">Received This Week</span><span class="purchase-stat__icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 16.2-4.2-4.2 1.4-1.4 2.8 2.8 7.8-7.8L18.2 7 9 16.2Z"/></svg></span></div><strong class="purchase-stat__value"><?= (int) ($summary['receivedThisWeek'] ?? 0) ?></strong><span class="purchase-stat__note"><?= (int) ($summary['fulfillmentRate'] ?? 0) ?>% fulfillment rate</span></article>
        </section>

        <section class="purchase-layout">
            <article class="purchase-panel">
                <div class="purchase-panel__head"><h2>Quick Draft PO</h2><p>Prepare a draft for later processing.</p></div>
                <div class="purchase-draft">
                    <div class="purchase-field"><label for="purchase-supplier">Supplier</label><select id="purchase-supplier"><option>Select a supplier...</option></select></div>
                    <div class="purchase-field"><label for="purchase-delivery">Expected Delivery</label><input id="purchase-delivery" type="date"></div>
                    <div class="purchase-field"><label for="purchase-item">Quick Add Item</label><div class="purchase-quick-add"><input id="purchase-item" placeholder="SKU or Name"><input id="purchase-quantity" type="number" min="1" placeholder="Qty"><button type="button" aria-label="Add item"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></svg></button></div></div>
                    <div class="purchase-empty-draft">No items added to draft yet.</div>
                </div>
            </article>

            <article class="purchase-panel">
                <div class="purchase-panel__head"><h2>Recent Purchase Orders</h2><p>Latest supplier orders from the purchasing ledger.</p></div>
                <div class="purchase-table-wrap">
                    <table class="purchase-table">
                        <thead><tr><th>PO Number</th><th>Supplier</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody data-purchase-rows>
                        <?php foreach ($orders as $order): ?>
                            <tr data-purchase-row data-order-id="<?= (int) $order['id'] ?>" data-search="<?= e(strtolower($order['po_number'] . ' ' . $order['supplier_name'])) ?>">
                                <td><div class="purchase-number"><?= e($order['po_number']) ?></div><div class="purchase-meta"><?= (int) $order['item_count'] ?> line item<?= (int) $order['item_count'] === 1 ? '' : 's' ?></div></td>
                                <td><?= e($order['supplier_name']) ?></td>
                                <td><?= e(date('M j, Y', strtotime($order['order_date']))) ?></td>
                                <td>Rs. <?= number_format((float) $order['total_amount'], 2) ?></td>
                                <td>
                                    <select class="purchase-status-select purchase-status--<?= e($order['status']) ?>" data-status-select <?= $isMock ? '' : '' ?> aria-label="Status for <?= e($order['po_number']) ?>">
                                        <?php foreach (['preparing' => 'Preparing', 'ready' => 'Ready', 'in_transit' => 'In Transit', 'received' => 'Received'] as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $order['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><button class="purchase-button" type="button" disabled>View</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$orders): ?><p class="purchase-empty">No purchase orders found.</p><?php endif; ?>
                </div>
            </article>
        </section>
    </main>
</div>
<script>window.PURCHASE_CONFIG = <?= json_encode(['csrfToken' => $csrfToken, 'baseUrl' => rtrim(url(), '/')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script>
(function () {
    const search = document.querySelector('[data-purchase-search]');
    if (!search) return;
    search.addEventListener('input', function () {
        const query = search.value.trim().toLowerCase();
        document.querySelectorAll('[data-purchase-row]').forEach(function (row) {
            row.hidden = query !== '' && !row.dataset.search.includes(query);
        });
    });

    document.querySelectorAll('[data-status-select]').forEach(function (select) {
        select.addEventListener('change', async function () {
            const row = select.closest('[data-purchase-row]');
            const previous = select.dataset.previous || select.value;
            const status = select.value;
            select.disabled = true;

            if (row.dataset.orderId === '0') {
                select.dataset.previous = status;
                select.className = 'purchase-status-select purchase-status--' + status;
                select.disabled = false;
                return;
            }

            try {
                const config = window.PURCHASE_CONFIG || {};
                const response = await fetch(config.baseUrl + '/admin/purchases/status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken || '' },
                    body: JSON.stringify({ csrf_token: config.csrfToken || '', order_id: Number(row.dataset.orderId), status: status })
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'Status update failed.');
                select.dataset.previous = status;
                select.className = 'purchase-status-select purchase-status--' + status;
            } catch (error) {
                select.value = previous;
                window.alert(error.message || 'Status update failed.');
            } finally {
                select.disabled = false;
            }
        });
    });
})();
</script>
</body>
</html>
