<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40">
            <h2>Welcome Back</h2>
            <p>Sign in to your AutoPartFlow account</p>
        </div>

        <form method="POST" action="<?= url('login') ?>" class="auth-form">
            <?= csrf_field() ?>
            <?php if (!empty($redirect)): ?>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
            <?php endif; ?>

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
                        placeholder="Username or email"
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

        <!-- Quick Demo Role Accounts for Testing RBAC -->
        <div class="demo-accounts-box" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--outline-variant, #e2e8f0); text-align: left;">
            <p style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">
                Quick Demo Accounts (Click to Fill)
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <button type="button" class="btn-demo-role" onclick="fillDemo('admin', 'admin123')" style="display: flex; flex-direction: column; align-items: flex-start; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; cursor: pointer; text-align: left; font-family: inherit;">
                    <strong style="font-size: 12px; color: #0f172a;">Admin / Owner</strong>
                    <span style="font-size: 11px; color: #64748b;">admin &bull; admin123</span>
                </button>
                <button type="button" class="btn-demo-role" onclick="fillDemo('salesrep', 'sales123')" style="display: flex; flex-direction: column; align-items: flex-start; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; cursor: pointer; text-align: left; font-family: inherit;">
                    <strong style="font-size: 12px; color: #0f172a;">Sales Rep</strong>
                    <span style="font-size: 11px; color: #64748b;">salesrep &bull; sales123</span>
                </button>
                <button type="button" class="btn-demo-role" onclick="fillDemo('storemanager', 'store123')" style="display: flex; flex-direction: column; align-items: flex-start; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; cursor: pointer; text-align: left; font-family: inherit;">
                    <strong style="font-size: 12px; color: #0f172a;">Store Manager</strong>
                    <span style="font-size: 11px; color: #64748b;">storemanager &bull; store123</span>
                </button>
                <button type="button" class="btn-demo-role" onclick="fillDemo('shopcustomer', 'customer123')" style="display: flex; flex-direction: column; align-items: flex-start; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; cursor: pointer; text-align: left; font-family: inherit;">
                    <strong style="font-size: 12px; color: #0f172a;">Shop Customer</strong>
                    <span style="font-size: 11px; color: #64748b;">shopcustomer &bull; customer123</span>
                </button>
            </div>
        </div>

        <script>
            function fillDemo(username, password) {
                document.getElementById('username').value = username;
                document.getElementById('password').value = password;
            }
        </script>

        <p class="auth-switch">New to AutoPartFlow? <a href="<?= url('register') ?>">Create an account</a></p>
    </div>
</div>
