/**
 * Sales Rep Dashboard behaviour.
 * Safe to edit: greeting hours, chart rendering, which links open POS/Customers/Orders.
 * PHP later: replace SALES_REP_MOCK_DATA with controller JSON.
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-sales-page="dashboard"]');
    if (!page) return;

    const u = window.SalesRepUtils;
    const data = window.SALES_REP_MOCK_DATA || {};
    const byId = u.byId;

    /**
     * Morning before 12, afternoon before 17, otherwise evening.
     */
    function greeting() {
        const hour = new Date().getHours();
        if (hour < 12) return 'Good morning';
        if (hour < 17) return 'Good afternoon';
        return 'Good evening';
    }

    function renderChart(period) {
        const chartData = (data.weeklySales && data.weeklySales[period]) || [];
        const maxValue = Math.max.apply(null, chartData.map(function (point) { return point.value; }).concat([1]));
        const chart = byId('sales-chart');
        chart.innerHTML = '';
        chartData.forEach(function (point, index) {
            const column = document.createElement('div');
            column.className = 'chart-column';
            const height = point.value === 0 ? 0 : Math.max(4, Math.round((point.value / maxValue) * 100));
            const current = period === 'week' && index === 3;
            column.innerHTML =
                '<div class="chart-column__track"><div class="chart-column__bar' + (current ? ' chart-column__bar--current' : '') + '" style="height:' + height + '%">' +
                (point.value ? '<span class="chart-column__value">' + u.escapeHtml(u.money(point.value, true)) + '</span>' : '') +
                '</div></div><span class="chart-column__label">' + u.escapeHtml(point.label) + '</span>';
            chart.appendChild(column);
        });
    }

    function renderRoutes() {
        byId('dashboard-route-list').innerHTML = (data.routeShops || []).map(function (shop) {
            return '<a class="route-item" href="' + u.escapeHtml(page.dataset.customersUrl) + '">' +
                '<span class="sales-avatar">' + u.escapeHtml(shop.initials) + '</span>' +
                '<div><strong>' + u.escapeHtml(shop.name) + '</strong><span>' + u.escapeHtml(shop.note) + '</span></div>' +
                '<span class="sales-badge sales-badge--info route-status--' + u.slug(shop.status) + '">' + u.escapeHtml(shop.status) + '</span></a>';
        }).join('');
    }

    function renderDeliveries() {
        byId('dashboard-deliveries').innerHTML = (data.deliveries || []).map(function (delivery) {
            const tone = delivery.status === 'Delayed' ? 'sales-badge--error' : 'sales-badge--info';
            return '<button type="button" class="delivery-item" data-goto-orders>' +
                '<div class="delivery-item__top"><strong>' + u.escapeHtml(delivery.id) + '</strong>' +
                '<span class="sales-badge ' + tone + '">' + u.escapeHtml(delivery.status) + '</span></div>' +
                '<p>' + u.escapeHtml(delivery.items) + '</p><p>To: ' + u.escapeHtml(delivery.customer) + '</p></button>';
        }).join('');
    }

    function renderRecentSales() {
        const list = byId('dashboard-recent-sales');
        if (!list) return;
        list.innerHTML = (data.recentSales || []).map(function (sale) {
            return '<a class="recent-sale-item" href="' + u.escapeHtml(page.dataset.ordersUrl) + '">' +
                '<span class="sales-avatar" aria-hidden="true"><svg class="sales-icon"><use href="#sales-icon-money"></use></svg></span>' +
                '<div><strong>' + u.escapeHtml(sale.id) + '</strong><span>' + u.escapeHtml(sale.customer) + '</span></div>' +
                '<span class="sales-badge ' + u.statusBadgeClass(sale.status) + '">' + u.escapeHtml(u.money(sale.total)) + '</span></a>';
        }).join('');
    }

    byId('dashboard-greeting').textContent = greeting() + ', Sales Rep';
    byId('dashboard-date').textContent = new Intl.DateTimeFormat('en-LK', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    }).format(new Date());

    byId('chart-period').addEventListener('change', function (event) {
        renderChart(event.target.value);
    });

    page.addEventListener('click', function (event) {
        if (event.target.closest('[data-goto-orders]')) {
            window.location.href = page.dataset.ordersUrl;
        }
    });

    renderChart('week');
    renderRoutes();
    renderDeliveries();
    renderRecentSales();
})();
