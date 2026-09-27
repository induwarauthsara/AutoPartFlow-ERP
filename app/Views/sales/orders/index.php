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
            <span class="stat-card__hint">Total orders</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Order Placed</span>
            <strong id="stat-pending">0</strong>
            <span class="stat-card__hint stat-card__hint--warning">Pending review</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Confirmed</span>
            <strong id="stat-confirmed">0</strong>
            <span class="stat-card__hint stat-card__hint--confirmed">Stock allocated</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Processing</span>
            <strong id="stat-processing">0</strong>
            <span class="stat-card__hint stat-card__hint--info">In preparation</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__label">Ready for Delivery</span>
            <strong id="stat-ready">0</strong>
            <span class="stat-card__hint stat-card__hint--ready">Ready to dispatch</span>
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
                    <button type="button" class="filter-chip" data-status="Pending"><span class="status-dot status-dot--pending"></span>Order Placed</button>
                    <button type="button" class="filter-chip" data-status="Confirmed"><span class="status-dot status-dot--confirmed"></span>Confirmed</button>
                    <button type="button" class="filter-chip" data-status="Processing"><span class="status-dot status-dot--processing"></span>Processing</button>
                    <button type="button" class="filter-chip" data-status="Ready"><span class="status-dot status-dot--ready"></span>Ready for Delivery</button>
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

    <dialog class="sales-dialog order-status-dialog" id="order-status-dialog" aria-labelledby="order-status-dialog-title">
        <div class="order-status-dialog__content">
            <div class="order-status-dialog__header">
                <div>
                    <h2 id="order-status-dialog-title">Edit Order Status</h2>
                    <p id="order-status-dialog-subtitle">Update or reverse the order progress stage.</p>
                </div>
                <button class="sales-icon-button" type="button" id="order-status-dialog-close" aria-label="Close dialog">
                    <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
                </button>
            </div>
            <div class="order-status-dialog__body">
                <div class="order-status-meta">
                    <div class="meta-item">
                        <span class="meta-label">Order</span>
                        <strong id="status-dialog-order-id">ORD-0000</strong>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Customer</span>
                        <span id="status-dialog-customer">—</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Current Status</span>
                        <span id="status-dialog-current-badge"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status-dialog-select" class="form-label">
                        Select New Status
                    </label>
                    <select id="status-dialog-select" class="sales-select">
                        <option value="Pending">Order Placed (Stock reserved, awaiting review)</option>
                        <option value="Confirmed">Confirmed (Order verified &amp; confirmed)</option>
                        <option value="Processing">Processing (Items picking &amp; packing)</option>
                        <option value="Ready">Ready for Delivery (Ready for courier / in transit)</option>
                        <option value="Delivered">Delivered (Handed over / Stock consumed)</option>
                        <option value="Cancelled">Cancelled (Order voided / Stock released)</option>
                    </select>
                </div>

                <div class="status-dialog-notice" id="status-dialog-notice">
                    Select a status above to review workflow implications.
                </div>

                <div class="form-group">
                    <label for="status-dialog-note" class="form-label">
                        Reason / Note <span class="form-label-hint">(optional)</span>
                    </label>
                    <input type="text" id="status-dialog-note" class="sales-input" placeholder="e.g. Correcting mistaken delivery status, customer request..." autocomplete="off">
                </div>
            </div>
            <div class="order-status-dialog__footer">
                <button class="sales-button sales-button--secondary" type="button" id="status-dialog-cancel">Cancel</button>
                <button class="sales-button sales-button--primary" type="button" id="status-dialog-save">Save Status</button>
            </div>
        </div>
    </dialog>

    <div class="toast" id="order-toast" role="status" aria-live="polite"></div>
</main>
