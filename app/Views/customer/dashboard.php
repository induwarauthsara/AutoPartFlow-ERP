<?php
$customer = $customer ?? [];
$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
?>
<section class="customer-dashboard">
    <div class="customer-dashboard__container">
        <?php if (!empty($flash)): ?>
            <div class="customer-flash customer-flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <div class="customer-welcome">
            <div>
                <span class="customer-eyebrow">CUSTOMER PORTAL</span>
                <h1>Welcome, <?= e($customer['contact_person'] ?: $customer['name'] ?: ($_SESSION['full_name'] ?? 'Customer')) ?></h1>
                <p>View your orders, find compatible spare parts and continue shopping from one place.</p>
            </div>
            <div class="customer-profile-chip">
                <span class="material-symbols-outlined">storefront</span>
                <div><strong><?= e($customer['shop_name'] ?: $customer['name'] ?: 'Shop Customer') ?></strong><span><?= e($customer['customer_code'] ?? '') ?></span></div>
            </div>
        </div>

        <div class="customer-stats">
            <article><span class="material-symbols-outlined">receipt_long</span><div><small>Total Orders</small><strong><?= (int) ($stats['total_orders'] ?? 0) ?></strong></div></article>
            <article><span class="material-symbols-outlined">pending_actions</span><div><small>Active Orders</small><strong><?= (int) ($stats['active_orders'] ?? 0) ?></strong></div></article>
            <article><span class="material-symbols-outlined">task_alt</span><div><small>Completed</small><strong><?= (int) ($stats['completed_orders'] ?? 0) ?></strong></div></article>
            <article><span class="material-symbols-outlined">payments</span><div><small>Order Value</small><strong>Rs. <?= number_format((float) ($stats['total_value'] ?? 0), 2) ?></strong></div></article>
        </div>

        <div class="customer-dashboard__grid">
            <section class="customer-panel">
                <div class="customer-panel__header"><div><span class="customer-eyebrow">RECENT ACTIVITY</span><h2>Recent Orders</h2></div><a href="<?= url('my-orders') ?>">View all</a></div>
                <?php if (!$recentOrders): ?>
                    <div class="customer-empty"><span class="material-symbols-outlined">shopping_bag</span><h3>No orders yet</h3><p>Your latest orders will appear here after checkout.</p><a class="customer-primary" href="<?= url('catalog') ?>">Browse Catalog</a></div>
                <?php else: ?>
                    <div class="customer-order-list">
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="customer-order-row">
                            <div><strong><?= e($order['order_number']) ?></strong><span><?= e(date('d M Y, h:i A', strtotime($order['order_date']))) ?></span></div>
                            <span class="customer-status customer-status--<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span>
                            <strong>Rs. <?= number_format((float) $order['total_amount'], 2) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="customer-panel customer-quick-links">
                <span class="customer-eyebrow">QUICK ACCESS</span><h2>What would you like to do?</h2>
                <a href="<?= url('finder') ?>"><span class="material-symbols-outlined">directions_car</span><div><strong>Spare Parts Finder</strong><small>Find parts for your vehicle</small></div><span class="material-symbols-outlined">chevron_right</span></a>
                <a href="<?= url('catalog') ?>"><span class="material-symbols-outlined">category</span><div><strong>Product Catalog</strong><small>Browse available spare parts</small></div><span class="material-symbols-outlined">chevron_right</span></a>
                <a href="<?= url('my-orders') ?>"><span class="material-symbols-outlined">package_2</span><div><strong>My Orders</strong><small>Manage your customer orders</small></div><span class="material-symbols-outlined">chevron_right</span></a>
                <a href="<?= url('track-order') ?>"><span class="material-symbols-outlined">local_shipping</span><div><strong>Track Order</strong><small>Check order and delivery status</small></div><span class="material-symbols-outlined">chevron_right</span></a>
                <a href="<?= url('logout') ?>"><span class="material-symbols-outlined">logout</span><div><strong>Sign Out</strong><small>End this customer session</small></div><span class="material-symbols-outlined">chevron_right</span></a>
            </aside>
        </div>
    </div>
</section>
