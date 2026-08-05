(function () {
    'use strict';

    const data = window.ORDER_MOCK_DATA || { orders: [], products: [], customers: {} };
    const page = document.querySelector('[data-orders-page]');

    function byId(id) {
        return document.getElementById(id);
    }

    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = String(value ?? '');
        return node.innerHTML;
    }

    function money(value) {
        return 'Rs. ' + Number(value || 0).toLocaleString('en-LK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function displayDate(value) {
        return new Intl.DateTimeFormat('en-LK', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }).format(new Date(value + 'T00:00:00'));
    }

    function statusClass(status) {
        const key = String(status).toLowerCase();
        return ['pending', 'processing', 'delivered', 'cancelled'].includes(key) ? key : 'pending';
    }

    function statusBadge(status) {
        const key = statusClass(status);
        return '<span class="status-badge status-badge--' + key + '">' +
            '<span class="status-dot status-dot--' + key + '"></span>' +
            escapeHtml(status) +
        '</span>';
    }

    let toastTimer;
    function showToast(message) {
        const toast = byId('order-toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2800);
    }

    function initNavigation() {
        const sidebar = byId('app-sidebar');
        const toggle = byId('sidebar-toggle');
        const backdrop = byId('sidebar-backdrop');

        function setOpen(open) {
            if (!sidebar || !toggle || !backdrop) return;
            sidebar.classList.toggle('is-open', open);
            backdrop.classList.toggle('is-visible', open);
            toggle.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                setOpen(!sidebar.classList.contains('is-open'));
            });
        }
        if (backdrop) {
            backdrop.addEventListener('click', function () { setOpen(false); });
        }
        window.addEventListener('resize', function () {
            if (window.innerWidth > 900) setOpen(false);
        });
    }

    function initManagementPage() {
        const state = {
            status: 'all',
            query: '',
            from: '',
            to: '',
            selectedId: data.orders[0] ? data.orders[0].id : null
        };
        const tbody = byId('orders-table-body');
        const empty = byId('orders-empty');
        const resultCount = byId('orders-result-count');

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

        function renderStats() {
            byId('stat-all').textContent = data.orders.length;
            ['Pending', 'Processing', 'Delivered'].forEach(function (status) {
                byId('stat-' + status.toLowerCase()).textContent = data.orders.filter(function (order) {
                    return order.status === status;
                }).length;
            });
        }

        function renderRows() {
            const orders = filteredOrders();
            tbody.innerHTML = '';
            orders.forEach(function (order) {
                const row = document.createElement('tr');
                row.dataset.orderId = order.id;
                row.tabIndex = 0;
                row.setAttribute('aria-label', 'View ' + order.id);
                row.classList.toggle('is-selected', order.id === state.selectedId);
                row.innerHTML =
                    '<td class="order-id">#' + escapeHtml(order.id) + '</td>' +
                    '<td><div class="customer-cell"><span class="avatar">' + escapeHtml(order.initials) + '</span><strong>' + escapeHtml(order.customer) + '</strong></div></td>' +
                    '<td>' + escapeHtml(displayDate(order.date)) + '</td>' +
                    '<td>' + statusBadge(order.status) + '</td>' +
                    '<td class="align-right"><strong>' + money(order.total) + '</strong></td>' +
                    '<td><button class="icon-button" type="button" aria-label="View ' + escapeHtml(order.id) + '"><svg class="ui-icon"><use href="#icon-chevron"></use></svg></button></td>';
                tbody.appendChild(row);
            });

            empty.classList.toggle('hidden', orders.length !== 0);
            resultCount.textContent = orders.length + (orders.length === 1 ? ' order' : ' orders');

            if (orders.length && !orders.some(function (order) { return order.id === state.selectedId; })) {
                state.selectedId = orders[0].id;
                renderRows();
                return;
            }
            if (!orders.length) {
                state.selectedId = null;
            }
            renderDetail();
        }

        function timelineFor(order) {
            const steps = [{
                title: 'Order received',
                description: 'Created by ' + order.rep
            }];
            if (order.status === 'Pending') {
                steps.unshift({ title: 'Awaiting confirmation', description: 'Review payment and stock availability' });
            } else if (order.status === 'Processing') {
                steps.unshift({ title: 'Order processing', description: 'Items are being prepared for delivery' });
            } else if (order.status === 'Delivered') {
                steps.unshift({ title: 'Order delivered', description: 'Customer delivery completed' });
            } else {
                steps.unshift({ title: 'Order cancelled', description: 'This order will not be processed' });
            }
            return steps.map(function (step) {
                return '<div class="timeline-step"><span class="timeline-step__dot"></span>' +
                    '<strong>' + escapeHtml(step.title) + '</strong>' +
                    '<span>' + escapeHtml(step.description) + '</span></div>';
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
                detail.innerHTML = '<p class="empty-state">Select an order to view its details.</p>';
                return;
            }
            const action = nextStatus(order);
            const itemLines = order.items.map(function (item) {
                return '<div class="detail-line"><div><strong>' + escapeHtml(item.name) + '</strong>' +
                    '<span>' + escapeHtml(item.sku) + ' · Qty ' + item.quantity + '</span></div>' +
                    '<strong>' + money(item.total) + '</strong></div>';
            }).join('');

            detail.innerHTML =
                '<div class="detail-header"><div><h2>#' + escapeHtml(order.id) + '</h2>' +
                '<p>Placed ' + escapeHtml(displayDate(order.date)) + ' at ' + escapeHtml(order.time) + '</p></div>' +
                statusBadge(order.status) + '</div>' +
                '<div class="detail-body">' +
                    '<div class="detail-info-grid">' +
                        '<div><p class="detail-label">Customer</p><div class="detail-person"><span class="avatar">' + escapeHtml(order.initials) + '</span><div><strong>' + escapeHtml(order.customer) + '</strong><span>' + escapeHtml(order.accountType) + '</span></div></div></div>' +
                        '<div><p class="detail-label">Sales Rep</p><div class="detail-person"><span class="avatar">SR</span><div><strong>' + escapeHtml(order.rep) + '</strong><span>Assigned representative</span></div></div></div>' +
                    '</div>' +
                    '<div class="detail-items"><p class="detail-label">Order Items</p>' + itemLines +
                        '<div class="detail-total"><strong>Order Total</strong><strong>' + money(order.total) + '</strong></div></div>' +
                    '<div class="timeline"><p class="detail-label">Status Timeline</p>' + timelineFor(order) + '</div>' +
                '</div>' +
                '<div class="detail-actions"><button class="button button--secondary" type="button" data-edit-order>Edit Order</button>' +
                (action ? '<button class="button button--primary" type="button" data-next-status="' + escapeHtml(action.value) + '">' + escapeHtml(action.label) + '</button>' : '') +
                '</div>';
        }

        tbody.addEventListener('click', function (event) {
            const row = event.target.closest('[data-order-id]');
            if (!row) return;
            state.selectedId = row.dataset.orderId;
            renderRows();
        });
        tbody.addEventListener('keydown', function (event) {
            const row = event.target.closest('[data-order-id]');
            if (row && (event.key === 'Enter' || event.key === ' ')) {
                event.preventDefault();
                state.selectedId = row.dataset.orderId;
                renderRows();
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
                chip.classList.toggle('filter-chip--active', chip === button);
            });
            renderRows();
        });

        const dateFilter = byId('date-filter');
        byId('date-filter-toggle').addEventListener('click', function () {
            const open = dateFilter.classList.toggle('hidden') === false;
            this.setAttribute('aria-expanded', String(open));
        });
        byId('date-from').addEventListener('change', function (event) {
            state.from = event.target.value;
            renderRows();
        });
        byId('date-to').addEventListener('change', function (event) {
            state.to = event.target.value;
            renderRows();
        });
        byId('clear-dates').addEventListener('click', function () {
            state.from = '';
            state.to = '';
            byId('date-from').value = '';
            byId('date-to').value = '';
            renderRows();
        });

        byId('order-detail').addEventListener('click', function (event) {
            const nextButton = event.target.closest('[data-next-status]');
            if (nextButton) {
                const order = data.orders.find(function (item) { return item.id === state.selectedId; });
                order.status = nextButton.dataset.nextStatus;
                state.status = order.status;
                document.querySelectorAll('[data-status]').forEach(function (chip) {
                    chip.classList.toggle('filter-chip--active', chip.dataset.status === state.status);
                });
                renderStats();
                renderRows();
                showToast(order.id + ' moved to ' + order.status + '.');
            }
            if (event.target.closest('[data-edit-order]')) {
                showToast('Connect this action to your OrderController edit route.');
            }
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
            showToast('Order export downloaded.');
        });

        byId('new-order-link').addEventListener('click', function (event) {
            if (this.getAttribute('href') === '#') {
                event.preventDefault();
                showToast('Connect this link to your OrderController create route.');
            }
        });

        renderStats();
        renderRows();
    }

    function initCreatePage() {
        const cart = [];
        const quantities = {};
        const TAX_RATE = 0.18;
        let query = '';
        let category = 'all';

        data.products.forEach(function (product) {
            quantities[product.id] = 1;
        });

        function matchingProducts() {
            const normalized = query.trim().toLowerCase();
            return data.products.filter(function (product) {
                const matchesQuery = !normalized ||
                    product.name.toLowerCase().includes(normalized) ||
                    product.sku.toLowerCase().includes(normalized);
                const matchesCategory = category === 'all' || product.category === category;
                return matchesQuery && matchesCategory;
            });
        }

        function renderProducts() {
            const products = matchingProducts();
            const list = byId('order-product-list');
            list.innerHTML = '';
            products.forEach(function (product) {
                const article = document.createElement('article');
                article.className = 'order-product';
                article.dataset.productId = product.id;
                article.innerHTML =
                    '<div class="order-product__visual"><svg class="ui-icon"><use href="#icon-inventory"></use></svg></div>' +
                    '<div><div class="order-product__badges"><span class="part-badge">SKU: ' + escapeHtml(product.sku) + '</span>' +
                    '<span class="part-badge ' + (product.stock <= 12 ? 'part-badge--low' : 'part-badge--stock') + '">' +
                    (product.stock <= 12 ? 'Low Stock' : 'In Stock') + ' (' + product.stock + ')</span></div>' +
                    '<h3>' + escapeHtml(product.name) + '</h3><p>' + escapeHtml(product.description) + '</p></div>' +
                    '<div class="order-product__action"><strong class="order-product__price">' + money(product.price) + '</strong>' +
                    '<div class="add-controls"><div class="quantity-control">' +
                    '<button type="button" data-product-minus aria-label="Decrease quantity">−</button>' +
                    '<input type="number" min="1" max="' + product.stock + '" value="' + quantities[product.id] + '" aria-label="Quantity for ' + escapeHtml(product.name) + '">' +
                    '<button type="button" data-product-plus aria-label="Increase quantity">+</button></div>' +
                    '<button class="button button--primary button--compact" type="button" data-add-product><svg class="ui-icon"><use href="#icon-plus"></use></svg>Add</button></div></div>';
                list.appendChild(article);
            });
            byId('parts-result-count').textContent = products.length + (products.length === 1 ? ' part' : ' parts');
            byId('parts-empty').classList.toggle('hidden', products.length !== 0);
        }

        function totals() {
            const subtotal = cart.reduce(function (sum, item) {
                return sum + item.price * item.quantity;
            }, 0);
            const tax = subtotal * TAX_RATE;
            return { subtotal: subtotal, tax: tax, total: subtotal + tax };
        }

        function renderSummary() {
            const container = byId('summary-items');
            container.innerHTML = '';
            cart.forEach(function (item) {
                const row = document.createElement('div');
                row.className = 'summary-line';
                row.innerHTML =
                    '<div><strong>' + escapeHtml(item.name) + '</strong><span>' + escapeHtml(item.sku) + ' · Qty ' + item.quantity + '</span></div>' +
                    '<div class="summary-line__price"><strong>' + money(item.price * item.quantity) + '</strong>' +
                    '<button class="summary-line__remove" type="button" data-remove-product="' + item.id + '">Remove</button></div>';
                container.appendChild(row);
            });
            const sum = totals();
            const itemCount = cart.reduce(function (count, item) { return count + item.quantity; }, 0);
            byId('summary-item-count').textContent = itemCount + (itemCount === 1 ? ' item' : ' items');
            byId('summary-subtotal').textContent = money(sum.subtotal);
            byId('summary-tax').textContent = money(sum.tax);
            byId('summary-total').textContent = money(sum.total);
            byId('summary-empty').classList.toggle('hidden', cart.length !== 0);
            byId('submit-order').disabled = cart.length === 0;
        }

        function setQuantity(productId, value) {
            const product = data.products.find(function (item) { return item.id === productId; });
            quantities[productId] = Math.max(1, Math.min(product.stock, Number(value) || 1));
            renderProducts();
        }

        byId('order-product-list').addEventListener('click', function (event) {
            const productNode = event.target.closest('[data-product-id]');
            if (!productNode) return;
            const productId = Number(productNode.dataset.productId);
            if (event.target.closest('[data-product-minus]')) {
                setQuantity(productId, quantities[productId] - 1);
            } else if (event.target.closest('[data-product-plus]')) {
                setQuantity(productId, quantities[productId] + 1);
            } else if (event.target.closest('[data-add-product]')) {
                const product = data.products.find(function (item) { return item.id === productId; });
                const existing = cart.find(function (item) { return item.id === productId; });
                if (existing) {
                    existing.quantity = Math.min(product.stock, existing.quantity + quantities[productId]);
                } else {
                    cart.push({
                        id: product.id,
                        sku: product.sku,
                        name: product.name,
                        price: product.price,
                        quantity: quantities[productId]
                    });
                }
                renderSummary();
                showToast(product.name + ' added to the order.');
            }
        });

        byId('order-product-list').addEventListener('change', function (event) {
            if (event.target.matches('input[type="number"]')) {
                const productNode = event.target.closest('[data-product-id]');
                setQuantity(Number(productNode.dataset.productId), event.target.value);
            }
        });

        byId('summary-items').addEventListener('click', function (event) {
            const remove = event.target.closest('[data-remove-product]');
            if (!remove) return;
            const index = cart.findIndex(function (item) { return item.id === Number(remove.dataset.removeProduct); });
            if (index !== -1) cart.splice(index, 1);
            renderSummary();
        });

        byId('part-search').addEventListener('input', function (event) {
            query = event.target.value;
            renderProducts();
        });
        byId('category-filter').addEventListener('change', function (event) {
            category = event.target.value;
            renderProducts();
        });

        byId('order-customer').addEventListener('change', function (event) {
            const customer = data.customers[event.target.value];
            const meta = byId('customer-meta');
            if (!customer) {
                meta.innerHTML = '<span class="avatar">?</span><div><strong>No customer selected</strong><span>Choose a customer account to continue</span></div>';
                return;
            }
            meta.innerHTML = '<span class="avatar">' + escapeHtml(customer.initials) + '</span><div><strong>' +
                escapeHtml(customer.name) + '</strong><span>' + escapeHtml(customer.detail) + '</span></div>';
        });

        byId('save-draft').addEventListener('click', function () {
            const draft = {
                customerId: byId('order-customer').value,
                cart: cart,
                notes: byId('order-notes').value,
                savedAt: new Date().toISOString()
            };
            localStorage.setItem('autopartflow-order-draft', JSON.stringify(draft));
            showToast('Draft saved in this browser.');
        });

        const dialog = byId('order-confirm-dialog');
        byId('submit-order').addEventListener('click', function () {
            if (!byId('order-customer').value) {
                showToast('Select a customer before placing the order.');
                byId('order-customer').focus();
                return;
            }
            byId('confirm-message').textContent = cart.length + ' line item' + (cart.length === 1 ? '' : 's') +
                ' totaling ' + money(totals().total) + ' will be submitted.';
            dialog.showModal();
        });
        document.querySelector('[data-dialog-close]').addEventListener('click', function () {
            dialog.close();
        });
        byId('confirm-order').addEventListener('click', function () {
            dialog.close();
            showToast('Frontend ready. POST this order payload in your OrderController.');
        });

        renderProducts();
        renderSummary();
    }

    initNavigation();
    if (!page) return;
    if (page.dataset.ordersPage === 'management') initManagementPage();
    if (page.dataset.ordersPage === 'create') initCreatePage();
})();
