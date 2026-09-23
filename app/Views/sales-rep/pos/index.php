<main class="sales-main" data-sales-page="pos">
    <p class="pos-compact-title">New Sale</p>

    <section class="sales-page-heading pos-heading-desktop">
        <div>
            <p class="sales-eyebrow">Sales Representative Workspace</p>
            <h1>Point of Sale (POS)</h1>
            <p>Fast checkout, quick product search and customer billing.</p>
        </div>
    </section>

    <p class="sr-only" id="pos-live" aria-live="polite"></p>

    <div class="pos-workspace-grid">
        <div class="pos-catalog-column">
            <section class="sales-card">
                <div class="pos-toolbar-row">
                    <div class="search-field">
                        <svg class="sales-icon search-field__icon" aria-hidden="true"><use href="#sales-icon-search"></use></svg>
                        <input type="search" id="product-search" class="search-field__input" placeholder="Search parts by name, code, or brand..." autocomplete="off">
                    </div>
                    <div class="customer-chips" role="tablist" aria-label="Customer type">
                        <button type="button" class="chip chip--active" data-customer-type="walking" role="tab" aria-selected="true">Walk-in Customer</button>
                        <button type="button" class="chip" data-customer-type="trade" role="tab" aria-selected="false">Trade Account</button>
                    </div>
                </div>
                <div class="trade-panel hidden" id="trade-panel">
                    <label class="field-label" for="trade-account">Trade Account Customer</label>
                    <select id="trade-account" class="field-select">
                        <option value="">Choose a registered trade shop customer...</option>
                        <option value="CUS-00001">City Auto Works — Dehiwala</option>
                        <option value="CUS-00002">Highway Garage &amp; Parts — Kadawatha</option>
                        <option value="CUS-00003">Nuwara Motors — Nuwara Eliya</option>
                    </select>
                </div>
            </section>

            <section class="sales-card">
                <div class="sales-card__header">
                    <div>
                        <h2>Quick Add Products</h2>
                        <p class="sales-card__subtitle">Tap + to add a part to the cart</p>
                    </div>
                </div>
                <div class="product-grid" id="product-grid"></div>
                <p class="sales-empty hidden" id="no-products">No parts match your search.<span class="sales-empty__action">Try a SKU or brand name.</span></p>
            </section>
        </div>

        <aside class="pos-cart-column">
            <section class="sales-card pos-cart-card" id="pos-cart-card">
                <div class="sales-card__header pos-cart-header">
                    <div>
                        <h2>Current Order</h2>
                        <span id="checkout-item-count" class="sales-card__subtitle">0 items</span>
                    </div>
                    <div class="pos-cart-actions">
                        <button type="button" class="btn-discount" id="btn-discount">Discount</button>
                        <button type="button" class="btn-clear-cart" id="btn-clear-cart">Clear</button>
                    </div>
                </div>
                <div class="cart-scroll-area">
                    <ul class="cart-list" id="cart-list"></ul>
                    <div class="cart-empty-state" id="cart-empty">
                        <p class="cart-empty-state__title">Cart is empty</p>
                        <small class="cart-empty-state__desc">Search parts above, then tap + to add items</small>
                    </div>
                </div>
                <div class="checkout-footer">
                    <div class="checkout-summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span id="checkout-total" class="summary-value">Rs. 0.00</span>
                    </div>
                    <small id="discount-note" class="hidden"></small>
                    <button type="button" class="sales-button sales-button--primary sales-button--full" id="btn-complete-sale" disabled>Complete Sale</button>
                </div>
            </section>
        </aside>
    </div>

    <div class="pos-sticky-bar" id="pos-sticky-bar">
        <div>
            <small id="sticky-item-count">0 items</small>
            <div id="sticky-total" class="summary-value">Rs. 0.00</div>
        </div>
        <button type="button" class="sales-button sales-button--secondary" id="btn-toggle-cart">Cart</button>
        <button type="button" class="sales-button sales-button--primary" id="btn-complete-sale-mobile" disabled>Complete Sale</button>
    </div>
</main>

<?php require APP_PATH . '/Views/sales-rep/pos/_modals.php'; ?>

<div class="sales-toast" id="sales-toast" role="status" aria-live="polite"></div>
