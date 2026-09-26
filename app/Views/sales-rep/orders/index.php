<main class="sales-main" data-sales-page="orders">
    <section class="sales-page-heading">
        <div>
            <p class="sales-eyebrow">Sales &amp; Customer Operations</p>
            <h1>Order Management</h1>
            <p>Manage and track customer sales orders.</p>
        </div>
        <div class="sales-page-heading__actions">
            <button class="sales-button sales-button--secondary" type="button" id="export-orders">
                <svg class="sales-icon"><use href="#sales-icon-download"></use></svg>
                Export
            </button>
            <a class="sales-button sales-button--primary" href="<?= url('sales/orders/create') ?>" id="new-order-link">
                <svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>
                New Order
            </a>
        </div>
    </section>

    <section class="stats-grid" aria-label="Order summary">
        <article class="stat-card sales-card">
            <span class="stat-card__label">All Orders</span>
            <strong id="stat-all">0</strong>
            <span class="stat-card__hint">Current records</span>
        </article>
        <article class="stat-card sales-card">
            <span class="stat-card__label">Pending</span>
            <strong id="stat-pending">0</strong>
            <span class="stat-card__hint stat-card__hint--warning">Needs attention</span>
        </article>
        <article class="stat-card sales-card">
            <span class="stat-card__label">Processing</span>
            <strong id="stat-processing">0</strong>
            <span class="stat-card__hint stat-card__hint--info">In progress</span>
        </article>
        <article class="stat-card sales-card">
            <span class="stat-card__label">Delivered</span>
            <strong id="stat-delivered">0</strong>
            <span class="stat-card__hint stat-card__hint--success">Completed</span>
        </article>
    </section>

    <div class="order-workspace">
        <section class="order-list-panel">
            <div class="order-toolbar sales-card">
                <div class="sales-search">
                    <svg class="sales-icon"><use href="#sales-icon-search"></use></svg>
                    <input id="order-search" type="search" placeholder="Search order ID or customer..." autocomplete="off">
                </div>
                <div class="filter-chips" id="status-filters" aria-label="Filter orders by status">
                    <button type="button" class="sales-chip sales-chip--active" data-status="all">All Orders</button>
                    <button type="button" class="sales-chip" data-status="Pending"><span class="status-dot status-dot--pending"></span>Pending</button>
                    <button type="button" class="sales-chip" data-status="Processing"><span class="status-dot status-dot--processing"></span>Processing</button>
                    <button type="button" class="sales-chip" data-status="Delivered"><span class="status-dot status-dot--delivered"></span>Delivered</button>
                    <button type="button" class="sales-chip" data-status="Cancelled"><span class="status-dot status-dot--cancelled"></span>Cancelled</button>
                </div>
                <button class="sales-icon-button" type="button" id="date-filter-toggle" aria-label="Show date filters" aria-expanded="false">
                    <svg class="sales-icon"><use href="#sales-icon-filter"></use></svg>
                </button>
                <div class="date-filter hidden" id="date-filter">
                    <label>From <input type="date" id="date-from"></label>
                    <label>To <input type="date" id="date-to"></label>
                    <button class="sales-button sales-button--secondary sales-button--compact" type="button" id="clear-dates">Clear</button>
                </div>
            </div>

            <div class="orders-table-card sales-card">
                <div class="table-scroll">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="align-right">Total</th>
                                <th class="align-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body"></tbody>
                    </table>
                </div>
                <p class="sales-empty hidden" id="orders-empty">No orders match the selected filters.<span class="sales-empty__action">Clear search or choose All Orders.</span></p>
                <div class="table-footer">
                    <span id="orders-result-count">0 orders</span>
                </div>
            </div>
        </section>

        <aside class="order-detail sales-card" id="order-detail" aria-label="Selected order details"></aside>
    </div>

    <div class="sales-toast" id="sales-toast" role="status" aria-live="polite"></div>
</main>
<script>
window.ORDER_MOCK_DATA = { orders: <?= json_encode($orders ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?> };
</script>
