(function () {
    'use strict';

    const data = window.INVENTORY_DATA || window.INVENTORY_MOCK_DATA || { items: [], locations: [], incomingPurchases: 0 };
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
                '<td class="inventory-muted">' + escapeHtml(item.bin ? item.location + ' - ' + item.bin : item.location) + '</td>' +
                '<td class="inventory-table__qty' + qtyClass + '">' + escapeHtml(item.qty.toLocaleString('en-LK')) + '</td>' +
                '<td><span class="inventory-status inventory-status--' + status + '">' + escapeHtml(statusLabel(status)) + '</span></td>' +
                '<td class="inventory-muted">' + escapeHtml(item.lastMovement) + '</td>' +
                '<td class="inventory-table__action">' +
                    '<button class="sales-button sales-button--primary sales-button--compact" type="button" data-add-stock="' + item.id + '">' +
                        '<svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>' +
                        'Add Stock' +
                    '</button>' +
                    '<button class="sales-icon-button sales-icon-button--danger" type="button" data-delete-stock="' + item.id + '" aria-label="Delete ' + escapeHtml(item.name) + '">' +
                        '<svg class="sales-icon" viewBox="0 0 24 24" aria-hidden="true">' +
                            '<path d="M8 3h8l1 2h4v2H3V5h4l1-2Zm-2 6h12l-1 12H7L6 9Zm3 2v7h2v-7H9Zm4 0v7h2v-7h-2Z" fill="currentColor"></path>' +
                        '</svg>' +
                    '</button>' +
                '</td>' +
                '</tr>';
        }).join('');

        empty.classList.toggle('hidden', rows.length > 0);
    }

    function fillProductSelect(selectedId) {
        const select = byId('stock-in-product');
        if (!select) return;
        const options = ['<option value="">Select a product</option>'];
        data.items.forEach(function (item) {
            const selected = String(item.id) === String(selectedId) ? ' selected' : '';
            options.push('<option value="' + item.id + '"' + selected + '>' +
                escapeHtml(item.partNo + ' — ' + item.name) + '</option>');
        });
        options.push('<option value="__new__">+ Create New Item...</option>');
        select.innerHTML = options.join('');
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

    function openItemDialog() {
        const dialog = byId('new-item-dialog');
        if (!dialog) return;
        const form = byId('new-item-form');
        if (form) form.reset();
        dialog.showModal();
    }

    function closeItemDialog() {
        const dialog = byId('new-item-dialog');
        if (dialog) dialog.close();
    }

    async function recordStockIn() {
        const productId = byId('stock-in-product').value;
        const qty = Number(byId('stock-in-qty').value);
        const location = byId('stock-in-location').value;
        const notes = byId('stock-in-notes').value.trim();
        const submitBtn = byId('stock-in-submit') || byId('stock-in-form').querySelector('button[type="submit"]');

        if (!productId || productId === '__new__') {
            showToast('Please select a product.');
            return false;
        }

        if (!qty || qty < 1) {
            showToast('Please enter a valid quantity of at least 1.');
            return false;
        }

        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Recording...';

        try {
            const baseUrl = (window.APP_CONFIG && window.APP_CONFIG.baseUrl) ? window.APP_CONFIG.baseUrl : '';
            const csrfToken = (window.APP_CONFIG && window.APP_CONFIG.csrfToken) || data.csrfToken || '';

            const response = await fetch(baseUrl + '/inventory/stock-in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    product_id: Number(productId),
                    quantity: qty,
                    location: location,
                    notes: notes
                })
            });

            const result = await response.json();

            if (!response.ok || !result.ok) {
                showToast(result.message || 'Failed to record stock in.');
                return false;
            }

            const item = data.items.find(function (row) { return String(row.id) === String(productId); });
            if (item && result.item) {
                item.qty = result.item.qty;
                item.location = result.item.location;
                item.status = result.item.status;
                item.lastMovement = result.item.lastMovement;
            }

            if (result.summary) {
                if (typeof result.summary.stockValue !== 'undefined') {
                    byId('inventory-stock-value').textContent = money(result.summary.stockValue, true);
                }
                if (typeof result.summary.lowStock !== 'undefined') {
                    byId('inventory-low-stock').textContent = String(result.summary.lowStock);
                }
                if (typeof result.summary.incomingPurchases !== 'undefined') {
                    byId('inventory-incoming').textContent = String(result.summary.incomingPurchases);
                }
            } else {
                renderKpis();
            }

            renderTable();
            showToast(result.message || (qty + ' units added to ' + (item ? item.partNo : 'item') + '.'));
            closeStockDialog();
            return true;
        } catch (err) {
            showToast('Network error while recording stock in.');
            return false;
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    }

    async function recordNewItem() {
        const code = byId('new-item-code').value.trim();
        const name = byId('new-item-name').value.trim();
        const categoryId = byId('new-item-category').value;
        const costPrice = parseFloat(byId('new-item-cost').value) || 0;
        const sellingPrice = parseFloat(byId('new-item-selling').value) || 0;
        const qty = parseInt(byId('new-item-qty').value, 10) || 0;
        const reorderLevel = parseInt(byId('new-item-reorder').value, 10) || 10;
        const location = byId('new-item-location') ? byId('new-item-location').value : 'Main Warehouse';
        const notes = byId('new-item-notes').value.trim();
        const submitBtn = byId('new-item-submit');

        if (!code) {
            showToast('Please enter a part number / SKU.');
            byId('new-item-code').focus();
            return false;
        }
        if (!name) {
            showToast('Please enter a product name.');
            byId('new-item-name').focus();
            return false;
        }
        if (!categoryId) {
            showToast('Please select a category.');
            byId('new-item-category').focus();
            return false;
        }

        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving Item...';

        try {
            const baseUrl = (window.APP_CONFIG && window.APP_CONFIG.baseUrl) ? window.APP_CONFIG.baseUrl : '';
            const csrfToken = (window.APP_CONFIG && window.APP_CONFIG.csrfToken) || data.csrfToken || '';

            const response = await fetch(baseUrl + '/inventory/add-item', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    product_code: code,
                    name: name,
                    category_id: Number(categoryId),
                    cost_price: costPrice,
                    selling_price: sellingPrice,
                    quantity_on_hand: qty,
                    reorder_level: reorderLevel,
                    location: location,
                    notes: notes
                })
            });

            const result = await response.json();

            if (!response.ok || !result.ok) {
                showToast(result.message || 'Failed to create inventory item.');
                return false;
            }

            if (result.item) {
                data.items.unshift(result.item);
            }

            if (result.summary) {
                if (typeof result.summary.stockValue !== 'undefined') {
                    byId('inventory-stock-value').textContent = money(result.summary.stockValue, true);
                }
                if (typeof result.summary.lowStock !== 'undefined') {
                    byId('inventory-low-stock').textContent = String(result.summary.lowStock);
                }
                if (typeof result.summary.incomingPurchases !== 'undefined') {
                    byId('inventory-incoming').textContent = String(result.summary.incomingPurchases);
                }
            } else {
                renderKpis();
            }

            renderTable();
            fillProductSelect('');
            byId('new-item-form').reset();
            closeItemDialog();
            showToast(result.message || ('Item ' + code + ' created successfully.'));
            return true;
        } catch (err) {
            showToast('Network error while saving inventory item.');
            return false;
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    }

    async function deleteItem(productId, button) {
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        if (!item || !window.confirm('Delete ' + item.name + '? This item will be removed from inventory.')) {
            return;
        }

        const baseUrl = (window.APP_CONFIG && window.APP_CONFIG.baseUrl) ? window.APP_CONFIG.baseUrl : '';
        const csrfToken = (window.APP_CONFIG && window.APP_CONFIG.csrfToken) || data.csrfToken || '';
        button.disabled = true;

        try {
            const response = await fetch(baseUrl + '/inventory/delete-item', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ csrf_token: csrfToken, product_id: Number(productId) })
            });
            const result = await response.json();

            if (!response.ok || !result.ok) {
                showToast(result.message || 'Failed to delete inventory item.');
                return;
            }

            const index = data.items.findIndex(function (row) { return String(row.id) === String(productId); });
            if (index !== -1) data.items.splice(index, 1);
            renderKpis();
            renderTable();
            showToast(result.message || 'Inventory item deleted successfully.');
        } catch (err) {
            showToast('Network error while deleting inventory item.');
        } finally {
            button.disabled = false;
        }
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

    const filterButton = byId('inventory-filter');
    const filterOptions = byId('inventory-filter-options');
    if (filterButton && filterOptions) {
        filterButton.addEventListener('click', function () {
            const isOpen = !filterOptions.classList.contains('hidden');
            filterOptions.classList.toggle('hidden', isOpen);
            filterButton.setAttribute('aria-expanded', String(!isOpen));
        });

        filterOptions.addEventListener('click', function (event) {
            const option = event.target.closest('[data-status-filter]');
            if (!option) return;

            state.statusFilter = option.dataset.statusFilter;
            filterOptions.querySelectorAll('[data-status-filter]').forEach(function (item) {
                item.setAttribute('aria-checked', String(item === option));
            });
            filterOptions.classList.add('hidden');
            filterButton.setAttribute('aria-expanded', 'false');
            showToast(option.textContent.trim() === 'All Stock'
                ? 'Showing all statuses'
                : 'Filtered to ' + option.textContent.trim().toLowerCase());
            renderTable();
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.inventory-filter-menu')) {
                filterOptions.classList.add('hidden');
                filterButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    ['add-stock', 'add-stock-toolbar'].forEach(function (id) {
        const el = byId(id);
        if (el) {
            el.addEventListener('click', function () {
                openStockDialog('');
            });
        }
    });

    ['add-item-btn', 'add-item-toolbar'].forEach(function (id) {
        const el = byId(id);
        if (el) {
            el.addEventListener('click', function () {
                openItemDialog();
            });
        }
    });

    const productSelect = byId('stock-in-product');
    if (productSelect) {
        productSelect.addEventListener('change', function () {
            if (this.value === '__new__') {
                closeStockDialog();
                openItemDialog();
            }
        });
    }

    byId('inventory-table-body').addEventListener('click', function (event) {
        const addStockButton = event.target.closest('[data-add-stock]');
        if (addStockButton) {
            openStockDialog(addStockButton.dataset.addStock);
            return;
        }

        const deleteButton = event.target.closest('[data-delete-stock]');
        if (deleteButton) {
            deleteItem(deleteButton.dataset.deleteStock, deleteButton);
            return;
        }
    });

    document.querySelectorAll('[data-close-stock-dialog]').forEach(function (button) {
        button.addEventListener('click', closeStockDialog);
    });

    document.querySelectorAll('[data-close-item-dialog]').forEach(function (button) {
        button.addEventListener('click', closeItemDialog);
    });

    byId('stock-in-form').addEventListener('submit', function (event) {
        event.preventDefault();
        recordStockIn();
    });

    const newItemForm = byId('new-item-form');
    if (newItemForm) {
        newItemForm.addEventListener('submit', function (event) {
            event.preventDefault();
            recordNewItem();
        });
    }

    renderLocations();
    renderKpis();
    renderTable();
    fillProductSelect('');
})();
