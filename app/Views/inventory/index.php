<main class="sales-main inventory-main" data-sales-page="inventory">
    <section class="sales-page-heading">
        <div>
            <p class="sales-eyebrow">Store Operations</p>
            <h1>Inventory Management</h1>
            <p>Monitor on-hand stock, low-stock alerts, and incoming purchases.</p>
        </div>
        <button class="sales-button sales-button--primary" type="button" id="add-stock">
            <svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>
            Add Stock
        </button>
    </section>

    <section class="dashboard-kpis inventory-kpis" aria-label="Inventory summary">
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Current Stock Value</span>
                <svg class="sales-icon"><use href="#sales-icon-money"></use></svg>
            </div>
            <strong id="inventory-stock-value">Rs. 0.00</strong>
            <small class="positive"><svg class="sales-icon"><use href="#sales-icon-trend"></use></svg>+5.2% from last month</small>
        </article>
        <article class="sales-card kpi-card inventory-kpi--alert">
            <div class="kpi-card__top">
                <span>Low Stock Alerts</span>
                <svg class="sales-icon inventory-icon--error"><use href="#sales-icon-warning"></use></svg>
            </div>
            <strong id="inventory-low-stock">0</strong>
            <small class="warning">Action required today</small>
        </article>
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Incoming Purchases</span>
                <svg class="sales-icon"><use href="#sales-icon-truck"></use></svg>
            </div>
            <strong id="inventory-incoming">0</strong>
            <small><svg class="sales-icon"><use href="#sales-icon-clock"></use></svg>Arriving this week</small>
        </article>
    </section>

    <section class="inventory-toolbar sales-card">
        <div class="inventory-locations" id="inventory-locations" role="tablist" aria-label="Warehouse locations">
            <!-- Rendered by inventory.js -->
        </div>
        <div class="inventory-toolbar__actions">
            <button class="sales-button sales-button--secondary" type="button" id="inventory-filter">
                <svg class="sales-icon"><use href="#sales-icon-filter"></use></svg>
                Filter
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
                <p>On-hand quantity and latest stock movement</p>
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
                        <th>Location</th>
                        <th class="inventory-table__qty">Qty</th>
                        <th>Status</th>
                        <th>Last Movement</th>
                        <th class="inventory-table__action">Action</th>
                    </tr>
                </thead>
                <tbody id="inventory-table-body">
                    <!-- Rendered by inventory.js; replace mock data with InventoryController output. -->
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
                        Quantity In
                        <input type="number" id="stock-in-qty" name="quantity" min="1" step="1" required>
                    </label>
                    <label>
                        Location
                        <select id="stock-in-location" name="location">
                            <option value="Detroit HQ">Detroit HQ</option>
                            <option value="Chicago Hub">Chicago Hub</option>
                            <option value="Atlanta Dist.">Atlanta Dist.</option>
                        </select>
                    </label>
                </div>
                <label>
                    Unit Cost (Rs.)
                    <input type="number" id="stock-in-cost" name="unit_cost" step="0.01" min="0" placeholder="Defaults to product cost">
                </label>
                <label>
                    Notes
                    <textarea id="stock-in-notes" name="notes" rows="3" maxlength="255" placeholder="Optional supplier receipt or reference note"></textarea>
                </label>
            </div>
            <div class="customer-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-close-stock-dialog>Cancel</button>
                <button class="sales-button sales-button--primary" type="submit">Record Stock In</button>
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
                <p style="color:var(--sales-text-secondary, #64748b);font-size:12px;">Current on-hand: <span id="writeoff-current-onhand">0</span></p>
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
</main>
<script>
window.INVENTORY_MOCK_DATA = {
    incomingPurchases: <?= (int) ($kpis['incoming_purchases'] ?? 0) ?>,
    stockValue: <?= (float) ($kpis['stock_value'] ?? 0) ?>,
    lowStockCount: <?= (int) ($kpis['low_stock_count'] ?? 0) ?>,
    locations: ['All Locations', 'Detroit HQ', 'Chicago Hub', 'Atlanta Dist.'],
    items: <?= json_encode($inventoryItems ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
};
</script>
