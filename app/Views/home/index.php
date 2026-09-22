<section class="hero-section">
    <div class="container hero-container">
        <div class="hero-content">
            <span class="hero-badge">
                <span class="badge-dot"></span> Next-Gen Auto ERP System
            </span>
            <h1 class="hero-title">
                Automobile Spare Parts <span class="gradient-text">Distribution & Sales Management</span>
            </h1>
            <p class="hero-subtitle">
                Unified real-time inventory management, multi-channel Point of Sale (POS), customer trade accounts, and automated sales order processing built for spare part distributors.
            </p>
            <div class="hero-actions">
                <a href="<?= url('login') ?>" class="btn-hero-primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                    </svg>
                    <span>Sign In to Portal</span>
                </a>
                <a href="<?= url('sales') ?>" class="btn-hero-secondary">
                    <span>Launch Sales Workspace</span>
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="hero-card-preview">
            <div class="glass-card">
                <div class="glass-card-header">
                    <div class="window-dots">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                    </div>
                    <span class="preview-tag">System Status: Active</span>
                </div>
                <div class="glass-card-body">
                    <div class="quick-kpi-row">
                        <div class="quick-kpi">
                            <span class="kpi-label">Today's Revenue</span>
                            <span class="kpi-value">Rs. 425,000</span>
                        </div>
                        <div class="quick-kpi">
                            <span class="kpi-label">Active Orders</span>
                            <span class="kpi-value highlight">142</span>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <a href="<?= url('sales/pos') ?>" class="preview-btn">Open POS Terminal</a>
                        <a href="<?= url('sales/orders') ?>" class="preview-btn preview-btn-subtle">View Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2>Core Operational Workspaces</h2>
            <p>Select a workspace module to access management tools</p>
        </div>

        <div class="features-grid">
            <a href="<?= url('sales') ?>" class="feature-card">
                <div class="feature-icon icon-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </div>
                <h3>Sales Dashboard</h3>
                <p>Monitor daily sales targets, revenue metrics, performance statistics, and quick transaction shortcuts.</p>
                <span class="card-link">Open Workspace &rarr;</span>
            </a>

            <a href="<?= url('sales/pos') ?>" class="feature-card">
                <div class="feature-icon icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M6 8h12M6 12h4M6 16h8"/>
                    </svg>
                </div>
                <h3>Point of Sale (POS)</h3>
                <p>Fast checkout interface with barcode scanning, walking customer billing, trade accounts, and receipt print.</p>
                <span class="card-link">Launch POS Terminal &rarr;</span>
            </a>

            <a href="<?= url('sales/orders') ?>" class="feature-card">
                <div class="feature-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <rect x="8" y="2" width="8" height="4" rx="1"/>
                    </svg>
                </div>
                <h3>Sales Orders</h3>
                <p>Track pending wholesale orders, dispatch status, customer delivery notes, and approval workflows.</p>
                <span class="card-link">Manage Orders &rarr;</span>
            </a>

            <a href="<?= url('sales/customers') ?>" class="feature-card">
                <div class="feature-icon icon-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Customer Directory</h3>
                <p>Manage customer trade profiles, credit limits, outstanding balances, and contact details.</p>
                <span class="card-link">View Directory &rarr;</span>
            </a>
        </div>
    </div>
</section>
