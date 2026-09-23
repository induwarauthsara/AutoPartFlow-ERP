/**
 * Sales Rep chrome: active nav, mobile sidebar, notifications panel.
 * Runs on every Sales Rep page. Reads data-sales-page from <main>.
 * Create Order uses data-sales-page="create" but Orders nav stays active.
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-sales-page]');
    const utils = window.SalesRepUtils;
    if (!utils) return;

    const byId = utils.byId;

    /**
     * Orders list and New Order both highlight the Orders item.
     */
    function navMatches(navPage, currentPage) {
        if (navPage === currentPage) return true;
        return navPage === 'orders' && currentPage === 'create';
    }

    function initActiveNav() {
        if (!page) return;
        const current = page.dataset.salesPage;

        document.querySelectorAll('[data-nav-page]').forEach(function (link) {
            const active = navMatches(link.dataset.navPage, current);
            link.classList.toggle('sales-nav__link--active', active);
            if (active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });

        document.querySelectorAll('[data-mobile-page]').forEach(function (link) {
            const active = navMatches(link.dataset.mobilePage, current);
            link.classList.toggle('sales-mobile-nav__item--active', active);
            if (active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    }

    /**
     * Off-canvas sidebar for screens ≤900px. Backdrop click and resize close it.
     */
    function initSidebar() {
        const sidebar = byId('sales-sidebar');
        const toggle = byId('sales-menu-toggle');
        const backdrop = byId('sales-sidebar-backdrop');
        if (!sidebar || !toggle || !backdrop) return;

        function setSidebar(open) {
            sidebar.classList.toggle('is-open', open);
            backdrop.classList.toggle('is-visible', open);
            toggle.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        }

        toggle.addEventListener('click', function () {
            setSidebar(!sidebar.classList.contains('is-open'));
        });
        backdrop.addEventListener('click', function () { setSidebar(false); });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 900) setSidebar(false);
        });
    }

    /**
     * Bell is visual-only until PHP wires notifications. Empty panel is honest UX.
     */
    function initNotifications() {
        const button = byId('sales-notify-toggle');
        const panel = byId('sales-notify-panel');
        if (!button || !panel) return;

        button.addEventListener('click', function () {
            panel.classList.toggle('hidden');
        });

        document.addEventListener('click', function (event) {
            if (!panel.classList.contains('hidden') && !event.target.closest('#sales-notify-toggle') && !event.target.closest('#sales-notify-panel')) {
                panel.classList.add('hidden');
            }
        });
    }

    initActiveNav();
    initSidebar();
    initNotifications();
})();
