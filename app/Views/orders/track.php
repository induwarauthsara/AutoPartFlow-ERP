<?php
$order = $order ?? null;
$searched = $searched ?? false;
$orderNumber = $orderNumber ?? '';
?>

<div class="catalog-page">
    <div class="catalog-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span class="material-symbols-outlined">chevron_right</span>
            <strong>Track Order</strong>
        </div>

        <!-- Header -->
        <section class="catalog-heading" style="margin-top:0;">
            <div>
                <h1>Track Your Order</h1>
                <p>Enter your order number to check real-time processing status and delivery updates.</p>
            </div>
        </section>

        <!-- Search Order Box -->
        <div class="checkout-card" style="margin-bottom: 24px;">
            <form action="<?= url('track-order') ?>" method="get" style="display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end;">
                <div class="form-group" style="flex:1; min-width:230px;">
                    <label class="form-label" for="order_number">Order Number</label>
                    <input class="form-input" id="order_number" name="order_number" value="<?= e($orderNumber) ?>" placeholder="e.g. ORD-00001" required type="text" style="font-family:var(--mono);">
                </div>
                <?php if (!($customer ?? null)): ?>
                    <div class="form-group" style="flex:1; min-width:230px;">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input class="form-input" id="phone" name="phone" value="<?= e($phone ?? '') ?>" placeholder="0712345678" required type="tel">
                    </div>
                <?php endif; ?>
                <button class="btn-primary" type="submit" style="padding:11px 24px; font-size:14px; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:20px;">search</span>
                    <span>Track Order</span>
                </button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="orders-alert orders-alert--error" style="margin-bottom:24px;">
                <span class="material-symbols-outlined">verified_user</span>
                <span><?= e($message) ?></span>
            </div>
        <?php endif; ?>

        <!-- Tracking Results -->
        <?php if ($searched): ?>
            <?php if ($order): ?>
                <?php
                    $status = strtolower((string) $order['status']);
                    $delivery = $order['delivery'] ?? null;
                    $deliveryStatus = strtolower((string) ($delivery['delivery_status'] ?? 'pending'));
                    $stages = ['pending' => 'Order Placed', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'ready' => 'Ready for Delivery', 'delivered' => 'Delivered'];
                    $currentStageIndex = array_search($status, array_keys($stages));
                    if ($deliveryStatus === 'delivered') $currentStageIndex = count($stages) - 1;
                    if ($deliveryStatus === 'in_transit') $currentStageIndex = max($currentStageIndex, 3);
                    if ($currentStageIndex === false) $currentStageIndex = 0;
                ?>
                <div class="checkout-card" style="display:flex; flex-direction:column; gap:24px;">
                    <!-- Order Status Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-bottom:1px solid var(--outline-variant); padding-bottom:16px;">
                        <div>
                            <span style="font-size:12px; color:var(--on-surface-variant); text-transform:uppercase; letter-spacing:0.05em;">Order Details</span>
                            <h2 style="margin:2px 0 0; color:var(--primary); font-family:var(--mono); font-size:22px;"><?= e($order['order_number']) ?></h2>
                        </div>
                        <div style="text-align:right;">
                            <span class="status-badge status-badge--pending" style="font-size:13px; padding:4px 14px; text-transform:capitalize;">
                                Status: <?= e($order['status']) ?>
                            </span>
                            <div style="font-size:12px; color:var(--on-surface-variant); margin-top:4px;">
                                Ordered on <?= date('M d, Y h:i A', strtotime($order['order_date'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div style="display:grid; grid-template-columns: repeat(<?= count($stages) ?>, 1fr); gap:8px; margin: 8px 0;">
                        <?php $i = 0; foreach ($stages as $key => $label): ?>
                            <?php $isDone = $i <= $currentStageIndex; ?>
                            <div style="display:flex; flex-direction:column; align-items:center; text-align:center; gap:6px;">
                                <div style="width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:<?= $isDone ? 'var(--primary)' : 'var(--surface-low)' ?>; color:<?= $isDone ? '#fff' : 'var(--outline)' ?>; font-weight:700; font-size:14px; border:2px solid <?= $isDone ? 'var(--primary)' : 'var(--outline-variant)' ?>;">
                                    <?= $isDone ? '<span class="material-symbols-outlined" style="font-size:20px;">check</span>' : ($i + 1) ?>
                                </div>
                                <span style="font-size:12px; font-weight:<?= $isDone ? '700' : '400' ?>; color:<?= $isDone ? 'var(--primary)' : 'var(--on-surface-variant)' ?>;"><?= e($label) ?></span>
                            </div>
                        <?php $i++; endforeach; ?>
                    </div>

                    <?php if ($delivery): ?>
                        <div class="orders-detail-grid" style="margin-top:4px;">
                            <div class="orders-detail-panel">
                                <h3>Delivery Status</h3>
                                <p><strong><?= e(ucwords(str_replace('_', ' ', $delivery['delivery_status']))) ?></strong></p>
                                <p>Delivery No: <code><?= e($delivery['delivery_number']) ?></code></p>
                            </div>
                            <div class="orders-detail-panel">
                                <h3>Scheduled Delivery</h3>
                                <p><?= $delivery['scheduled_date'] ? e(date('M d, Y', strtotime($delivery['scheduled_date']))) : 'To be confirmed by the store.' ?></p>
                                <?php if ($delivery['delivered_at']): ?><p>Delivered <?= e(date('M d, Y h:i A', strtotime($delivery['delivered_at']))) ?></p><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Delivery & Customer Information -->
                    <div class="form-grid" style="background:var(--surface-low); padding:16px; border-radius:8px;">
                        <div>
                            <strong style="display:block; font-size:12px; color:var(--on-surface-variant); text-transform:uppercase;">Customer & Contact</strong>
                            <div style="font-size:14px; margin-top:4px; font-weight:600;"><?= e($order['customer_name']) ?></div>
                            <div style="font-size:13px; color:var(--on-surface-variant);"><?= e($order['customer_phone'] ?? '—') ?></div>
                        </div>
                        <div>
                            <strong style="display:block; font-size:12px; color:var(--on-surface-variant); text-transform:uppercase;">Delivery Address</strong>
                            <div style="font-size:13px; margin-top:4px; color:var(--on-surface); line-height:1.4;">
                                <?= nl2br(e($order['delivery_address'] ?? 'Standard Store Dispatch')) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div>
                        <h3 style="font-size:16px; margin:0 0 12px; color:var(--primary);">Order Items</h3>
                        <table class="compat-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Code</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th style="text-align:right;">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td><strong><?= e($item['product_name']) ?></strong></td>
                                        <td><code style="font-family:var(--mono); color:var(--outline);"><?= e($item['product_code']) ?></code></td>
                                        <td><?= (int) $item['quantity'] ?></td>
                                        <td>Rs. <?= number_format((float) $item['unit_price'], 0) ?></td>
                                        <td style="text-align:right; font-weight:700;">Rs. <?= number_format((float) $item['line_total'], 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Summary Totals -->
                    <div style="margin-left:auto; width:100%; max-width:320px; border-top:1px solid var(--outline-variant); padding-top:12px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px; color:var(--on-surface-variant);">
                            <span>Subtotal</span>
                            <span>Rs. <?= number_format((float) $order['subtotal'], 0) ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px; color:var(--on-surface-variant);">
                            <span>Delivery Fee</span>
                            <span>Rs. 350</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:700; color:var(--primary); border-top:1px solid rgba(26,43,80,0.2); padding-top:8px;">
                            <span>Total Amount</span>
                            <span>Rs. <?= number_format((float) $order['total_amount'], 0) ?></span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-products" style="background:#fff; border:1px solid var(--outline-variant); border-radius:12px; padding:48px 24px; text-align:center;">
                    <span class="material-symbols-outlined" style="font-size:48px; color:var(--error); margin-bottom:8px;">search_off</span>
                    <h3 style="margin:0 0 8px; color:var(--primary);">Order Not Found</h3>
                    <p style="margin:0 0 16px; color:var(--on-surface-variant); font-size:14px;">No order matching <strong><?= e($orderNumber) ?></strong> was found in our system.</p>
                    <p style="margin:0; font-size:13px; color:var(--on-surface-variant);">Please double-check your order number from your confirmation message and try again.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="checkout-card" style="text-align:center; padding:48px 24px;">
                <div class="success-icon-badge" style="margin:0 auto 16px;">
                    <span class="material-symbols-outlined text-secondary" style="font-size:32px;">local_shipping</span>
                </div>
                <h3 style="margin:0 0 8px; color:var(--primary);">Looking for your auto parts order?</h3>
                <p style="margin:0 auto; max-width:480px; color:var(--on-surface-variant); font-size:14px;">
                    Enter your Order ID (such as <strong style="font-family:var(--mono);">ORD-00001</strong>) in the field above to view real-time shipping status and delivery tracking.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
