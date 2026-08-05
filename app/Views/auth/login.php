<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <span class="auth-brand-badge">A</span>
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access your ERP workspace</p>
        </div>

        <form method="POST" action="<?= url('login') ?>" class="auth-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="username">Username or Email</label>
                <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="salesrep@autopartflow.com"
                        value="salesrep@autopartflow.com"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <div class="label-row">
                    <label for="password">Password</label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        value="password123"
                        required
                    >
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" checked>
                    <span>Remember me on this device</span>
                </label>
            </div>

            <button type="submit" class="btn-auth-submit">
                <span>Sign In to Workspace</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </form>

        <div class="demo-credentials-box">
            <span class="demo-title">Demo Account:</span>
            <div class="demo-details">
                <span><strong>Role:</strong> Sales Representative</span>
                <span><strong>Email:</strong> salesrep@autopartflow.com</span>
            </div>
            <a href="<?= url('sales') ?>" class="btn-demo-quick">
                Quick Access Sales Rep Workspace &rarr;
            </a>
        </div>
    </div>
</div>
