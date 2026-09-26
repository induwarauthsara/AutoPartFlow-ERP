<?php
$customer = $customer ?? null;
$orders = $orders ?? [];
$order = $order ?? null;
$orderNumber = $orderNumber ?? '';
$phone = $phone ?? '';
$message = $message ?? null;
$flash = $flash ?? null;
?>

<div class="orders-page">
    <div class="orders-container">
        <div class="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span class="material-symbols-outlined">chevron_right</span>
            <strong>My Orders</strong>
        </div>

        <section class="orders-heading">
            <div>
                <h1><?= $customer ? 'My Orders' : 'Manage an Order' ?></h1>
                <p><?= $customer ? 'View your customer orders and update or cancel eligible pending orders.' : 'Guests can securely manage an order using its order number and phone number.' ?></p>
            </div>
            <a class="orders-back-link" href="<?= url('catalog') ?>">
                <span class="material-symbols-outlined">arrow_back</span>
                Continue Shopping
            </a>
        </section>

        <?php if ($flash): ?>
            <div class="orders-alert orders-alert--<?= e($flash['type']) ?>">
                <span class="material-symbols-outlined"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="orders-alert orders-alert--error">
                <span class="material-symbols-outlined">info</span>
                <span><?= e($message) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!$customer): ?>
            <section class="orders-card orders-lookup-card">
                <div class="orders-card__header">
                    <div>
                        <h2>Find Your Order</h2>
                        <p>Use the same phone number entered during checkout.</p>
                    </div>
                    <span class="material-symbols-outlined">verified_user</span>
                </div>
                <form method="get" action="<?= url('orders') ?>" class="orders-lookup-form">
                    <div class="form-group">
                        <label class="form-label" for="order_number">Order Number</label>
                        <input class="form-input" id="order_number" name="order_number" value="<?= e($orderNumber) ?>" placeholder="e.g. ORD-00001" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input class="form-input" id="phone" name="phone" value="<?= e($phone) ?>" placeholder="0712345678" required>
                    </div>
                    <button class="btn-primary orders-lookup-btn" type="submit">
                        <span class="material-symbols-outlined">search</span>
                        Find Order
                    </button>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($customer): ?>
            <section class="orders-card">
                <div class="orders-card__header">
                    <div>
                        <h2>Order History</h2>
                        <p>Orders associated with your signed-in shop customer account.</p>
                    </div>
                    <a class="orders-secondary-btn" href="<?= url('track-order') ?>">Track an Order</a>
                </div>

                <?php if (!$orders): ?>
                    <div class="orders-empty">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <h3>No orders yet</h3>
                        <p>Your confirmed online orders will appear here.</p>
                        <a class="btn-primary" href="<?= url('catalog') ?>">Browse Catalog</a>
                    </div>
                <?php else: ?>
                    <div class="orders-table-wrap">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $row): ?>
                                    <?php $rowStatus = strtolower((string) $row['status']); ?>
                                    <tr>
                                        <td><strong class="order-code"><?= e($row['order_number']) ?></strong></td>
                                        <td><?= date('M d, Y', strtotime($row['order_date'])) ?></td>
                                        <td><span class="order-status order-status--<?= e($rowStatus) ?>"><?= e(ucwords(str_replace('_', ' ', $rowStatus))) ?></span></td>
                                        <td><strong>Rs. <?= number_format((float) $row['total_amount'], 0) ?></strong></td>
                                        <td><a class="orders-view-btn" href="<?= url('orders') ?>?order_number=<?= rawurlencode($row['order_number']) ?>">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($order): ?>
            <?php
                $status = strtolower((string) $order['status']);
                $canEdit = $status === 'pending';
                $canCancel = in_array($status, ['pending', 'confirmed'], true);
            ?>
            <section class="orders-card orders-detail-card">
                <div class="orders-card__header">
                    <div>
                        <span class="orders-kicker">Customer Order</span>
                        <h2 class="order-code-large"><?= e($order['order_number']) ?></h2>
                        <p>Placed on <?= date('M d, Y h:i A', strtotime($order['order_date'])) ?></p>
                    </div>
                    <span class="order-status order-status--<?= e($status) ?>"><?= e(ucwords(str_replace('_', ' ', $status))) ?></span>
                </div>

                <div class="orders-detail-grid">
                    <div class="orders-detail-panel">
                        <h3>Customer Details</h3>
                        <p><strong><?= e($order['customer_name']) ?></strong></p>
                        <p><?= e($order['customer_phone'] ?? '—') ?></p>
                    </div>
                    <div class="orders-detail-panel">
                        <h3>Delivery Address</h3>
                        <p><?= nl2br(e($order['delivery_address'] ?? '—')) ?></p>
                    </div>
                </div>

                <div class="orders-items-wrap">
                    <h3>Order Items</h3>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Code</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= e($item['product_name']) ?></td>
                                    <td><code><?= e($item['product_code']) ?></code></td>
                                    <td><?= (int) $item['quantity'] ?></td>
                                    <td>Rs. <?= number_format((float) $item['unit_price'], 0) ?></td>
                                    <td><strong>Rs. <?= number_format((float) $item['line_total'], 0) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="orders-total-box">
                    <span>Total Amount</span>
                    <strong>Rs. <?= number_format((float) $order['total_amount'], 0) ?></strong>
                </div>

                <?php if ($canEdit): ?>
                    <div class="orders-edit-box">
                        <div>
                            <h3>Update Delivery Address</h3>
                            <p>Address changes are available only while the order is still pending.</p>
                        </div>
                        <form method="post" action="<?= url('orders/update') ?>" class="orders-update-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_number" value="<?= e($order['order_number']) ?>">
                            <?php if (!$customer): ?>
                                <input type="hidden" name="phone" value="<?= e($phone) ?>">
                            <?php endif; ?>
                            <textarea class="form-textarea" name="delivery_address" rows="3" required><?= e($order['delivery_address'] ?? '') ?></textarea>
                            <button class="btn-primary" type="submit">
                                <span class="material-symbols-outlined">save</span>
                                Update Address
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="orders-actions">
                    <a class="orders-secondary-btn" href="<?= url('track-order') ?>?order_number=<?= rawurlencode($order['order_number']) ?>">
                        <span class="material-symbols-outlined">local_shipping</span>
                        Track Order
                    </a>

                    <?php if ($canCancel): ?>
                        <form method="post" action="<?= url('orders/cancel') ?>" onsubmit="return confirm('Cancel this order? The order record will remain in the system as cancelled.');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_number" value="<?= e($order['order_number']) ?>">
                            <?php if (!$customer): ?>
                                <input type="hidden" name="phone" value="<?= e($phone) ?>">
                            <?php endif; ?>
                            <button class="orders-cancel-btn" type="submit">
                                <span class="material-symbols-outlined">cancel</span>
                                Cancel Order
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
