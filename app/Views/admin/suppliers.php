<?php
/** @var array $rows */
/** @var array $summary */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Supplier Management') ?></title>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<style>
body { margin: 0; }
.supplier-main ~ .supplier-modal,
body > .sidebar { position: fixed; }
body > .sidebar { top: 0; left: 0; }
.supplier-main { min-height: 100vh; margin-left: 256px; background: var(--sales-surface, #f9f9ff); }
.supplier-content { padding: 32px; max-width: 1600px; margin: 0 auto; }
.supplier-topbar { min-height: 52px; padding: 6px 28px; background: var(--sales-surface-lowest); border-bottom: 1px solid var(--sales-outline-variant); display: flex; align-items: center; gap: 16px; }
.supplier-search-wrap { position: relative; width: min(420px, 100%); }
.supplier-search-wrap .nav-icon { position: absolute; left: 12px; top: 50%; width: 18px; height: 18px; transform: translateY(-50%); color: var(--sales-on-surface-variant); }
.supplier-search { width: 100%; padding: 10px 14px 10px 40px; border: 1px solid var(--sales-outline-variant); border-radius: 999px; background: var(--sales-surface-low); color: var(--sales-on-surface); }
.supplier-topbar__spacer { flex: 1; }
.supplier-topbar__action { width: 40px; height: 40px; display: grid; place-items: center; border: 0; border-radius: 50%; color: var(--sales-on-surface-variant); background: transparent; text-decoration: none; cursor: pointer; }
.supplier-topbar__action:hover { background: var(--sales-surface-low); color: var(--sales-primary); }
.supplier-page-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; margin-bottom: 24px; }
.supplier-page-head h1 { margin: 0 0 6px; font-size: 30px; color: var(--sales-on-surface); }
.supplier-page-head p { margin: 0; color: var(--sales-on-surface-variant); }
.supplier-button { min-height: 42px; padding: 0 16px; border: 0; border-radius: 9px; background: var(--sales-primary); color: white; font-weight: 700; cursor: pointer; }
.supplier-button:hover { background: var(--sales-primary-container); }
.supplier-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
.supplier-stat, .supplier-directory { background: white; border: 1px solid var(--sales-outline-variant); border-radius: 12px; box-shadow: var(--sales-shadow); }
.supplier-stat { padding: 20px; }
.supplier-stat__label { color: var(--sales-on-surface-variant); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
.supplier-stat__value { display: block; margin-top: 8px; color: var(--sales-primary); font-size: 28px; font-weight: 800; }
.supplier-directory__head { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--sales-outline-variant); }
.supplier-directory__head h2 { margin: 0; font-size: 20px; }
.supplier-table-wrap { overflow-x: auto; }
.supplier-table { width: 100%; border-collapse: collapse; min-width: 900px; }
.supplier-table th { padding: 12px 24px; color: var(--sales-on-surface-variant); background: var(--sales-surface-low); font-size: 11px; text-align: left; text-transform: uppercase; letter-spacing: .06em; }
.supplier-table td { padding: 16px 24px; border-top: 1px solid var(--sales-outline-variant); color: var(--sales-on-surface); vertical-align: middle; }
.supplier-name { font-weight: 700; }
.supplier-meta { margin-top: 3px; color: var(--sales-on-surface-variant); font-size: 12px; }
.supplier-badge { display: inline-flex; padding: 5px 10px; border-radius: 999px; background: var(--sales-success-container); color: var(--sales-success); font-size: 12px; font-weight: 700; }
.supplier-badge--inactive { background: var(--sales-error-container); color: var(--sales-error); }
.supplier-empty { padding: 40px 24px; text-align: center; color: var(--sales-on-surface-variant); }
.supplier-modal[hidden] { display: none; }
.supplier-modal { position: fixed; inset: 0; z-index: 100; display: grid; place-items: center; padding: 20px; }
.supplier-modal__backdrop { position: absolute; inset: 0; background: rgba(18, 28, 44, .52); }
.supplier-modal__panel { position: relative; width: min(680px, 100%); max-height: calc(100vh - 40px); overflow: auto; background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(18, 28, 44, .24); }
.supplier-modal__head, .supplier-modal__actions { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--sales-outline-variant); }
.supplier-modal__head h2 { margin: 0; }
.supplier-modal__close { border: 0; background: transparent; color: var(--sales-on-surface-variant); font-size: 24px; cursor: pointer; }
.supplier-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; padding: 24px; }
.supplier-form label { display: grid; gap: 6px; color: var(--sales-on-surface); font-size: 13px; font-weight: 700; }
.supplier-form label.full { grid-column: 1 / -1; }
.supplier-form input, .supplier-form select, .supplier-form textarea { width: 100%; border: 1px solid var(--sales-outline-variant); border-radius: 8px; padding: 10px 12px; background: white; color: var(--sales-on-surface); }
.supplier-modal__actions { justify-content: flex-end; gap: 10px; border-top: 1px solid var(--sales-outline-variant); border-bottom: 0; }
.supplier-button--secondary { background: white; color: var(--sales-primary); border: 1px solid var(--sales-outline-variant); }
.supplier-toast { position: fixed; right: 24px; bottom: 24px; z-index: 110; padding: 12px 16px; border-radius: 8px; background: var(--sales-primary); color: white; opacity: 0; transform: translateY(8px); pointer-events: none; transition: .2s ease; }
.supplier-toast.is-visible { opacity: 1; transform: translateY(0); }
@media (max-width: 900px) { body > .sidebar { position: relative; } .supplier-main { margin-left: 0; } .supplier-topbar { padding: 6px 20px; } .supplier-content { padding: 24px 20px; } .supplier-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 560px) { .supplier-page-head { align-items: stretch; flex-direction: column; } .supplier-stats { grid-template-columns: 1fr; } .supplier-form { grid-template-columns: 1fr; } .supplier-form label.full { grid-column: auto; } }
</style>
</head>
<body>
<?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>
<div class="supplier-main">
    <header class="supplier-topbar">
        <div class="supplier-search-wrap">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m20 18.6-4.4-4.4a7 7 0 1 0-1.4 1.4l4.4 4.4 1.4-1.4ZM5 10a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"/></svg>
            <input class="supplier-search" id="supplier-search" type="search" placeholder="Search suppliers..." autocomplete="off">
        </div>
        <div class="supplier-topbar__spacer"></div>
        <a class="supplier-topbar__action" href="<?= url('admin/notifications') ?>" aria-label="Notifications">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>
        </a>
        <a class="supplier-topbar__action" href="<?= url('logout') ?>" aria-label="Sign out">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 7l-1.4 1.4 2.6 2.6H8v2h10.2l-2.6 2.6L17 17l5-5-5-5ZM4 5h8V3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8v-2H4V5Z"/></svg>
        </a>
    </header>
    <main class="supplier-content">
        <section class="supplier-page-head">
            <div>
                <h1>Supplier Management</h1>
                <p>Manage automotive part vendors, purchase activity, and outstanding payables.</p>
            </div>
            <button class="supplier-button" type="button" id="open-supplier-modal">+ Add Supplier</button>
        </section>
        <section class="supplier-stats" aria-label="Supplier summary">
            <article class="supplier-stat"><span class="supplier-stat__label">Total Suppliers</span><strong class="supplier-stat__value" id="supplier-total"><?= (int) ($summary['totalSuppliers'] ?? 0) ?></strong></article>
            <article class="supplier-stat"><span class="supplier-stat__label">Active POs</span><strong class="supplier-stat__value" id="supplier-orders"><?= (int) ($summary['activePurchaseOrders'] ?? 0) ?></strong></article>
            <article class="supplier-stat"><span class="supplier-stat__label">Outstanding Payables</span><strong class="supplier-stat__value" id="supplier-payables">Rs. <?= number_format((float) ($summary['outstandingPayables'] ?? 0), 2) ?></strong></article>
            <article class="supplier-stat"><span class="supplier-stat__label">Active Suppliers</span><strong class="supplier-stat__value" id="supplier-active"><?= (int) ($summary['activeSuppliers'] ?? 0) ?></strong></article>
        </section>
        <section class="supplier-directory">
            <div class="supplier-directory__head"><div><h2>Supplier Directory</h2><p class="supplier-meta">Supplier contacts and purchasing overview</p></div></div>
            <div class="supplier-table-wrap">
                <table class="supplier-table">
                    <thead><tr><th>Supplier Name</th><th>Contact</th><th>Key Products</th><th>YTD Purchases</th><th>Outstanding</th><th>Status</th></tr></thead>
                    <tbody id="supplier-table-body">
                    <?php foreach ($rows as $supplier): ?>
                        <tr data-supplier-row data-search="<?= e(strtolower($supplier['company_name'] . ' ' . $supplier['supplier_code'] . ' ' . ($supplier['email'] ?? ''))) ?>">
                            <td><div class="supplier-name"><?= e($supplier['company_name']) ?></div><div class="supplier-meta">ID: <?= e($supplier['supplier_code']) ?></div></td>
                            <td><div><?= e($supplier['contact_person'] ?: 'No contact assigned') ?></div><div class="supplier-meta"><?= e($supplier['email']) ?> · <?= e($supplier['phone']) ?></div></td>
                            <td><?= (int) $supplier['key_products'] ?></td>
                            <td>Rs. <?= number_format((float) $supplier['ytd_purchases'], 2) ?></td>
                            <td>Rs. <?= number_format((float) $supplier['outstanding_balance'], 2) ?></td>
                            <td><span class="supplier-badge <?= (int) $supplier['is_active'] ? '' : 'supplier-badge--inactive' ?>"><?= (int) $supplier['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="supplier-empty" id="supplier-empty" <?= $rows ? 'hidden' : '' ?>>No suppliers found.</p>
            </div>
        </section>
    </main>
</div>
<div class="supplier-modal" id="supplier-modal" hidden>
    <div class="supplier-modal__backdrop" data-close-supplier-modal></div>
    <section class="supplier-modal__panel" role="dialog" aria-modal="true" aria-labelledby="supplier-modal-title">
        <div class="supplier-modal__head"><h2 id="supplier-modal-title">Onboard New Supplier</h2><button class="supplier-modal__close" type="button" data-close-supplier-modal aria-label="Close">&times;</button></div>
        <form id="supplier-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <div class="supplier-form">
                <label class="full">Company Name *<input name="company_name" required maxlength="150"></label>
                <label>Contact Name<input name="contact_person" maxlength="150"></label>
                <label>Email Address *<input name="email" type="email" required maxlength="150"></label>
                <label>Phone Number *<input name="phone" type="tel" required maxlength="20"></label>
                <label>Payment Terms<select name="payment_terms"><option>Net 30</option><option>Net 60</option><option>Due on Receipt</option></select></label>
                <label>City<input name="city" maxlength="100"></label>
                <label class="full">Address<textarea name="address" rows="2"></textarea></label>
                <label class="full">Notes<textarea name="notes" rows="2"></textarea></label>
            </div>
            <div class="supplier-modal__actions"><button class="supplier-button supplier-button--secondary" type="button" data-close-supplier-modal>Cancel</button><button class="supplier-button" type="submit" id="supplier-submit">Save Supplier</button></div>
        </form>
    </section>
</div>
<div class="supplier-toast" id="supplier-toast" role="status" aria-live="polite"></div>
<script>
(function () {
    const modal = document.getElementById('supplier-modal');
    const form = document.getElementById('supplier-form');
    const toast = document.getElementById('supplier-toast');
    const search = document.getElementById('supplier-search');
    let toastTimer;
    function showToast(message) { toast.textContent = message; toast.classList.add('is-visible'); clearTimeout(toastTimer); toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 3000); }
    function closeModal() { modal.hidden = true; }
    document.getElementById('open-supplier-modal').addEventListener('click', () => { form.reset(); modal.hidden = false; form.elements.company_name.focus(); });
    document.querySelectorAll('[data-close-supplier-modal]').forEach((button) => button.addEventListener('click', closeModal));
    search.addEventListener('input', () => { const query = search.value.trim().toLowerCase(); let visible = 0; document.querySelectorAll('[data-supplier-row]').forEach((row) => { const matches = !query || row.dataset.search.includes(query); row.hidden = !matches; if (matches) visible++; }); document.getElementById('supplier-empty').hidden = visible > 0; });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = document.getElementById('supplier-submit');
        const original = submit.textContent;
        submit.disabled = true;
        submit.textContent = 'Saving...';
        try {
            const payload = Object.fromEntries(new FormData(form).entries());
            const response = await fetch('<?= url('admin/suppliers') ?>', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': payload.csrf_token }, body: JSON.stringify(payload) });
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.message || 'Supplier could not be saved.');
            showToast(result.message);
            closeModal();
            window.location.reload();
        } catch (error) { showToast(error.message || 'Network error while saving supplier.'); }
        finally { submit.disabled = false; submit.textContent = original; }
    });
})();
</script>
</body>
</html>
