<?php
$customer = $customer ?? [];
$stats = $stats ?? [];
$recentOrders = $recentOrders ?? ($orders ?? []);
$contactName = $customer['contact_person'] ?: ($customer['name'] ?: ($_SESSION['full_name'] ?? 'Customer'));
$shopName = $customer['shop_name'] ?: ($customer['name'] ?: 'Shop Customer');
?>
<section class="customer-dashboard">
    <div class="customer-dashboard__container">
        <?php if (!empty($flash)): ?>
            <div class="customer-flash customer-flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="customer-welcome">
            <div>
                <span class="customer-eyebrow">CUSTOMER PORTAL</span>
                <h1>Welcome, <?= e($contactName) ?></h1>
                <p>Manage your orders, check fulfillment and delivery status, and order new parts directly.</p>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                <div class="customer-profile-chip">
                    <span class="material-symbols-outlined">storefront</span>
                    <div>
                        <strong><?= e($shopName) ?></strong>
                        <span>Code: <?= e($customer['customer_code'] ?? 'CUS-00001') ?></span>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="<?= url('catalog') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 8px; background: #002045; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">search</span>
                        Browse Catalog
                    </a>
                    <a href="<?= url('profile') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 8px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; text-decoration: none; font-size: 13px; font-weight: 600;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">manage_accounts</span>
                        Edit Profile
                    </a>
                    <a href="<?= url('cart') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 8px; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 600;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">shopping_cart</span>
                        Cart
                    </a>
                    <a href="<?= url('logout') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 8px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; text-decoration: none; font-size: 13px; font-weight: 600;" title="Sign out">
                        <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                        Sign Out
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="customer-stats">
            <article>
                <span class="material-symbols-outlined">receipt_long</span>
                <div>
                    <small>Total Orders</small>
                    <strong><?= (int) ($stats['total_orders'] ?? count($recentOrders)) ?></strong>
                </div>
            </article>
            <article>
                <span class="material-symbols-outlined">pending_actions</span>
                <div>
                    <small>Active Orders</small>
                    <strong><?= (int) ($stats['active_orders'] ?? 0) ?></strong>
                </div>
            </article>
            <article>
                <span class="material-symbols-outlined">task_alt</span>
                <div>
                    <small>Delivered / Completed</small>
                    <strong><?= (int) ($stats['completed_orders'] ?? 0) ?></strong>
                </div>
            </article>
            <article>
                <span class="material-symbols-outlined">payments</span>
                <div>
                    <small>Total Purchases</small>
                    <strong>Rs. <?= number_format((float) ($stats['total_value'] ?? 0), 2) ?></strong>
                </div>
            </article>
        </div>

        <!-- Two Column Content Grid -->
        <div class="customer-dashboard__grid">
            <!-- Left Column: Recent Orders -->
            <section class="customer-panel">
                <div class="customer-panel__header">
                    <div>
                        <span class="customer-eyebrow">ORDER REQUISITIONS</span>
                        <h2>Recent Orders</h2>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <a href="<?= url('catalog') ?>" style="color: #0284c7; font-weight: 600; text-decoration: none; font-size: 13px;">+ Order New Parts</a>
                        <span style="color: #cbd5e1;">|</span>
                        <a href="<?= url('orders') ?>">View All Orders &rarr;</a>
                    </div>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div class="customer-empty">
                        <span class="material-symbols-outlined">shopping_bag</span>
                        <h3>No orders yet</h3>
                        <p>Browse our catalog of genuine spare parts and place your first stock order.</p>
                        <a class="customer-primary" href="<?= url('catalog') ?>">Start Browsing Catalog</a>
                    </div>
                <?php else: ?>
                    <div class="customer-order-list">
                        <?php foreach ($recentOrders as $order): ?>
                            <?php
                            $orderNum = (string) ($order['order_number'] ?? ('ORD-' . $order['id']));
                            $orderStatus = (string) ($order['status'] ?? ($order['order_status'] ?? 'pending'));
                            $orderDate = !empty($order['order_date']) ? $order['order_date'] : ($order['created_at'] ?? 'now');
                            $totalAmt = (float) ($order['total_amount'] ?? 0);
                            ?>
                            <div class="customer-order-row">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <strong><?= e($orderNum) ?></strong>
                                        <span class="customer-status customer-status--<?= e($orderStatus) ?>">
                                            <?= e(ucfirst($orderStatus)) ?>
                                        </span>
                                    </div>
                                    <span style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                        Ordered on <?= e(date('d M Y, h:i A', strtotime((string) $orderDate))) ?>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <strong style="font-size: 15px; color: #0b192e;">Rs. <?= number_format($totalAmt, 2) ?></strong>
                                    <div>
                                        <a href="<?= url('orders?order_number=' . rawurlencode($orderNum)) ?>" style="font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: none;">
                                            Track Order &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Right Column: Quick Links & Account Details -->
            <aside style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Shop Details Card -->
                <div class="customer-panel" style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e5eaf0;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0b192e;">Shop Account Details</h3>
                        <a href="<?= url('profile') ?>" style="font-size: 12px; color: #2563eb; text-decoration: none; font-weight: 600;">Edit &rarr;</a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #334155;">
                        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block;">Shop Name</strong><?= e($shopName) ?></div>
                        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block;">Contact Person</strong><?= e($contactName) ?></div>
                        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block;">Phone Number</strong><?= e($customer['phone'] ?? 'Not set') ?></div>
                        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block;">Email Address</strong><?= e($customer['email'] ?? 'Not set') ?></div>
                        <?php if (!empty($customer['address'])): ?>
                            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase; display: block;">Delivery Address</strong><?= e($customer['address']) ?><?= !empty($customer['city']) ? ', ' . e($customer['city']) : '' ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="customer-panel customer-quick-links" style="padding: 20px;">
                    <span class="customer-eyebrow">QUICK ACCESS</span>
                    <h2 style="font-size: 16px; margin: 4px 0 12px;">What would you like to do?</h2>
                    <a href="<?= url('finder') ?>">
                        <span class="material-symbols-outlined">directions_car</span>
                        <div><strong>Spare Parts Finder</strong><small>Find parts matching your vehicle make & model</small></div>
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                    <a href="<?= url('catalog') ?>">
                        <span class="material-symbols-outlined">category</span>
                        <div><strong>Product Catalog</strong><small>Browse inventory and order new parts</small></div>
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                    <a href="<?= url('orders') ?>">
                        <span class="material-symbols-outlined">package_2</span>
                        <div><strong>My Orders</strong><small>Check fulfillment and update delivery</small></div>
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                    <a href="<?= url('track-order') ?>">
                        <span class="material-symbols-outlined">local_shipping</span>
                        <div><strong>Track Order</strong><small>Check delivery status and courier timeline</small></div>
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                    <a href="<?= url('profile') ?>">
                        <span class="material-symbols-outlined">manage_accounts</span>
                        <div><strong>Edit Profile</strong><small>Update shop details and password</small></div>
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                    <a href="<?= url('logout') ?>">
                        <span class="material-symbols-outlined">logout</span>
                        <div><strong>Sign Out</strong><small>Safely end your customer session</small></div>
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>
