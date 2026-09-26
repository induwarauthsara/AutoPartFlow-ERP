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
            baseUrl: '<?= rtrim(url(), '/') ?>',
            csrfToken: '<?= e(csrf_token()) ?>'
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
            <button class="header-icon-btn" type="button" title="Help Center" onclick="window.location.href='<?= url('help') ?>'">
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
                    <span class="material-symbols-outlined text-secondary">account_circle</span>
                    <span>Customer Account</span>
                </h2>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="material-symbols-outlined" style="font-size: 36px; color: #3b6090; background: #e0f2fe; padding: 8px; border-radius: 50%;">person</span>
                            <div>
                                <strong style="font-size: 16px; color: #0b192e; display: block;"><?= e($customer['name'] ?? ($_SESSION['full_name'] ?? 'Shop Customer')) ?></strong>
                                <span style="font-size: 13px; color: #64748b;"><?= e($customer['email'] ?? ($_SESSION['email'] ?? '')) ?></span>
                                <?php if (!empty($customer['customer_code'])): ?>
                                    <span style="display: inline-block; margin-left: 8px; font-size: 11px; font-weight: 700; background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 4px;">
                                        <?= e($customer['customer_code']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="<?= url('profile') ?>" target="_blank" style="font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">manage_accounts</span>
                            Edit Profile
                        </a>
                    </div>
                </div>

                <input type="hidden" id="fullName" name="fullName" value="<?= e($customer['name'] ?? ($_SESSION['full_name'] ?? 'Shop Customer')) ?>">

                <?php if (!empty($customer['phone'])): ?>
                    <input type="hidden" id="phoneNumber" name="phoneNumber" value="<?= e($customer['phone']) ?>">
                    <div style="margin-bottom: 20px; font-size: 13px; color: #166534; display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px 14px; border-radius: 8px;">
                        <span class="material-symbols-outlined" style="color: #16a34a; font-size: 20px;">call</span>
                        <span>Contact Phone: <strong><?= e($customer['phone']) ?></strong> (from verified profile)</span>
                    </div>
                <?php else: ?>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" for="phoneNumber">Contact Phone Number <span class="text-error">*</span> <small style="color: #64748b; font-weight: 400;">(Required for delivery; will save to your profile)</small></label>
                        <input class="form-input" id="phoneNumber" name="phoneNumber" value="" placeholder="07XXXXXXXX" required type="tel" maxlength="12">
                        <span class="error-msg hidden" id="phoneError">Valid 10-digit Sri Lankan number required (e.g., 0712345678).</span>
                    </div>
                <?php endif; ?>

                <h2 class="form-section__title" style="margin-top: 24px;">
                    <span class="material-symbols-outlined text-secondary">local_shipping</span>
                    <span>Delivery Address</span>
                </h2>

                <div class="form-group">
                    <label class="form-label" for="deliveryAddress">Delivery Address <span class="text-error">*</span></label>
                    <textarea class="form-textarea" id="deliveryAddress" name="deliveryAddress" placeholder="Enter full delivery address with city and postal code" required rows="3"><?= e($customer['address'] ?? '') ?></textarea>
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
                </div>
            </div>
        </form>
    </div>

    <!-- Right Column: Order Summary Card -->
    <div class="checkout-summary-col">
        <div class="summary-card">
            <h2 class="summary-card__title">Order Summary</h2>

            <ul class="summary-items" id="summaryItemsList" aria-live="polite"></ul>

            <div class="summary-calc">
                <div class="summary-calc__row">
                    <span>Subtotal</span>
                    <span id="summarySubtotal">Rs. 0</span>
                </div>
                <div class="summary-calc__row">
                    <span>Delivery Fee</span>
                    <span id="summaryDeliveryFee">Rs. 350</span>
                </div>
            </div>

            <div class="summary-total-row">
                <span class="summary-total__label">Total Amount</span>
                <span class="summary-total__value" id="summaryTotal">Rs. 0</span>
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
            Order #: <span class="order-code-badge" id="successOrderNumber"></span>
        </p>

        <div class="delivery-notice-box">
            <span class="material-symbols-outlined text-secondary">local_shipping</span>
            <span><strong>Estimated Delivery:</strong> <span id="successDeliveryTime">The store will confirm the delivery date.</span></span>
        </div>

        <div class="modal-action-buttons">
            <button class="btn-modal-primary" type="button" id="trackOrderBtn">
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
            <a href="<?= url('privacy') ?>">Privacy Policy</a>
            <a href="<?= url('terms') ?>">Terms of Service</a>
            <a href="<?= url('help') ?>">Help Center</a>
        </div>
    </div>
</footer>

<script src="<?= asset('js/public/checkout.js') ?>"></script>
</body>
</html>
