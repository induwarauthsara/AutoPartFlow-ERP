/**
 * Customer Management: tabs, search, master-detail, register/edit/delete.
 * Safe to edit: filters, validation, delete confirm copy, View All history URL.
 * PHP later: POST form to CustomerController store/update; DELETE destroy.
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-sales-page="customers"]');
    if (!page) return;

    const u = window.SalesRepUtils;
    const data = window.SALES_REP_MOCK_DATA || { customers: [] };
    const byId = u.byId;
    const state = { type: 'shop', filter: 'all', query: '', selectedId: null };
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

    function renderList() {
        const customers = matchingCustomers();
        if (!customers.some(function (customer) { return customer.id === state.selectedId; })) {
            state.selectedId = customers[0] ? customers[0].id : null;
        }
        list.innerHTML = customers.map(function (customer) {
            return '<button type="button" class="customer-list-item' + (customer.id === state.selectedId ? ' customer-list-item--active' : '') + '" data-customer-id="' + u.escapeHtml(customer.id) + '">' +
                '<span class="customer-list-item__top"><strong>' + u.escapeHtml(customer.name) + '</strong><span class="customer-id">' + u.escapeHtml(customer.id) + '</span></span>' +
                '<p><svg class="sales-icon"><use href="#sales-icon-location"></use></svg> ' + u.escapeHtml(customer.address) + '</p>' +
                '<span class="customer-list-item__bottom"><span><small>Outstanding</small><strong class="' + (customer.overdue ? 'is-overdue' : '') + '">' + u.money(customer.outstanding) + '</strong></span>' +
                '<span class="sales-avatar">' + u.escapeHtml(customer.initials) + '</span></span></button>';
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
            return '<tr><td><strong>' + u.escapeHtml(purchase.id) + '</strong></td><td>' + u.escapeHtml(u.displayDate(purchase.date)) + '</td>' +
                '<td>' + u.escapeHtml(purchase.items) + '</td><td><strong>' + u.money(purchase.total) + '</strong></td>' +
                '<td><span class="sales-badge ' + u.statusBadgeClass(purchase.status) + '">' + u.escapeHtml(purchase.status) + '</span></td></tr>';
        }).join('');

        detail.innerHTML =
            '<div class="customer-identity-grid">' +
                '<article class="sales-card customer-identity"><span class="customer-identity__mark">' + u.escapeHtml(customer.initials) + '</span>' +
                    '<div><div><h2>' + u.escapeHtml(customer.name) + '</h2><span class="customer-active">' + (customer.active ? 'Active' : 'Inactive') + '</span></div>' +
                    '<p class="customer-identity__sub">' + (customer.type === 'shop' ? 'Retail Partner' : 'Walk-in Customer') + ' · Account since ' + u.escapeHtml(customer.accountSince) + '</p>' +
                    '<div class="customer-contact-grid"><span class="customer-contact"><svg class="sales-icon"><use href="#sales-icon-phone"></use></svg><span>' + u.escapeHtml(customer.phone) + '</span></span>' +
                    '<span class="customer-contact"><svg class="sales-icon"><use href="#sales-icon-mail"></use></svg><span>' + u.escapeHtml(customer.email || 'No email provided') + '</span></span>' +
                    '<span class="customer-contact"><svg class="sales-icon"><use href="#sales-icon-location"></use></svg><span>' + u.escapeHtml(customer.address) + '</span></span></div>' +
                    '<p class="rep-chip"><span class="sales-avatar sales-avatar--small" aria-hidden="true"><svg class="sales-icon"><use href="#sales-icon-user"></use></svg></span> Assigned representative: You</p></div>' +
                    '<div class="customer-identity__actions">' +
                        '<button class="sales-button sales-button--secondary" type="button" data-edit-customer>Edit</button>' +
                        '<button class="sales-button sales-button--danger" type="button" data-delete-customer>Delete</button>' +
                    '</div></article>' +
                '<article class="sales-card balance-card"><div><h3>Outstanding Balance</h3><div class="balance-card__amount' + (customer.overdue ? ' balance-card__amount--overdue' : '') + '">' + u.money(customer.outstanding) + '</div>' +
                    '<p>' + (customer.overdue ? 'Payment terms exceeded' : customer.outstanding ? 'Balance within payment terms' : 'Account is fully paid') + '</p></div>' +
                    '<button class="sales-button sales-button--secondary" type="button" data-statement>Send Statement</button></article>' +
            '</div>' +
            '<section class="sales-card customer-metrics"><div class="customer-metric"><span class="metrics-label">YTD Revenue</span><strong>' + u.money(customer.ytdRevenue, true) + '</strong><small class="positive">Active customer sales</small></div>' +
                '<div class="customer-metric"><span class="metrics-label">Average Order Value</span><strong>' + u.money(customer.averageOrder) + '</strong><small>Based on ' + customer.orderCount + ' orders</small></div>' +
                '<div class="customer-metric"><span class="metrics-label">Return Rate</span><strong>' + customer.returnRate.toFixed(1) + '%</strong><small>Product return history</small></div></section>' +
            '<section class="sales-card purchase-card"><div class="sales-card__header"><div><h2>Recent Purchase History</h2><p>Latest customer sales and orders</p></div><a class="sales-text-button" href="' + u.escapeHtml(page.dataset.ordersUrl) + '">View All</a></div>' +
                '<div class="purchase-table-wrap"><table class="purchase-table"><thead><tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th></tr></thead><tbody>' +
                (rows || '<tr><td colspan="5" class="sales-empty">No purchases yet.</td></tr>') +
                '</tbody></table></div></section>';
    }

    function setError(id, message) {
        const input = byId(id);
        const hint = byId(id + '-error');
        input.classList.toggle('is-invalid', Boolean(message));
        if (hint) hint.textContent = message || '';
        return !message;
    }

    /**
     * Phone required. Email optional but must look like an email if filled.
     */
    function validateForm() {
        const phone = byId('customer-form-phone').value.trim();
        const email = byId('customer-form-email').value.trim();
        const nameOk = setError('customer-form-name', byId('customer-form-name').value.trim() ? '' : 'Name is required.');
        const phoneOk = setError('customer-form-phone', phone ? '' : 'Phone number is required.');
        const emailOk = setError('customer-form-email', (!email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) ? '' : 'Enter a valid email or leave blank.');
        return nameOk && phoneOk && emailOk;
    }

    /**
     * Remove a customer from mock data after confirm.
     * PHP later: DELETE to CustomerController@destroy, then refresh this list.
     */
    function deleteCustomer(customer) {
        if (!customer) return;
        u.confirmAction(
            'Delete customer?',
            customer.outstanding
                ? customer.name + ' still has ' + u.money(customer.outstanding) + ' outstanding. Delete this account anyway?'
                : 'Remove ' + customer.name + ' (' + customer.id + ')? This cannot be undone.',
            'Delete'
        ).then(function (ok) {
            if (!ok) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
            fetch(baseUrl.replace(/\/$/, '') + '/sales/customers/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    id: customer.databaseId || 0,
                    customer_code: customer.id,
                    csrf_token: csrfToken
                })
            }).then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.ok) {
                    const index = data.customers.findIndex(function (item) { return item.id === customer.id; });
                    if (index !== -1) data.customers.splice(index, 1);
                    state.selectedId = null;
                    renderList();
                    u.showToast(customer.name + ' deleted.');
                } else {
                    u.showToast(json.message || 'Failed to delete customer.');
                }
            }).catch(function () {
                u.showToast('Network error deleting customer.');
            });
        });
    }

    function openCustomerDialog(customer) {
        form.reset();
        ['customer-form-name', 'customer-form-phone', 'customer-form-email'].forEach(function (id) { setError(id, ''); });
        byId('customer-edit-id').value = customer ? customer.id : '';
        byId('customer-dialog-title').textContent = customer ? 'Edit Customer' : 'New Customer';
        byId('customer-form-type').value = customer ? customer.type : state.type;
        byId('customer-form-name').value = customer ? customer.name : '';
        byId('customer-form-phone').value = customer ? customer.phone : '';
        byId('customer-form-email').value = customer ? customer.email : '';
        byId('customer-form-address').value = customer ? customer.address : '';
        dialog.showModal();
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
                filter.classList.toggle('sales-filter--active', filter.dataset.customerFilter === 'all');
            });
            renderList();
        });
    });

    byId('customer-filters').addEventListener('click', function (event) {
        const button = event.target.closest('[data-customer-filter]');
        if (!button) return;
        state.filter = button.dataset.customerFilter;
        document.querySelectorAll('[data-customer-filter]').forEach(function (item) {
            item.classList.toggle('sales-filter--active', item === button);
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
        if (event.target.closest('[data-delete-customer]')) deleteCustomer(customer);
        if (event.target.closest('[data-statement]')) u.showToast('Connect Send Statement to your invoice/statement PHP action.');
    });

    byId('new-customer').addEventListener('click', function () { openCustomerDialog(null); });
    document.querySelectorAll('[data-close-customer-dialog]').forEach(function (button) {
        button.addEventListener('click', function () { dialog.close(); });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!validateForm()) return;

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

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
        const payload = {
            id: existing ? (existing.databaseId || 0) : 0,
            customer_code: existing ? existing.id : '',
            name: name,
            customer_type: values.type,
            phone: values.phone,
            email: values.email,
            address: values.address,
            csrf_token: csrfToken
        };

        fetch(baseUrl.replace(/\/$/, '') + '/sales/customers/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload)
        }).then(function (res) { return res.json(); })
        .then(function (json) {
            if (json.ok) {
                if (json.customers) {
                    data.customers = json.customers;
                } else if (existing) {
                    Object.assign(existing, values);
                } else {
                    const newId = json.customer?.code || ('CUS-' + String(data.customers.length + 1).padStart(5, '0'));
                    const customer = Object.assign({
                        id: newId,
                        databaseId: json.customer?.id,
                        active: true,
                        accountSince: new Intl.DateTimeFormat('en-LK', { month: 'long', year: 'numeric' }).format(new Date()),
                        outstanding: 0, overdue: false, highVolume: false,
                        ytdRevenue: 0, averageOrder: 0, orderCount: 0, returnRate: 0, purchases: []
                    }, values);
                    data.customers.unshift(customer);
                }
                state.type = values.type;
                state.selectedId = existing ? existing.id : (data.customers[0] ? data.customers[0].id : null);
                u.showToast(name + (existing ? ' updated.' : ' added.'));

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
            } else {
                u.showToast(json.message || 'Failed to save customer.');
            }
        }).catch(function () {
            u.showToast('Network error saving customer.');
        });
    });

    renderList();
})();
