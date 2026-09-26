document.addEventListener('DOMContentLoaded', () => {
    const cartPage = document.getElementById('cartPage');
    if (!cartPage) return;

    const itemsContainer = document.getElementById('cartItems');
    const emptyState = document.getElementById('cartEmpty');
    const itemCount = document.getElementById('cartItemCount');
    const subtotalEl = document.getElementById('cartSubtotal');
    const deliveryFeeEl = document.getElementById('cartDeliveryFee');
    const totalEl = document.getElementById('cartTotal');
    const checkoutBtn = document.getElementById('cartCheckoutBtn');
    const cartBadge = document.querySelector('.cart-count');
    const baseUrl = window.APP_CONFIG?.baseUrl || '';
    const deliveryFee = 350;

    function readCart() {
        try {
            const parsed = JSON.parse(localStorage.getItem('autopartflow_cart') || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('autopartflow_cart', JSON.stringify(cart));
    }

    function escapeHTML(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMoney(value) {
        return `Rs. ${Number(value || 0).toLocaleString('en-LK')}`;
    }

    function normalizeCart(cart) {
        const normalized = [];

        cart.forEach(item => {
            const code = String(item?.code || '').trim();
            const name = String(item?.name || code).trim();
            const price = Number(item?.price || 0);
            const qty = Math.max(1, Math.min(99, parseInt(item?.qty || 1, 10)));

            if (!code || !Number.isFinite(price) || price < 0) return;

            const existing = normalized.find(entry => entry.code === code);
            if (existing) {
                existing.qty = Math.min(99, existing.qty + qty);
            } else {
                normalized.push({ code, name, price, qty });
            }
        });

        return normalized;
    }

    function updateHeaderBadge(cart) {
        if (!cartBadge) return;
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        cartBadge.textContent = String(count);
    }

    function render() {
        const cart = normalizeCart(readCart());
        saveCart(cart);
        updateHeaderBadge(cart);

        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
        const total = cart.length > 0 ? subtotal + deliveryFee : 0;

        itemCount.textContent = `${count} item${count === 1 ? '' : 's'}`;
        subtotalEl.textContent = formatMoney(subtotal);
        deliveryFeeEl.textContent = formatMoney(cart.length > 0 ? deliveryFee : 0);
        totalEl.textContent = formatMoney(total);
        checkoutBtn.disabled = cart.length === 0;

        if (cart.length === 0) {
            itemsContainer.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        itemsContainer.innerHTML = cart.map(item => `
            <article class="cart-item" data-code="${escapeHTML(item.code)}">
                <div>
                    <code class="cart-item__code">${escapeHTML(item.code)}</code>
                    <h3 class="cart-item__name">${escapeHTML(item.name)}</h3>
                    <div class="cart-item__unit">${formatMoney(item.price)} each</div>
                </div>
                <div class="cart-quantity" aria-label="Quantity controls">
                    <button type="button" data-action="decrease" data-code="${escapeHTML(item.code)}" aria-label="Decrease quantity">−</button>
                    <span>${item.qty}</span>
                    <button type="button" data-action="increase" data-code="${escapeHTML(item.code)}" aria-label="Increase quantity">+</button>
                </div>
                <strong class="cart-item__total">${formatMoney(item.price * item.qty)}</strong>
                <button type="button" class="cart-remove" data-action="remove" data-code="${escapeHTML(item.code)}" aria-label="Remove ${escapeHTML(item.name)} from cart" title="Remove item">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </article>
        `).join('');
    }

    function updateQuantity(code, delta) {
        const cart = readCart();
        const item = cart.find(entry => entry.code === code);
        if (!item) return;

        item.qty = Math.max(0, Math.min(99, parseInt(item.qty || 1, 10) + delta));
        if (item.qty === 0) {
            const index = cart.indexOf(item);
            cart.splice(index, 1);
        }

        saveCart(cart);
        render();
    }

    function removeItem(code) {
        const cart = readCart().filter(item => item.code !== code);
        saveCart(cart);
        render();
    }

    itemsContainer.addEventListener('click', event => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;

        const code = button.dataset.code || '';
        const action = button.dataset.action;

        if (action === 'increase') updateQuantity(code, 1);
        if (action === 'decrease') updateQuantity(code, -1);
        if (action === 'remove') removeItem(code);
    });

    checkoutBtn.addEventListener('click', () => {
        if (!checkoutBtn.disabled) {
            window.location.href = `${baseUrl}/checkout`;
        }
    });

    render();
});
