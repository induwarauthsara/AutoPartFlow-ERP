(function () {
    'use strict';

    const data = window.SALES_REP_MOCK_DATA || {
        weeklySales: { week: [], previous: [] },
        routeShops: [],
        deliveries: [],
        customers: []
    };
    const page = document.querySelector('[data-sales-page]');

    function byId(id) {
        return document.getElementById(id);
    }

    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = String(value ?? '');
        return node.innerHTML;
    }

    function money(value, compact) {
        if (compact && Number(value) >= 1000000) {
            return 'Rs. ' + (Number(value) / 1000000).toFixed(1) + 'M';
        }
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

    function slug(value) {
        return String(value).toLowerCase().replace(/\s+/g, '-').replace(/[^a-z-]/g, '');
    }

    let toastTimer;
    function showToast(message) {
        const toast = byId('sales-toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2800);
    }

    function initShell() {
        if (!page) return;
        const activePage = page.dataset.salesPage;
        document.querySelectorAll('[data-nav-page]').forEach(function (link) {
            const active = link.dataset.navPage === activePage;
            link.classList.toggle('sales-nav__link--active', active);
            if (active) link.setAttribute('aria-current', 'page');
        });
        document.querySelectorAll('[data-mobile-page]').forEach(function (link) {
            const active = link.dataset.mobilePage === activePage;
            link.classList.toggle('sales-mobile-nav__item--active', active);
            if (active) link.setAttribute('aria-current', 'page');
        });

        const sidebar = byId('sales-sidebar');
        const toggle = byId('sales-menu-toggle');
        const backdrop = byId('sales-sidebar-backdrop');

        function setSidebar(open) {
            if (!sidebar || !toggle || !backdrop) return;
            sidebar.classList.toggle('is-open', open);
            backdrop.classList.toggle('is-visible', open);
            toggle.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        }

        toggle.addEventListener('click', function () {
            setSidebar(!sidebar.classList.contains('is-open'));
        });
        backdrop.addEventListener('click', function () { setSidebar(false); });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 900) setSidebar(false);
        });
    }

    function initDashboard() {
        byId('dashboard-date').textContent = new Intl.DateTimeFormat('en-LK', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }).format(new Date());

        function renderChart(period) {
            const chartData = data.weeklySales[period] || [];
            const maxValue = Math.max.apply(null, chartData.map(function (point) { return point.value; }).concat([1]));
            const chart = byId('sales-chart');
            chart.innerHTML = '';
            chartData.forEach(function (point, index) {
                const column = document.createElement('div');
                column.className = 'chart-column';
                const height = point.value === 0 ? 0 : Math.max(4, Math.round((point.value / maxValue) * 100));
                const current = period === 'week' && index === 3;
                column.innerHTML =
                    '<div class="chart-column__track"><div class="chart-column__bar' + (current ? ' chart-column__bar--current' : '') + '" style="height:' + height + '%">' +
                    (point.value ? '<span class="chart-column__value">' + escapeHtml(money(point.value, true)) + '</span>' : '') +
                    '</div></div><span class="chart-column__label">' + escapeHtml(point.label) + '</span>';
                chart.appendChild(column);
            });
        }

        function renderRoutes() {
            byId('dashboard-route-list').innerHTML = data.routeShops.map(function (shop) {
                return '<article class="route-item"><span class="sales-avatar">' + escapeHtml(shop.initials) + '</span>' +
                    '<div><strong>' + escapeHtml(shop.name) + '</strong><span>' + escapeHtml(shop.note) + '</span></div>' +
                    '<span class="route-status route-status--' + slug(shop.status) + '">' + escapeHtml(shop.status) + '</span></article>';
            }).join('');
        }

        function renderDeliveries() {
            byId('dashboard-deliveries').innerHTML = data.deliveries.map(function (delivery) {
                return '<article class="delivery-item"><div class="delivery-item__top"><strong>' + escapeHtml(delivery.id) + '</strong>' +
                    '<span class="delivery-status delivery-status--' + slug(delivery.status) + '">' + escapeHtml(delivery.status) + '</span></div>' +
                    '<p>' + escapeHtml(delivery.items) + '</p><p>To: ' + escapeHtml(delivery.customer) + '</p></article>';
            }).join('');
        }

        byId('chart-period').addEventListener('change', function (event) {
            renderChart(event.target.value);
        });
        byId('view-all-shops').addEventListener('click', function () {
            showToast('Connect this action to your CustomerController index route.');
        });
        document.querySelector('.pos-shortcut').addEventListener('click', function (event) {
            if (this.getAttribute('href') === '#') {
                event.preventDefault();
                showToast('Connect this shortcut to your POS route.');
            }
        });

        renderChart('week');
        renderRoutes();
        renderDeliveries();
    }

    function initCustomers() {
        const state = {
            type: 'shop',
            filter: 'all',
            query: '',
            selectedId: null
        };
        const list = byId('customer-list');
        const detail = byId('customer-detail');
        const dialog = byId('customer-dialog');
        const form = byId('customer-form');

        function matchingCustomers() {
            const query = state.query.trim().toLowerCase();
            return data.customers.filter(function (customer) {
                const matchesType = customer.type === state.type;
                const matchesQuery = !query ||
                    customer.name.toLowerCase().includes(query) ||
                    customer.id.toLowerCase().includes(query) ||
                    customer.phone.toLowerCase().includes(query);
                const matchesFilter = state.filter === 'all' ||
                    (state.filter === 'overdue' && customer.overdue) ||
                    (state.filter === 'high-volume' && customer.highVolume);
                return matchesType && matchesQuery && matchesFilter;
            });
        }

        function purchaseStatusClass(status) {
            return status === 'Delivered' ? ' purchase-status--delivered' : '';
        }

        function renderList() {
            const customers = matchingCustomers();
            if (!customers.some(function (customer) { return customer.id === state.selectedId; })) {
                state.selectedId = customers[0] ? customers[0].id : null;
            }
            list.innerHTML = customers.map(function (customer) {
                return '<button type="button" class="customer-list-item' + (customer.id === state.selectedId ? ' customer-list-item--active' : '') + '" data-customer-id="' + escapeHtml(customer.id) + '">' +
                    '<span class="customer-list-item__top"><strong>' + escapeHtml(customer.name) + '</strong><span class="customer-id">' + escapeHtml(customer.id) + '</span></span>' +
                    '<p><svg class="sales-icon"><use href="#sales-icon-location"></use></svg> ' + escapeHtml(customer.address) + '</p>' +
                    '<span class="customer-list-item__bottom"><span><small>Outstanding</small><strong class="' + (customer.overdue ? 'is-overdue' : '') + '">' + money(customer.outstanding) + '</strong></span>' +
                    '<span class="sales-avatar">' + escapeHtml(customer.initials) + '</span></span></button>';
            }).join('');
            byId('customer-empty').classList.toggle('hidden', customers.length !== 0);
            renderDetail();
        }

        function renderDetail() {
            const customer = data.customers.find(function (item) { return item.id === state.selectedId; });
            if (!customer) {
                detail.innerHTML = '<div class="sales-card sales-empty">Select a customer to view their profile.</div>';
                return;
            }

            const rows = customer.purchases.map(function (purchase) {
                return '<tr><td><strong>' + escapeHtml(purchase.id) + '</strong></td><td>' + escapeHtml(displayDate(purchase.date)) + '</td>' +
                    '<td>' + escapeHtml(purchase.items) + '</td><td><strong>' + money(purchase.total) + '</strong></td>' +
                    '<td><span class="purchase-status' + purchaseStatusClass(purchase.status) + '">' + escapeHtml(purchase.status) + '</span></td></tr>';
            }).join('');

            detail.innerHTML =
                '<div class="customer-identity-grid">' +
                    '<article class="sales-card customer-identity"><span class="customer-identity__mark">' + escapeHtml(customer.initials) + '</span>' +
                        '<div><div><h2>' + escapeHtml(customer.name) + '</h2><span class="customer-active">' + (customer.active ? 'Active' : 'Inactive') + '</span></div>' +
                        '<p class="customer-identity__sub">' + (customer.type === 'shop' ? 'Retail Partner' : 'Walk-in Customer') + ' · Account since ' + escapeHtml(customer.accountSince) + '</p>' +
                        '<div class="customer-contact-grid"><span class="customer-contact"><svg class="sales-icon"><use href="#sales-icon-phone"></use></svg><span>' + escapeHtml(customer.phone) + '</span></span>' +
                        '<span class="customer-contact"><svg class="sales-icon"><use href="#sales-icon-mail"></use></svg><span>' + escapeHtml(customer.email || 'No email provided') + '</span></span>' +
                        '<span class="customer-contact"><svg class="sales-icon"><use href="#sales-icon-location"></use></svg><span>' + escapeHtml(customer.address) + '</span></span></div></div>' +
                        '<button class="sales-button sales-button--secondary" type="button" data-edit-customer>Edit</button></article>' +
                    '<article class="sales-card balance-card"><div><h3>Outstanding Balance</h3><div class="balance-card__amount' + (customer.overdue ? ' balance-card__amount--overdue' : '') + '">' + money(customer.outstanding) + '</div>' +
                        '<p>' + (customer.overdue ? 'Payment terms exceeded' : customer.outstanding ? 'Balance within payment terms' : 'Account is fully paid') + '</p></div>' +
                        '<button class="sales-button sales-button--secondary" type="button" data-statement>Send Statement</button></article>' +
                '</div>' +
                '<section class="sales-card customer-metrics"><div class="customer-metric"><span class="metrics-label">YTD Revenue</span><strong>' + money(customer.ytdRevenue, true) + '</strong><small class="positive">Active customer sales</small></div>' +
                    '<div class="customer-metric"><span class="metrics-label">Average Order Value</span><strong>' + money(customer.averageOrder) + '</strong><small>Based on ' + customer.orderCount + ' orders</small></div>' +
                    '<div class="customer-metric"><span class="metrics-label">Return Rate</span><strong>' + customer.returnRate.toFixed(1) + '%</strong><small>Product return history</small></div></section>' +
                '<section class="sales-card purchase-card"><div class="sales-card__header"><div><h2>Recent Purchase History</h2><p>Latest customer sales and orders</p></div><button class="sales-text-button" type="button" data-view-history>View All</button></div>' +
                    '<div class="purchase-table-wrap"><table class="purchase-table"><thead><tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th></tr></thead><tbody>' + rows + '</tbody></table></div></section>';
        }

        function openCustomerDialog(customer) {
            form.reset();
            byId('customer-edit-id').value = customer ? customer.id : '';
            byId('customer-dialog-title').textContent = customer ? 'Edit Customer' : 'New Customer';
            byId('customer-form-type').value = customer ? customer.type : state.type;
            byId('customer-form-name').value = customer ? customer.name : '';
            byId('customer-form-phone').value = customer ? customer.phone : '';
            byId('customer-form-email').value = customer ? customer.email : '';
            byId('customer-form-address').value = customer ? customer.address : '';
            dialog.showModal();
        }

        function closeDialog() {
            dialog.close();
        }

        document.querySelectorAll('[data-customer-type]').forEach(function (tab) {
            tab.addEventListener('click', function () {
                state.type = tab.dataset.customerType;
                state.filter = 'all';
                state.query = '';
                state.selectedId = null;
                byId('customer-search').value = '';
                document.querySelectorAll('[data-customer-type]').forEach(function (item) {
                    const active = item === tab;
                    item.classList.toggle('customer-type-tab--active', active);
                    item.setAttribute('aria-selected', String(active));
                });
                document.querySelectorAll('[data-customer-filter]').forEach(function (filter) {
                    filter.classList.toggle('customer-filter--active', filter.dataset.customerFilter === 'all');
                });
                renderList();
            });
        });

        byId('customer-filters').addEventListener('click', function (event) {
            const button = event.target.closest('[data-customer-filter]');
            if (!button) return;
            state.filter = button.dataset.customerFilter;
            document.querySelectorAll('[data-customer-filter]').forEach(function (item) {
                item.classList.toggle('customer-filter--active', item === button);
            });
            renderList();
        });

        byId('customer-search').addEventListener('input', function (event) {
            state.query = event.target.value;
            renderList();
        });

        list.addEventListener('click', function (event) {
            const item = event.target.closest('[data-customer-id]');
            if (!item) return;
            state.selectedId = item.dataset.customerId;
            renderList();
        });

        detail.addEventListener('click', function (event) {
            const customer = data.customers.find(function (item) { return item.id === state.selectedId; });
            if (event.target.closest('[data-edit-customer]')) openCustomerDialog(customer);
            if (event.target.closest('[data-statement]')) showToast('Connect this action to your statement/invoice backend.');
            if (event.target.closest('[data-view-history]')) showToast('Connect this action to the customer purchase history route.');
        });

        byId('new-customer').addEventListener('click', function () {
            openCustomerDialog(null);
        });
        document.querySelectorAll('[data-close-customer-dialog]').forEach(function (button) {
            button.addEventListener('click', closeDialog);
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!form.reportValidity()) return;

            const editId = byId('customer-edit-id').value;
            const existing = data.customers.find(function (item) { return item.id === editId; });
            const name = byId('customer-form-name').value.trim();
            const initials = name.split(/\s+/).slice(0, 2).map(function (part) { return part.charAt(0); }).join('').toUpperCase() || 'CU';
            const values = {
                type: byId('customer-form-type').value,
                name: name,
                initials: initials,
                phone: byId('customer-form-phone').value.trim(),
                email: byId('customer-form-email').value.trim(),
                address: byId('customer-form-address').value.trim()
            };

            if (existing) {
                Object.assign(existing, values);
                state.type = existing.type;
                state.selectedId = existing.id;
                showToast(existing.name + ' updated.');
            } else {
                const customer = Object.assign({
                    id: 'CUS-' + String(data.customers.length + 1).padStart(5, '0'),
                    active: true,
                    accountSince: new Intl.DateTimeFormat('en-LK', { month: 'long', year: 'numeric' }).format(new Date()),
                    outstanding: 0,
                    overdue: false,
                    highVolume: false,
                    ytdRevenue: 0,
                    averageOrder: 0,
                    orderCount: 0,
                    returnRate: 0,
                    purchases: []
                }, values);
                data.customers.push(customer);
                state.type = customer.type;
                state.selectedId = customer.id;
                showToast(customer.name + ' added.');
            }

            state.filter = 'all';
            state.query = '';
            byId('customer-search').value = '';
            document.querySelectorAll('[data-customer-type]').forEach(function (tab) {
                const active = tab.dataset.customerType === state.type;
                tab.classList.toggle('customer-type-tab--active', active);
                tab.setAttribute('aria-selected', String(active));
            });
            dialog.close();
            renderList();
        });

        renderList();
    }

    initShell();
    if (!page) return;
    if (page.dataset.salesPage === 'dashboard') initDashboard();
    if (page.dataset.salesPage === 'customers') initCustomers();
})();
