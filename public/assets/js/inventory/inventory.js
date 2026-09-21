(function () {
    'use strict';

    const data = window.INVENTORY_MOCK_DATA || { items: [], locations: [], incomingPurchases: 0 };
    const page = document.querySelector('[data-sales-page="inventory"]');
    if (!page) return;

    const state = {
        location: 'All Locations',
        query: '',
        statusFilter: 'all'
    };

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

    function statusLabel(status) {
        if (status === 'low') return 'Low Stock';
        if (status === 'critical') return 'Critical';
        if (status === 'restocking') return 'Restocking';
        return 'Optimal';
    }

    function deriveStatus(item) {
        if (item.status === 'restocking') return 'restocking';
        if (item.qty <= Math.max(1, Math.floor(item.reorderLevel / 2))) return 'critical';
        if (item.qty <= item.reorderLevel) return 'low';
        return 'optimal';
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

    function filteredItems() {
        const query = state.query.trim().toLowerCase();
        return data.items.filter(function (item) {
            const status = deriveStatus(item);
            const matchesLocation = state.location === 'All Locations' || item.location === state.location;
            const matchesStatus = state.statusFilter === 'all' || status === state.statusFilter;
            const matchesQuery = !query ||
                item.partNo.toLowerCase().indexOf(query) !== -1 ||
                item.name.toLowerCase().indexOf(query) !== -1 ||
                item.bin.toLowerCase().indexOf(query) !== -1;
            return matchesLocation && matchesStatus && matchesQuery;
        });
    }

    function renderKpis() {
        const stockValue = data.items.reduce(function (sum, item) {
            return sum + (item.qty * item.unitCost);
        }, 0);
        const lowStock = data.items.filter(function (item) {
            const status = deriveStatus(item);
            return status === 'low' || status === 'critical';
        }).length;

        byId('inventory-stock-value').textContent = money(stockValue, true);
        byId('inventory-low-stock').textContent = String(lowStock);
        byId('inventory-incoming').textContent = String(data.incomingPurchases);
    }

    function renderLocations() {
        const host = byId('inventory-locations');
        host.innerHTML = data.locations.map(function (location) {
            const active = location === state.location ? ' inventory-chip--active' : '';
            return '<button class="inventory-chip' + active + '" type="button" role="tab" data-location="' +
                escapeHtml(location) + '">' + escapeHtml(location) + '</button>';
        }).join('');
    }

    function renderTable() {
        const rows = filteredItems();
        const body = byId('inventory-table-body');
        const empty = byId('inventory-empty');

        body.innerHTML = rows.map(function (item, index) {
            const status = deriveStatus(item);
            const qtyClass = (status === 'low' || status === 'critical') ? ' inventory-qty--alert' : '';
            const zebra = index % 2 === 1 ? ' inventory-row--alt' : '';
            return '<tr class="inventory-row' + zebra + '" data-product-id="' + item.id + '">' +
                '<td class="inventory-part">' + escapeHtml(item.partNo) + '</td>' +
                '<td>' + escapeHtml(item.name) + '</td>' +
                '<td class="inventory-muted">' + escapeHtml(item.location + ' - ' + item.bin) + '</td>' +
                '<td class="inventory-table__qty' + qtyClass + '">' + escapeHtml(item.qty.toLocaleString('en-LK')) + '</td>' +
                '<td><span class="inventory-status inventory-status--' + status + '">' + escapeHtml(statusLabel(status)) + '</span></td>' +
                '<td class="inventory-muted">' + escapeHtml(item.lastMovement) + '</td>' +
                '<td class="inventory-table__action">' +
                    '<button class="sales-icon-button" type="button" data-edit-stock="' + item.id + '" aria-label="Edit stock for ' + escapeHtml(item.name) + '">' +
                        '<svg class="sales-icon"><use href="#sales-icon-edit"></use></svg>' +
                    '</button>' +
                '</td>' +
                '</tr>';
        }).join('');

        empty.classList.toggle('hidden', rows.length > 0);
    }

    function fillProductSelect(selectedId) {
        const select = byId('stock-in-product');
        select.innerHTML = '<option value="">Select a product</option>' + data.items.map(function (item) {
            const selected = String(item.id) === String(selectedId) ? ' selected' : '';
            return '<option value="' + item.id + '"' + selected + '>' +
                escapeHtml(item.partNo + ' — ' + item.name) + '</option>';
        }).join('');
    }

    function openStockDialog(productId) {
        const dialog = byId('stock-in-dialog');
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        byId('stock-in-dialog-title').textContent = item ? 'Add Stock — ' + item.name : 'Add Stock';
        byId('stock-in-product-id').value = item ? String(item.id) : '';
        byId('stock-in-qty').value = '';
        byId('stock-in-notes').value = '';
        fillProductSelect(item ? item.id : '');
        if (item) {
            byId('stock-in-location').value = item.location;
        }
        dialog.showModal();
    }

    function closeStockDialog() {
        byId('stock-in-dialog').close();
    }

    function recordStockIn() {
        const productId = byId('stock-in-product').value;
        const qty = Number(byId('stock-in-qty').value);
        const location = byId('stock-in-location').value;
        const notes = byId('stock-in-notes').value.trim();
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });

        if (!item || !qty || qty < 1) {
            showToast('Select a product and enter a quantity.');
            return false;
        }

        item.qty += qty;
        item.location = location;
        item.lastMovement = 'Just now (In)';
        if (item.status === 'restocking' && item.qty > item.reorderLevel) {
            item.status = 'optimal';
        }
        if (notes) {
            item.lastMovement = 'Just now (In) — ' + notes;
        }

        renderKpis();
        renderTable();
        showToast(qty + ' units added to ' + item.partNo + '. Connect this action to StockMovementController.');
        return true;
    }

    byId('inventory-locations').addEventListener('click', function (event) {
        const button = event.target.closest('[data-location]');
        if (!button) return;
        state.location = button.dataset.location;
        renderLocations();
        renderTable();
    });

    function onSearch(event) {
        state.query = event.target.value;
        renderTable();
    }

    const headerSearch = byId('inventory-header-search');
    if (headerSearch) {
        headerSearch.addEventListener('input', onSearch);
    }

    byId('inventory-filter').addEventListener('click', function () {
        const cycle = ['all', 'low', 'critical', 'restocking', 'optimal'];
        const next = cycle[(cycle.indexOf(state.statusFilter) + 1) % cycle.length];
        state.statusFilter = next;
        const labels = {
            all: 'Showing all statuses',
            low: 'Filtered to low stock',
            critical: 'Filtered to critical stock',
            restocking: 'Filtered to restocking',
            optimal: 'Filtered to optimal stock'
        };
        showToast(labels[next]);
        renderTable();
    });

    ['add-stock', 'add-stock-toolbar'].forEach(function (id) {
        byId(id).addEventListener('click', function () {
            openStockDialog('');
        });
    });

    byId('inventory-table-body').addEventListener('click', function (event) {
        const button = event.target.closest('[data-edit-stock]');
        if (!button) return;
        openStockDialog(button.dataset.editStock);
    });

    document.querySelectorAll('[data-close-stock-dialog]').forEach(function (button) {
        button.addEventListener('click', closeStockDialog);
    });

    byId('stock-in-form').addEventListener('submit', function (event) {
        event.preventDefault();
        if (recordStockIn()) {
            closeStockDialog();
        }
    });

    renderLocations();
    renderKpis();
    renderTable();
    fillProductSelect('');
})();
