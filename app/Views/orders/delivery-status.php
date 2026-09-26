<?php
$delivery = $delivery ?? null;
$orderNumber = $orderNumber ?? '';
$phone = $phone ?? '';
$error = $error ?? null;
$customer = $customer ?? null;
$status = $delivery ? strtolower((string) $delivery['delivery_status']) : '';
$statusLabels = [
    'pending' => 'Preparing Delivery',
    'in_transit' => 'Out for Delivery',
    'delivered' => 'Delivered',
    'failed' => 'Delivery Attempt Failed',
    'returned' => 'Returned to Store',
];
$statusLabel = $statusLabels[$status] ?? 'Delivery Status';
?>

<div class="delivery-page">
    <div class="delivery-container">
        <div class="breadcrumb">
            <a href="<?= url() ?>">Home</a><span class="material-symbols-outlined">chevron_right</span>
            <strong>Delivery Status</strong>
        </div>

        <section class="delivery-heading">
            <div>
                <span class="delivery-eyebrow">CUSTOMER DELIVERY</span>
                <h1>Delivery Status</h1>
                <p>Check the latest delivery status for your online spare parts order.</p>
            </div>
            <span class="material-symbols-outlined delivery-heading-icon">local_shipping</span>
        </section>

        <?php if (!$customer): ?>
            <section class="delivery-card delivery-lookup">
                <div>
                    <h2>Find a Delivery</h2>
                    <p>Guests need both the order number and the phone number used at checkout.</p>
                </div>
                <form method="get" action="<?= url('delivery-status') ?>" class="delivery-form">
                    <div class="delivery-field">
                        <label for="delivery-order-number">Order Number</label>
                        <input id="delivery-order-number" name="order_number" value="<?= e($orderNumber) ?>" placeholder="e.g. ORD-00001" required>
                    </div>
                    <div class="delivery-field">
                        <label for="delivery-phone">Phone Number</label>
                        <input id="delivery-phone" name="phone" value="<?= e($phone) ?>" placeholder="0712345678" required>
                    </div>
                    <button class="delivery-primary" type="submit"><span class="material-symbols-outlined">search</span> Check Status</button>
                </form>
            </section>
        <?php else: ?>
            <section class="delivery-card delivery-account-note">
                <span class="material-symbols-outlined">verified_user</span>
                <div><strong>Signed-in customer</strong><p>Your delivery information is limited to orders belonging to your customer account.</p></div>
            </section>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="delivery-alert"><span class="material-symbols-outlined">error</span><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($delivery): ?>
            <?php
            $steps = [
                ['key' => 'pending', 'label' => 'Preparing', 'icon' => 'inventory_2'],
                ['key' => 'in_transit', 'label' => 'Out for Delivery', 'icon' => 'local_shipping'],
                ['key' => 'delivered', 'label' => 'Delivered', 'icon' => 'check_circle'],
            ];
            $stepIndex = match ($status) {
                'in_transit' => 1,
                'delivered' => 2,
                'failed', 'returned' => 1,
                default => 0,
            };
            ?>
            <section class="delivery-card">
                <div class="delivery-result-header">
                    <div><span class="delivery-eyebrow">DELIVERY</span><h2><?= e($delivery['delivery_number']) ?></h2><p>Order <strong><?= e($delivery['order_number']) ?></strong></p></div>
                    <span class="delivery-status delivery-status--<?= e($status) ?>"><?= e($statusLabel) ?></span>
                </div>

                <div class="delivery-timeline">
                    <?php foreach ($steps as $index => $step): ?>
                        <div class="delivery-step <?= $index <= $stepIndex ? 'is-done' : '' ?>">
                            <div class="delivery-step-icon"><span class="material-symbols-outlined"><?= e($step['icon']) ?></span></div>
                            <span><?= e($step['label']) ?></span>
                        </div>
                        <?php if ($index < count($steps) - 1): ?><div class="delivery-line <?= $index < $stepIndex ? 'is-done' : '' ?>"></div><?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="delivery-info-grid">
                    <div><span>Recipient</span><strong><?= e($delivery['recipient_name'] ?: '—') ?></strong><small><?= e($delivery['recipient_phone'] ?: '—') ?></small></div>
                    <div><span>Delivery Address</span><strong><?= nl2br(e($delivery['delivery_address'] ?: '—')) ?></strong></div>
                    <div><span>Scheduled Date</span><strong><?= $delivery['scheduled_date'] ? date('M d, Y', strtotime($delivery['scheduled_date'])) : 'To be confirmed' ?></strong></div>
                    <div><span>Delivered At</span><strong><?= $delivery['delivered_at'] ? date('M d, Y h:i A', strtotime($delivery['delivered_at'])) : 'Not delivered yet' ?></strong></div>
                </div>

                <?php if ($status === 'failed' || $status === 'returned'): ?>
                    <div class="delivery-alert delivery-alert--soft"><span class="material-symbols-outlined">info</span><?= e($statusLabel) ?>. Please contact the store for the next delivery arrangement.</div>
                <?php endif; ?>
            </section>
        <?php elseif ($orderNumber && !$error): ?>
            <section class="delivery-card delivery-empty"><span class="material-symbols-outlined">local_shipping</span><h3>Delivery not created yet</h3><p>The order is valid, but a delivery record has not been assigned yet. Please check again after the store processes the order.</p></section>
        <?php endif; ?>
    </div>
</div>
