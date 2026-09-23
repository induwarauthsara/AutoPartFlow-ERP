/**
 * Shared Sales Rep helpers.
 * Safe to edit: currency format, toast duration, date locale (en-LK / Rs.).
 * Used by dashboard, POS, orders, and customers scripts.
 */
(function (window) {
    'use strict';

    /**
     * Find an element by id. Returns null if missing so callers can skip safely.
     */
    function byId(id) {
        return document.getElementById(id);
    }

    /**
     * Escape user/mock text before inserting into innerHTML (XSS-safe for mock UI).
     * When PHP renders names, still escape in the view with e().
     */
    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = String(value ?? '');
        return node.innerHTML;
    }

    /**
     * Format a rupee amount. compact=true turns 1,000,000+ into Rs. 1.0M for KPIs.
     */
    function money(value, compact) {
        if (compact && Number(value) >= 1000000) {
            return 'Rs. ' + (Number(value) / 1000000).toFixed(1) + 'M';
        }
        return 'Rs. ' + Number(value || 0).toLocaleString('en-LK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /**
     * Display YYYY-MM-DD as a readable Sri Lanka date.
     */
    function displayDate(value) {
        return new Intl.DateTimeFormat('en-LK', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }).format(new Date(value + 'T00:00:00'));
    }

    /** Turn "Needs Stock" into "needs-stock" for CSS status classes. */
    function slug(value) {
        return String(value).toLowerCase().replace(/\s+/g, '-').replace(/[^a-z-]/g, '');
    }

    let toastTimer;

    /**
     * Show a short status message in #sales-toast (one toast for the whole module).
     */
    function showToast(message) {
        const toast = byId('sales-toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2800);
    }

    /**
     * Map order/sale status to a sales-badge modifier class.
     */
    function statusBadgeClass(status) {
        const key = String(status).toLowerCase();
        if (key === 'pending') return 'sales-badge--pending';
        if (key === 'processing' || key === 'shipped') return 'sales-badge--processing';
        if (key === 'delivered') return 'sales-badge--delivered';
        if (key === 'cancelled' || key === 'returned') return 'sales-badge--cancelled';
        return 'sales-badge--info';
    }

    let confirmResolver = null;

    /**
     * Ask the user to confirm a destructive action (Delete customer / order).
     * Returns a Promise<boolean>. Uses #sales-confirm-dialog in the layout.
     * PHP later: after true, send DELETE to your controller; this helper stays UI-only.
     */
    function confirmAction(title, message, confirmLabel) {
        const dialog = byId('sales-confirm-dialog');
        if (!dialog) {
            return Promise.resolve(window.confirm(message));
        }

        byId('sales-confirm-title').textContent = title;
        byId('sales-confirm-message').textContent = message;
        byId('sales-confirm-ok').textContent = confirmLabel || 'Delete';

        return new Promise(function (resolve) {
            confirmResolver = resolve;
            dialog.showModal();
        });
    }

    function bindConfirmDialog() {
        const dialog = byId('sales-confirm-dialog');
        if (!dialog || dialog.dataset.bound) return;
        dialog.dataset.bound = '1';

        function finish(ok) {
            if (confirmResolver) confirmResolver(ok);
            confirmResolver = null;
            dialog.close();
        }

        byId('sales-confirm-ok').addEventListener('click', function () { finish(true); });
        byId('sales-confirm-cancel').addEventListener('click', function () { finish(false); });
        dialog.addEventListener('cancel', function (event) {
            event.preventDefault();
            finish(false);
        });
    }

    bindConfirmDialog();

    window.SalesRepUtils = {
        byId: byId,
        escapeHtml: escapeHtml,
        money: money,
        displayDate: displayDate,
        slug: slug,
        showToast: showToast,
        statusBadgeClass: statusBadgeClass,
        confirmAction: confirmAction
    };
})(window);
