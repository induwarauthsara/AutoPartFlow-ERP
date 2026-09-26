<div class="auth-wrapper">
    <div class="auth-card auth-card--wide">
        <div class="auth-card-header">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
            <h2>Create your account</h2>
            <p>Create your customer account to order parts and manage your orders.</p>
        </div>

        <form method="POST" action="<?= url('register') ?>" class="auth-form">
            <?= csrf_field() ?>
            <div class="auth-form-grid">
                <div class="form-group">
                    <label for="full_name">Full name</label>
                    <input type="text" id="full_name" name="full_name" maxlength="150" required autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" maxlength="60" pattern="[A-Za-z0-9._-]{3,60}" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" maxlength="150" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="phone">Phone number</label>
                    <input type="tel" id="phone" name="phone" maxlength="20" autocomplete="tel">
                </div>
                <div class="form-group auth-form-grid__full">
                    <label for="role_slug">Account type</label>
                    <select id="role_slug" name="role_slug" required>
                        <option value="shop_customer" selected>Shop Customer (B2B Portal)</option>
                        <option value="sales_rep">Sales Representative</option>
                        <option value="store_manager">Store Manager</option>
                        <option value="owner">Business Owner</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="8" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" required autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn-auth-submit">
                <span>Create Account</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
        </form>
        <p class="auth-switch">Already registered? <a href="<?= url('login') ?>">Sign in</a></p>
    </div>
</div>
