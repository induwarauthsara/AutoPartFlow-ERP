/**
 * Sales Rep POS: search, walk-in vs trade, cart, discount, checkout, invoice.
 * Totals: subtotal minus discount. No tax. PHP later: POST /api/sales.
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-sales-page="pos"]');
    if (!page || typeof MOCK_PRODUCTS === 'undefined') return;

    const u = window.SalesRepUtils;
    const state = {
        customerType: 'walking',
        tradeAccountId: '',
        cart: [],
        discount: { type: 'percent', value: 0 },
        searchQuery: ''
    };

    const productGrid = document.getElementById('product-grid');
    const cartList = document.getElementById('cart-list');
    const cartEmpty = document.getElementById('cart-empty');
    const noProducts = document.getElementById('no-products');
    const live = document.getElementById('pos-live');

    function getStockStatus(product) {
        if (product.stock <= product.reorderLevel) {
            return { label: 'Low Stock', className: 'sales-badge--low-stock' };
        }
        return { label: 'In Stock', className: 'sales-badge--in-stock' };
    }

    function getFilteredProducts() {
        const q = state.searchQuery.trim().toLowerCase();
        if (!q) return MOCK_PRODUCTS;
        return MOCK_PRODUCTS.filter(function (p) {
            return p.name.toLowerCase().includes(q) || p.shortName.toLowerCase().includes(q) || p.code.toLowerCase().includes(q);
        });
    }

    /**
     * Discount comes off the subtotal. Due amount is afterDiscount (no tax).
     */
    function getCartTotals() {
        const subtotal = state.cart.reduce(function (sum, item) { return sum + item.price * item.quantity; }, 0);
        let discountAmount = 0;
        if (state.discount.value > 0) {
            discountAmount = state.discount.type === 'percent'
                ? subtotal * (state.discount.value / 100)
                : Math.min(state.discount.value, subtotal);
        }
        const afterDiscount = subtotal - discountAmount;
        const itemCount = state.cart.reduce(function (sum, item) { return sum + item.quantity; }, 0);
        return { subtotal: subtotal, discountAmount: discountAmount, total: afterDiscount, itemCount: itemCount, afterDiscount: afterDiscount };
    }

    function renderProducts() {
        const products = getFilteredProducts();
        productGrid.innerHTML = '';
        noProducts.classList.toggle('hidden', products.length !== 0);
        products.forEach(function (product) {
            const status = getStockStatus(product);
            const card = document.createElement('article');
            card.className = 'product-card';
            card.dataset.productId = product.id;
            card.innerHTML =
                '<div class="product-card__icon-wrap"><svg class="product-card__icon" aria-hidden="true"><use href="#' + product.icon + '"></use></svg></div>' +
                '<div class="product-card__details"><p class="product-card__name">' + u.escapeHtml(product.shortName) + '</p>' +
                '<p class="product-card__code">#' + u.escapeHtml(product.code) + '</p>' +
                '<p class="product-card__price">' + u.money(product.price) + '</p></div>' +
                '<div class="product-card__action"><span class="sales-badge ' + status.className + '">' + status.label + '</span>' +
                '<button type="button" class="product-card__add" data-add="' + product.id + '" aria-label="Add ' + u.escapeHtml(product.shortName) + '">+</button></div>';
            productGrid.appendChild(card);
        });
    }

    function renderCart() {
        cartList.innerHTML = '';
        const empty = state.cart.length === 0;
        cartEmpty.classList.toggle('hidden', !empty);
        document.getElementById('btn-complete-sale').disabled = empty;
        document.getElementById('btn-complete-sale-mobile').disabled = empty;

        state.cart.forEach(function (item) {
            const li = document.createElement('li');
            li.className = 'cart-item';
            li.innerHTML =
                '<div class="cart-item__info"><p class="cart-item__name">' + u.escapeHtml(item.shortName) + '</p>' +
                '<p class="cart-item__meta">#' + u.escapeHtml(item.code) + ' · ' + u.money(item.price) + ' each</p></div>' +
                '<div class="cart-item__right"><div class="qty-control">' +
                '<button type="button" class="qty-control__btn" data-qty-minus="' + item.id + '" aria-label="Decrease">&minus;</button>' +
                '<span class="qty-control__value">' + item.quantity + '</span>' +
                '<button type="button" class="qty-control__btn" data-qty-plus="' + item.id + '" aria-label="Increase">&plus;</button></div>' +
                '<span class="cart-item__subtotal">' + u.money(item.price * item.quantity) + '</span></div>';
            cartList.appendChild(li);
        });
        updateCheckoutBar();
    }

    function updateCheckoutBar() {
        const totals = getCartTotals();
        const countLabel = totals.itemCount + (totals.itemCount === 1 ? ' item' : ' items');
        document.getElementById('checkout-item-count').textContent = countLabel;
        document.getElementById('sticky-item-count').textContent = countLabel;
        document.getElementById('checkout-total').textContent = u.money(totals.afterDiscount);
        document.getElementById('sticky-total').textContent = u.money(totals.afterDiscount);
        const discountNote = document.getElementById('discount-note');
        if (discountNote) {
            discountNote.textContent = totals.discountAmount
                ? 'Discount − ' + u.money(totals.discountAmount)
                : '';
            discountNote.classList.toggle('hidden', !totals.discountAmount);
        }
    }

    function announce(message) {
        if (live) live.textContent = message;
        u.showToast(message);
    }

    function addToCart(productId) {
        const product = MOCK_PRODUCTS.find(function (p) { return p.id === productId; });
        if (!product) return;
        const existing = state.cart.find(function (item) { return item.id === productId; });
        if (existing) existing.quantity += 1;
        else state.cart.push({ id: product.id, code: product.code, shortName: product.shortName, price: product.price, quantity: 1 });
        renderCart();
        announce(product.shortName + ' added to cart');
    }

    function updateQuantity(productId, delta) {
        const item = state.cart.find(function (i) { return i.id === productId; });
        if (!item) return;
        item.quantity += delta;
        if (item.quantity <= 0) state.cart = state.cart.filter(function (i) { return i.id !== productId; });
        renderCart();
    }

    function openCheckout() {
        if (state.customerType === 'trade' && !state.tradeAccountId) {
            u.showToast('Select a trade account before completing the sale.');
            document.getElementById('trade-panel').classList.remove('hidden');
            document.getElementById('trade-account').focus();
            return;
        }
        const totals = getCartTotals();
        document.getElementById('modal-subtotal').textContent = u.money(totals.subtotal);
        document.getElementById('modal-discount').textContent = '- ' + u.money(totals.discountAmount);
        document.getElementById('modal-total').textContent = u.money(totals.total);
        document.getElementById('amount-paid').value = totals.total.toFixed(2);
        updateChangeAmount();
        document.getElementById('checkout-modal').showModal();
    }

    function updateChangeAmount() {
        const totals = getCartTotals();
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        document.getElementById('change-amount').textContent = u.money(Math.max(0, paid - totals.total));
    }

    function confirmSale() {
        const totals = getCartTotals();
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const paid = parseFloat(document.getElementById('amount-paid').value) || 0;
        if (paid < totals.total && paymentMethod !== 'credit') {
            u.showToast('Amount paid is less than the total.');
            return;
        }
        const items = state.cart.map(function (item) { return Object.assign({}, item); });
        const invoiceNumber = 'INV-' + String(Date.now()).slice(-6);
        const customerLabel = state.customerType === 'walking'
            ? 'Walk-in Retail Customer'
            : document.getElementById('trade-account').options[document.getElementById('trade-account').selectedIndex].text;
        let rows = '';
        items.forEach(function (item) {
            rows += '<tr><td>' + u.escapeHtml(item.shortName) + '</td><td>' + item.quantity + '</td><td>' + u.money(item.price * item.quantity) + '</td></tr>';
        });
        document.getElementById('invoice-preview').innerHTML =
            '<div class="invoice-preview__header">' + document.querySelector('.app-logo').outerHTML + '<h3>AutoPartFlow Spare Parts</h3><p>Sales Representative Invoice</p><p>Tel: +94 11 234 5678</p></div>' +
            '<div class="invoice-preview__meta"><p><strong>Invoice:</strong> ' + invoiceNumber + '</p>' +
            '<p><strong>Date:</strong> ' + new Date().toLocaleString('en-LK') + '</p>' +
            '<p><strong>Customer:</strong> ' + u.escapeHtml(customerLabel) + '</p>' +
            '<p><strong>Payment:</strong> ' + paymentMethod.toUpperCase() + '</p></div>' +
            '<table class="invoice-preview__table"><thead><tr><th>Item</th><th>Qty</th><th>Amount</th></tr></thead><tbody>' + rows + '</tbody></table>' +
            '<p class="invoice-preview__total">Subtotal: ' + u.money(totals.subtotal) + '</p>' +
            '<p class="invoice-preview__total">Discount: - ' + u.money(totals.discountAmount) + '</p>' +
            '<p class="invoice-preview__total">Total: ' + u.money(totals.total) + '</p>' +
            '<p class="invoice-preview__total">Paid: ' + u.money(paid) + '</p>' +
            '<p class="invoice-preview__total">Change: ' + u.money(Math.max(0, paid - totals.total)) + '</p>' +
            '<p style="text-align:center;margin-top:16px">Thank you for your business.</p>';
        document.getElementById('checkout-modal').close();
        document.getElementById('invoice-modal').showModal();
        state.cart = [];
        state.discount = { type: 'percent', value: 0 };
        renderCart();
    }

    document.getElementById('product-search').addEventListener('input', function (e) {
        state.searchQuery = e.target.value;
        renderProducts();
    });

    document.querySelectorAll('.chip[data-customer-type]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.chip[data-customer-type]').forEach(function (c) {
                c.classList.remove('chip--active');
                c.setAttribute('aria-selected', 'false');
            });
            chip.classList.add('chip--active');
            chip.setAttribute('aria-selected', 'true');
            state.customerType = chip.dataset.customerType;
            document.getElementById('trade-panel').classList.toggle('hidden', state.customerType !== 'trade');
            if (state.customerType !== 'trade') {
                state.tradeAccountId = '';
                document.getElementById('trade-account').value = '';
            }
        });
    });

    document.getElementById('trade-account').addEventListener('change', function () {
        state.tradeAccountId = this.value;
    });

    productGrid.addEventListener('click', function (e) {
        const add = e.target.closest('[data-add]');
        const card = e.target.closest('.product-card');
        if (add) addToCart(parseInt(add.dataset.add, 10));
        else if (card) addToCart(parseInt(card.dataset.productId, 10));
    });

    function onCartClick(e) {
        const minus = e.target.closest('[data-qty-minus]');
        const plus = e.target.closest('[data-qty-plus]');
        if (minus) updateQuantity(parseInt(minus.dataset.qtyMinus, 10), -1);
        if (plus) updateQuantity(parseInt(plus.dataset.qtyPlus, 10), 1);
        if (e.target.closest('#btn-clear-cart') || e.target.closest('.btn-clear-cart')) {
            if (state.cart.length && window.confirm('Clear all items from the cart?')) {
                state.cart = [];
                state.discount = { type: 'percent', value: 0 };
                renderCart();
            }
        }
        if (e.target.closest('#btn-discount') || e.target.closest('.btn-discount')) {
            document.getElementById('discount-type').value = state.discount.type;
            document.getElementById('discount-value').value = state.discount.value || '';
            document.getElementById('discount-modal').showModal();
        }
    }

    document.getElementById('pos-cart-card').addEventListener('click', onCartClick);
    document.getElementById('btn-complete-sale').addEventListener('click', openCheckout);
    document.getElementById('btn-complete-sale-mobile').addEventListener('click', openCheckout);
    document.getElementById('btn-toggle-cart').addEventListener('click', function () {
        document.querySelector('.pos-cart-column').classList.toggle('is-open');
    });
    document.getElementById('btn-confirm-sale').addEventListener('click', confirmSale);
    document.getElementById('amount-paid').addEventListener('input', updateChangeAmount);

    document.getElementById('discount-form').addEventListener('submit', function (e) {
        e.preventDefault();
        state.discount = {
            type: document.getElementById('discount-type').value,
            value: parseFloat(document.getElementById('discount-value').value) || 0
        };
        document.getElementById('discount-modal').close();
        renderCart();
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('dialog').close(); });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            document.getElementById('product-search').focus();
        }
    });

    renderProducts();
    renderCart();
})();
