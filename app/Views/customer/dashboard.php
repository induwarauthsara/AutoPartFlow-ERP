<div class="customer-dashboard-page" style="padding: 32px 0 64px;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">

        <!-- Dashboard Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 28px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span class="material-symbols-outlined" style="font-size: 28px; color: var(--secondary, #3b6090);">storefront</span>
                    <h1 style="font-size: 26px; font-weight: 700; margin: 0; color: var(--on-surface, #1e293b);">
                        <?= e($customer['shop_name'] ?? 'My Shop Portal') ?>
                    </h1>
                    <span style="font-size: 11px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 999px; text-transform: uppercase;">
                        Shop Customer
                    </span>
                </div>
                <p style="margin: 0; color: #64748b; font-size: 14px;">
                    Account Code: <strong style="color: #334155;"><?= e($customer['customer_code'] ?? 'CUS-00001') ?></strong>
                    &bull; Contact: <strong><?= e($customer['name'] ?? '') ?></strong> (<?= e($customer['email'] ?? '') ?>)
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="<?= url('catalog') ?>" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; background: #002045; color: #fff;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">search</span>
                    Browse Catalog
                </a>
                <a href="<?= url('checkout') ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">shopping_cart</span>
                    Cart / Checkout
                </a>
                <a href="<?= url('logout') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;" title="Sign out">
                    <span class="material-symbols-outlined" style="font-size: 20px;">logout</span>
                    Sign Out
                </a>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #64748b;">Credit Limit</span>
                    <span class="material-symbols-outlined" style="color: #3b82f6;">credit_card</span>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a;">
                    Rs. <?= number_format((float) ($customer['credit_limit'] ?? 0), 2) ?>
                </div>
                <div style="font-size: 12px; color: #10b981; margin-top: 4px; font-weight: 600;">
                    Payment Terms: <?= (int) ($customer['payment_terms_days'] ?? 30) ?> Days
                </div>
            </div>

            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #64748b;">Outstanding Balance</span>
                    <span class="material-symbols-outlined" style="color: #f59e0b;">account_balance_wallet</span>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a;">
                    Rs. <?= number_format((float) ($customer['credit_balance'] ?? 0), 2) ?>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                    Available: Rs. <?= number_format(max(0, (float) ($customer['credit_limit'] ?? 0) - (float) ($customer['credit_balance'] ?? 0)), 2) ?>
                </div>
            </div>

            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #64748b;">Total Orders</span>
                    <span class="material-symbols-outlined" style="color: #10b981;">receipt_long</span>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a;">
                    <?= count($orders) ?>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                    B2B Wholesale Purchases
                </div>
            </div>

            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: #64748b;">Account Status</span>
                    <span class="material-symbols-outlined" style="color: #10b981;">verified_user</span>
                </div>
                <div style="font-size: 18px; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #16a34a;"></span>
                    Active Verified Shop
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                    Direct Distribution Access
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h2 style="font-size: 18px; font-weight: 700; margin: 0; color: #0f172a;">Recent Orders & Requisitions</h2>
                <a href="<?= url('catalog') ?>" style="font-size: 13px; font-weight: 600; color: var(--secondary, #3b6090); text-decoration: none;">
                    + New Part Order
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 48px 16px; color: #64748b;">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #cbd5e1; margin-bottom: 12px;">shopping_bag</span>
                    <h3 style="font-size: 16px; font-weight: 600; color: #334155; margin-bottom: 6px;">No orders placed yet</h3>
                    <p style="font-size: 13px; margin-bottom: 18px;">Browse our comprehensive spare parts catalog and place your first B2B stock replenishment order.</p>
                    <a href="<?= url('catalog') ?>" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; background: #002045; color: #fff;">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 1px solid #e2e8f0; text-align: left; color: #64748b;">
                                <th style="padding: 10px 12px;">Order #</th>
                                <th style="padding: 10px 12px;">Date</th>
                                <th style="padding: 10px 12px;">Items</th>
                                <th style="padding: 10px 12px;">Total</th>
                                <th style="padding: 10px 12px;">Order Status</th>
                                <th style="padding: 10px 12px;">Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px; font-weight: 700; color: #0f172a;"><?= e($order['order_number'] ?? ('ORD-' . $order['id'])) ?></td>
                                    <td style="padding: 12px; color: #64748b;"><?= date('M j, Y', strtotime((string) $order['created_at'])) ?></td>
                                    <td style="padding: 12px;"><?= (int) ($order['item_count'] ?? 1) ?> items</td>
                                    <td style="padding: 12px; font-weight: 700;">Rs. <?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></td>
                                    <td style="padding: 12px;">
                                        <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; background: #e0e7ff; color: #3730a3;">
                                            <?= e($order['order_status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; background: #fef3c7; color: #92400e;">
                                            <?= e($order['payment_status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
