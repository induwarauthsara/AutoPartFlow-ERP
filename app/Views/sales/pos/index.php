<main class="sales-main" data-sales-page="pos">
    <section class="sales-page-heading">
        <div>
            <p class="sales-eyebrow">Sales Representative Workspace</p>
            <h1>Point of Sale (POS)</h1>
            <p>Fast checkout, quick product search & customer billing terminal.</p>
        </div>
    </section>

    <div class="pos-workspace-grid">
        
        <!-- Catalog Column -->
        <div class="pos-catalog-column">
            
            <!-- Toolbar Card -->
            <section class="sales-card">
                <div class="pos-toolbar-row">
                    <div class="search-field" style="flex: 1; min-width: 260px;">
                        <svg class="sales-icon search-field__icon" aria-hidden="true"><use href="#sales-icon-search"></use></svg>
                        <input
                            type="search"
                            id="product-search"
                            class="search-field__input"
                            placeholder="Search parts by name, code, or brand..."
                            autocomplete="off"
                        >
                    </div>

                    <div class="customer-chips" role="tablist" aria-label="Customer type">
                        <button type="button" class="chip chip--active" data-customer-type="walking" role="tab" aria-selected="true">
                            Walk-in Customer
                        </button>
                        <button type="button" class="chip" data-customer-type="trade" role="tab" aria-selected="false">
                            Trade Account
                        </button>
                    </div>
                </div>

                <!-- Trade Account Panel -->
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

            <!-- Product Catalog Card -->
            <section class="sales-card">
                <div class="sales-card__header">
                    <div>
                        <h2>Quick Add Products</h2>
                        <p class="sales-card__subtitle">Click any product to add it to cart</p>
                    </div>
                </div>

                <div class="product-grid" id="product-grid">
                    <!-- JS renders cards here -->
                </div>
                <p class="empty-message hidden" id="no-products">No parts match your search.</p>
            </section>
        </div>

        <!-- Cart Column -->
        <aside class="pos-cart-column">
            <section class="sales-card pos-cart-card">
                <div class="sales-card__header pos-cart-header">
                    <div>
                        <h2>Current Order</h2>
                        <span id="checkout-item-count" class="sales-card__subtitle">0 Items Selected</span>
                    </div>
                    <button type="button" class="btn-clear-cart" id="btn-clear-cart">Clear</button>
                </div>

                <!-- Cart Items Scroll Area -->
                <div class="cart-scroll-area">
                    <ul class="cart-list" id="cart-list">
                        <!-- JS renders cart items here -->
                    </ul>
                    <div class="cart-empty-state" id="cart-empty">
                        <p class="cart-empty-state__title">Cart is empty</p>
                        <small class="cart-empty-state__desc">Click any product on the left to add items</small>
                    </div>
                </div>

                <div class="checkout-footer">
                    <div class="checkout-summary-row">
                        <span class="summary-label">Total Amount</span>
                        <span id="checkout-total" class="summary-value">Rs. 0.00</span>
                    </div>
                    <button type="button" class="sales-primary-btn" id="btn-complete-sale" disabled>
                        Complete Sale
                    </button>
                </div>
            </section>
        </aside>

    </div>
</main>

<?php require APP_PATH . '/Views/sales/pos/_modals.php'; ?>
