<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Checkout - AutoPartFlow-ERP') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@400&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0..200" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/public/checkout.css') ?>">
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(url(), '/') ?>'
        };
    </script>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
</head>
<body class="checkout-body">

<!-- Top Navigation Bar -->
<header class="checkout-header">
    <div class="checkout-header__inner">
        <a class="checkout-brand" href="<?= url('catalog') ?>">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
            <span>AutoPartFlow</span>
        </a>

        <div class="checkout-header__actions">
            <button class="header-icon-btn" type="button" title="Help Center" onclick="alert('For assistance with your order, please contact support at +1 (800) 555-0198.')">
                <span class="material-symbols-outlined">help</span>
            </button>
            <a class="header-icon-btn" href="<?= url('catalog') ?>" title="Cancel and return to Catalog">
                <span class="material-symbols-outlined">close</span>
            </a>
        </div>
    </div>
</header>

<!-- Main Checkout Container -->
<main class="checkout-main">
    <!-- Left Column: Order & Delivery Form -->
    <div class="checkout-form-col">
        <div class="checkout-heading">
            <div class="checkout-title-row">
                <h1>Checkout</h1>
                <span class="status-badge status-badge--pending">Pending</span>
            </div>
            <p>Complete your delivery and payment details to place your order.</p>
        </div>

        <form class="checkout-card" id="checkoutForm" novalidate autocomplete="on">
            <!-- Delivery Details Section -->
            <div class="form-section">
                <h2 class="form-section__title">
                    <span class="material-symbols-outlined text-secondary">local_shipping</span>
                    <span>Delivery Details</span>
                </h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="fullName">Full Name <span class="text-error">*</span></label>
                        <input class="form-input" id="fullName" name="fullName" placeholder="Enter your full name" required type="text">
                        <span class="error-msg hidden" id="fullNameError">Full Name is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phoneNumber">Phone Number <span class="text-error">*</span></label>
                        <input class="form-input" id="phoneNumber" name="phoneNumber" placeholder="07XXXXXXXX" required type="tel" maxlength="12">
                        <span class="error-msg hidden" id="phoneError">Valid 10-digit Sri Lankan number required (e.g., 0712345678).</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="deliveryAddress">Delivery Address <span class="text-error">*</span></label>
                    <textarea class="form-textarea" id="deliveryAddress" name="deliveryAddress" placeholder="Enter full delivery address with city" required rows="3"></textarea>
                    <span class="error-msg hidden" id="addressError">Delivery Address is required.</span>
                </div>
            </div>

            <!-- Payment Method Section -->
            <div class="form-section">
                <h2 class="form-section__title">
                    <span class="material-symbols-outlined text-secondary">payments</span>
                    <span>Payment Method</span>
                </h2>

                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="paymentMethod" value="cod" checked>
                        <span class="payment-option__text">Cash on Delivery</span>
                        <span class="material-symbols-outlined payment-option__icon">local_atm</span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="paymentMethod" value="card">
                        <span class="payment-option__text">Card Payment (Online)</span>
                        <span class="material-symbols-outlined payment-option__icon">credit_card</span>
                    </label>
                </div>
            </div>
        </form>
    </div>

    <!-- Right Column: Order Summary Card -->
    <div class="checkout-summary-col">
        <div class="summary-card">
            <h2 class="summary-card__title">Order Summary</h2>

            <ul class="summary-items" id="summaryItemsList">
                <!-- Dynamically populated from localStorage / default fallback -->
                <li class="summary-item">
                    <div class="summary-item__info">
                        <strong class="summary-item__name">Front Brake Pad Set - Brembo</strong>
                        <span class="summary-item__qty">Qty: 1</span>
                    </div>
                    <span class="summary-item__price">Rs. 4,500</span>
                </li>
            </ul>

            <div class="summary-calc">
                <div class="summary-calc__row">
                    <span>Subtotal</span>
                    <span id="summarySubtotal">Rs. 4,500</span>
                </div>
                <div class="summary-calc__row">
                    <span>Delivery Fee</span>
                    <span id="summaryDeliveryFee">Rs. 350</span>
                </div>
            </div>

            <div class="summary-total-row">
                <span class="summary-total__label">Total Amount</span>
                <span class="summary-total__value" id="summaryTotal">Rs. 4,850</span>
            </div>

            <button class="confirm-order-btn" id="confirmOrderBtn" type="button">
                <span>Confirm Order</span>
                <span class="material-symbols-outlined">check_circle</span>
            </button>
        </div>
    </div>
</main>

<!-- Success Modal -->
<div class="modal-overlay hidden" id="successModal" aria-hidden="true">
    <div class="success-modal-card" id="successModalContent">
        <div class="success-icon-badge">
            <span class="material-symbols-outlined fill text-secondary">check_circle</span>
        </div>

        <h2 class="success-title">Order Placed Successfully!</h2>

        <p class="success-order-num">
            Order #: <span class="order-code-badge" id="successOrderNumber">ORD-2026-00124</span>
        </p>

        <div class="delivery-notice-box">
            <span class="material-symbols-outlined text-secondary">local_shipping</span>
            <span><strong>Estimated Delivery:</strong> <span id="successDeliveryTime">Tomorrow by 2PM</span></span>
        </div>

        <div class="modal-action-buttons">
            <button class="btn-modal-primary" type="button" onclick="alert('Order tracking status: Pending Confirmation by store warehouse.')">
                Track Order Status
            </button>
            <button class="btn-modal-secondary" type="button" onclick="closeSuccessModal()">
                Continue Shopping
            </button>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="checkout-footer">
    <div class="checkout-footer__inner">
        <div>&copy; <?= date('Y') ?> AutoPartFlow. All rights reserved.</div>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Help Center</a>
        </div>
    </div>
</footer>

<script src="<?= asset('js/public/checkout.js') ?>"></script>
</body>
</html>
