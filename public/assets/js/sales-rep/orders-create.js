/**
 * Customer Order Entry (New Order Request).
 * Order total equals line subtotal and is persisted through the sales API.
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-sales-page="create"]');
    if (!page) return;

    const u = window.SalesRepUtils;
    const data = window.ORDER_MOCK_DATA || { products: [], customers: {} };
    const byId = u.byId;
    const cart = [];
    const quantities = {};
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
            article.className = 'order-product sales-card';
            article.dataset.productId = product.id;
            const low = product.stock <= 12;
            article.innerHTML =
                '<div class="order-product__visual"><svg class="sales-icon"><use href="#sales-icon-inventory"></use></svg></div>' +
                '<div><div class="order-product__badges"><span class="part-badge">SKU: ' + u.escapeHtml(product.sku) + '</span>' +
                '<span class="sales-badge ' + (low ? 'sales-badge--low-stock' : 'sales-badge--in-stock') + '">' +
                (low ? 'Low Stock' : 'In Stock') + ' (' + product.stock + ')</span></div>' +
                '<h3>' + u.escapeHtml(product.name) + '</h3><p>' + u.escapeHtml(product.description) + '</p></div>' +
                '<div class="order-product__action"><strong class="order-product__price">' + u.money(product.price) + '</strong>' +
                '<div class="add-controls"><div class="quantity-control">' +
                '<button type="button" data-product-minus aria-label="Decrease quantity">−</button>' +
                '<input type="number" min="1" max="' + product.stock + '" value="' + quantities[product.id] + '" aria-label="Quantity for ' + u.escapeHtml(product.name) + '">' +
                '<button type="button" data-product-plus aria-label="Increase quantity">+</button></div>' +
                '<button class="sales-button sales-button--primary sales-button--compact" type="button" data-add-product><svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>Add</button></div></div>';
            list.appendChild(article);
        });
        byId('parts-result-count').textContent = products.length + (products.length === 1 ? ' part' : ' parts');
        byId('parts-empty').classList.toggle('hidden', products.length !== 0);
    }

    function totals() {
        const subtotal = cart.reduce(function (sum, item) { return sum + item.price * item.quantity; }, 0);
        return { subtotal: subtotal, total: subtotal };
    }

    function renderSummary() {
        const container = byId('summary-items');
        container.innerHTML = '';
        cart.forEach(function (item) {
            const row = document.createElement('div');
            row.className = 'summary-line';
            row.innerHTML =
                '<div><strong>' + u.escapeHtml(item.name) + '</strong><span>' + u.escapeHtml(item.sku) + ' · Qty ' + item.quantity + '</span></div>' +
                '<div class="summary-line__price"><strong>' + u.money(item.price * item.quantity) + '</strong>' +
                '<button class="summary-line__remove" type="button" data-remove-product="' + item.id + '">Remove</button></div>';
            container.appendChild(row);
        });
        const sum = totals();
        const itemCount = cart.reduce(function (count, item) { return count + item.quantity; }, 0);
        byId('summary-item-count').textContent = itemCount + (itemCount === 1 ? ' item' : ' items');
        byId('summary-subtotal').textContent = u.money(sum.subtotal);
        byId('summary-total').textContent = u.money(sum.total);
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
                cart.push({ id: product.id, sku: product.sku, name: product.name, price: product.price, quantity: quantities[productId] });
            }
            renderSummary();
            u.showToast(product.name + ' added to the order.');
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

    byId('part-search').addEventListener('input', function (event) { query = event.target.value; renderProducts(); });
    byId('category-filter').addEventListener('change', function (event) { category = event.target.value; renderProducts(); });

    byId('order-customer').addEventListener('change', function (event) {
        const customer = data.customers[event.target.value];
        const meta = byId('customer-meta');
        if (!customer) {
            meta.innerHTML = '<span class="sales-avatar" aria-hidden="true"><svg class="sales-icon"><use href="#sales-icon-user"></use></svg></span><div><strong>No customer selected</strong><span>Choose a customer account to continue</span></div>';
            return;
        }
        meta.innerHTML = '<span class="sales-avatar">' + u.escapeHtml(customer.initials) + '</span><div><strong>' +
            u.escapeHtml(customer.name) + '</strong><span>' + u.escapeHtml(customer.detail) + '</span></div>';
        if (!byId('delivery-address').value && customer.address) {
            byId('delivery-address').value = customer.address;
        }
    });

    byId('save-draft').addEventListener('click', function () {
        localStorage.setItem('autopartflow-order-draft', JSON.stringify({
            customerId: byId('order-customer').value,
            cart: cart,
            notes: byId('order-notes').value,
            savedAt: new Date().toISOString()
        }));
        u.showToast('Draft saved in this browser.');
    });

    const dialog = byId('order-confirm-dialog');
    byId('submit-order').addEventListener('click', function () {
        if (!byId('order-customer').value) {
            u.showToast('Select a customer before placing the order.');
            byId('order-customer').focus();
            return;
        }
        byId('confirm-message').textContent = cart.length + ' line item' + (cart.length === 1 ? '' : 's') +
            ' totaling ' + u.money(totals().total) + ' will be submitted.';
        dialog.showModal();
    });
    document.querySelector('[data-dialog-close]').addEventListener('click', function () { dialog.close(); });
    byId('confirm-order').addEventListener('click', function () {
        const button = this;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
        button.disabled = true;
        button.textContent = 'Saving...';
        fetch(baseUrl.replace(/\/$/, '') + '/sales/orders/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                customer_id: Number(byId('order-customer').value),
                items: cart.map(function (item) { return { product_id: item.id, quantity: item.quantity }; }),
                notes: byId('order-notes').value,
                delivery_address: byId('delivery-address').value,
                csrf_token: csrfToken
            })
        }).then(function (response) { return response.json(); })
        .then(function (json) {
            if (!json.ok) throw new Error(json.message || 'The order could not be saved.');
            localStorage.removeItem('autopartflow-order-draft');
            u.showToast('Order ' + json.orderNumber + ' created successfully.');
            window.setTimeout(function () { window.location.href = json.redirect; }, 500);
        }).catch(function (error) {
            button.disabled = false;
            button.textContent = 'Confirm Order';
            u.showToast(error.message || 'Network error while saving the order.');
        });
    });

    renderProducts();
    renderSummary();
})();
