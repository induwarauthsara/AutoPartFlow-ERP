<main class="sales-main inventory-main" data-sales-page="inventory">
    <section class="sales-page-heading">
        <div>
            <p class="sales-eyebrow">Store Operations</p>
            <h1>Inventory Management</h1>
            <p>Monitor on-hand stock, low-stock alerts, and incoming purchases.</p>
        </div>
        <div class="sales-page-actions" style="display:flex;gap:8px;align-items:center;">
            <button class="sales-button sales-button--secondary" type="button" id="add-item-btn">
                <svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>
                New Item
            </button>
            <button class="sales-button sales-button--primary" type="button" id="add-stock">
                <svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>
                Add Stock
            </button>
        </div>
    </section>

    <section class="dashboard-kpis inventory-kpis" aria-label="Inventory summary">
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Current Stock Value</span>
                <svg class="sales-icon"><use href="#sales-icon-money"></use></svg>
            </div>
            <strong id="inventory-stock-value">Rs. <?= number_format((float) ($inventorySummary['stockValue'] ?? 0), 2) ?></strong>
            <small class="positive"><svg class="sales-icon"><use href="#sales-icon-trend"></use></svg>+5.2% from last month</small>
        </article>
        <article class="sales-card kpi-card inventory-kpi--alert inventory-kpi--clickable" id="kpi-low-stock" style="cursor:pointer;" title="Click to filter low and critical stock items">
            <div class="kpi-card__top">
                <span>Low Stock Alerts</span>
                <svg class="sales-icon inventory-icon--error"><use href="#sales-icon-warning"></use></svg>
            </div>
            <strong id="inventory-low-stock"><?= (int) ($inventorySummary['lowStock'] ?? 0) ?></strong>
            <small class="warning">Click to filter alerts</small>
        </article>
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Incoming Purchases</span>
                <svg class="sales-icon"><use href="#sales-icon-truck"></use></svg>
            </div>
            <strong id="inventory-incoming"><?= (int) ($inventorySummary['incomingPurchases'] ?? 0) ?></strong>
            <small><svg class="sales-icon"><use href="#sales-icon-clock"></use></svg>Arriving this week</small>
        </article>
    </section>

    <section class="inventory-toolbar sales-card">
        <div class="inventory-toolbar__status-info" id="inventory-status-info" style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--sales-on-surface-variant,#64748b);">
            <span style="font-weight:600;color:var(--sales-on-surface,#0f172a);">Stock Overview</span>
            <span>&bull;</span>
            <span id="inventory-item-count"><?= count($inventoryItems ?? []) ?> items registered</span>
        </div>
        <div class="inventory-toolbar__actions">
            <div class="inventory-filter-menu">
                <button class="sales-button sales-button--secondary" type="button" id="inventory-filter" aria-expanded="false" aria-controls="inventory-filter-options">
                    <svg class="sales-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 5h18v2H3V5Zm3 6h12v2H6v-2Zm4 6h4v2h-4v-2Z" fill="currentColor"></path>
                    </svg>
                    Filter
                </button>
                <div class="inventory-filter-options hidden" id="inventory-filter-options" role="menu" aria-label="Filter inventory by stock status">
                    <button type="button" role="menuitemradio" aria-checked="true" data-status-filter="all">All Stock</button>
                    <button type="button" role="menuitemradio" aria-checked="false" data-status-filter="optimal">Optimal Stock</button>
                    <button type="button" role="menuitemradio" aria-checked="false" data-status-filter="low">Low Stock Alerts</button>
                    <button type="button" role="menuitemradio" aria-checked="false" data-status-filter="critical">Critical Stock</button>
                    <button type="button" role="menuitemradio" aria-checked="false" data-status-filter="restocking">Restocking</button>
                </div>
            </div>
            <button class="sales-button sales-button--secondary" type="button" id="add-item-toolbar">
                <svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>
                New Item
            </button>
            <button class="sales-button sales-button--primary" type="button" id="add-stock-toolbar">
                <svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>
                Add Stock
            </button>
        </div>
    </section>

    <section class="sales-card inventory-table-card">
        <div class="sales-card__header">
            <div>
                <h2>Detailed Inventory</h2>
                <p>Live on-hand, available and reserved quantities with low-stock alerts</p>
            </div>
            <button class="sales-icon-button" type="button" aria-label="More inventory options">
                <svg class="sales-icon"><use href="#sales-icon-more"></use></svg>
            </button>
        </div>
        <div class="inventory-table-wrap">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Part No.</th>
                        <th>Item Name</th>
                        <th class="inventory-table__qty">On Hand</th>
                        <th class="inventory-table__threshold">Low Stock Threshold</th>
                        <th>Status</th>
                        <th>Last Movement</th>
                        <th class="inventory-table__action">Action</th>
                    </tr>
                </thead>
                <tbody id="inventory-table-body">
                    <!-- Rendered by inventory.js from window.INVENTORY_DATA -->
                </tbody>
            </table>
        </div>
        <p class="sales-empty hidden" id="inventory-empty">No inventory items match your search.</p>
    </section>

    <dialog class="customer-dialog sales-dialog" id="stock-in-dialog" aria-labelledby="stock-in-dialog-title">
        <form class="customer-form" id="stock-in-form" method="dialog">
            <div class="customer-dialog__header">
                <div>
                    <p class="sales-eyebrow">Stock Movement</p>
                    <h2 id="stock-in-dialog-title">Add Stock</h2>
                </div>
                <button class="sales-icon-button" type="button" data-close-stock-dialog aria-label="Close">
                    <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
                </button>
            </div>
            <div class="customer-dialog__body">
                <input type="hidden" id="stock-in-product-id">
                <label>
                    Product
                    <select id="stock-in-product" name="product_id" required>
                        <option value="">Select a product</option>
                        <?php foreach (($products ?? []) as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" data-cost="<?= (float) $p['cost_price'] ?>">
                                <?= e($p['product_code']) ?> - <?= e($p['name']) ?> (On Hand: <?= (int) $p['on_hand'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="form-grid">
                    <label>
                        Quantity In <span style="color:#dc2626;">*</span>
                        <input type="number" id="stock-in-qty" name="quantity" min="1" step="1" required placeholder="Units to add">
                    </label>
                    <label>
                        Unit Cost (Rs.)
                        <input type="number" id="stock-in-cost" name="unit_cost" step="0.01" min="0" placeholder="Defaults to product cost">
                    </label>
                </div>
                <label>
                    Notes
                    <textarea id="stock-in-notes" name="notes" rows="3" maxlength="255" placeholder="Optional supplier receipt or reference note"></textarea>
                </label>
            </div>
            <div class="customer-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-close-stock-dialog>Cancel</button>
                <button class="sales-button sales-button--primary" type="submit" id="stock-in-submit">Record Stock In</button>
            </div>
        </form>
    </dialog>

    <dialog class="customer-dialog sales-dialog" id="new-item-dialog" aria-labelledby="new-item-dialog-title">
        <form class="customer-form" id="new-item-form" method="dialog">
            <div class="customer-dialog__header">
                <div>
                    <p class="sales-eyebrow">Catalog &amp; Stock</p>
                    <h2 id="new-item-dialog-title">New Inventory Item</h2>
                </div>
                <button class="sales-icon-button" type="button" data-close-item-dialog aria-label="Close">
                    <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
                </button>
            </div>
            <div class="customer-dialog__body">
                <div class="form-grid">
                    <label>
                        Part No. / SKU <span style="color:#dc2626;">*</span>
                        <input type="text" id="new-item-code" name="product_code" placeholder="e.g. BP-TY-005" maxlength="30" required>
                    </label>
                    <label>
                        Category <span style="color:#dc2626;">*</span>
                        <select id="new-item-category" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach (($categories ?? []) as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <label>
                    Item Name <span style="color:#dc2626;">*</span>
                    <input type="text" id="new-item-name" name="name" placeholder="e.g. Front Brake Pads - Toyota Hilux" maxlength="200" required>
                </label>
                <div class="form-grid">
                    <label>
                        Cost Price (Rs.)
                        <input type="number" id="new-item-cost" name="cost_price" min="0" step="0.01" value="0.00" placeholder="0.00">
                    </label>
                    <label>
                        Selling Price (Rs.)
                        <input type="number" id="new-item-selling" name="selling_price" min="0" step="0.01" value="0.00" placeholder="0.00">
                    </label>
                </div>
                <div class="form-grid">
                    <label>
                        Initial Stock Quantity
                        <input type="number" id="new-item-qty" name="quantity_on_hand" min="0" step="1" value="0" placeholder="0">
                    </label>
                    <label>
                        Low Stock Alert Threshold <span style="color:#dc2626;">*</span>
                        <input type="number" id="new-item-reorder" name="reorder_level" min="1" step="1" value="10" placeholder="10" required>
                    </label>
                </div>
                <label>
                    Notes / Description
                    <textarea id="new-item-notes" name="notes" rows="2" maxlength="255" placeholder="Optional reference, supplier or receiving note"></textarea>
                </label>
            </div>
            <div class="customer-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-close-item-dialog>Cancel</button>
                <button class="sales-button sales-button--primary" type="submit" id="new-item-submit">Save Item to Inventory</button>
            </div>
        </form>
    </dialog>

    <!-- Set Low Stock Alert Threshold Dialog -->
    <dialog class="customer-dialog sales-dialog" id="threshold-dialog" aria-labelledby="threshold-dialog-title">
        <form class="customer-form" id="threshold-form" method="dialog">
            <div class="customer-dialog__header">
                <div>
                    <p class="sales-eyebrow">Stock Alert Settings</p>
                    <h2 id="threshold-dialog-title">Manage Low Stock Alert Threshold</h2>
                </div>
                <button class="sales-icon-button" type="button" data-close-threshold-dialog aria-label="Close">
                    <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
                </button>
            </div>
            <div class="customer-dialog__body">
                <input type="hidden" id="threshold-product-id" name="product_id">
                <div style="background:var(--sales-surface-container, #f1f5f9);padding:12px 14px;border-radius:8px;margin-bottom:14px;border:1px solid var(--sales-outline-variant, #e2e8f0);">
                    <div style="font-weight:700;font-size:14px;color:var(--sales-on-surface,#0f172a);" id="threshold-product-name">Product Name</div>
                    <div style="font-size:12px;color:var(--sales-on-surface-variant,#64748b);margin-top:3px;">
                        Part No: <span id="threshold-product-code" style="font-weight:600;">-</span> &bull; Current On-Hand: <strong id="threshold-current-stock" style="color:var(--sales-primary, #0b1220);">0</strong> units
                    </div>
                </div>
                <label>
                    Low Stock Alert Threshold (Units) <span style="color:#dc2626;">*</span>
                    <input type="number" id="threshold-level" name="reorder_level" min="1" step="1" required placeholder="e.g. 10">
                    <small style="display:block;color:var(--sales-on-surface-variant,#64748b);font-size:11.5px;margin-top:5px;line-height:1.4;">
                        When on-hand quantity is at or below this threshold, the product will trigger a <strong>Low Stock Alert</strong> and appear in alerts.
                    </small>
                </label>
                <div id="threshold-status-preview" style="display:none;margin-top:12px;padding:9px 12px;border-radius:6px;font-size:12.5px;font-weight:600;"></div>
            </div>
            <div class="customer-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-close-threshold-dialog>Cancel</button>
                <button class="sales-button sales-button--primary" type="submit" id="threshold-submit">Save Alert Threshold</button>
            </div>
        </form>
    </dialog>

    <!-- Adjust Stock Dialog (Count Adjustment) -->
    <dialog class="customer-dialog sales-dialog" id="stock-adjust-dialog" aria-labelledby="stock-adjust-dialog-title">
        <form class="customer-form" id="stock-adjust-form" method="dialog">
            <div class="customer-dialog__header">
                <div>
                    <p class="sales-eyebrow">Physical Audit</p>
                    <h2 id="stock-adjust-dialog-title">Adjust Inventory Count</h2>
                </div>
                <button class="sales-icon-button" type="button" data-close-adjust-dialog aria-label="Close">
                    <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
                </button>
            </div>
            <div class="customer-dialog__body">
                <input type="hidden" id="adjust-product-id" name="product_id">
                <p><strong id="adjust-product-name">Product Name</strong></p>
                <label>
                    New On-Hand Count
                    <input type="number" id="adjust-qty" name="quantity" min="0" step="1" required>
                </label>
                <label>
                    Reason / Audit Notes
                    <textarea id="adjust-notes" name="notes" rows="2" maxlength="255" placeholder="e.g. Physical inventory cycle count adjustment" required></textarea>
                </label>
            </div>
            <div class="customer-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-close-adjust-dialog>Cancel</button>
                <button class="sales-button sales-button--primary" type="submit">Save Adjustment</button>
            </div>
        </form>
    </dialog>

    <!-- Write-off Damaged Stock Dialog -->
    <dialog class="customer-dialog sales-dialog" id="stock-writeoff-dialog" aria-labelledby="stock-writeoff-dialog-title">
        <form class="customer-form" id="stock-writeoff-form" method="dialog">
            <div class="customer-dialog__header">
                <div>
                    <p class="sales-eyebrow">Loss &amp; Damage</p>
                    <h2 id="stock-writeoff-dialog-title">Write-off Damaged Stock</h2>
                </div>
                <button class="sales-icon-button" type="button" data-close-writeoff-dialog aria-label="Close">
                    <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
                </button>
            </div>
            <div class="customer-dialog__body">
                <input type="hidden" id="writeoff-product-id" name="product_id">
                <p><strong id="writeoff-product-name">Product Name</strong></p>
                <p style="color:var(--sales-text-secondary, #64748b);font-size:12px;">Available, unreserved stock: <span id="writeoff-current-onhand">0</span></p>
                <label>
                    Damaged Quantity to Write Off
                    <input type="number" id="writeoff-qty" name="quantity" min="1" step="1" required>
                </label>
                <label>
                    Damage Reason
                    <textarea id="writeoff-reason" name="reason" rows="2" maxlength="255" placeholder="e.g. Packaging damaged during handling, expired or broken core" required></textarea>
                </label>
            </div>
            <div class="customer-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-close-writeoff-dialog>Cancel</button>
                <button class="sales-button sales-button--danger" type="submit">Confirm Write-Off</button>
            </div>
        </form>
    </dialog>

    <div class="sales-toast" id="sales-toast" role="status" aria-live="polite"></div>

    <?php
    $inventoryData = [
        'incomingPurchases' => (int) ($inventorySummary['incomingPurchases'] ?? ($kpis['incoming_purchases'] ?? 0)),
        'items'             => $inventoryItems ?? [],
        'categories'        => $categories ?? [],
        'csrfToken'         => csrf_token(),
        'baseUrl'           => rtrim(url(), '/'),
    ];
    ?>
    <script>
        window.APP_CONFIG = window.APP_CONFIG || {};
        window.APP_CONFIG.baseUrl = '<?= rtrim(url(), '/') ?>';
        window.APP_CONFIG.csrfToken = '<?= csrf_token() ?>';
        window.INVENTORY_DATA = <?= json_encode($inventoryData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        window.INVENTORY_MOCK_DATA = window.INVENTORY_DATA;
    </script>
</main>
