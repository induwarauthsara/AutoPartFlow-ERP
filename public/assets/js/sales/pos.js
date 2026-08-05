/**
 * SmartAuto ERP — Sales Rep POS
 * Frontend-only: cart, search, checkout UI
 * Wire POST /api/sales in PHP when backend is ready
 */

(function () {
    'use strict';

    const state = {
        customerType: 'walking',
        tradeAccountId: '',
        cart: [],
        discount: { type: 'percent', value: 0 },
        searchQuery: ''
    };

    // DOM refs
    const productGrid = document.getElementById('product-grid');
    const cartList = document.getElementById('cart-list');
    const cartEmpty = document.getElementById('cart-empty');
    const noProducts = document.getElementById('no-products');
    const searchInput = document.getElementById('product-search');
    const tradePanel = document.getElementById('trade-panel');
    const tradeSelect = document.getElementById('trade-account');
    const checkoutItemCount = document.getElementById('checkout-item-count');
    const checkoutTotal = document.getElementById('checkout-total');
    const btnCompleteSale = document.getElementById('btn-complete-sale');
    const btnClearCart = document.getElementById('btn-clear-cart');
    const btnDiscount = document.getElementById('btn-discount');    // optional
    const discountModal = document.getElementById('discount-modal');
    const discountForm = document.getElementById('discount-form');
    const checkoutModal = document.getElementById('checkout-modal');
    const invoiceModal = document.getElementById('invoice-modal');
    const btnConfirmSale = document.getElementById('btn-confirm-sale');
    const amountPaidInput = document.getElementById('amount-paid');

    // -------------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------------

    function formatCurrency(amount) {
        return 'Rs. ' + amount.toLocaleString('en-LK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getStockStatus(product) {
        if (product.stock <= product.reorderLevel) {
            return { label: 'Low Stock', className: 'status-badge--low-stock' };
        }
        return { label: 'In Stock', className: 'status-badge--in-stock' };
    }

    function getFilteredProducts() {
        const q = state.searchQuery.trim().toLowerCase();
        if (!q) return MOCK_PRODUCTS;
        return MOCK_PRODUCTS.filter(function (p) {
            return (
                p.name.toLowerCase().includes(q) ||
                p.shortName.toLowerCase().includes(q) ||
                p.code.toLowerCase().includes(q)
            );
        });
    }

    function getCartTotals() {
        const subtotal = state.cart.reduce(function (sum, item) {
            return sum + item.price * item.quantity;
        }, 0);

        let discountAmount = 0;
        if (state.discount.value > 0) {
            if (state.discount.type === 'percent') {
                discountAmount = subtotal * (state.discount.value / 100);
            } else {
                discountAmount = Math.min(state.discount.value, subtotal);
            }
        }

        const afterDiscount = subtotal - discountAmount;
        const tax = afterDiscount * TAX_RATE;
        const total = afterDiscount + tax;
        const itemCount = state.cart.reduce(function (sum, item) {
            return sum + item.quantity;
        }, 0);

        return { subtotal, discountAmount, tax, total, itemCount };
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    function renderProducts() {
        const products = getFilteredProducts();
        productGrid.innerHTML = '';

        if (products.length === 0) {
            noProducts.classList.remove('hidden');
            return;
        }

        noProducts.classList.add('hidden');

        products.forEach(function (product) {
            const status = getStockStatus(product);
            const card = document.createElement('article');
            card.className = 'product-card';
            card.dataset.productId = product.id;

            card.innerHTML =
                '<div class="product-card__icon-wrap">' +
                    '<svg class="product-card__icon" aria-hidden="true"><use href="#' + product.icon + '"></use></svg>' +
                '</div>' +
                '<div class="product-card__details">' +
                    '<p class="product-card__name">' + escapeHtml(product.shortName) + '</p>' +
                    '<p class="product-card__code">#' + escapeHtml(product.code) + '</p>' +
                    '<p class="product-card__price">' + formatCurrency(product.price) + '</p>' +
                '</div>' +
                '<div class="product-card__action">' +
                    '<span class="status-badge ' + status.className + '">' + status.label + '</span>' +
                    '<span class="product-card__add-label">+ Add</span>' +
                '</div>';

            productGrid.appendChild(card);
        });
    }

    function renderCart() {
        cartList.innerHTML = '';

        if (state.cart.length === 0) {
            cartEmpty.classList.remove('hidden');
            btnCompleteSale.disabled = true;
        } else {
            cartEmpty.classList.add('hidden');
            btnCompleteSale.disabled = false;
        }

        state.cart.forEach(function (item) {
            const li = document.createElement('li');
            li.className = 'cart-item';
            li.dataset.cartId = item.id;

            li.innerHTML =
                '<div class="cart-item__info">' +
                    '<p class="cart-item__name">' + escapeHtml(item.shortName) + '</p>' +
                    '<p class="cart-item__meta">#' + escapeHtml(item.code) + ' &middot; ' + formatCurrency(item.price) + ' each</p>' +
                '</div>' +
                '<div class="cart-item__right">' +
                    '<div class="qty-control">' +
                        '<button type="button" class="qty-control__btn" data-qty-minus="' + item.id + '" aria-label="Decrease">&minus;</button>' +
                        '<span class="qty-control__value">' + item.quantity + '</span>' +
                        '<button type="button" class="qty-control__btn" data-qty-plus="' + item.id + '" aria-label="Increase">&plus;</button>' +
                    '</div>' +
                    '<span class="cart-item__subtotal">' + formatCurrency(item.price * item.quantity) + '</span>' +
                '</div>';

            cartList.appendChild(li);
        });

        updateCheckoutBar();
    }

    function updateCheckoutBar() {
        const totals = getCartTotals();
        checkoutItemCount.textContent = totals.itemCount + (totals.itemCount === 1 ? ' Item' : ' Items');
        // Show subtotal (after any discount, before tax).
        // Tax breakdown is shown in the checkout modal only.
        const displayAmount = totals.subtotal - totals.discountAmount;
        checkoutTotal.textContent = formatCurrency(displayAmount);
    }

    function updateCheckoutModal() {
        const totals = getCartTotals();
        document.getElementById('modal-subtotal').textContent = formatCurrency(totals.subtotal);
        document.getElementById('modal-discount').textContent = '- ' + formatCurrency(totals.discountAmount);
        document.getElementById('modal-tax').textContent = formatCurrency(totals.tax);
        document.getElementById('modal-total').textContent = formatCurrency(totals.total);
        amountPaidInput.value = totals.total.toFixed(2);
        updateChangeAmount();
    }

    function updateChangeAmount() {
        const totals = getCartTotals();
        const paid = parseFloat(amountPaidInput.value) || 0;
        const change = Math.max(0, paid - totals.total);
        document.getElementById('change-amount').textContent = formatCurrency(change);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // -------------------------------------------------------------------------
    // Cart actions
    // -------------------------------------------------------------------------

    function addToCart(productId) {
        const product = MOCK_PRODUCTS.find(function (p) { return p.id === productId; });
        if (!product) return;

        const existing = state.cart.find(function (item) { return item.id === productId; });
        if (existing) {
            existing.quantity += 1;
        } else {
            state.cart.push({
                id: product.id,
                code: product.code,
                shortName: product.shortName,
                price: product.price,
                quantity: 1
            });
        }

        renderCart();
    }

    function updateQuantity(productId, delta) {
        const item = state.cart.find(function (i) { return i.id === productId; });
        if (!item) return;

        item.quantity += delta;
        if (item.quantity <= 0) {
            state.cart = state.cart.filter(function (i) { return i.id !== productId; });
        }

        renderCart();
    }

    function clearCart() {
        if (state.cart.length === 0) return;
        if (window.confirm('Clear all items from the cart?')) {
            state.cart = [];
            state.discount = { type: 'percent', value: 0 };
            renderCart();
        }
    }

    // -------------------------------------------------------------------------
    // Checkout & invoice
    // -------------------------------------------------------------------------

    function openCheckout() {
        if (state.customerType === 'trade' && !state.tradeAccountId) {
            alert('Please select a trade account before completing the sale.');
            tradePanel.classList.remove('hidden');
            tradeSelect.focus();
            return;
        }

        updateCheckoutModal();
        checkoutModal.showModal();
    }

    function confirmSale() {
        const totals = getCartTotals();
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const paid = parseFloat(amountPaidInput.value) || 0;

        if (paid < totals.total && paymentMethod !== 'credit') {
            alert('Amount paid is less than the total.');
            return;
        }

        const cartSnapshot = state.cart.map(function (item) {
            return Object.assign({}, item);
        });

        // TODO (PHP): POST to /api/sales with cart payload
        const invoiceNumber = 'INV-' + String(Date.now()).slice(-6);
        renderInvoice(invoiceNumber, totals, paymentMethod, paid, cartSnapshot);
        checkoutModal.close();
        invoiceModal.showModal();

        state.cart = [];
        state.discount = { type: 'percent', value: 0 };
        renderCart();
    }

    function renderInvoice(invoiceNumber, totals, paymentMethod, paid, items) {
        const customerLabel = state.customerType === 'walking'
            ? 'Walk-in Retail Customer'
            : tradeSelect.options[tradeSelect.selectedIndex].text;

        let rows = '';
        items.forEach(function (item) {
            rows +=
                '<tr>' +
                    '<td>' + escapeHtml(item.shortName) + '</td>' +
                    '<td>' + item.quantity + '</td>' +
                    '<td>' + formatCurrency(item.price * item.quantity) + '</td>' +
                '</tr>';
        });

        document.getElementById('invoice-preview').innerHTML =
            '<div class="invoice-preview__header">' +
                '<h3>SmartAuto Spare Parts</h3>' +
                '<p>123 Industrial Zone, Colombo</p>' +
                '<p>Tel: +94 11 234 5678</p>' +
            '</div>' +
            '<div class="invoice-preview__meta">' +
                '<p><strong>Invoice:</strong> ' + invoiceNumber + '</p>' +
                '<p><strong>Date:</strong> ' + new Date().toLocaleString('en-LK') + '</p>' +
                '<p><strong>Customer:</strong> ' + escapeHtml(customerLabel) + '</p>' +
                '<p><strong>Payment:</strong> ' + paymentMethod.toUpperCase() + '</p>' +
            '</div>' +
            '<table class="invoice-preview__table">' +
                '<thead><tr><th>Item</th><th>Qty</th><th>Amount</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>' +
            '<p class="invoice-preview__total">Total: ' + formatCurrency(totals.total) + '</p>' +
            '<p class="invoice-preview__total">Paid: ' + formatCurrency(paid) + '</p>' +
            '<p style="text-align:center;margin-top:16px;color:var(--color-on-surface-variant)">Thank you for your business!</p>';
    }

    // -------------------------------------------------------------------------
    // Event listeners
    // -------------------------------------------------------------------------

    searchInput.addEventListener('input', function (e) {
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

            if (state.customerType === 'trade') {
                tradePanel.classList.remove('hidden');
            } else {
                tradePanel.classList.add('hidden');
                state.tradeAccountId = '';
                tradeSelect.value = '';
            }
        });
    });

    tradeSelect.addEventListener('change', function () {
        state.tradeAccountId = tradeSelect.value;
    });

    productGrid.addEventListener('click', function (e) {
        const card = e.target.closest('.product-card');
        if (card && card.dataset.productId) {
            addToCart(parseInt(card.dataset.productId, 10));
        }
    });

    cartList.addEventListener('click', function (e) {
        const minus = e.target.closest('[data-qty-minus]');
        const plus = e.target.closest('[data-qty-plus]');
        if (minus) updateQuantity(parseInt(minus.dataset.qtyMinus, 10), -1);
        if (plus) updateQuantity(parseInt(plus.dataset.qtyPlus, 10), 1);
    });

    if (btnClearCart) btnClearCart.addEventListener('click', clearCart);
    btnCompleteSale.addEventListener('click', openCheckout);
    if (btnConfirmSale) btnConfirmSale.addEventListener('click', confirmSale);
    if (amountPaidInput) amountPaidInput.addEventListener('input', updateChangeAmount);

    if (btnDiscount && discountModal && discountForm) {
        btnDiscount.addEventListener('click', function () {
            document.getElementById('discount-type').value = state.discount.type;
            document.getElementById('discount-value').value = state.discount.value || '';
            discountModal.showModal();
        });

        discountForm.addEventListener('submit', function (e) {
            e.preventDefault();
            state.discount = {
                type: document.getElementById('discount-type').value,
                value: parseFloat(document.getElementById('discount-value').value) || 0
            };
            discountModal.close();
            renderCart();
        });
    }

    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('dialog').close();
        });
    });

    // -------------------------------------------------------------------------
    // Init
    // -------------------------------------------------------------------------

    renderProducts();
    renderCart();
})();
