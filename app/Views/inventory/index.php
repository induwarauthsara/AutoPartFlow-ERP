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
        <article class="sales-card kpi-card inventory-kpi--alert">
            <div class="kpi-card__top">
                <span>Low Stock Alerts</span>
                <svg class="sales-icon inventory-icon--error"><use href="#sales-icon-warning"></use></svg>
            </div>
            <strong id="inventory-low-stock"><?= (int) ($inventorySummary['lowStock'] ?? 0) ?></strong>
            <small class="warning">Action required today</small>
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
        <div class="inventory-locations" id="inventory-locations" role="tablist" aria-label="Warehouse locations">
            <!-- Rendered by inventory.js -->
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
                    <button type="button" role="menuitemradio" aria-checked="false" data-status-filter="low">Low Stock</button>
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
                            <?php foreach (($inventoryLocations ?? ['All Locations', 'Main Warehouse']) as $location): ?>
                                <?php if ($location === 'All Locations') { continue; } ?>
                                <option value="<?= e($location) ?>"><?= e($location) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <label>
                    Notes
                    <textarea id="stock-in-notes" name="notes" rows="3" maxlength="255" placeholder="Optional reference or receiving note"></textarea>
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
                        Reorder Alert Level
                        <input type="number" id="new-item-reorder" name="reorder_level" min="1" step="1" value="10" placeholder="10">
                    </label>
                </div>
                <label>
                    Location
                    <select id="new-item-location" name="location">
                        <?php foreach (($inventoryLocations ?? ['All Locations', 'Main Warehouse']) as $location): ?>
                            <?php if ($location === 'All Locations') { continue; } ?>
                            <option value="<?= e($location) ?>"><?= e($location) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
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

    <div class="sales-toast" id="sales-toast" role="status" aria-live="polite"></div>

    <?php
    $inventoryData = [
        'incomingPurchases' => (int) ($inventorySummary['incomingPurchases'] ?? 0),
        'locations'         => $inventoryLocations ?? ['All Locations'],
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
    </script>
</main>