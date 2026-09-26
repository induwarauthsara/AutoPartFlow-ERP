<?php
$roleSlug = $user['role_slug'] ?? 'shop_customer';
$roleName = $user['role_name'] ?? 'User';
$userInitials = strtoupper(substr(trim($user['full_name'] ?? 'U'), 0, 2)) ?: 'U';

$roleBadgeBg = match ($roleSlug) {
    'owner'         => '#f3e8ff',
    'sales_rep'     => '#e0f2fe',
    'store_manager' => '#ccfbf1',
    'shop_customer' => '#fef3c7',
    default         => '#f1f5f9',
};
$roleBadgeColor = match ($roleSlug) {
    'owner'         => '#7e22ce',
    'sales_rep'     => '#0369a1',
    'store_manager' => '#0f766e',
    'shop_customer' => '#b45309',
    default         => '#334155',
};
?>
<div class="profile-container" style="max-width: 1140px; margin: 0 auto; padding: 28px 20px 60px;">
    <!-- Breadcrumb & Back -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <a href="<?= auth_dashboard_url() ?>" style="display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: #3b82f6; text-decoration: none; padding: 6px 12px; border-radius: 8px; background: rgba(59, 130, 246, 0.08); transition: background 0.15s;">
            <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; fill: currentColor;"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Back to <?= auth_dashboard_label() ?>
        </a>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 13px; color: #64748b;">Signed in as:</span>
            <span style="font-size: 13px; font-weight: 700; color: #0f172a;"><?= e($user['username']) ?></span>
            <span style="display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: <?= $roleBadgeBg ?>; color: <?= $roleBadgeColor ?>;">
                <?= e($roleName) ?>
            </span>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if (!empty($flash)): ?>
        <div style="margin-bottom: 24px; padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 12px; <?= ($flash['type'] ?? '') === 'success' ? 'background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>">
            <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: currentColor; flex-shrink: 0;">
                <?php if (($flash['type'] ?? '') === 'success'): ?>
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                <?php else: ?>
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                <?php endif; ?>
            </svg>
            <span><?= e($flash['message'] ?? '') ?></span>
        </div>
    <?php endif; ?>

    <!-- User Header Hero Card -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: #ffffff; display: grid; place-items: center; font-size: 26px; font-weight: 800; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25); flex-shrink: 0;">
                <?= $userInitials ?>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="font-size: 24px; font-weight: 800; margin: 0; color: #0f172a; line-height: 1.2;">
                        <?= e($user['full_name']) ?>
                    </h1>
                    <span style="padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: <?= $roleBadgeBg ?>; color: <?= $roleBadgeColor ?>;">
                        <?= e($roleName) ?>
                    </span>
                </div>
                <p style="margin: 6px 0 0; color: #64748b; font-size: 14px;">
                    <span>@<?= e($user['username']) ?></span>
                    <span style="margin: 0 6px;">&bull;</span>
                    <span><?= e($user['email']) ?></span>
                    <?php if (!empty($user['phone'])): ?>
                        <span style="margin: 0 6px;">&bull;</span>
                        <span><?= e($user['phone']) ?></span>
                    <?php endif; ?>
                </p>
                <?php if ($employee): ?>
                    <p style="margin: 4px 0 0; font-size: 13px; color: #475569;">
                        Code: <strong><?= e($employee['employee_code']) ?></strong>
                        &bull; Designation: <strong><?= e($employee['designation'] ?? 'Staff') ?></strong>
                        &bull; Department: <strong style="text-transform: capitalize;"><?= e($employee['department'] ?? 'General') ?></strong>
                    </p>
                <?php elseif ($customer): ?>
                    <p style="margin: 4px 0 0; font-size: 13px; color: #475569;">
                        Customer Code: <strong><?= e($customer['customer_code']) ?></strong>
                        &bull; Shop: <strong><?= e($customer['shop_name'] ?? 'B2B Client') ?></strong>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="<?= auth_dashboard_url() ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 9px; font-size: 13px; font-weight: 600; text-decoration: none; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; transition: background 0.15s;">
                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                Workspace
            </a>
            <a href="<?= url('logout') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 9px; font-size: 13px; font-weight: 600; text-decoration: none; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; transition: background 0.15s;" title="Sign out of system">
                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Sign Out
            </a>
        </div>
    </div>

    <!-- 2-Column Content Grid -->
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
        <!-- Left: Edit Forms -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Card 1: Edit Profile Details -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); color: #2563eb; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: currentColor;"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div>
                        <h2 style="font-size: 17px; font-weight: 700; margin: 0; color: #0f172a;">Personal & Account Information</h2>
                        <p style="font-size: 13px; color: #64748b; margin: 2px 0 0;">Update your name, contact phone, username, and email address.</p>
                    </div>
                </div>

                <form action="<?= url('profile/update') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Full Name <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Username <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="username" value="<?= e($user['username']) ?>" required pattern="[A-Za-z0-9._-]{3,60}" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Email Address <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="email" name="email" value="<?= e($user['email']) ?>" required style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Phone Number
                            </label>
                            <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+94 77 123 4567" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                        </div>
                    </div>

                    <?php if ($roleSlug === 'shop_customer'): ?>
                        <div style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed #e2e8f0;">
                            <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 12px;">Shop Business Profile</h3>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Shop / Company Name</label>
                                    <input type="text" name="shop_name" value="<?= e($customer['shop_name'] ?? '') ?>" placeholder="e.g. City Auto Works" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Tax / BR Number</label>
                                    <input type="text" name="tax_number" value="<?= e($customer['tax_number'] ?? '') ?>" placeholder="Optional registration / VAT #" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">City</label>
                                    <input type="text" name="city" value="<?= e($customer['city'] ?? '') ?>" placeholder="e.g. Colombo 03" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Physical Street Address</label>
                                    <input type="text" name="address" value="<?= e($customer['address'] ?? '') ?>" placeholder="e.g. 128 Main Street" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 22px; display: flex; justify-content: flex-end;">
                        <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; font-size: 14px; font-weight: 600; border-radius: 8px; border: none; background: #2563eb; color: #ffffff; cursor: pointer; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25); transition: background 0.15s;">
                            <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; fill: currentColor;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Change Password -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: #dc2626; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: currentColor;"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    </div>
                    <div>
                        <h2 style="font-size: 17px; font-weight: 700; margin: 0; color: #0f172a;">Change Password & Security</h2>
                        <p style="font-size: 13px; color: #64748b; margin: 2px 0 0;">Ensure your account uses a strong password with at least 8 characters.</p>
                    </div>
                </div>

                <form action="<?= url('profile/password') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            Current Password <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="password" name="current_password" required placeholder="Enter your existing account password" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                New Password <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="password" name="new_password" required minlength="8" placeholder="At least 8 characters" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Confirm New Password <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="password" name="new_password_confirmation" required minlength="8" placeholder="Re-enter new password" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; font-size: 14px; font-weight: 600; border-radius: 8px; border: none; background: #0f172a; color: #ffffff; cursor: pointer; transition: background 0.15s;">
                            <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; fill: currentColor;"><path d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6-9h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6h1.9c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm0 12H6V10h12v10z"/></svg>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Information & Security Card -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Role Privileges Card -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; fill: #2563eb;"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                    Role &amp; Permissions
                </h3>

                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span style="color: #64748b;">Account Type</span>
                        <span style="font-weight: 700; color: #0f172a;"><?= e($roleName) ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span style="color: #64748b;">Role Code</span>
                        <code style="font-size: 12px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= e($roleSlug) ?></code>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span style="color: #64748b;">Account Status</span>
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: #16a34a;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a;"></span>
                            Active
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                        <span style="color: #64748b;">Member Since</span>
                        <span style="color: #334155; font-weight: 500;">
                            <?= !empty($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : 'N/A' ?>
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Last Signed In</span>
                        <span style="color: #334155; font-weight: 500;">
                            <?= !empty($user['last_login_at']) ? date('M j, Y H:i', strtotime($user['last_login_at'])) : 'Recent session' ?>
                        </span>
                    </div>
                </div>

                <div style="margin-top: 18px; padding: 12px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 12px; line-height: 1.5; color: #475569;">
                    <?php if ($roleSlug === 'owner'): ?>
                        <strong>Full Access:</strong> You have system-wide business ownership privileges covering finances, user management, and company reporting.
                    <?php elseif ($roleSlug === 'sales_rep'): ?>
                        <strong>Sales Workspace:</strong> Authorized for point-of-sale transactions, quotes, orders, customer accounts, and daily sales register.
                    <?php elseif ($roleSlug === 'store_manager'): ?>
                        <strong>Inventory Workspace:</strong> Authorized for warehouse inventory stock-in, stock adjustments, product catalog, and loss write-offs.
                    <?php else: ?>
                        <strong>B2B Customer Portal:</strong> Authorized for wholesale spare parts catalog requisition, order tracking, and delivery oversight.
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Action Card -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 12px;">Quick Navigation</h3>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="<?= auth_dashboard_url() ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; text-decoration: none; font-size: 13px; font-weight: 600; transition: background 0.15s;">
                        <span>Open <?= auth_dashboard_label() ?></span>
                        <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: #64748b;"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                    </a>
                    <a href="<?= url('logout') ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; text-decoration: none; font-size: 13px; font-weight: 600; transition: background 0.15s;">
                        <span>Sign Out of Account</span>
                        <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: #be123c;"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
