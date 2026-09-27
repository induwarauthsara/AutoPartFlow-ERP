(function () {
    'use strict';

    const data = window.INVENTORY_DATA || window.INVENTORY_MOCK_DATA || { items: [], incomingPurchases: 0 };
    const page = document.querySelector('[data-sales-page="inventory"]');
    if (!page) return;

    const state = {
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
        const reorder = Number(item.reorderLevel) || 10;
        const available = Number(item.availableQty ?? item.qty);
        if (available <= Math.max(1, Math.floor(reorder / 2))) return 'critical';
        if (available <= reorder) return 'low';
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
            const matchesStatus = state.statusFilter === 'all' ||
                (state.statusFilter === 'low' && (status === 'low' || status === 'critical')) ||
                status === state.statusFilter;
            const matchesQuery = !query ||
                item.partNo.toLowerCase().indexOf(query) !== -1 ||
                item.name.toLowerCase().indexOf(query) !== -1 ||
                (item.category && item.category.toLowerCase().indexOf(query) !== -1);
            return matchesStatus && matchesQuery;
        });
    }

    function renderKpis() {
        const stockValue = data.items.reduce(function (sum, item) {
            return sum + (item.qty * (item.unitCost || 0));
        }, 0);
        const lowStock = data.items.filter(function (item) {
            const status = deriveStatus(item);
            return status === 'low' || status === 'critical';
        }).length;

        const valEl = byId('inventory-stock-value');
        if (valEl) valEl.textContent = money(stockValue, true);
        const lowEl = byId('inventory-low-stock');
        if (lowEl) lowEl.textContent = String(lowStock);
        const incEl = byId('inventory-incoming');
        if (incEl) incEl.textContent = String(data.incomingPurchases ?? 0);

        const countEl = byId('inventory-item-count');
        if (countEl) countEl.textContent = data.items.length + ' items registered';
    }

    function renderTable() {
        const rows = filteredItems();
        const body = byId('inventory-table-body');
        const empty = byId('inventory-empty');
        if (!body) return;

        body.innerHTML = rows.map(function (item, index) {
            const status = deriveStatus(item);
            const isAlert = (status === 'low' || status === 'critical');
            const qtyClass = isAlert ? ' inventory-qty--alert' : '';
            const zebra = index % 2 === 1 ? ' inventory-row--alt' : '';
            const threshold = item.reorderLevel ?? 10;
            return '<tr class="inventory-row' + zebra + '" data-product-id="' + item.id + '">' +
                '<td class="inventory-part">' + escapeHtml(item.partNo) + '</td>' +
                '<td><div style="font-weight:600;">' + escapeHtml(item.name) + '</div><div style="font-size:11px;color:var(--sales-on-surface-variant,#64748b);">' + escapeHtml(item.category || 'Auto Parts') + '</div></td>' +
                '<td class="inventory-table__qty' + qtyClass + '"><strong>' + escapeHtml(item.qty.toLocaleString('en-LK')) + '</strong>' +
                    '<div style="font-size:10px;color:var(--sales-on-surface-variant,#64748b);">Available ' + escapeHtml(Number(item.availableQty ?? item.qty).toLocaleString('en-LK')) +
                    (Number(item.reservedQty || 0) ? ' · Reserved ' + escapeHtml(Number(item.reservedQty).toLocaleString('en-LK')) : '') + '</div></td>' +
                '<td class="inventory-table__threshold">' +
                    '<div style="display:inline-flex;align-items:center;gap:6px;">' +
                        '<span class="threshold-value" style="font-weight:600;font-variant-numeric:tabular-nums;">' + escapeHtml(threshold) + ' units</span>' +
                        '<button class="sales-icon-button threshold-edit-btn" type="button" data-edit-threshold="' + item.id + '" title="Edit low stock threshold for ' + escapeHtml(item.name) + '" aria-label="Edit low stock threshold" style="width:26px;height:26px;padding:4px;border-radius:6px;color:var(--sales-primary,#4f5bd5);">' +
                            '<svg class="sales-icon" viewBox="0 0 24 24" style="width:13px;height:13px;" aria-hidden="true">' +
                                '<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="currentColor"></path>' +
                            '</svg>' +
                        '</button>' +
                    '</div>' +
                '</td>' +
                '<td><span class="inventory-status inventory-status--' + status + '">' + escapeHtml(statusLabel(status)) + '</span></td>' +
                '<td class="inventory-muted">' + escapeHtml(item.lastMovement) + '</td>' +
                '<td class="inventory-table__action">' +
                    '<button class="sales-button sales-button--primary sales-button--compact" type="button" data-add-stock="' + item.id + '">' +
                        '<svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>' +
                        'Add Stock' +
                    '</button>' +
                    '<button class="sales-button sales-button--secondary sales-button--compact" type="button" data-edit-threshold="' + item.id + '" title="Manage Alert Threshold">' +
                        'Set Alert' +
                    '</button>' +
                    '<button class="sales-button sales-button--secondary sales-button--compact" type="button" data-adjust-stock="' + item.id + '">Adjust</button>' +
                    '<button class="sales-button sales-button--danger sales-button--compact" type="button" data-writeoff-stock="' + item.id + '">Write Off</button>' +
                    '<button class="sales-icon-button sales-icon-button--danger" type="button" data-delete-stock="' + item.id + '" aria-label="Delete ' + escapeHtml(item.name) + '">' +
                        '<svg class="sales-icon" viewBox="0 0 24 24" aria-hidden="true">' +
                            '<path d="M8 3h8l1 2h4v2H3V5h4l1-2Zm-2 6h12l-1 12H7L6 9Zm3 2v7h2v-7H9Zm4 0v7h2v-7h-2Z" fill="currentColor"></path>' +
                        '</svg>' +
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
        if (!dialog) return;
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        byId('stock-in-dialog-title').textContent = item ? 'Add Stock — ' + item.name : 'Add Stock';
        byId('stock-in-product-id').value = item ? String(item.id) : '';
        byId('stock-in-qty').value = '';
        byId('stock-in-notes').value = '';
        fillProductSelect(item ? item.id : '');
        dialog.showModal();
    }

    function closeStockDialog() {
        const dialog = byId('stock-in-dialog');
        if (dialog) dialog.close();
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

    function updateThresholdPreview(currentQty, threshold) {
        const preview = byId('threshold-status-preview');
        if (!preview) return;
        if (isNaN(threshold) || threshold < 0) {
            preview.style.display = 'none';
            return;
        }
        preview.style.display = 'block';
        const criticalThreshold = Math.max(1, Math.floor(threshold / 2));
        if (currentQty <= criticalThreshold) {
            preview.style.background = '#fee2e2';
            preview.style.color = '#dc2626';
            preview.style.border = '1px solid #fecaca';
            preview.innerHTML = 'Preview: Current stock (' + currentQty + ') is &le; ' + criticalThreshold + ' &rarr; triggers <strong>CRITICAL STOCK ALERT</strong>.';
        } else if (currentQty <= threshold) {
            preview.style.background = '#fef3c7';
            preview.style.color = '#b45309';
            preview.style.border = '1px solid #fde68a';
            preview.innerHTML = 'Preview: Current stock (' + currentQty + ') is &le; ' + threshold + ' &rarr; triggers <strong>LOW STOCK ALERT</strong>.';
        } else {
            preview.style.background = '#e0f2fe';
            preview.style.color = '#0369a1';
            preview.style.border = '1px solid #bae6fd';
            preview.innerHTML = 'Preview: Current stock (' + currentQty + ') is above threshold (' + threshold + ') &rarr; status is <strong>OPTIMAL</strong>.';
        }
    }

    function openThresholdDialog(productId) {
        const dialog = byId('threshold-dialog');
        if (!dialog) return;
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        if (!item) return;

        byId('threshold-product-id').value = String(item.id);
        byId('threshold-product-name').textContent = item.name;
        byId('threshold-product-code').textContent = item.partNo;
        byId('threshold-current-stock').textContent = String(item.qty.toLocaleString('en-LK'));

        const input = byId('threshold-level');
        input.value = item.reorderLevel ?? 10;
        updateThresholdPreview(item.qty, Number(input.value));

        dialog.showModal();
        input.focus();
        input.select();
    }

    function closeThresholdDialog() {
        const dialog = byId('threshold-dialog');
        if (dialog) dialog.close();
    }

    function openAdjustDialog(productId) {
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        const dialog = byId('stock-adjust-dialog');
        if (!item || !dialog) return;
        byId('adjust-product-id').value = String(item.id);
        byId('adjust-product-name').textContent = item.partNo + ' — ' + item.name;
        byId('adjust-qty').value = String(item.qty);
        byId('adjust-notes').value = '';
        dialog.showModal();
    }

    function closeAdjustDialog() {
        const dialog = byId('stock-adjust-dialog');
        if (dialog) dialog.close();
    }

    function openWriteoffDialog(productId) {
        const item = data.items.find(function (row) { return String(row.id) === String(productId); });
        const dialog = byId('stock-writeoff-dialog');
        if (!item || !dialog) return;
        byId('writeoff-product-id').value = String(item.id);
        byId('writeoff-product-name').textContent = item.partNo + ' — ' + item.name;
        const available = Number(item.availableQty ?? item.qty);
        byId('writeoff-current-onhand').textContent = String(available);
        byId('writeoff-qty').value = '';
        byId('writeoff-qty').max = String(available);
        byId('writeoff-reason').value = '';
        dialog.showModal();
    }

    function closeWriteoffDialog() {
        const dialog = byId('stock-writeoff-dialog');
        if (dialog) dialog.close();
    }

    function applyServerInventory(result) {
        if (Array.isArray(result.items)) {
            data.items = result.items;
        }
        if (result.summary && typeof result.summary.incomingPurchases !== 'undefined') {
            data.incomingPurchases = result.summary.incomingPurchases;
        }
        renderKpis();
        renderTable();
        fillProductSelect('');
    }

    const thresholdInput = byId('threshold-level');
    if (thresholdInput) {
        thresholdInput.addEventListener('input', function () {
            const currentStock = Number(byId('threshold-current-stock').textContent.replace(/,/g, '')) || 0;
            updateThresholdPreview(currentStock, Number(this.value));
        });
    }

    async function recordThresholdUpdate() {
        const productId = byId('threshold-product-id').value;
        const thresholdVal = parseInt(byId('threshold-level').value, 10);
        const submitBtn = byId('threshold-submit');

        if (!productId) {
            showToast('Invalid product selected.');
            return false;
        }
        if (isNaN(thresholdVal) || thresholdVal < 0) {
            showToast('Please enter a valid threshold (0 or greater).');
            byId('threshold-level').focus();
            return false;
        }

        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        try {
            const baseUrl = (window.APP_CONFIG && window.APP_CONFIG.baseUrl) ? window.APP_CONFIG.baseUrl : '';
            const csrfToken = (window.APP_CONFIG && window.APP_CONFIG.csrfToken) || data.csrfToken || '';

            const response = await fetch(baseUrl + '/inventory/update-threshold', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    product_id: Number(productId),
                    reorder_level: thresholdVal
                })
            });

            const result = await response.json();

            if (!response.ok || !result.ok) {
                showToast(result.message || 'Failed to update alert threshold.');
                return false;
            }

            const item = data.items.find(function (row) { return String(row.id) === String(productId); });
            if (item) {
                item.reorderLevel = thresholdVal;
                item.status = deriveStatus(item);
                if (result.item) {
                    item.status = result.item.status;
                }
            }

            if (result.summary) {
                if (typeof result.summary.stockValue !== 'undefined') {
                    byId('inventory-stock-value').textContent = money(result.summary.stockValue, true);
                }
                if (typeof result.summary.lowStock !== 'undefined') {
                    byId('inventory-low-stock').textContent = String(result.summary.lowStock);
                }
            } else {
                renderKpis();
            }

            renderTable();
            closeThresholdDialog();
            showToast(result.message || 'Low stock threshold updated successfully.');
            return true;
        } catch (err) {
            showToast('Network error while updating alert threshold.');
            return false;
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    }

    async function recordStockIn() {
        const productId = byId('stock-in-product').value;
        const qty = Number(byId('stock-in-qty').value);
        const notes = byId('stock-in-notes').value.trim();
        const submitBtn = byId('stock-in-submit') || byId('stock-in-form').querySelector('button[type="submit"]');

        if (window.AppValidation && !window.AppValidation.validateForm(byId('stock-in-form'))) {
            return false;
        }

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
                item.reorderLevel = result.item.reorderLevel;
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

    async function recordAdjustment() {
        const productId = Number(byId('adjust-product-id').value);
        const quantity = Number(byId('adjust-qty').value);
        const notes = byId('adjust-notes').value.trim();
        const submitBtn = byId('stock-adjust-form').querySelector('button[type="submit"]');
        if (!productId || !Number.isInteger(quantity) || quantity < 0 || !notes) {
            showToast('Enter a non-negative physical count and an audit reason.');
            return false;
        }
        submitBtn.disabled = true;
        try {
            const baseUrl = (window.APP_CONFIG && window.APP_CONFIG.baseUrl) ? window.APP_CONFIG.baseUrl : '';
            const csrfToken = (window.APP_CONFIG && window.APP_CONFIG.csrfToken) || data.csrfToken || '';
            const response = await fetch(baseUrl + '/inventory/adjust', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ csrf_token: csrfToken, product_id: productId, quantity: quantity, notes: notes })
            });
            const result = await response.json();
            if (!response.ok || !result.ok) {
                showToast(result.message || 'Failed to reconcile the inventory count.');
                return false;
            }
            applyServerInventory(result);
            closeAdjustDialog();
            showToast(result.message || 'Inventory count reconciled.');
            return true;
        } catch (err) {
            showToast('Network error while reconciling inventory.');
            return false;
        } finally {
            submitBtn.disabled = false;
        }
    }

    async function recordWriteoff() {
        const productId = Number(byId('writeoff-product-id').value);
        const quantity = Number(byId('writeoff-qty').value);
        const reason = byId('writeoff-reason').value.trim();
        const item = data.items.find(function (row) { return Number(row.id) === productId; });
        const submitBtn = byId('stock-writeoff-form').querySelector('button[type="submit"]');
        if (!productId || !Number.isInteger(quantity) || quantity < 1 || !reason) {
            showToast('Enter a positive damaged quantity and a reason.');
            return false;
        }
        if (item && quantity > Number(item.availableQty ?? item.qty)) {
            showToast('Write-off quantity cannot exceed unreserved stock.');
            return false;
        }
        submitBtn.disabled = true;
        try {
            const baseUrl = (window.APP_CONFIG && window.APP_CONFIG.baseUrl) ? window.APP_CONFIG.baseUrl : '';
            const csrfToken = (window.APP_CONFIG && window.APP_CONFIG.csrfToken) || data.csrfToken || '';
            const response = await fetch(baseUrl + '/inventory/write-off', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ csrf_token: csrfToken, product_id: productId, quantity: quantity, reason: reason })
            });
            const result = await response.json();
            if (!response.ok || !result.ok) {
                showToast(result.message || 'Failed to write off damaged stock.');
                return false;
            }
            applyServerInventory(result);
            closeWriteoffDialog();
            showToast(result.message || 'Damaged stock written off.');
            return true;
        } catch (err) {
            showToast('Network error while writing off stock.');
            return false;
        } finally {
            submitBtn.disabled = false;
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
        const notes = byId('new-item-notes').value.trim();
        const submitBtn = byId('new-item-submit');

        if (window.AppValidation && !window.AppValidation.validateForm(byId('new-item-form'))) {
            return false;
        }

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
                ? 'Showing all stock'
                : 'Filtered to ' + option.textContent.trim());
            renderTable();
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.inventory-filter-menu')) {
                filterOptions.classList.add('hidden');
                filterButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const lowStockKpi = byId('kpi-low-stock');
    if (lowStockKpi) {
        lowStockKpi.addEventListener('click', function () {
            if (state.statusFilter === 'low') {
                state.statusFilter = 'all';
                showToast('Showing all stock');
            } else {
                state.statusFilter = 'low';
                showToast('Filtered to items with Low Stock Alerts');
            }
            if (filterOptions) {
                filterOptions.querySelectorAll('[data-status-filter]').forEach(function (opt) {
                    opt.setAttribute('aria-checked', String(opt.dataset.statusFilter === state.statusFilter));
                });
            }
            renderTable();
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

        const editThresholdBtn = event.target.closest('[data-edit-threshold]');
        if (editThresholdBtn) {
            openThresholdDialog(editThresholdBtn.dataset.editThreshold);
            return;
        }

        const deleteButton = event.target.closest('[data-delete-stock]');
        if (deleteButton) {
            deleteItem(deleteButton.dataset.deleteStock, deleteButton);
            return;
        }

        const adjustButton = event.target.closest('[data-adjust-stock]');
        if (adjustButton) {
            openAdjustDialog(adjustButton.dataset.adjustStock);
            return;
        }

        const writeoffButton = event.target.closest('[data-writeoff-stock]');
        if (writeoffButton) {
            openWriteoffDialog(writeoffButton.dataset.writeoffStock);
        }
    });

    document.querySelectorAll('[data-close-stock-dialog]').forEach(function (button) {
        button.addEventListener('click', closeStockDialog);
    });

    document.querySelectorAll('[data-close-item-dialog]').forEach(function (button) {
        button.addEventListener('click', closeItemDialog);
    });

    document.querySelectorAll('[data-close-threshold-dialog]').forEach(function (button) {
        button.addEventListener('click', closeThresholdDialog);
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

    const newItemForm = byId('new-item-form');
    if (newItemForm) {
        newItemForm.addEventListener('submit', function (event) {
            event.preventDefault();
            recordNewItem();
        });
    }

    const thresholdForm = byId('threshold-form');
    if (thresholdForm) {
        thresholdForm.addEventListener('submit', function (event) {
            event.preventDefault();
            recordThresholdUpdate();
        });
    }

    byId('stock-adjust-form').addEventListener('submit', function (event) {
        event.preventDefault();
        recordAdjustment();
    });

    byId('stock-writeoff-form').addEventListener('submit', function (event) {
        event.preventDefault();
        recordWriteoff();
    });

    renderKpis();
    renderTable();
    fillProductSelect('');
})();
