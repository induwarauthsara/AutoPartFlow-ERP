<main class="sales-main" id="main-content" data-sales-page="dashboard" data-customers-url="<?= url('sales/customers') ?>" data-orders-url="<?= url('sales/orders') ?>">
    <section class="sales-page-heading">
        <div>
            <p class="sales-eyebrow">Sales Representative Workspace</p>
            <h1 id="dashboard-greeting">Good morning, Sales Rep</h1>
            <p>Here is today’s sales and customer activity.</p>
        </div>
        <span class="dashboard-date" id="dashboard-date"></span>
    </section>

    <!-- Primary CTA: always go to POS, not a toast placeholder. -->
    <a class="pos-shortcut" href="<?= url('sales/pos') ?>">
        <span class="pos-shortcut__icon">
            <svg class="sales-icon"><use href="#sales-icon-pos"></use></svg>
        </span>
        <span>
            <strong>New POS Transaction</strong>
            <small>Start a walk-in or trade customer sale</small>
        </span>
        <svg class="sales-icon"><use href="#sales-icon-arrow"></use></svg>
    </a>

    <section class="dashboard-kpis" aria-label="Today's performance">
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Today’s Sales</span>
                <svg class="sales-icon"><use href="#sales-icon-money"></use></svg>
            </div>
            <strong id="today-sales">Rs. 425,000.00</strong>
            <small class="positive"><svg class="sales-icon"><use href="#sales-icon-trend"></use></svg>12% vs yesterday</small>
        </article>
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Orders Today</span>
                <svg class="sales-icon"><use href="#sales-icon-orders"></use></svg>
            </div>
            <strong>24</strong>
            <small>4 pending processing</small>
        </article>
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Customers Visited</span>
                <svg class="sales-icon"><use href="#sales-icon-customers"></use></svg>
            </div>
            <strong>8</strong>
            <small>3 route visits remaining</small>
        </article>
        <article class="sales-card kpi-card">
            <div class="kpi-card__top">
                <span>Pending Deliveries</span>
                <svg class="sales-icon"><use href="#sales-icon-truck"></use></svg>
            </div>
            <strong>6</strong>
            <small class="warning">1 delivery delayed</small>
        </article>
    </section>

    <div class="dashboard-grid">
        <section class="sales-card chart-card">
            <div class="sales-card__header">
                <div>
                    <h2>Weekly Sales Volume</h2>
                    <p>Sales completed during the current week</p>
                </div>
                <select id="chart-period" aria-label="Sales chart period">
                    <option value="week">This week</option>
                    <option value="previous">Previous week</option>
                </select>
            </div>
            <div class="sales-chart" id="sales-chart" aria-label="Weekly sales bar chart"></div>
        </section>

        <section class="sales-card target-card">
            <div class="sales-card__header">
                <div>
                    <h2>Monthly Target</h2>
                    <p>August 2026</p>
                </div>
                <strong>72%</strong>
            </div>
            <div class="target-progress" aria-label="72 percent of monthly target completed">
                <span style="width:72%"></span>
            </div>
            <div class="target-values">
                <span><small>Achieved</small><strong>Rs. 3.6M</strong></span>
                <span><small>Target</small><strong>Rs. 5.0M</strong></span>
            </div>
            <p class="target-message">Rs. 1.4M remaining to reach this month’s target.</p>
        </section>

        <section class="sales-card route-card">
            <div class="sales-card__header">
                <div>
                    <h2>Assigned Route Shops</h2>
                    <p>Customer visits and account activity</p>
                </div>
                <a class="sales-text-button" href="<?= url('sales/customers') ?>">View All</a>
            </div>
            <div class="route-list" id="dashboard-route-list"></div>
        </section>

        <section class="sales-card deliveries-card">
            <div class="sales-card__header">
                <div>
                    <h2>Pending Deliveries</h2>
                    <p>Orders requiring follow-up</p>
                </div>
                <a class="sales-text-button" href="<?= url('sales/orders') ?>">View All</a>
            </div>
            <div class="delivery-list" id="dashboard-deliveries"></div>
        </section>

        <section class="sales-card recent-sales-card">
            <div class="sales-card__header">
                <div>
                    <h2>Recent Sales</h2>
                    <p>Latest POS sales and shop orders</p>
                </div>
                <a class="sales-text-button" href="<?= url('sales/orders') ?>">View all</a>
            </div>
            <div class="recent-sales-list" id="dashboard-recent-sales"></div>
        </section>
    </div>

    <div class="sales-toast" id="sales-toast" role="status" aria-live="polite"></div>
</main>
