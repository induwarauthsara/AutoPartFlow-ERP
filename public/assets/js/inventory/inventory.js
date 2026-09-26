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
        if (item.qty <= 0) return 'critical';
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
        }, 3200);
    }

    function filteredItems() {
        const query = state.query.trim().toLowerCase();
        return data.items.filter(function (item) {
            const matchesLocation = state.location === 'All Locations' || item.location === state.location;
            const matchesQuery = !query ||
                item.partNo.toLowerCase().includes(query) ||
                item.name.toLowerCase().includes(query) ||
                (item.bin && item.bin.toLowerCase().includes(query));
            const status = deriveStatus(item);
            const matchesStatus = state.statusFilter === 'all' || status === state.statusFilter;
            return matchesLocation && matchesQuery && matchesStatus;
        });
    }

    function renderKpis() {
        const stockValue = data.items.reduce(function (total, item) {
            return total + (item.qty * (item.unitCost || 0));
        }, 0);

        const lowStock = data.items.filter(function (item) {
            const status = deriveStatus(item);
            return status === 'low' || status === 'critical';
        }).length;

        byId('inventory-stock-value').textContent = money(stockValue, true);
        byId('inventory-low-stock').textContent = String(lowStock);
        byId('inventory-incoming').textContent = String(data.incomingPurchases || 0);
    }

    function renderLocations() {
        const host = byId('inventory-locations');
        if (!host) return;
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
        if (!body) return;

        body.innerHTML = rows.map(function (item, index) {
            const status = deriveStatus(item);
            const qtyClass = (status === 'low' || status === 'critical') ? ' inventory-qty--alert' : '';
            const zebra = index % 2 === 1 ? ' inventory-row--alt' : '';
            return '<tr class="inventory-row' + zebra + '" data-product-id="' + item.id + '">' +
                '<td class="inventory-part">' + escapeHtml(item.partNo) + '</td>' +
                '<td>' + escapeHtml(item.name) + '</td>' +
                '<td class="inventory-muted">' + escapeHtml(item.location + ' - ' + (item.bin || 'A01')) + '</td>' +
                '<td class="inventory-table__qty' + qtyClass + '">' + escapeHtml(item.qty.toLocaleString('en-LK')) + '</td>' +
                '<td><span class="inventory-status inventory-status--' + status + '">' + escapeHtml(statusLabel(status)) + '</span></td>' +
                '<td class="inventory-muted">' + escapeHtml(item.lastMovement || 'Active') + '</td>' +
                '<td class="inventory-table__action" style="white-space:nowrap;">' +
                    '<button class="sales-icon-button" type="button" data-edit-stock="' + item.id + '" title="Add Stock">' +
                        '<svg class="sales-icon" viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/></svg>' +
                    '</button>' +
                    '<button class="sales-icon-button" type="button" data-adjust-stock="' + item.id + '" title="Adjust Count" style="margin-left:4px;">' +
                        '<svg class="sales-icon" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>' +
                    '</button>' +
                    '<button class="sales-icon-button sales-icon-button--danger" type="button" data-writeoff-stock="' + item.id + '" title="Write Off Damaged" style="margin-left:4px;">' +
                        '<svg class="sales-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>' +
                    '</button>' +
                '</td>' +
                '</tr>';
        }).join('');

        if (empty) {
            empty.classList.toggle('hidden', rows.length > 0);
        }
    }

    function fillProductSelect(selectedId) {
        const select = byId('stock-in-product');
        if (!select) return;
        if (select.children.length > 1) {
            if (selectedId) select.value = String(selectedId);
            return;
        }
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
            byId('stock-in-product').value = String(item.id);
            byId('stock-in-location').value = item.location;
            if (byId('stock-in-cost')) byId('stock-in-cost').value = item.unitCost || '';
        }
        dialog.showModal();
    }

    function closeStockDialog() {
        byId('stock-in-dialog').close();
    }

    function openAdjustDialog(productId) {
        const dialog = byId('stock-adjust-dialog');
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        if (!item || !dialog) return;
        byId('adjust-product-id').value = item.id;
        byId('adjust-product-name').textContent = item.partNo + ' — ' + item.name;
        byId('adjust-qty').value = item.qty;
        byId('adjust-notes').value = '';
        dialog.showModal();
    }

    function closeAdjustDialog() {
        const dialog = byId('stock-adjust-dialog');
        if (dialog) dialog.close();
    }

    function openWriteoffDialog(productId) {
        const dialog = byId('stock-writeoff-dialog');
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        if (!item || !dialog) return;
        byId('writeoff-product-id').value = item.id;
        byId('writeoff-product-name').textContent = item.partNo + ' — ' + item.name;
        byId('writeoff-current-onhand').textContent = item.qty.toLocaleString('en-LK');
        byId('writeoff-qty').value = '1';
        byId('writeoff-qty').max = item.qty;
        byId('writeoff-reason').value = '';
        dialog.showModal();
    }

    function closeWriteoffDialog() {
        const dialog = byId('stock-writeoff-dialog');
        if (dialog) dialog.close();
    }

    function getApiConfig() {
        return {
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
            baseUrl: (document.querySelector('meta[name="base-url"]')?.content || '/').replace(/\/$/, '')
        };
    }

    function recordStockIn() {
        const productId = byId('stock-in-product').value;
        const qty = Number(byId('stock-in-qty').value);
        const costInput = byId('stock-in-cost');
        const unitCost = costInput && costInput.value ? Number(costInput.value) : null;
        const notes = byId('stock-in-notes').value.trim();

        if (!productId || !qty || qty < 1) {
            showToast('Select a product and enter a positive quantity.');
            return;
        }

        const config = getApiConfig();
        fetch(config.baseUrl + '/inventory/stock-in', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            body: JSON.stringify({
                product_id: Number(productId),
                quantity: qty,
                unit_cost: unitCost,
                notes: notes,
                csrf_token: config.csrfToken
            })
        }).then(function (res) { return res.json(); })
        .then(function (json) {
            if (json.ok) {
                if (json.items) data.items = json.items;
                closeStockDialog();
                renderKpis();
                renderTable();
                showToast(json.message || 'Stock added successfully.');
            } else {
                showToast(json.message || 'Failed to record stock in.');
            }
        }).catch(function () {
            showToast('Network error recording stock in.');
        });
    }

    function recordAdjustment() {
        const productId = Number(byId('adjust-product-id').value);
        const qty = Number(byId('adjust-qty').value);
        const notes = byId('adjust-notes').value.trim();

        if (!productId || qty < 0) {
            showToast('Valid count is required.');
            return;
        }

        const config = getApiConfig();
        fetch(config.baseUrl + '/inventory/adjust', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            body: JSON.stringify({
                product_id: productId,
                quantity: qty,
                notes: notes,
                csrf_token: config.csrfToken
            })
        }).then(function (res) { return res.json(); })
        .then(function (json) {
            if (json.ok) {
                if (json.items) data.items = json.items;
                closeAdjustDialog();
                renderKpis();
                renderTable();
                showToast(json.message || 'Stock adjusted successfully.');
            } else {
                showToast(json.message || 'Failed to adjust stock.');
            }
        }).catch(function () {
            showToast('Network error adjusting stock.');
        });
    }

    function recordWriteOff() {
        const productId = Number(byId('writeoff-product-id').value);
        const qty = Number(byId('writeoff-qty').value);
        const reason = byId('writeoff-reason').value.trim();

        if (!productId || qty < 1) {
            showToast('Valid write-off quantity is required.');
            return;
        }

        const config = getApiConfig();
        fetch(config.baseUrl + '/inventory/write-off', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
            body: JSON.stringify({
                product_id: productId,
                quantity: qty,
                reason: reason,
                csrf_token: config.csrfToken
            })
        }).then(function (res) { return res.json(); })
        .then(function (json) {
            if (json.ok) {
                if (json.items) data.items = json.items;
                closeWriteoffDialog();
                renderKpis();
                renderTable();
                showToast(json.message || 'Stock written off successfully.');
            } else {
                showToast(json.message || 'Failed to write off stock.');
            }
        }).catch(function () {
            showToast('Network error writing off stock.');
        });
    }

    // Event listeners
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
        const el = byId(id);
        if (el) {
            el.addEventListener('click', function () {
                openStockDialog('');
            });
        }
    });

    byId('inventory-table-body').addEventListener('click', function (event) {
        const editBtn = event.target.closest('[data-edit-stock]');
        if (editBtn) {
            openStockDialog(editBtn.dataset.editStock);
            return;
        }
        const adjBtn = event.target.closest('[data-adjust-stock]');
        if (adjBtn) {
            openAdjustDialog(adjBtn.dataset.adjustStock);
            return;
        }
        const writeBtn = event.target.closest('[data-writeoff-stock]');
        if (writeBtn) {
            openWriteoffDialog(writeBtn.dataset.writeoffStock);
            return;
        }
    });

    document.querySelectorAll('[data-close-stock-dialog]').forEach(function (button) {
        button.addEventListener('click', closeStockDialog);
    });
    document.querySelectorAll('[data-close-adjust-dialog]').forEach(function (button) {
        button.addEventListener('click', closeAdjustDialog);
    });
    document.querySelectorAll('[data-close-writeoff-dialog]').forEach(function (button) {
        button.addEventListener('click', closeWriteoffDialog);
    });

    byId('stock-in-form').addEventListener('submit', function (event) {
        event.preventDefault();
        recordStockIn();
    });

    const adjustForm = byId('stock-adjust-form');
    if (adjustForm) {
        adjustForm.addEventListener('submit', function (event) {
            event.preventDefault();
            recordAdjustment();
        });
    }

    const writeoffForm = byId('stock-writeoff-form');
    if (writeoffForm) {
        writeoffForm.addEventListener('submit', function (event) {
            event.preventDefault();
            recordWriteOff();
        });
    }

    renderLocations();
    renderKpis();
    renderTable();
    fillProductSelect('');
})();
