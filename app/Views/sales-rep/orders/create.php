<?php
$products = $products ?? [];
$customers = $customers ?? [];
$customerData = [];
$categories = [];
foreach ($customers as $customer) {
    $customerData[(string) $customer['databaseId']] = $customer;
}
foreach ($products as $product) {
    if (!empty($product['category'])) {
        $categories[(string) $product['category']] = true;
    }
}
ksort($categories);
?>
<main class="sales-main" data-sales-page="create">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= url('sales/orders') ?>">Orders</a>
        <span>/</span>
        <span aria-current="page">New Order</span>
    </nav>

    <section class="sales-page-heading">
        <div>
            <p class="sales-eyebrow">Customer Order Entry</p>
            <h1>New Order Request</h1>
            <p>Create a customer order and prepare it for processing.</p>
        </div>
        <button class="sales-button sales-button--secondary" type="button" id="save-draft">Save Draft</button>
    </section>

    <section class="customer-card sales-card">
        <div class="field-group">
            <label for="order-customer">Customer</label>
            <select id="order-customer" name="customer_id">
                <option value="">Select a customer...</option>
                <?php foreach ($customers as $code => $customer): ?>
                    <option value="<?= (int) $customer['databaseId'] ?>"><?= e($customer['name']) ?> — <?= e($customer['detail']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="customer-card__meta" id="customer-meta">
            <span class="sales-avatar" aria-hidden="true">
                <svg class="sales-icon"><use href="#sales-icon-user"></use></svg>
            </span>
            <div>
                <strong>No customer selected</strong>
                <span>Choose a customer account to continue</span>
            </div>
        </div>
    </section>

    <div class="order-entry-layout">
        <section class="product-selection">
            <div class="product-toolbar sales-card">
                <div class="sales-search">
                    <svg class="sales-icon"><use href="#sales-icon-search"></use></svg>
                    <input type="search" id="part-search" placeholder="Search by SKU or part name..." autocomplete="off">
                </div>
                <select id="category-filter" aria-label="Filter by category">
                    <option value="all">Category: All</option>
                    <?php foreach (array_keys($categories) as $category): ?>
                        <option value="<?= e($category) ?>"><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="section-heading">
                <div>
                    <h2>Available Parts</h2>
                    <p id="parts-result-count">0 parts</p>
                </div>
            </div>
            <div class="product-list" id="order-product-list"></div>
            <p class="sales-empty hidden" id="parts-empty">No parts match your search.<span class="sales-empty__action">Try another SKU or category.</span></p>
        </section>

        <aside class="order-summary sales-card">
            <div class="order-summary__header">
                <div>
                    <h2>Order Summary</h2>
                    <span id="summary-item-count">0 items</span>
                </div>
                <svg class="sales-icon"><use href="#sales-icon-cart"></use></svg>
            </div>
            <div class="summary-items" id="summary-items"></div>
            <p class="summary-empty" id="summary-empty">Add a part to start this order.</p>
            <div class="summary-totals">
                <div><span>Subtotal</span><strong id="summary-subtotal">Rs. 0.00</strong></div>
                <div class="summary-totals__grand"><span>Total</span><strong id="summary-total">Rs. 0.00</strong></div>
                <label for="order-notes">Order notes</label>
                <textarea id="order-notes" rows="3" placeholder="Delivery instructions or customer notes..."></textarea>
                <label for="delivery-address">Delivery address</label>
                <textarea id="delivery-address" rows="3" placeholder="Required when this order needs delivery"></textarea>
                <button class="sales-button sales-button--primary sales-button--full" type="button" id="submit-order" disabled>
                    Place Order
                    <svg class="sales-icon"><use href="#sales-icon-chevron"></use></svg>
                </button>
            </div>
        </aside>
    </div>

    <dialog class="order-dialog sales-dialog" id="order-confirm-dialog">
        <div class="order-dialog__content">
            <div class="success-mark"><svg class="sales-icon"><use href="#sales-icon-check"></use></svg></div>
            <h2>Order ready to submit</h2>
            <p id="confirm-message">Review the order information before sending it to your backend.</p>
            <div class="order-dialog__actions">
                <button class="sales-button sales-button--secondary" type="button" data-dialog-close>Continue Editing</button>
                <button class="sales-button sales-button--primary" type="button" id="confirm-order">Confirm Order</button>
            </div>
        </div>
    </dialog>

    <div class="sales-toast" id="sales-toast" role="status" aria-live="polite"></div>
</main>
<script>
window.ORDER_MOCK_DATA = {
    products: <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    customers: <?= json_encode($customerData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
};
</script>
