<?php
/** @var array $summary */
/** @var array $orders */
/** @var bool $isMock */
/** @var array $suppliers */
/** @var array $products */
/** @var string $csrfToken */
$statusLabel = static fn(string $status): string => ucwords(str_replace('_', ' ', $status));
$suppliers = $suppliers ?? [];
$products = $products ?? [];
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
    <link rel="stylesheet" href="<?= asset('css/admin-purchases.css') ?>?v=6">
    <link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
</head>
<body>
<?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>
<div class="purchase-main">
    <header class="purchase-topbar">
        <div class="purchase-search-wrap">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></svg>
            <input class="purchase-search" type="search" placeholder="Search orders, suppliers, PO#..." aria-label="Search orders and suppliers" data-purchase-search>
        </div>
        <div class="purchase-topbar__spacer"></div>
        <a class="purchase-topbar__action" href="<?= url('admin/notifications') ?>" aria-label="Notifications">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>
        </a>
        <a class="purchase-topbar__action" href="<?= url('profile') ?>" title="Edit Profile — <?= e($_SESSION['full_name'] ?? 'Admin') ?>" aria-label="Edit Profile">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </a>
    </header>

    <main class="purchase-content">
        <section class="purchase-page-head">
            <div>
                <h1>Purchase Management</h1>
                <p>Manage supplier orders, track shipments, and receive inventory.</p>
            </div>
            <div class="purchase-actions">
                <!-- Filter Dropdown Menu -->
                <div class="purchase-filter-menu">
                    <button class="purchase-button" type="button" id="purchase-filter-btn" aria-expanded="false" aria-controls="purchase-filter-dropdown" aria-label="Filter purchase orders">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v2H3V5Zm3 6h12v2H6v-2Zm4 6h4v2h-4v-2Z"/></svg>
                        <span>Filter</span>
                        <span class="purchase-filter-badge" id="purchase-filter-badge" style="display:none;">0</span>
                    </button>
                    <div class="purchase-filter-dropdown" id="purchase-filter-dropdown" hidden>
                        <div class="purchase-filter-dropdown__section">
                            <div class="purchase-filter-dropdown__label">Filter by Status</div>
                            <div class="purchase-filter-chips" id="status-filter-chips">
                                <button type="button" class="filter-chip active" data-status-filter="all">All</button>
                                <button type="button" class="filter-chip" data-status-filter="preparing">Preparing</button>
                                <button type="button" class="filter-chip" data-status-filter="ready">Ready</button>
                                <button type="button" class="filter-chip" data-status-filter="in_transit">In Transit</button>
                                <button type="button" class="filter-chip" data-status-filter="received">Received</button>
                            </div>
                        </div>
                        <div class="purchase-filter-dropdown__section">
                            <div class="purchase-filter-dropdown__label">Filter by Supplier</div>
                            <select class="purchase-filter-select" id="supplier-filter-select">
                                <option value="all">All Suppliers</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= e(strtolower($s['company_name'])) ?>"><?= e($s['company_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="purchase-filter-dropdown__foot">
                            <button type="button" class="purchase-filter-reset" id="reset-filters-btn">Reset Filters</button>
                        </div>
                    </div>
                </div>

                <!-- New PO Trigger Button -->
                <button class="purchase-button purchase-button--primary" type="button" id="open-new-po-modal" aria-label="Create purchase order">
                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></svg>
                    New P.O.
                </button>
            </div>
        </section>

        <section class="purchase-summary" aria-label="Purchase summary">
            <article class="purchase-stat">
                <div class="purchase-stat__top">
                    <span class="purchase-stat__label">Pending Approval</span>
                    <span class="purchase-stat__icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 5h-2v6l5 3 1-1.7-4-2.3V7Z"/></svg></span>
                </div>
                <strong class="purchase-stat__value" id="kpi-pending-approval"><?= (int) ($summary['pendingApproval'] ?? 0) ?></strong>
                <span class="purchase-stat__note">Purchase orders awaiting review</span>
            </article>
            <article class="purchase-stat">
                <div class="purchase-stat__top">
                    <span class="purchase-stat__label">In Transit</span>
                    <span class="purchase-stat__icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h11v4h4l3 4v6h-2a3 3 0 0 1-6 0H9a3 3 0 0 1-6 0H2V6a2 2 0 0 1 1-2Zm1 2v8.8A3 3 0 0 1 8.8 16H14V6H4Zm12 4v4h3v-1.4L17 10h-1Z"/></svg></span>
                </div>
                <strong class="purchase-stat__value" id="kpi-in-transit"><?= (int) ($summary['inTransit'] ?? 0) ?></strong>
                <span class="purchase-stat__note">Partially received shipments</span>
            </article>
            <article class="purchase-stat">
                <div class="purchase-stat__top">
                    <span class="purchase-stat__label">Received This Week</span>
                    <span class="purchase-stat__icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 16.2-4.2-4.2 1.4-1.4 2.8 2.8 7.8-7.8L18.2 7 9 16.2Z"/></svg></span>
                </div>
                <strong class="purchase-stat__value" id="kpi-received-week"><?= (int) ($summary['receivedThisWeek'] ?? 0) ?></strong>
                <span class="purchase-stat__note"><span id="kpi-fulfillment-rate"><?= (int) ($summary['fulfillmentRate'] ?? 0) ?></span>% fulfillment rate</span>
            </article>
        </section>

        <section class="purchase-layout">
            <!-- Quick Draft PO Panel -->
            <article class="purchase-panel">
                <div class="purchase-panel__head">
                    <h2>Quick Draft PO</h2>
                    <p>Prepare a draft for later processing or convert to full P.O.</p>
                </div>
                <div class="purchase-draft">
                    <div class="purchase-field">
                        <label for="purchase-supplier">Supplier</label>
                        <select id="purchase-supplier">
                            <option value="">Select a supplier...</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e($s['company_name']) ?> (<?= e($s['supplier_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="purchase-field">
                        <label for="purchase-delivery">Expected Delivery</label>
                        <input id="purchase-delivery" type="date" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="purchase-field">
                        <label for="purchase-item">Quick Add Item</label>
                        <div class="purchase-quick-add">
                            <select id="purchase-item" aria-label="Select product">
                                <option value="">Choose product...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>" data-name="<?= e($p['name']) ?>" data-code="<?= e($p['product_code']) ?>" data-cost="<?= (float) $p['cost_price'] ?>">
                                        <?= e($p['product_code']) ?> - <?= e($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input id="purchase-item-cost" type="number" step="0.01" min="0" placeholder="Rs. Cost" aria-label="Unit cost">
                            <input id="purchase-quantity" type="number" min="1" value="1" placeholder="Qty" aria-label="Quantity">
                            <button type="button" id="quick-add-btn" aria-label="Add item to draft" title="Add item to draft">
                                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div id="quick-draft-items-wrap">
                        <div class="purchase-empty-draft" id="quick-draft-empty">No items added to draft yet.</div>
                        <ul id="quick-draft-list" style="display:none;list-style:none;padding:0;margin:0 0 16px;display:flex;flex-direction:column;gap:8px;"></ul>
                    </div>
                    <div id="quick-draft-actions" style="display:none;margin-top:14px;padding-top:12px;border-top:1px solid var(--sales-outline-variant);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <span style="font-size:13px;font-weight:600;color:var(--sales-on-surface-variant);">Draft Total:</span>
                            <strong style="font-size:16px;color:var(--sales-primary);" id="quick-draft-total">Rs. 0.00</strong>
                        </div>
                        <button type="button" class="purchase-button purchase-button--primary" id="open-modal-with-draft" style="width:100%;justify-content:center;">
                            Finalize in Full P.O. Modal &rarr;
                        </button>
                    </div>
                </div>
            </article>

            <!-- Recent Purchase Orders Table -->
            <article class="purchase-panel">
                <div class="purchase-panel__head" style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h2>Recent Purchase Orders</h2>
                        <p>Latest supplier orders from the purchasing ledger.</p>
                    </div>
                    <div style="font-size:12.5px;color:var(--sales-on-surface-variant);" id="po-table-counter">
                        <?= count($orders) ?> order<?= count($orders) === 1 ? '' : 's' ?>
                    </div>
                </div>
                <div class="purchase-table-wrap">
                    <table class="purchase-table">
                        <thead><tr><th>PO Number</th><th>Supplier</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody data-purchase-rows id="purchase-table-body">
                        <?php foreach ($orders as $order): ?>
                            <tr data-purchase-row data-order-id="<?= (int) $order['id'] ?>" data-status="<?= e(strtolower($order['status'])) ?>" data-supplier="<?= e(strtolower($order['supplier_name'])) ?>" data-search="<?= e(strtolower($order['po_number'] . ' ' . $order['supplier_name'])) ?>">
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
                                <td><button class="purchase-button" type="button" data-view-order="<?= (int) $order['id'] ?>">View</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="purchase-empty" id="purchase-table-empty" style="<?= empty($orders) ? '' : 'display:none;' ?>">
                        No purchase orders found matching your criteria.
                    </div>
                </div>
            </article>
        </section>
    </main>
</div>

<!-- ========================================================================= -->
<!-- Create Purchase Order Modal -->
<!-- ========================================================================= -->
<dialog class="purchase-modal" id="new-po-modal" aria-labelledby="new-po-modal-title">
    <div class="purchase-modal__backdrop" data-close-po-modal></div>
    <div class="purchase-modal__panel" role="dialog" aria-modal="true">
        <div class="purchase-modal__head">
            <div>
                <p class="sales-eyebrow" style="margin:0 0 4px;font-size:11px;font-weight:700;color:var(--sales-primary);text-transform:uppercase;letter-spacing:.05em;">Procurement &amp; Receiving</p>
                <h2 id="new-po-modal-title" style="margin:0;font-size:20px;color:var(--sales-on-surface);">New Purchase Order</h2>
            </div>
            <button class="purchase-modal__close" type="button" data-close-po-modal aria-label="Close dialog">&times;</button>
        </div>
        <form id="new-po-form" novalidate>
            <div class="purchase-modal__body">
                <!-- Supplier & Dates -->
                <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:14px;margin-bottom:14px;" class="po-form-grid">
                    <div class="purchase-field" style="margin:0;">
                        <label for="po-modal-supplier">Supplier <span style="color:#dc2626;">*</span></label>
                        <select id="po-modal-supplier" required>
                            <option value="">Select Supplier...</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e($s['company_name']) ?> (<?= e($s['supplier_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <span class="field-error" id="po-err-supplier" style="color:#dc2626;font-size:11.5px;display:none;margin-top:2px;">Please select a supplier.</span>
                    </div>
                    <div class="purchase-field" style="margin:0;">
                        <label for="po-modal-order-date">Order Date <span style="color:#dc2626;">*</span></label>
                        <input type="date" id="po-modal-order-date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="purchase-field" style="margin:0;">
                        <label for="po-modal-expected-date">Expected Delivery</label>
                        <input type="date" id="po-modal-expected-date" min="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="purchase-field" style="margin-bottom:18px;">
                    <label for="po-modal-notes">Notes / Reference Note</label>
                    <input type="text" id="po-modal-notes" placeholder="e.g. Urgent stock replenishment, container reference, etc.">
                </div>

                <!-- Line Items Section -->
                <div class="po-items-section" style="border:1px solid var(--sales-outline-variant);border-radius:var(--sales-radius-md);padding:16px;background:var(--sales-surface-low);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <h3 style="margin:0;font-size:13.5px;font-weight:700;color:var(--sales-on-surface);text-transform:uppercase;letter-spacing:.04em;">Order Line Items</h3>
                        <span style="font-size:12px;color:var(--sales-on-surface-variant);" id="po-item-count-label">0 items added</span>
                    </div>

                    <!-- Add Line Item Row -->
                    <div class="po-add-row" style="display:grid;grid-template-columns:2fr 90px 120px 90px;gap:10px;align-items:flex-end;margin-bottom:12px;">
                        <div class="purchase-field" style="margin:0;">
                            <label for="po-item-product" style="font-size:12px;">Product <span style="color:#dc2626;">*</span></label>
                            <select id="po-item-product">
                                <option value="">Select a product...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>" data-code="<?= e($p['product_code']) ?>" data-cost="<?= (float) $p['cost_price'] ?>" data-name="<?= e($p['name']) ?>">
                                        <?= e($p['product_code']) ?> &ndash; <?= e($p['name']) ?> (Rs. <?= number_format((float) $p['cost_price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="purchase-field" style="margin:0;">
                            <label for="po-item-qty" style="font-size:12px;">Qty <span style="color:#dc2626;">*</span></label>
                            <input type="number" id="po-item-qty" min="1" step="1" value="1" placeholder="Qty">
                        </div>
                        <div class="purchase-field" style="margin:0;">
                            <label for="po-item-cost" style="font-size:12px;">Unit Cost (Rs.) <span style="color:#dc2626;">*</span></label>
                            <input type="number" id="po-item-cost" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <button type="button" class="purchase-button purchase-button--primary" id="po-add-item-btn" style="height:42px;justify-content:center;cursor:pointer;">
                            + Add
                        </button>
                    </div>
                    <span class="field-error" id="po-err-items" style="color:#dc2626;font-size:11.5px;display:none;margin-bottom:8px;">Please add at least one line item to the order.</span>

                    <!-- Line Items Table -->
                    <div style="background:var(--sales-surface-lowest);border:1px solid var(--sales-outline-variant);border-radius:8px;overflow:hidden;max-height:220px;overflow-y:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                            <thead>
                                <tr style="background:var(--sales-surface-container);color:var(--sales-on-surface-variant);font-size:11px;text-transform:uppercase;">
                                    <th style="padding:9px 12px;">Product</th>
                                    <th style="padding:9px 12px;text-align:center;">Qty</th>
                                    <th style="padding:9px 12px;text-align:right;">Unit Cost</th>
                                    <th style="padding:9px 12px;text-align:right;">Line Total</th>
                                    <th style="padding:9px 12px;text-align:center;width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="po-modal-items-tbody">
                                <tr id="po-items-empty-row">
                                    <td colspan="5" style="padding:24px;text-align:center;color:var(--sales-on-surface-variant);font-size:12.5px;">
                                        No items added yet. Select a product above and click <strong>+ Add</strong>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Totals -->
                    <div style="display:flex;justify-content:flex-end;margin-top:14px;padding-top:12px;border-top:1px solid var(--sales-outline-variant);gap:24px;align-items:baseline;">
                        <div style="font-size:13px;color:var(--sales-on-surface-variant);">Total Order Value:</div>
                        <strong style="font-size:22px;color:var(--sales-primary);" id="po-modal-total-display">Rs. 0.00</strong>
                    </div>
                </div>
            </div>
            <div class="purchase-modal__actions">
                <button type="button" class="purchase-button" data-close-po-modal>Cancel</button>
                <button type="submit" class="purchase-button purchase-button--primary" id="po-modal-submit-btn">Create Purchase Order</button>
            </div>
        </form>
    </div>
</dialog>

<!-- ========================================================================= -->
<!-- View Purchase Order Details Modal -->
<!-- ========================================================================= -->
<dialog class="purchase-modal" id="view-po-modal" aria-labelledby="view-po-modal-title">
    <div class="purchase-modal__backdrop" data-close-view-modal></div>
    <div class="purchase-modal__panel" role="dialog" aria-modal="true" style="width:min(840px, 96vw);">
        <div class="purchase-modal__head">
            <div>
                <p class="sales-eyebrow" style="margin:0 0 4px;font-size:11px;font-weight:700;color:var(--sales-primary);text-transform:uppercase;letter-spacing:.05em;">Order Details</p>
                <h2 id="view-po-modal-title" style="margin:0;font-size:20px;color:var(--sales-on-surface);">Purchase Order</h2>
            </div>
            <button class="purchase-modal__close" type="button" data-close-view-modal aria-label="Close dialog">&times;</button>
        </div>
        <div class="purchase-modal__body" id="view-po-body">
            <div style="text-align:center;padding:32px;color:var(--sales-on-surface-variant);">Loading order details...</div>
        </div>
        <div class="purchase-modal__actions">
            <button type="button" class="purchase-button" data-close-view-modal>Close</button>
        </div>
    </div>
</dialog>

<script src="<?= asset('js/validation.js') ?>"></script>
<script>
window.PURCHASE_CONFIG = <?= json_encode([
    'csrfToken' => $csrfToken,
    'baseUrl'   => rtrim(url(), '/'),
    'suppliers' => $suppliers,
    'products'  => $products,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script>
(function () {
    'use strict';

    const config = window.PURCHASE_CONFIG || {};
    const csrfToken = config.csrfToken || '';
    const baseUrl = config.baseUrl || '';

    // =========================================================================
    // Toast Notification Utility
    // =========================================================================
    function showToast(message, type = 'success') {
        const existing = document.querySelector('.purchase-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'purchase-toast purchase-toast--' + type;
        toast.innerHTML = (type === 'success'
            ? '<svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="currentColor"><path d="m9 16.2-4.2-4.2 1.4-1.4 2.8 2.8 7.8-7.8L18.2 7 9 16.2Z"/></svg>'
            : '<svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>')
            + '<span>' + message + '</span>';
        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity .3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3500);
    }

    // =========================================================================
    // Filter State & Functionality
    // =========================================================================
    const filterState = {
        query: '',
        status: 'all',
        supplier: 'all'
    };

    const filterBtn = document.getElementById('purchase-filter-btn');
    const filterDropdown = document.getElementById('purchase-filter-dropdown');
    const filterBadge = document.getElementById('purchase-filter-badge');
    const statusChips = document.getElementById('status-filter-chips');
    const supplierFilterSelect = document.getElementById('supplier-filter-select');
    const resetFiltersBtn = document.getElementById('reset-filters-btn');
    const searchInput = document.querySelector('[data-purchase-search]');
    const tableBody = document.getElementById('purchase-table-body');
    const emptyRow = document.getElementById('purchase-table-empty');
    const tableCounter = document.getElementById('po-table-counter');

    function updateFilterBadge() {
        let count = 0;
        if (filterState.status !== 'all') count++;
        if (filterState.supplier !== 'all') count++;

        if (count > 0) {
            filterBadge.textContent = count;
            filterBadge.style.display = 'inline-block';
        } else {
            filterBadge.style.display = 'none';
        }
    }

    function applyFilters() {
        const rows = document.querySelectorAll('[data-purchase-row]');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = (row.dataset.search || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();
            const rowSupplier = (row.dataset.supplier || '').toLowerCase();

            const matchesQuery = filterState.query === '' || rowSearch.includes(filterState.query);
            const matchesStatus = filterState.status === 'all' || rowStatus === filterState.status;
            const matchesSupplier = filterState.supplier === 'all' || rowSupplier === filterState.supplier;

            const isVisible = matchesQuery && matchesStatus && matchesSupplier;
            row.hidden = !isVisible;
            if (isVisible) visibleCount++;
        });

        if (emptyRow) {
            emptyRow.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        if (tableCounter) {
            tableCounter.textContent = visibleCount + ' order' + (visibleCount === 1 ? '' : 's');
        }
        updateFilterBadge();
    }

    if (filterBtn && filterDropdown) {
        filterBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isHidden = filterDropdown.hidden;
            filterDropdown.hidden = !isHidden;
            filterBtn.setAttribute('aria-expanded', String(isHidden));
        });

        document.addEventListener('click', function (e) {
            if (!filterDropdown.contains(e.target) && e.target !== filterBtn && !filterBtn.contains(e.target)) {
                filterDropdown.hidden = true;
                filterBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (statusChips) {
        statusChips.addEventListener('click', function (e) {
            const chip = e.target.closest('[data-status-filter]');
            if (!chip) return;
            statusChips.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            filterState.status = chip.dataset.statusFilter;
            applyFilters();
        });
    }

    if (supplierFilterSelect) {
        supplierFilterSelect.addEventListener('change', function () {
            filterState.supplier = this.value;
            applyFilters();
        });
    }

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function () {
            filterState.status = 'all';
            filterState.supplier = 'all';
            if (statusChips) {
                statusChips.querySelectorAll('.filter-chip').forEach(c => {
                    c.classList.toggle('active', c.dataset.statusFilter === 'all');
                });
            }
            if (supplierFilterSelect) supplierFilterSelect.value = 'all';
            applyFilters();
            if (filterDropdown) filterDropdown.hidden = true;
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterState.query = this.value.trim().toLowerCase();
            applyFilters();
        });
    }

    // =========================================================================
    // Status Select Changes on Rows
    // =========================================================================
    function bindStatusSelect(select) {
        select.addEventListener('change', async function () {
            const row = select.closest('[data-purchase-row]');
            const previous = select.dataset.previous || select.value;
            const status = select.value;
            select.disabled = true;

            if (row.dataset.orderId === '0') {
                select.dataset.previous = status;
                select.className = 'purchase-status-select purchase-status--' + status;
                row.dataset.status = status;
                select.disabled = false;
                applyFilters();
                return;
            }

            try {
                const response = await fetch(baseUrl + '/admin/purchases/status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ csrf_token: csrfToken, order_id: Number(row.dataset.orderId), status: status })
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'Status update failed.');

                select.dataset.previous = status;
                select.className = 'purchase-status-select purchase-status--' + status;
                row.dataset.status = status;
                showToast('Status updated to ' + status.replace('_', ' ') + '.');

                if (result.summary) {
                    updateSummaryKpis(result.summary);
                }
                applyFilters();
            } catch (error) {
                select.value = previous;
                alert(error.message || 'Status update failed.');
            } finally {
                select.disabled = false;
            }
        });
    }

    document.querySelectorAll('[data-status-select]').forEach(bindStatusSelect);

    function updateSummaryKpis(s) {
        if (!s) return;
        if (s.pendingApproval !== undefined) {
            const el = document.getElementById('kpi-pending-approval');
            if (el) el.textContent = String(s.pendingApproval);
        }
        if (s.inTransit !== undefined) {
            const el = document.getElementById('kpi-in-transit');
            if (el) el.textContent = String(s.inTransit);
        }
        if (s.receivedThisWeek !== undefined) {
            const el = document.getElementById('kpi-received-week');
            if (el) el.textContent = String(s.receivedThisWeek);
        }
        if (s.fulfillmentRate !== undefined) {
            const el = document.getElementById('kpi-fulfillment-rate');
            if (el) el.textContent = String(s.fulfillmentRate);
        }
    }

    // =========================================================================
    // New Purchase Order Modal
    // =========================================================================
    const poModal = document.getElementById('new-po-modal');
    const openPoBtn = document.getElementById('open-new-po-modal');
    const poForm = document.getElementById('new-po-form');
    const poSupplier = document.getElementById('po-modal-supplier');
    const poOrderDate = document.getElementById('po-modal-order-date');
    const poExpectedDate = document.getElementById('po-modal-expected-date');
    const poNotes = document.getElementById('po-modal-notes');
    const poProductSelect = document.getElementById('po-item-product');
    const poQtyInput = document.getElementById('po-item-qty');
    const poCostInput = document.getElementById('po-item-cost');
    const poAddItemBtn = document.getElementById('po-add-item-btn');
    const poItemsTbody = document.getElementById('po-modal-items-tbody');
    const poEmptyRow = document.getElementById('po-items-empty-row');
    const poTotalDisplay = document.getElementById('po-modal-total-display');
    const poItemCountLabel = document.getElementById('po-item-count-label');
    const poSubmitBtn = document.getElementById('po-modal-submit-btn');

    let modalItems = [];

    function openNewPoModal(initialItems = [], initialSupplierId = '') {
        modalItems = [...initialItems];
        renderModalItems();
        if (initialSupplierId) {
            poSupplier.value = String(initialSupplierId);
        } else {
            poSupplier.value = '';
        }
        poOrderDate.value = new Date().toISOString().split('T')[0];
        poExpectedDate.value = '';
        poNotes.value = '';
        document.getElementById('po-err-supplier').style.display = 'none';
        document.getElementById('po-err-items').style.display = 'none';

        if (poModal.showModal) {
            poModal.showModal();
        } else {
            poModal.setAttribute('open', '');
        }
    }

    function closeNewPoModal() {
        if (poModal.close) {
            poModal.close();
        } else {
            poModal.removeAttribute('open');
        }
    }

    if (openPoBtn) {
        openPoBtn.addEventListener('click', () => openNewPoModal());
    }

    document.querySelectorAll('[data-close-po-modal]').forEach(b => {
        b.addEventListener('click', closeNewPoModal);
    });

    if (poProductSelect) {
        poProductSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.cost) {
                poCostInput.value = parseFloat(opt.dataset.cost).toFixed(2);
            } else {
                poCostInput.value = '';
            }
        });
    }

    if (poAddItemBtn) {
        poAddItemBtn.addEventListener('click', function () {
            const prodId = Number(poProductSelect.value);
            const qty = parseInt(poQtyInput.value, 10);
            const cost = parseFloat(poCostInput.value);

            if (!prodId || isNaN(prodId)) {
                alert('Please select a valid product.');
                poProductSelect.focus();
                return;
            }
            if (!qty || qty <= 0) {
                alert('Please enter a quantity greater than zero.');
                poQtyInput.focus();
                return;
            }
            if (isNaN(cost) || cost < 0) {
                alert('Please enter a valid unit cost.');
                poCostInput.focus();
                return;
            }

            const opt = poProductSelect.options[poProductSelect.selectedIndex];
            const name = opt.dataset.name || opt.text;
            const code = opt.dataset.code || '';

            // Check if already in items
            const existing = modalItems.find(i => Number(i.product_id) === prodId);
            if (existing) {
                existing.quantity += qty;
                existing.unit_cost = cost;
                existing.line_total = existing.quantity * cost;
            } else {
                modalItems.push({
                    product_id: prodId,
                    name: name,
                    product_code: code,
                    quantity: qty,
                    unit_cost: cost,
                    line_total: qty * cost
                });
            }

            // Reset add row
            poProductSelect.value = '';
            poQtyInput.value = '1';
            poCostInput.value = '';
            document.getElementById('po-err-items').style.display = 'none';

            renderModalItems();
        });
    }

    function renderModalItems() {
        poItemsTbody.innerHTML = '';
        if (modalItems.length === 0) {
            poItemsTbody.appendChild(poEmptyRow);
            poTotalDisplay.textContent = 'Rs. 0.00';
            poItemCountLabel.textContent = '0 items added';
            return;
        }

        let total = 0;
        modalItems.forEach((itm, idx) => {
            const lineTotal = itm.quantity * itm.unit_cost;
            total += lineTotal;

            const tr = document.createElement('tr');
            tr.style.borderTop = '1px solid var(--sales-outline-variant)';
            tr.innerHTML = `
                <td style="padding:10px 12px;">
                    <div style="font-weight:600;color:var(--sales-on-surface);">${escapeHtml(itm.name)}</div>
                    <div style="font-size:11px;color:var(--sales-on-surface-variant);font-family:monospace;">${escapeHtml(itm.product_code || '')}</div>
                </td>
                <td style="padding:10px 12px;text-align:center;">
                    <input type="number" min="1" step="1" value="${itm.quantity}" data-item-idx="${idx}" class="item-qty-edit" style="width:65px;padding:4px 6px;border:1px solid var(--sales-outline-variant);border-radius:4px;text-align:center;font-size:12.5px;">
                </td>
                <td style="padding:10px 12px;text-align:right;">
                    Rs. ${Number(itm.unit_cost).toFixed(2)}
                </td>
                <td style="padding:10px 12px;text-align:right;font-weight:600;color:var(--sales-on-surface);">
                    Rs. ${lineTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </td>
                <td style="padding:10px 12px;text-align:center;">
                    <button type="button" data-remove-item="${idx}" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:16px;padding:4px 6px;line-height:1;" title="Remove line item">&times;</button>
                </td>
            `;
            poItemsTbody.appendChild(tr);
        });

        poTotalDisplay.textContent = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        poItemCountLabel.textContent = modalItems.length + ' item' + (modalItems.length === 1 ? '' : 's') + ' added';

        // Bind remove & edit qty
        poItemsTbody.querySelectorAll('[data-remove-item]').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.dataset.removeItem);
                modalItems.splice(idx, 1);
                renderModalItems();
            });
        });

        poItemsTbody.querySelectorAll('.item-qty-edit').forEach(inp => {
            inp.addEventListener('change', function () {
                const idx = Number(this.dataset.itemIdx);
                const val = parseInt(this.value, 10);
                if (val > 0) {
                    modalItems[idx].quantity = val;
                    modalItems[idx].line_total = val * modalItems[idx].unit_cost;
                    renderModalItems();
                } else {
                    this.value = modalItems[idx].quantity;
                }
            });
        });
    }

    if (poForm) {
        poForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const supplierId = poSupplier.value;
            const supplierError = document.getElementById('po-err-supplier');
            const itemsError = document.getElementById('po-err-items');

            let hasError = false;
            if (!supplierId) {
                supplierError.style.display = 'block';
                hasError = true;
            } else {
                supplierError.style.display = 'none';
            }

            if (modalItems.length === 0) {
                itemsError.style.display = 'block';
                hasError = true;
            } else {
                itemsError.style.display = 'none';
            }

            if (hasError) return;

            poSubmitBtn.disabled = true;
            poSubmitBtn.textContent = 'Creating Purchase Order...';

            try {
                const payload = {
                    csrf_token: csrfToken,
                    supplier_id: Number(supplierId),
                    order_date: poOrderDate.value,
                    expected_date: poExpectedDate.value || null,
                    notes: poNotes.value.trim(),
                    status: 'pending',
                    items: modalItems.map(i => ({
                        product_id: i.product_id,
                        quantity: i.quantity,
                        unit_cost: i.unit_cost
                    }))
                };

                const res = await fetch(baseUrl + '/admin/purchases/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (!res.ok || !data.ok) {
                    throw new Error(data.message || 'Failed to create purchase order.');
                }

                showToast(data.message || 'Purchase Order created successfully!');
                closeNewPoModal();

                // Add to table
                if (data.order) {
                    prependOrderRow(data.order);
                }
                if (data.summary) {
                    updateSummaryKpis(data.summary);
                }
                // Clear quick draft if it was transferred
                resetQuickDraft();
            } catch (err) {
                alert(err.message || 'An error occurred while creating the purchase order.');
            } finally {
                poSubmitBtn.disabled = false;
                poSubmitBtn.textContent = 'Create Purchase Order';
            }
        });
    }

    function prependOrderRow(order) {
        if (!tableBody) return;

        // If table had mock orders with ID 0, or empty message, clean it
        const emptyMsg = document.getElementById('purchase-table-empty');
        if (emptyMsg) emptyMsg.style.display = 'none';

        const row = document.createElement('tr');
        row.dataset.purchaseRow = '';
        row.dataset.orderId = String(order.id);
        row.dataset.status = (order.status || 'preparing').toLowerCase();
        row.dataset.supplier = (order.supplier_name || '').toLowerCase();
        row.dataset.search = (order.po_number + ' ' + order.supplier_name).toLowerCase();

        const formattedDate = new Date(order.order_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const formattedTotal = Number(order.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        row.innerHTML = `
            <td>
                <div class="purchase-number">${escapeHtml(order.po_number)}</div>
                <div class="purchase-meta">${order.item_count} line item${order.item_count === 1 ? '' : 's'}</div>
            </td>
            <td>${escapeHtml(order.supplier_name)}</td>
            <td>${formattedDate}</td>
            <td>Rs. ${formattedTotal}</td>
            <td>
                <select class="purchase-status-select purchase-status--${order.status}" data-status-select aria-label="Status for ${escapeHtml(order.po_number)}">
                    <option value="preparing" ${order.status === 'preparing' ? 'selected' : ''}>Preparing</option>
                    <option value="ready" ${order.status === 'ready' ? 'selected' : ''}>Ready</option>
                    <option value="in_transit" ${order.status === 'in_transit' ? 'selected' : ''}>In Transit</option>
                    <option value="received" ${order.status === 'received' ? 'selected' : ''}>Received</option>
                </select>
            </td>
            <td><button class="purchase-button" type="button" data-view-order="${order.id}">View</button></td>
        `;

        tableBody.prepend(row);
        const sel = row.querySelector('[data-status-select]');
        if (sel) bindStatusSelect(sel);
        const viewBtn = row.querySelector('[data-view-order]');
        if (viewBtn) bindViewButton(viewBtn);

        applyFilters();
    }

    // =========================================================================
    // View Purchase Order Modal
    // =========================================================================
    const viewModal = document.getElementById('view-po-modal');
    const viewModalBody = document.getElementById('view-po-body');
    const viewModalTitle = document.getElementById('view-po-modal-title');

    function bindViewButton(btn) {
        btn.addEventListener('click', async function () {
            const orderId = Number(this.dataset.viewOrder);
            if (!orderId) {
                // Mock order
                const row = btn.closest('[data-purchase-row]');
                const poNum = row.querySelector('.purchase-number')?.textContent || 'PO';
                const supplier = row.cells[1]?.textContent || '';
                const total = row.cells[3]?.textContent || '';
                showMockOrderDetails(poNum, supplier, total);
                return;
            }

            if (viewModal.showModal) viewModal.showModal();
            else viewModal.setAttribute('open', '');
            viewModalBody.innerHTML = '<div style="text-align:center;padding:36px;color:var(--sales-on-surface-variant);">Loading purchase order details...</div>';

            try {
                const res = await fetch(baseUrl + '/admin/purchases/view?id=' + orderId);
                const data = await res.json();
                if (!res.ok || !data.ok || !data.order) {
                    throw new Error(data.message || 'Could not load order details.');
                }
                renderOrderDetails(data.order);
            } catch (err) {
                viewModalBody.innerHTML = '<div style="padding:24px;color:#dc2626;text-align:center;">Failed to load order: ' + escapeHtml(err.message) + '</div>';
            }
        });
    }

    document.querySelectorAll('[data-view-order]').forEach(bindViewButton);

    function showMockOrderDetails(poNum, supplier, total) {
        if (viewModal.showModal) viewModal.showModal();
        else viewModal.setAttribute('open', '');
        viewModalTitle.textContent = poNum + ' Details';
        viewModalBody.innerHTML = `
            <div style="background:var(--sales-surface-low);padding:16px;border-radius:8px;margin-bottom:18px;border:1px solid var(--sales-outline-variant);">
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:12px;font-size:13px;">
                    <div><span style="color:var(--sales-on-surface-variant);display:block;font-size:11px;text-transform:uppercase;">Supplier</span><strong>${escapeHtml(supplier)}</strong></div>
                    <div><span style="color:var(--sales-on-surface-variant);display:block;font-size:11px;text-transform:uppercase;">Order Total</span><strong style="color:var(--sales-primary);">${escapeHtml(total)}</strong></div>
                    <div><span style="color:var(--sales-on-surface-variant);display:block;font-size:11px;text-transform:uppercase;">Source</span><span>Historical Record</span></div>
                </div>
            </div>
            <p style="color:var(--sales-on-surface-variant);font-size:13px;text-align:center;">Line items breakdown is archived for historical demo records.</p>
        `;
    }

    function renderOrderDetails(order) {
        viewModalTitle.textContent = order.po_number + ' Details';
        const formattedTotal = Number(order.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const items = order.items || [];

        let itemsRows = '';
        if (items.length === 0) {
            itemsRows = '<tr><td colspan="5" style="padding:16px;text-align:center;color:var(--sales-on-surface-variant);">No line items found.</td></tr>';
        } else {
            items.forEach(itm => {
                const lineTotal = Number(itm.line_total || (itm.quantity_ordered * itm.unit_cost)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                itemsRows += `
                    <tr style="border-top:1px solid var(--sales-outline-variant);">
                        <td style="padding:10px 14px;">
                            <div style="font-weight:600;color:var(--sales-on-surface);">${escapeHtml(itm.product_name)}</div>
                            <div style="font-size:11px;color:var(--sales-on-surface-variant);font-family:monospace;">${escapeHtml(itm.product_code || '')}</div>
                        </td>
                        <td style="padding:10px 14px;text-align:center;">${itm.quantity_ordered}</td>
                        <td style="padding:10px 14px;text-align:center;color:#059669;font-weight:600;">${itm.quantity_received}</td>
                        <td style="padding:10px 14px;text-align:right;">Rs. ${Number(itm.unit_cost).toFixed(2)}</td>
                        <td style="padding:10px 14px;text-align:right;font-weight:600;">Rs. ${lineTotal}</td>
                    </tr>
                `;
            });
        }

        viewModalBody.innerHTML = `
            <div style="background:var(--sales-surface-low);padding:16px 20px;border-radius:10px;margin-bottom:20px;border:1px solid var(--sales-outline-variant);">
                <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:16px;font-size:13px;">
                    <div><span style="color:var(--sales-on-surface-variant);display:block;font-size:11px;text-transform:uppercase;font-weight:600;">Supplier</span><strong>${escapeHtml(order.supplier_name)}</strong></div>
                    <div><span style="color:var(--sales-on-surface-variant);display:block;font-size:11px;text-transform:uppercase;font-weight:600;">Order Date</span><span>${order.order_date}</span></div>
                    <div><span style="color:var(--sales-on-surface-variant);display:block;font-size:11px;text-transform:uppercase;font-weight:600;">Expected Delivery</span><span>${order.expected_date || 'Not specified'}</span></div>
                    <div><span style="color:var(--sales-on-surface-variant);display:block;font-size:11px;text-transform:uppercase;font-weight:600;">Status</span><span class="purchase-status purchase-status--${order.status}">${order.status}</span></div>
                </div>
                ${order.notes ? `<div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--sales-outline-variant);font-size:12.5px;color:var(--sales-on-surface);"><span style="color:var(--sales-on-surface-variant);font-weight:600;">Notes: </span>${escapeHtml(order.notes)}</div>` : ''}
            </div>

            <h3 style="font-size:13.5px;font-weight:700;color:var(--sales-on-surface);text-transform:uppercase;letter-spacing:.04em;margin:0 0 10px;">Ordered Line Items (${items.length})</h3>
            <div style="border:1px solid var(--sales-outline-variant);border-radius:8px;overflow:hidden;margin-bottom:16px;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                    <thead>
                        <tr style="background:var(--sales-surface-container);color:var(--sales-on-surface-variant);font-size:11px;text-transform:uppercase;">
                            <th style="padding:10px 14px;">Product</th>
                            <th style="padding:10px 14px;text-align:center;">Ordered</th>
                            <th style="padding:10px 14px;text-align:center;">Received</th>
                            <th style="padding:10px 14px;text-align:right;">Unit Cost</th>
                            <th style="padding:10px 14px;text-align:right;">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>${itemsRows}</tbody>
                </table>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:24px;align-items:baseline;padding:12px 16px;background:var(--sales-surface-low);border-radius:8px;border:1px solid var(--sales-outline-variant);">
                <span style="font-size:13px;color:var(--sales-on-surface-variant);">Grand Total:</span>
                <strong style="font-size:22px;color:var(--sales-primary);">Rs. ${formattedTotal}</strong>
            </div>
        `;
    }

    document.querySelectorAll('[data-close-view-modal]').forEach(b => {
        b.addEventListener('click', () => {
            if (viewModal.close) viewModal.close();
            else viewModal.removeAttribute('open');
        });
    });

    // =========================================================================
    // Quick Draft PO Panel Logic
    // =========================================================================
    let quickDraftItems = [];
    const quickSupplier = document.getElementById('purchase-supplier');
    const quickItemSelect = document.getElementById('purchase-item');
    const quickCostInput = document.getElementById('purchase-item-cost');
    const quickQtyInput = document.getElementById('purchase-quantity');
    const quickAddBtn = document.getElementById('quick-add-btn');
    const quickEmpty = document.getElementById('quick-draft-empty');
    const quickList = document.getElementById('quick-draft-list');
    const quickActions = document.getElementById('quick-draft-actions');
    const quickTotal = document.getElementById('quick-draft-total');
    const openModalWithDraftBtn = document.getElementById('open-modal-with-draft');

    if (quickItemSelect) {
        quickItemSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.cost) {
                quickCostInput.value = parseFloat(opt.dataset.cost).toFixed(2);
            } else {
                quickCostInput.value = '';
            }
        });
    }

    if (quickAddBtn) {
        quickAddBtn.addEventListener('click', function () {
            const prodId = Number(quickItemSelect.value);
            const qty = parseInt(quickQtyInput.value, 10);
            const cost = parseFloat(quickCostInput.value);

            if (!prodId || isNaN(prodId)) {
                alert('Please select a product.');
                quickItemSelect.focus();
                return;
            }
            if (!qty || qty <= 0) {
                alert('Quantity must be greater than zero.');
                quickQtyInput.focus();
                return;
            }
            if (isNaN(cost) || cost < 0) {
                alert('Please enter a valid cost.');
                quickCostInput.focus();
                return;
            }

            const opt = quickItemSelect.options[quickItemSelect.selectedIndex];
            const name = opt.dataset.name || opt.text;
            const code = opt.dataset.code || '';

            const existing = quickDraftItems.find(i => Number(i.product_id) === prodId);
            if (existing) {
                existing.quantity += qty;
                existing.unit_cost = cost;
                existing.line_total = existing.quantity * cost;
            } else {
                quickDraftItems.push({
                    product_id: prodId,
                    name: name,
                    product_code: code,
                    quantity: qty,
                    unit_cost: cost,
                    line_total: qty * cost
                });
            }

            quickItemSelect.value = '';
            quickQtyInput.value = '1';
            quickCostInput.value = '';

            renderQuickDraft();
        });
    }

    function renderQuickDraft() {
        if (!quickList) return;
        quickList.innerHTML = '';

        if (quickDraftItems.length === 0) {
            quickEmpty.style.display = 'grid';
            quickList.style.display = 'none';
            quickActions.style.display = 'none';
            return;
        }

        quickEmpty.style.display = 'none';
        quickList.style.display = 'flex';
        quickActions.style.display = 'block';

        let total = 0;
        quickDraftItems.forEach((itm, idx) => {
            const sub = itm.quantity * itm.unit_cost;
            total += sub;
            const li = document.createElement('li');
            li.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--sales-surface-low);border:1px solid var(--sales-outline-variant);border-radius:6px;font-size:12.5px;';
            li.innerHTML = `
                <div>
                    <div style="font-weight:600;color:var(--sales-on-surface);">${escapeHtml(itm.name)}</div>
                    <div style="font-size:11px;color:var(--sales-on-surface-variant);">${itm.quantity} &times; Rs. ${Number(itm.unit_cost).toFixed(2)}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <strong style="color:var(--sales-primary);">Rs. ${sub.toFixed(2)}</strong>
                    <button type="button" data-del-draft="${idx}" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:16px;padding:2px 4px;" title="Remove">&times;</button>
                </div>
            `;
            quickList.appendChild(li);
        });

        quickTotal.textContent = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        quickList.querySelectorAll('[data-del-draft]').forEach(b => {
            b.addEventListener('click', function () {
                const idx = Number(this.dataset.delDraft);
                quickDraftItems.splice(idx, 1);
                renderQuickDraft();
            });
        });
    }

    function resetQuickDraft() {
        quickDraftItems = [];
        if (quickSupplier) quickSupplier.value = '';
        renderQuickDraft();
    }

    if (openModalWithDraftBtn) {
        openModalWithDraftBtn.addEventListener('click', function () {
            const suppId = quickSupplier ? quickSupplier.value : '';
            openNewPoModal(quickDraftItems, suppId);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
</script>
</body>
</html>
