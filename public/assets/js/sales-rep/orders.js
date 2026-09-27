/**
 * Order Management list: search, status chips, date filter, status flow, address editing and cancellation.
 * Business rule: Pending → Processing → Delivered. Cancelled has no next step.
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-sales-page="orders"]');
    if (!page) return;

    const u = window.SalesRepUtils;
    const data = window.ORDER_MOCK_DATA || { orders: [] };
    const byId = u.byId;
    const state = {
        status: 'all',
        query: '',
        from: '',
        to: '',
        selectedId: data.orders[0] ? data.orders[0].id : null
    };

    function filteredOrders() {
        const query = state.query.trim().toLowerCase();
        return data.orders.filter(function (order) {
            const matchesStatus = state.status === 'all' || order.status === state.status;
            const matchesQuery = !query ||
                order.id.toLowerCase().includes(query) ||
                order.customer.toLowerCase().includes(query);
            const matchesFrom = !state.from || order.date >= state.from;
            const matchesTo = !state.to || order.date <= state.to;
            return matchesStatus && matchesQuery && matchesFrom && matchesTo;
        });
    }

    function statusBadge(status) {
        const key = String(status).toLowerCase();
        return '<span class="sales-badge ' + u.statusBadgeClass(status) + '">' +
            '<span class="status-dot status-dot--' + key + '"></span>' + u.escapeHtml(status) + '</span>';
    }

    function renderStats() {
        byId('stat-all').textContent = data.orders.length;
        ['Pending', 'Processing', 'Delivered'].forEach(function (status) {
            byId('stat-' + status.toLowerCase()).textContent = data.orders.filter(function (order) {
                return order.status === status;
            }).length;
        });
    }

    function renderRows() {
        const tbody = byId('orders-table-body');
        const orders = filteredOrders();
        tbody.innerHTML = '';
        orders.forEach(function (order) {
            const row = document.createElement('tr');
            row.dataset.orderId = order.id;
            row.tabIndex = 0;
            if (order.id === state.selectedId) row.className = 'is-selected';
            row.innerHTML =
                '<td><span class="order-id">' + u.escapeHtml(order.id) + '</span></td>' +
                '<td><span class="customer-cell"><span class="sales-avatar sales-avatar--small">' + u.escapeHtml(order.initials) + '</span>' + u.escapeHtml(order.customer) + '</span></td>' +
                '<td>' + u.escapeHtml(u.displayDate(order.date)) + '</td>' +
                '<td>' + statusBadge(order.status) + '</td>' +
                '<td class="align-right"><strong>' + u.money(order.total) + '</strong></td>' +
                '<td><div class="order-row-actions">' +
                    '<button class="sales-icon-button" type="button" aria-label="View ' + u.escapeHtml(order.id) + '"><svg class="sales-icon"><use href="#sales-icon-chevron"></use></svg></button>' +
                    (order.status === 'Pending' ? '<button class="sales-icon-button sales-icon-button--danger" type="button" data-delete-order="' + u.escapeHtml(order.id) + '" aria-label="Cancel ' + u.escapeHtml(order.id) + '"><svg class="sales-icon"><use href="#sales-icon-trash"></use></svg></button>' : '') +
                '</div></td>';
            tbody.appendChild(row);
        });
        byId('orders-empty').classList.toggle('hidden', orders.length !== 0);
        byId('orders-result-count').textContent = orders.length + (orders.length === 1 ? ' order' : ' orders');
        renderDetail();
    }

    /**
     * Newest step first. Completed steps get a check icon (Stitch timeline).
     */
    function timelineFor(order) {
        const steps = [{ title: 'Order received', description: 'Created by ' + order.rep, done: true }];
        if (order.status === 'Pending') {
            steps.unshift({ title: 'Awaiting confirmation', description: 'Review payment and stock availability', done: false });
        } else if (order.status === 'Processing') {
            steps.unshift({ title: 'Order processing', description: 'Items are being prepared for delivery', done: false });
            steps[1].done = true;
        } else if (order.status === 'Delivered') {
            steps.unshift({ title: 'Order delivered', description: 'Customer delivery completed', done: true });
            steps[1].done = true;
        } else {
            steps.unshift({ title: 'Order cancelled', description: 'This order will not be processed', done: false });
        }
        return steps.map(function (step) {
            return '<div class="timeline-step' + (step.done ? ' timeline-step--done' : '') + '"><span class="timeline-step__dot">' +
                (step.done ? '<svg class="sales-icon"><use href="#sales-icon-check"></use></svg>' : '') +
                '</span><strong>' + u.escapeHtml(step.title) + '</strong><span>' + u.escapeHtml(step.description) + '</span></div>';
        }).join('');
    }

    function nextStatus(order) {
        if (order.status === 'Pending') return { label: 'Start Processing', value: 'Processing' };
        if (order.status === 'Processing') return { label: 'Mark Delivered', value: 'Delivered' };
        return null;
    }

    function renderDetail() {
        const detail = byId('order-detail');
        const order = data.orders.find(function (item) { return item.id === state.selectedId; });
        if (!order) {
            detail.innerHTML = '<p class="sales-empty">Select an order to view its details.</p>';
            detail.classList.remove('is-open');
            return;
        }
        const action = nextStatus(order);
        const itemLines = order.items.map(function (item) {
            return '<div class="detail-line"><div><strong>' + u.escapeHtml(item.name) + '</strong>' +
                '<span>' + u.escapeHtml(item.sku) + ' · Qty ' + item.quantity + '</span></div>' +
                '<strong>' + u.money(item.total) + '</strong></div>';
        }).join('');

        detail.innerHTML =
            '<div class="detail-header"><div><h2>#' + u.escapeHtml(order.id) + '</h2>' +
            '<p>Placed ' + u.escapeHtml(u.displayDate(order.date)) + ' at ' + u.escapeHtml(order.time) + '</p></div>' +
            '<button class="sales-icon-button order-detail-close" type="button" data-close-detail aria-label="Close details"><svg class="sales-icon"><use href="#sales-icon-close"></use></svg></button>' +
            statusBadge(order.status) + '</div>' +
            '<div class="detail-body">' +
                '<div class="detail-info-grid">' +
                    '<div><p class="detail-label">Customer</p><div class="detail-person"><span class="sales-avatar">' + u.escapeHtml(order.initials) + '</span><div><strong>' + u.escapeHtml(order.customer) + '</strong><span>' + u.escapeHtml(order.accountType) + '</span></div></div></div>' +
                    '<div><p class="detail-label">Sales Rep</p><div class="detail-person"><span class="sales-avatar" aria-hidden="true"><svg class="sales-icon"><use href="#sales-icon-user"></use></svg></span><div><strong>' + u.escapeHtml(order.rep) + '</strong><span>Assigned representative</span></div></div></div>' +
                '</div>' +
                '<div class="detail-items"><p class="detail-label">Delivery Address</p><p>' + u.escapeHtml(order.deliveryAddress || 'No delivery address recorded') + '</p></div>' +
                '<div class="detail-items"><p class="detail-label">Order Items</p>' + itemLines +
                    '<div class="detail-total"><strong>Order Total</strong><strong>' + u.money(order.total) + '</strong></div></div>' +
                '<div class="timeline"><p class="detail-label">Status Timeline</p>' + timelineFor(order) + '</div>' +
            '</div>' +
            '<div class="detail-actions">' + (order.status === 'Pending' ? '<button class="sales-button sales-button--secondary" type="button" data-edit-order>Update Address</button>' : '') +
            (action ? '<button class="sales-button sales-button--primary" type="button" data-next-status="' + u.escapeHtml(action.value) + '">' + u.escapeHtml(action.label) + '</button>' : '') +
            (order.status === 'Pending' ? '<button class="sales-button sales-button--danger" type="button" data-delete-order="' + u.escapeHtml(order.id) + '">Cancel Order</button>' : '') + '</div>';
        detail.classList.add('is-open');
    }

    function selectOrder(id) {
        state.selectedId = id;
        renderRows();
    }

    function cancelOrder(orderId) {
        const order = data.orders.find(function (item) { return item.id === orderId; });
        if (!order) return;
        u.confirmAction(
            'Cancel pending order?',
            'Void ' + order.id + ' for ' + order.customer + '? The record will remain in order history.',
            'Cancel Order'
        ).then(function (ok) {
            if (!ok) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
            fetch(baseUrl.replace(/\/$/, '') + '/sales/orders/cancel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    id: order.databaseId || 0,
                    order_number: order.id,
                    csrf_token: csrfToken
                })
            }).then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.ok) {
                    order.status = 'Cancelled';
                    renderStats();
                    renderRows();
                    u.showToast(order.id + ' cancelled.');
                } else {
                    u.showToast(json.message || 'Failed to cancel order.');
                }
            }).catch(function () {
                u.showToast('Network error cancelling order.');
            });
        });
    }

    byId('orders-table-body').addEventListener('click', function (event) {
        const deleteButton = event.target.closest('[data-delete-order]');
        if (deleteButton) {
            event.stopPropagation();
            cancelOrder(deleteButton.dataset.deleteOrder);
            return;
        }
        const row = event.target.closest('[data-order-id]');
        if (row) selectOrder(row.dataset.orderId);
    });
    byId('orders-table-body').addEventListener('keydown', function (event) {
        const row = event.target.closest('[data-order-id]');
        if (row && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            selectOrder(row.dataset.orderId);
        }
    });

    byId('order-search').addEventListener('input', function (event) {
        state.query = event.target.value;
        renderRows();
    });

    byId('status-filters').addEventListener('click', function (event) {
        const button = event.target.closest('[data-status]');
        if (!button) return;
        state.status = button.dataset.status;
        document.querySelectorAll('[data-status]').forEach(function (chip) {
            chip.classList.toggle('sales-chip--active', chip === button);
        });
        renderRows();
    });

    const dateFilter = byId('date-filter');
    byId('date-filter-toggle').addEventListener('click', function () {
        const open = dateFilter.classList.toggle('hidden') === false;
        this.setAttribute('aria-expanded', String(open));
    });
    byId('date-from').addEventListener('change', function (event) { state.from = event.target.value; renderRows(); });
    byId('date-to').addEventListener('change', function (event) { state.to = event.target.value; renderRows(); });
    byId('clear-dates').addEventListener('click', function () {
        state.from = '';
        state.to = '';
        byId('date-from').value = '';
        byId('date-to').value = '';
        renderRows();
    });

    byId('order-detail').addEventListener('click', function (event) {
        if (event.target.closest('[data-close-detail]')) {
            byId('order-detail').classList.remove('is-open');
            return;
        }
        const nextButton = event.target.closest('[data-next-status]');
        if (nextButton) {
            const order = data.orders.find(function (item) { return item.id === state.selectedId; });
            const targetStatus = nextButton.dataset.nextStatus;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
            fetch(baseUrl.replace(/\/$/, '') + '/sales/orders/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    id: order.databaseId || 0,
                    order_number: order.id,
                    status: targetStatus.toLowerCase(),
                    csrf_token: csrfToken
                })
            }).then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.ok) {
                    order.status = targetStatus;
                    renderStats();
                    renderRows();
                    u.showToast(order.id + ' moved to ' + order.status + '.');
                } else {
                    u.showToast(json.message || 'Failed to update order status.');
                }
            }).catch(function () {
                u.showToast('Network error updating order status.');
            });
        }
        if (event.target.closest('[data-edit-order]')) {
            const order = data.orders.find(function (item) { return item.id === state.selectedId; });
            const address = window.prompt('Enter the delivery address for ' + order.id + ':', order.deliveryAddress || '');
            if (address === null) return;
            if (!address.trim()) {
                u.showToast('Delivery address is required.');
                return;
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
            fetch(baseUrl.replace(/\/$/, '') + '/sales/orders/address', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    id: order.databaseId || 0,
                    order_number: order.id,
                    delivery_address: address.trim(),
                    csrf_token: csrfToken
                })
            }).then(function (res) { return res.json(); })
            .then(function (json) {
                if (!json.ok) throw new Error(json.message || 'Failed to update the delivery address.');
                order.deliveryAddress = address.trim();
                renderDetail();
                u.showToast(order.id + ' delivery address updated.');
            }).catch(function (error) {
                u.showToast(error.message || 'Network error updating the delivery address.');
            });
        }
        const deleteButton = event.target.closest('[data-delete-order]');
        if (deleteButton) cancelOrder(deleteButton.dataset.deleteOrder);
    });

    byId('export-orders').addEventListener('click', function () {
        const rows = [['Order ID', 'Customer', 'Date', 'Status', 'Total']].concat(filteredOrders().map(function (order) {
            return [order.id, order.customer, order.date, order.status, order.total];
        }));
        const csv = rows.map(function (row) {
            return row.map(function (cell) { return '"' + String(cell).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\r\n');
        const blobUrl = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = 'orders-export.csv';
        link.click();
        URL.revokeObjectURL(blobUrl);
        u.showToast('Order export downloaded.');
    });

    renderStats();
    renderRows();
})();
