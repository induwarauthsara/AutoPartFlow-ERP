<main class="orders-main" data-orders-page="management">
    <section class="page-heading">
        <div>
            <p class="eyebrow">Sales &amp; Customer Operations</p>
            <h1>Order Management</h1>
            <p>Manage and track customer sales orders.</p>
        </div>
        <div class="page-heading__actions">
            <button class="button button--secondary" type="button" id="export-orders">
                <svg class="ui-icon"><use href="#icon-download"></use></svg>
                Export
            </button>
            <!-- PHP integration: link to your OrderController create action -->
            <a class="button button--primary" href="#" id="new-order-link">
                <svg class="ui-icon"><use href="#icon-plus"></use></svg>
                New Order
            </a>
        </div>
    </section>

    <section class="stats-grid" aria-label="Order summary">
        <article class="stat-card">
            <span class="stat-card__label">All Orders</span>
            <strong id="stat-all">0</strong>
            <span class="stat-card__hint">Current records</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Pending</span>
            <strong id="stat-pending">0</strong>
            <span class="stat-card__hint stat-card__hint--warning">Needs attention</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Processing</span>
            <strong id="stat-processing">0</strong>
            <span class="stat-card__hint stat-card__hint--info">In progress</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Delivered</span>
            <strong id="stat-delivered">0</strong>
            <span class="stat-card__hint stat-card__hint--success">Completed</span>
        </article>
    </section>

    <div class="order-workspace">
        <section class="order-list-panel">
            <div class="order-toolbar card">
                <div class="search-control">
                    <svg class="ui-icon"><use href="#icon-search"></use></svg>
                    <input id="order-search" type="search" placeholder="Search order ID or customer..." autocomplete="off">
                </div>
                <div class="filter-chips" id="status-filters" aria-label="Filter orders by status">
                    <button type="button" class="filter-chip filter-chip--active" data-status="all">All Orders</button>
                    <button type="button" class="filter-chip" data-status="Pending"><span class="status-dot status-dot--pending"></span>Pending</button>
                    <button type="button" class="filter-chip" data-status="Processing"><span class="status-dot status-dot--processing"></span>Processing</button>
                    <button type="button" class="filter-chip" data-status="Delivered"><span class="status-dot status-dot--delivered"></span>Delivered</button>
                    <button type="button" class="filter-chip" data-status="Cancelled"><span class="status-dot status-dot--cancelled"></span>Cancelled</button>
                </div>
                <button class="icon-button order-toolbar__filter" type="button" id="date-filter-toggle" aria-label="Show date filters" aria-expanded="false">
                    <svg class="ui-icon"><use href="#icon-filter"></use></svg>
                </button>
                <div class="date-filter hidden" id="date-filter">
                    <label>From <input type="date" id="date-from"></label>
                    <label>To <input type="date" id="date-to"></label>
                    <button class="button button--secondary button--compact" type="button" id="clear-dates">Clear</button>
                </div>
            </div>

            <div class="orders-table-card card">
                <div class="table-scroll">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="align-right">Total</th>
                                <th><span class="sr-only">View</span></th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body">
                            <!-- JavaScript mock data; replace with PHP order loop later. -->
                        </tbody>
                    </table>
                </div>
                <p class="empty-state hidden" id="orders-empty">No orders match the selected filters.</p>
                <div class="table-footer">
                    <span id="orders-result-count">0 orders</span>
                    <div class="pagination" aria-label="Order list pages">
                        <button type="button" disabled aria-label="Previous page">&lsaquo;</button>
                        <button type="button" class="pagination__active">1</button>
                        <button type="button">2</button>
                        <button type="button" aria-label="Next page">&rsaquo;</button>
                    </div>
                </div>
            </div>
        </section>

        <aside class="order-detail card" id="order-detail" aria-label="Selected order details">
            <!-- Rendered by orders.js -->
        </aside>
    </div>

    <div class="toast" id="order-toast" role="status" aria-live="polite"></div>
</main>
