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

    const STAGES = [
        { key: 'pending', label: 'Order Placed' },
        { key: 'confirmed', label: 'Confirmed' },
        { key: 'processing', label: 'Processing' },
        { key: 'ready', label: 'Ready for Delivery' },
        { key: 'delivered', label: 'Delivered' }
    ];

    function filteredOrders() {
        const query = state.query.trim().toLowerCase();
        const filterStatus = state.status.toLowerCase();
        return data.orders.filter(function (order) {
            const orderStatus = String(order.status || '').toLowerCase();
            const matchesStatus = filterStatus === 'all' || orderStatus === filterStatus;
            const matchesQuery = !query ||
                order.id.toLowerCase().includes(query) ||
                order.customer.toLowerCase().includes(query);
            const matchesFrom = !state.from || order.date >= state.from;
            const matchesTo = !state.to || order.date <= state.to;
            return matchesStatus && matchesQuery && matchesFrom && matchesTo;
        });
    }

    function statusDisplayLabel(status) {
        const key = String(status || '').toLowerCase();
        if (key === 'pending') return 'Order Placed';
        if (key === 'confirmed') return 'Confirmed';
        if (key === 'processing') return 'Processing';
        if (key === 'ready') return 'Ready for Delivery';
        if (key === 'delivered') return 'Delivered';
        if (key === 'cancelled') return 'Cancelled';
        return String(status || '');
    }

    function statusBadge(status) {
        const key = String(status || '').toLowerCase();
        const label = statusDisplayLabel(status);
        return '<span class="sales-badge ' + u.statusBadgeClass(status) + '">' +
            '<span class="status-dot status-dot--' + key + '"></span>' + u.escapeHtml(label) + '</span>';
    }

    function renderStats() {
        if (byId('stat-all')) byId('stat-all').textContent = data.orders.length;
        ['pending', 'confirmed', 'processing', 'ready', 'delivered'].forEach(function (key) {
            const el = byId('stat-' + key);
            if (el) {
                el.textContent = data.orders.filter(function (order) {
                    return String(order.status || '').toLowerCase() === key;
                }).length;
            }
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
                    '<button class="sales-icon-button" type="button" data-status-order="' + u.escapeHtml(order.id) + '" title="Edit Status" aria-label="Edit Status ' + u.escapeHtml(order.id) + '"><svg class="sales-icon"><use href="#sales-icon-edit"></use></svg></button>' +
                    (String(order.status).toLowerCase() !== 'cancelled' ? '<button class="sales-icon-button sales-icon-button--danger" type="button" data-delete-order="' + u.escapeHtml(order.id) + '" aria-label="Cancel ' + u.escapeHtml(order.id) + '" title="Cancel Order"><svg class="sales-icon"><use href="#sales-icon-trash"></use></svg></button>' : '') +
                '</div></td>';
            tbody.appendChild(row);
        });
        byId('orders-empty').classList.toggle('hidden', orders.length !== 0);
        byId('orders-result-count').textContent = orders.length + (orders.length === 1 ? ' order' : ' orders');
        renderDetail();
    }

    /**
     * 5-Stage Customer Tracking Progress Stepper matching customer dashboard:
     * Order Placed -> Confivered -> Processing -> Ready for Delivery -> Delivered.
     * Completed steps show check icon; future steps show number.
     */
    function renderStepper(order) {
        const s = String(order.status || '').toLowerCase();
        if (s === 'cancelled') {
            return '<div class="order-stepper--cancelled">' +
                '<svg class="sales-icon"><use href="#sales-icon-close"></use></svg>' +
                '<span>Order Cancelled · Stock Reservation Released</span>' +
                '</div>';
        }

        const currentStageIndex = STAGES.findIndex(function (st) { return st.key === s; });
        const effectiveIndex = currentStageIndex === -1 ? 0 : currentStageIndex;

        const stepsHtml = STAGES.map(function (stage, i) {
            const isDone = i <= effectiveIndex;
            const isCurrent = i === effectiveIndex;
            const iconOrNum = isDone
                ? '<svg class="sales-icon"><use href="#sales-icon-check"></use></svg>'
                : (i + 1);

            let cls = 'order-step';
            if (isDone) cls += ' order-step--done';
            if (isCurrent) cls += ' order-step--current';

            return '<div class="' + cls + '">' +
                '<div class="order-step__circle">' + iconOrNum + '</div>' +
                '<span class="order-step__label">' + u.escapeHtml(stage.label) + '</span>' +
                '</div>';
        }).join('');

        return '<div class="order-stepper" aria-label="Order progress">' + stepsHtml + '</div>';
    }

    /**
     * Vertical timeline showing completed & active stages.
     */
    function timelineFor(order) {
        const s = String(order.status || '').toLowerCase();
        if (s === 'cancelled') {
            return '<div class="timeline-step timeline-step--done">' +
                '<span class="timeline-step__dot"><svg class="sales-icon"><use href="#sales-icon-close"></use></svg></span>' +
                '<strong>Order cancelled</strong><span>Order voided and stock reservation released</span></div>' +
                '<div class="timeline-step timeline-step--done">' +
                '<span class="timeline-step__dot"><svg class="sales-icon"><use href="#sales-icon-check"></use></svg></span>' +
                '<strong>Order placed</strong><span>Created by ' + u.escapeHtml(order.rep) + '</span></div>';
        }

        const timelineSteps = [
            { key: 'delivered', title: 'Order delivered', description: 'Customer delivery completed and stock consumed' },
            { key: 'ready', title: 'Ready for delivery', description: 'Items packed and assigned for courier dispatch' },
            { key: 'processing', title: 'Order processing', description: 'Items in picking, packing, and fulfillment' },
            { key: 'confirmed', title: 'Order confirmed', description: 'Order verified and approved for fulfillment' },
            { key: 'pending', title: 'Order placed', description: 'Created by ' + order.rep + ' (stock reserved)' }
        ];

        const stageKeys = ['pending', 'confirmed', 'processing', 'ready', 'delivered'];
        const currentIndex = stageKeys.indexOf(s);

        return timelineSteps.map(function (step) {
            const stepIndex = stageKeys.indexOf(step.key);
            const isDone = currentIndex >= stepIndex && currentIndex !== -1;
            const isCurrent = currentIndex === stepIndex;
            return '<div class="timeline-step' + (isDone ? ' timeline-step--done' : '') + '">' +
                '<span class="timeline-step__dot">' +
                (isDone ? '<svg class="sales-icon"><use href="#sales-icon-check"></use></svg>' : '') +
                '</span><strong>' + u.escapeHtml(step.title) + (isCurrent ? ' <span style="font-size:11px;font-weight:700;color:var(--sales-primary);">(Current)</span>' : '') + '</strong>' +
                '<span>' + u.escapeHtml(step.description) + '</span></div>';
        }).join('');
    }

    function nextStatus(order) {
        const s = String(order.status || '').toLowerCase();
        if (s === 'pending') return { label: 'Confirm Order', value: 'Confirmed' };
        if (s === 'confirmed') return { label: 'Start Processing', value: 'Processing' };
        if (s === 'processing') return { label: 'Ready for Delivery', value: 'Ready' };
        if (s === 'ready') return { label: 'Mark Delivered', value: 'Delivered' };
        return null;
    }

    function reverseStatus(order) {
        const s = String(order.status || '').toLowerCase();
        if (s === 'confirmed') {
            return {
                label: 'Revert to Order Placed',
                value: 'Pending',
                description: 'Revert ' + order.id + ' from Confirmed back to Order Placed? Stock reservation remains active.'
            };
        }
        if (s === 'processing') {
            return {
                label: 'Revert to Confirmed',
                value: 'Confirmed',
                description: 'Revert ' + order.id + ' from Processing back to Confirmed? Stock reservation remains active.'
            };
        }
        if (s === 'ready') {
            return {
                label: 'Revert to Processing',
                value: 'Processing',
                description: 'Revert ' + order.id + ' from Ready for Delivery back to Processing? Stock reservation remains active.'
            };
        }
        if (s === 'delivered') {
            return {
                label: 'Revert to Ready for Delivery',
                value: 'Ready',
                description: 'Revert ' + order.id + ' from Delivered back to Ready for Delivery? Consumed stock will be restored to inventory on hand and re-reserved.'
            };
        }
        if (s === 'cancelled') {
            return {
                label: 'Reopen Order',
                value: 'Pending',
                description: 'Reopen cancelled order ' + order.id + ' back to Order Placed (Pending)? Available inventory will be verified and re-reserved.'
            };
        }
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
        const s = String(order.status || '').toLowerCase();
        const action = nextStatus(order);
        const reverse = reverseStatus(order);
        const itemLines = order.items.map(function (item) {
            return '<div class="detail-line"><div><strong>' + u.escapeHtml(item.name) + '</strong>' +
                '<span>' + u.escapeHtml(item.sku) + ' · Qty ' + item.quantity + '</span></div>' +
                '<strong>' + u.money(item.total) + '</strong></div>';
        }).join('');

        let actionButtons = '';
        if (action) {
            actionButtons += '<button class="sales-button sales-button--primary" type="button" data-next-status="' + u.escapeHtml(action.value) + '">' + u.escapeHtml(action.label) + '</button>';
        } else if (s === 'cancelled' && reverse) {
            actionButtons += '<button class="sales-button sales-button--primary" type="button" data-reverse-status="' + u.escapeHtml(reverse.value) + '">' + u.escapeHtml(reverse.label) + '</button>';
        }

        if (reverse && s !== 'cancelled') {
            actionButtons += '<button class="sales-button sales-button--secondary" type="button" data-reverse-status="' + u.escapeHtml(reverse.value) + '">' +
                '<svg class="sales-icon" style="width:13px;height:13px;vertical-align:-1px;margin-right:4px;"><use href="#sales-icon-undo"></use></svg>' +
                u.escapeHtml(reverse.label) + '</button>';
        }

        actionButtons += '<button class="sales-button sales-button--secondary" type="button" data-status-order="' + u.escapeHtml(order.id) + '">' +
            '<svg class="sales-icon" style="width:13px;height:13px;vertical-align:-1px;margin-right:4px;"><use href="#sales-icon-edit"></use></svg>' +
            'Edit Status</button>';

        if (['pending', 'confirmed', 'processing', 'ready'].includes(s)) {
            actionButtons += '<button class="sales-button sales-button--secondary" type="button" data-edit-order>Update Address</button>';
        }

        if (s !== 'cancelled') {
            actionButtons += '<button class="sales-button sales-button--danger" type="button" data-delete-order="' + u.escapeHtml(order.id) + '">Cancel Order</button>';
        }

        detail.innerHTML =
            '<div class="detail-header"><div><h2>#' + u.escapeHtml(order.id) + '</h2>' +
            '<p>Placed ' + u.escapeHtml(u.displayDate(order.date)) + ' at ' + u.escapeHtml(order.time) + '</p></div>' +
            '<button class="sales-icon-button order-detail-close" type="button" data-close-detail aria-label="Close details"><svg class="sales-icon"><use href="#sales-icon-close"></use></svg></button>' +
            '<div class="detail-header-status-wrap">' +
                statusBadge(order.status) +
                '<button class="sales-button sales-button--secondary sales-button--compact" type="button" data-status-order="' + u.escapeHtml(order.id) + '" title="Edit or reverse status">' +
                    '<svg class="sales-icon" style="width:13px;height:13px;vertical-align:-1px;margin-right:4px;"><use href="#sales-icon-edit"></use></svg>' +
                    'Edit Status' +
                '</button>' +
            '</div></div>' +
            '<div class="detail-body">' +
                renderStepper(order) +
                '<div class="detail-info-grid">' +
                    '<div><p class="detail-label">Customer</p><div class="detail-person"><span class="sales-avatar">' + u.escapeHtml(order.initials) + '</span><div><strong>' + u.escapeHtml(order.customer) + '</strong><span>' + u.escapeHtml(order.accountType) + '</span></div></div></div>' +
                    '<div><p class="detail-label">Sales Rep</p><div class="detail-person"><span class="sales-avatar" aria-hidden="true"><svg class="sales-icon"><use href="#sales-icon-user"></use></svg></span><div><strong>' + u.escapeHtml(order.rep) + '</strong><span>Assigned representative</span></div></div></div>' +
                '</div>' +
                '<div class="detail-items"><p class="detail-label">Delivery Address</p><p>' + u.escapeHtml(order.deliveryAddress || 'No delivery address recorded') + '</p></div>' +
                '<div class="detail-items"><p class="detail-label">Order Items</p>' + itemLines +
                    '<div class="detail-total"><strong>Order Total</strong><strong>' + u.money(order.total) + '</strong></div></div>' +
                '<div class="timeline"><p class="detail-label">Status Timeline</p>' + timelineFor(order) + '</div>' +
            '</div>' +
            '<div class="detail-actions">' + actionButtons + '</div>';
        detail.classList.add('is-open');
    }

    function selectOrder(id) {
        state.selectedId = id;
        renderRows();
    }

    let activeEditingOrder = null;

    function getStatusNotice(currentStatus, targetStatus) {
        const cur = String(currentStatus || '').toLowerCase();
        const tgt = String(targetStatus || '').toLowerCase();
        const curLabel = statusDisplayLabel(currentStatus);
        const tgtLabel = statusDisplayLabel(targetStatus);

        if (cur === tgt) {
            return {
                type: 'info',
                text: 'Order is currently ' + curLabel + '. Selecting this leaves the status unchanged.'
            };
        }

        const reservedStages = ['pending', 'confirmed', 'processing', 'ready'];
        const isCurReserved = reservedStages.includes(cur);
        const isTgtReserved = reservedStages.includes(tgt);

        if (cur === 'delivered' && isTgtReserved) {
            return {
                type: 'warning',
                text: '⚠️ Reversing from Delivered to ' + tgtLabel + ': Consumed items will be restored to inventory on hand and re-reserved for this order. Delivery status will be reset.'
            };
        }
        if (cur === 'delivered' && tgt === 'cancelled') {
            return {
                type: 'danger',
                text: '⚠️ Cancelling a delivered order: Delivered items will be returned to inventory on hand, and this order will be marked cancelled.'
            };
        }
        if (isCurReserved && isTgtReserved) {
            const curIdx = reservedStages.indexOf(cur);
            const tgtIdx = reservedStages.indexOf(tgt);
            if (tgtIdx > curIdx) {
                return {
                    type: 'info',
                    text: '➡️ Advancing order stage from ' + curLabel + ' to ' + tgtLabel + '. Stock reservation remains active.'
                };
            } else {
                return {
                    type: 'warning',
                    text: '↩️ Reverting order stage from ' + curLabel + ' back to ' + tgtLabel + '. Stock reservation remains active.'
                };
            }
        }
        if (isCurReserved && tgt === 'delivered') {
            return {
                type: 'success',
                text: '✅ Completing Order: Order will be marked Delivered and items will be deducted from inventory on hand.'
            };
        }
        if (isCurReserved && tgt === 'cancelled') {
            return {
                type: 'danger',
                text: '❌ Cancelling Order: Reserved items will be released back to general available stock.'
            };
        }
        if (cur === 'cancelled' && isTgtReserved) {
            return {
                type: 'warning',
                text: '🔄 Reopening Cancelled Order: Available stock will be verified and items will be re-reserved at stage ' + tgtLabel + '.'
            };
        }
        if (cur === 'cancelled' && tgt === 'delivered') {
            return {
                type: 'success',
                text: '✅ Fulfilling Cancelled Order: Available stock will be verified and consumed directly from inventory on hand.'
            };
        }
        return {
            type: 'info',
            text: 'Change order status from ' + curLabel + ' to ' + tgtLabel + '.'
        };
    }

    function openStatusDialog(orderId) {
        const order = data.orders.find(function (item) { return item.id === orderId; });
        if (!order) return;
        activeEditingOrder = order;

        const dialog = byId('order-status-dialog');
        if (!dialog) return;

        const idEl = byId('status-dialog-order-id');
        if (idEl) idEl.textContent = order.id;

        const customerEl = byId('status-dialog-customer');
        if (customerEl) customerEl.textContent = order.customer;

        const badgeEl = byId('status-dialog-current-badge');
        if (badgeEl) badgeEl.innerHTML = statusBadge(order.status);

        const select = byId('status-dialog-select');
        if (select) {
            const rawStatus = String(order.status || '').toLowerCase();
            const option = Array.from(select.options).find(function (opt) {
                return opt.value.toLowerCase() === rawStatus;
            });
            if (option) {
                select.value = option.value;
            } else {
                select.value = order.status;
            }
            select.onchange = updateNotice;
        }

        const noteInput = byId('status-dialog-note');
        if (noteInput) noteInput.value = '';

        function updateNotice() {
            const noticeEl = byId('status-dialog-notice');
            if (!noticeEl || !select) return;
            const notice = getStatusNotice(order.status, select.value);
            noticeEl.textContent = notice.text;
            noticeEl.className = 'status-dialog-notice status-dialog-notice--' + notice.type;
        }

        updateNotice();

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute('open', '');
        }
    }

    function closeStatusDialog() {
        const dialog = byId('order-status-dialog');
        if (!dialog) return;
        if (typeof dialog.close === 'function') {
            dialog.close();
        } else {
            dialog.removeAttribute('open');
        }
        activeEditingOrder = null;
    }

    function submitStatusUpdate(order, targetStatus, note) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
        const url = baseUrl.replace(/\/$/, '') + '/sales/orders/status';

        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                id: order.databaseId || 0,
                order_number: order.id,
                status: targetStatus.toLowerCase(),
                note: note || '',
                csrf_token: csrfToken
            })
        }).then(function (res) { return res.json(); })
        .then(function (json) {
            if (json.ok) {
                const prev = order.status;
                order.status = targetStatus;
                renderStats();
                renderRows();
                u.showToast(order.id + ' status updated from ' + prev + ' to ' + order.status + '.');
                return true;
            } else {
                u.showToast(json.message || 'Failed to update order status.');
                return false;
            }
        }).catch(function () {
            u.showToast('Network error updating order status.');
            return false;
        });
    }

    function cancelOrder(orderId) {
        const order = data.orders.find(function (item) { return item.id === orderId; });
        if (!order) return;
        const msg = order.status === 'Delivered'
            ? 'Cancel delivered order ' + order.id + '? Items will be returned to inventory on hand.'
            : 'Cancel order ' + order.id + ' for ' + order.customer + '? Reserved items will be released back to inventory.';
        u.confirmAction(
            'Cancel order?',
            msg,
            'Cancel Order'
        ).then(function (ok) {
            if (!ok) return;
            submitStatusUpdate(order, 'Cancelled', 'Order cancelled by sales representative');
        });
    }

    byId('orders-table-body').addEventListener('click', function (event) {
        const statusBtn = event.target.closest('[data-status-order]');
        if (statusBtn) {
            event.stopPropagation();
            openStatusDialog(statusBtn.dataset.statusOrder);
            return;
        }
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
        const statusBtn = event.target.closest('[data-status-order]');
        if (statusBtn) {
            openStatusDialog(statusBtn.dataset.statusOrder);
            return;
        }
        const nextButton = event.target.closest('[data-next-status]');
        if (nextButton) {
            const order = data.orders.find(function (item) { return item.id === state.selectedId; });
            if (order) {
                submitStatusUpdate(order, nextButton.dataset.nextStatus, 'Advanced to ' + nextButton.dataset.nextStatus);
            }
            return;
        }
        const reverseButton = event.target.closest('[data-reverse-status]');
        if (reverseButton) {
            const order = data.orders.find(function (item) { return item.id === state.selectedId; });
            if (!order) return;
            const targetStatus = reverseButton.dataset.reverseStatus;
            const rev = reverseStatus(order);
            const promptMsg = rev ? rev.description : ('Change ' + order.id + ' status to ' + targetStatus + '?');
            u.confirmAction('Change Order Status?', promptMsg, 'Confirm').then(function (ok) {
                if (ok) {
                    submitStatusUpdate(order, targetStatus, 'Reversed from ' + order.status + ' to ' + targetStatus);
                }
            });
            return;
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
            return;
        }
        const deleteButton = event.target.closest('[data-delete-order]');
        if (deleteButton) cancelOrder(deleteButton.dataset.deleteOrder);
    });

    const statusDialogClose = byId('order-status-dialog-close');
    if (statusDialogClose) {
        statusDialogClose.addEventListener('click', closeStatusDialog);
    }

    const statusDialogCancel = byId('status-dialog-cancel');
    if (statusDialogCancel) {
        statusDialogCancel.addEventListener('click', closeStatusDialog);
    }

    const statusDialogSave = byId('status-dialog-save');
    if (statusDialogSave) {
        statusDialogSave.addEventListener('click', function () {
            if (!activeEditingOrder) return;
            const select = byId('status-dialog-select');
            const targetStatus = select ? select.value : '';
            const noteInput = byId('status-dialog-note');
            const note = noteInput ? noteInput.value.trim() : '';

            if (targetStatus.toLowerCase() === String(activeEditingOrder.status || '').toLowerCase()) {
                closeStatusDialog();
                u.showToast('Status unchanged.');
                return;
            }

            statusDialogSave.disabled = true;
            submitStatusUpdate(activeEditingOrder, targetStatus, note).then(function (ok) {
                statusDialogSave.disabled = false;
                if (ok) {
                    closeStatusDialog();
                }
            });
        });
    }

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
