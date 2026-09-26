<?php
$cartTitle = $title ?? 'Shopping Cart | AutoPartFlow';
?>

<div class="cart-page" id="cartPage">
    <div class="cart-container">
        <div class="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span class="material-symbols-outlined">chevron_right</span>
            <a href="<?= url('catalog') ?>">Catalog</a>
            <span class="material-symbols-outlined">chevron_right</span>
            <strong>Shopping Cart</strong>
        </div>

        <section class="cart-heading">
            <div>
                <h1>Shopping Cart</h1>
                <p>Review your selected spare parts before continuing to checkout.</p>
            </div>
            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                <a class="cart-continue-link" href="<?= url('orders') ?>">
                    <span class="material-symbols-outlined">receipt_long</span>
                    My Orders
                </a>
                <a class="cart-continue-link" href="<?= url('catalog') ?>">
                <span class="material-symbols-outlined">arrow_back</span>
                Continue Shopping
            </a>
            </div>
        </section>

        <div class="cart-layout">
            <section class="cart-card">
                <div class="cart-card__header">
                    <h2>Your Items</h2>
                    <span id="cartItemCount">0 items</span>
                </div>

                <div id="cartItems" class="cart-items" aria-live="polite"></div>

                <div id="cartEmpty" class="cart-empty hidden">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <h3>Your cart is empty</h3>
                    <p>Add spare parts from the catalog before continuing to checkout.</p>
                    <a class="btn-primary" href="<?= url('catalog') ?>">Browse Spare Parts</a>
                </div>
            </section>

            <aside class="cart-summary-card">
                <h2>Cart Summary</h2>
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <strong id="cartSubtotal">Rs. 0</strong>
                </div>
                <div class="cart-summary-row">
                    <span>Delivery Fee</span>
                    <strong id="cartDeliveryFee">Rs. 350</strong>
                </div>
                <div class="cart-summary-total">
                    <span>Total</span>
                    <strong id="cartTotal">Rs. 0</strong>
                </div>
                <button type="button" class="cart-checkout-btn" id="cartCheckoutBtn" disabled>
                    Proceed to Checkout
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
                <p class="cart-summary-note">Final price, stock availability and totals are verified again by the server at checkout.</p>
            </aside>
        </div>
    </div>
</div>
