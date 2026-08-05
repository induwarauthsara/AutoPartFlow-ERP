<!-- App Bar -->
<header class="pos-header">
    <div class="pos-header__brand">
        <svg class="icon icon--md" aria-hidden="true"><use href="#icon-storefront"></use></svg>
        <h1 class="pos-header__title">New Sale</h1>
    </div>
    <div class="pos-header__meta">
        <!-- PHP: echo logged-in rep name from session -->
        <span class="pos-header__rep">Sales Rep</span>
        <span class="pos-header__date" id="pos-today"></span>
    </div>
</header>

<main class="pos-main" id="pos-main">

    <!-- Search & Customer -->
    <section class="pos-toolbar">
        <div class="search-field">
            <svg class="icon search-field__icon" aria-hidden="true"><use href="#icon-search"></use></svg>
            <input
                type="search"
                id="product-search"
                class="search-field__input"
                placeholder="Search parts by name or code..."
                autocomplete="off"
            >
        </div>

        <div class="customer-chips" role="tablist" aria-label="Customer type">
            <button type="button" class="chip chip--active" data-customer-type="walking" role="tab" aria-selected="true">
                <svg class="icon icon--sm" aria-hidden="true"><use href="#icon-person"></use></svg>
                Walk-in Retail
            </button>
            <button type="button" class="chip" data-customer-type="trade" role="tab" aria-selected="false">
                <svg class="icon icon--sm" aria-hidden="true"><use href="#icon-badge"></use></svg>
                Select Trade Account
            </button>
        </div>
    </section>

    <!-- Trade account picker (hidden until trade selected) -->
    <section class="trade-panel hidden" id="trade-panel">
        <label class="field-label" for="trade-account">Trade Account</label>
        <select id="trade-account" class="field-select">
            <option value="">Choose a shop customer...</option>
            <!-- PHP: loop shop customers from Customer model -->
            <option value="CUS-00001">City Auto Works — Dehiwala</option>
            <option value="CUS-00002">Highway Garage &amp; Parts — Kadawatha</option>
            <option value="CUS-00003">Nuwara Motors — Nuwara Eliya</option>
        </select>
    </section>

    <!-- Product Grid -->
    <section class="pos-section">
        <h2 class="section-label">Quick Add</h2>
        <div class="product-grid" id="product-grid">
            <!-- Rendered by JS (wire to Product API) -->
        </div>
        <p class="empty-message hidden" id="no-products">No parts match your search.</p>
    </section>
</main>

<!-- Cart panel: stacks below products on mobile, side panel on wide screens -->
<aside class="pos-cart" aria-label="Current order">
    <section class="pos-section pos-section--cart">
        <div class="section-header">
            <h2 class="section-title">Current Order</h2>
            <button type="button" class="btn-text" id="btn-clear-cart">Clear</button>
        </div>
        <ul class="cart-list" id="cart-list">
            <!-- Rendered by JS -->
        </ul>
        <p class="empty-message" id="cart-empty">Cart is empty. Tap + on a product to add.</p>
        <button type="button" class="btn-outline btn-outline--full" id="btn-discount">
            <svg class="icon icon--sm" aria-hidden="true"><use href="#icon-tag"></use></svg>
            Apply Discount
        </button>
    </section>

    <!-- Checkout summary + action -->
    <div class="checkout-bar" id="checkout-bar">
        <div class="checkout-bar__summary">
            <div>
                <span class="checkout-bar__items" id="checkout-item-count">0 Items</span>
                <p class="checkout-bar__total">Total: <strong id="checkout-total">Rs. 0.00</strong></p>
            </div>
            <span class="checkout-bar__tax">Tax included</span>
        </div>
        <button type="button" class="btn-primary btn-primary--full" id="btn-complete-sale" disabled>
            Complete Sale
            <svg class="icon icon--sm" aria-hidden="true"><use href="#icon-arrow-forward"></use></svg>
        </button>
    </div>
</aside>

<!-- Navigation: bottom bar on mobile, side rail on wide screens -->
<nav class="bottom-nav" aria-label="Main navigation">
    <!-- PHP: href="<?= url('sales/pos') ?>" -->
    <a href="#" class="bottom-nav__item bottom-nav__item--active" aria-current="page">
        <svg class="icon" aria-hidden="true"><use href="#icon-pos"></use></svg>
        <span>POS</span>
    </a>
    <!-- PHP: href="<?= url('sales/inventory') ?>" -->
    <a href="#" class="bottom-nav__item">
        <svg class="icon" aria-hidden="true"><use href="#icon-inventory"></use></svg>
        <span>Inventory</span>
    </a>
    <!-- PHP: href="<?= url('sales/orders') ?>" -->
    <a href="#" class="bottom-nav__item">
        <svg class="icon" aria-hidden="true"><use href="#icon-orders"></use></svg>
        <span>Orders</span>
    </a>
    <!-- PHP: href="<?= url('sales/customers') ?>" -->
    <a href="#" class="bottom-nav__item">
        <svg class="icon" aria-hidden="true"><use href="#icon-person"></use></svg>
        <span>Customers</span>
    </a>
</nav>

<?php require APP_PATH . '/Views/sales/pos/_modals.php'; ?>
