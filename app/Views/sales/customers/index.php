<main class="sales-main customers-main" data-sales-page="customers">
    <section class="sales-page-heading">
        <div>
            <p class="sales-eyebrow">Sales &amp; Customer Operations</p>
            <h1>Customer Management</h1>
            <p>Manage trade accounts and walk-in customer profiles.</p>
        </div>
        <button class="sales-button sales-button--primary" type="button" id="new-customer">
            <svg class="sales-icon"><use href="#sales-icon-plus"></use></svg>
            New Customer
        </button>
    </section>

    <section class="customer-toolbar sales-card">
        <div class="customer-type-tabs" role="tablist" aria-label="Customer type">
            <button class="customer-type-tab customer-type-tab--active" type="button" role="tab" aria-selected="true" data-customer-type="shop">Retail Shops</button>
            <button class="customer-type-tab" type="button" role="tab" aria-selected="false" data-customer-type="walking">Walk-in Customers</button>
        </div>
        <div class="sales-search">
            <svg class="sales-icon"><use href="#sales-icon-search"></use></svg>
            <input type="search" id="customer-search" placeholder="Search customer name, phone or ID..." autocomplete="off">
        </div>
    </section>

    <div class="customer-workspace">
        <section class="customer-master sales-card">
            <div class="customer-filters" id="customer-filters">
                <button class="customer-filter customer-filter--active" type="button" data-customer-filter="all">All Customers</button>
                <button class="customer-filter" type="button" data-customer-filter="overdue">Overdue Balance</button>
                <button class="customer-filter" type="button" data-customer-filter="high-volume">High Volume</button>
            </div>
            <div class="customer-list" id="customer-list">
                <!-- Rendered by sales.js; replace mock data with CustomerController output. -->
            </div>
            <p class="sales-empty hidden" id="customer-empty">No customers match your search.</p>
        </section>

        <section class="customer-detail" id="customer-detail" aria-label="Selected customer profile">
            <!-- Rendered by sales.js -->
        </section>
    </div>

    <dialog class="customer-dialog" id="customer-dialog">
        <form class="customer-form" id="customer-form" method="dialog">
            <div class="customer-dialog__header">
                <div>
                    <p class="sales-eyebrow">Customer Record</p>
                    <h2 id="customer-dialog-title">New Customer</h2>
                </div>
                <button class="sales-icon-button" type="button" data-close-customer-dialog aria-label="Close">
                    <svg class="sales-icon"><use href="#sales-icon-close"></use></svg>
                </button>
            </div>
            <div class="customer-dialog__body">
                <input type="hidden" id="customer-edit-id">
                <label>
                    Customer Type
                    <select id="customer-form-type" name="customer_type" required>
                        <option value="shop">Retail Shop</option>
                        <option value="walking">Walk-in Customer</option>
                    </select>
                </label>
                <label>
                    Customer or Business Name
                    <input type="text" id="customer-form-name" name="name" required maxlength="100">
                </label>
                <div class="form-grid">
                    <label>
                        Phone Number
                        <input type="tel" id="customer-form-phone" name="phone" required maxlength="20">
                    </label>
                    <label>
                        Email Address
                        <input type="email" id="customer-form-email" name="email" maxlength="120">
                    </label>
                </div>
                <label>
                    Address
                    <textarea id="customer-form-address" name="address" rows="3" maxlength="250"></textarea>
                </label>
            </div>
            <div class="customer-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-close-customer-dialog>Cancel</button>
                <button class="sales-button sales-button--primary" type="submit">Save Customer</button>
            </div>
        </form>
    </dialog>

    <div class="sales-toast" id="sales-toast" role="status" aria-live="polite"></div>
</main>
