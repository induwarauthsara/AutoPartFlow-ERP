<?php
/** @var array $rows */
$flash = $_SESSION['emp_flash'] ?? null;
unset($_SESSION['emp_flash']);

$statusLabels = ['active' => 'Active', 'on_leave' => 'On Leave', 'inactive' => 'Inactive'];

$employees = array_map(fn($r) => [
    'id'              => (int) ($r['id'] ?? 0),
    'full_name'       => (string) ($r['full_name'] ?? ''),
    'employee_code'   => (string) ($r['employee_code'] ?? ''),
    'designation'     => (string) ($r['designation'] ?? ''),
    'base_salary'     => (float) ($r['base_salary'] ?? 0),
    'commission_rate' => (float) ($r['commission_rate'] ?? 0),
    'status'          => (string) ($r['status'] ?? 'active'),
], $rows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Employee Management') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--green:#16a34a;--green-bg:#e7f8ee;--amber:#d97706;--amber-bg:#fef3e2;--red:#dc2626;--red-bg:#fdecec;}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.app-shell{display:flex;min-height:100vh;}
.sidebar{width:230px;flex-shrink:0;background:linear-gradient(180deg,var(--navy-900),var(--navy-800));color:#cbd5e1;padding:18px 12px;display:flex;flex-direction:column;}
.brand{display:flex;align-items:center;gap:9px;padding:6px 8px 20px;}
.brand-mark{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--indigo-500),#7c8cf0);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0;}
.brand-title{color:#fff;font-weight:700;font-size:14px;}
.brand-sub{font-size:10.5px;color:#8590b3;text-transform:uppercase;letter-spacing:.04em;}
.nav-group{margin-top:12px;}
.nav-group-label{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:#5b6689;padding:0 10px;margin-bottom:6px;font-weight:600;}
.nav-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;color:#b7c0dd;font-size:13px;font-weight:500;margin-bottom:2px;text-decoration:none;}
.nav-link.active{background:var(--indigo-500);color:#fff;}
.sidebar-footer{margin-top:auto;padding-top:12px;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:9px;}
.avatar{width:30px;height:30px;border-radius:50%;background:var(--indigo-500);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:11px;flex-shrink:0;}
.sidebar-footer .name{color:#fff;font-size:12.5px;font-weight:600;}
.sidebar-footer .role{color:#7f8bb0;font-size:11px;}
.main{flex:1;min-width:0;}
.topbar{background:#fff;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;gap:16px;padding:12px 24px;font-weight:700;}
.search-box{flex:1;max-width:380px;display:flex;align-items:center;gap:8px;background:var(--slate-100);border-radius:9px;padding:8px 12px;color:var(--slate-500);font-weight:400;}
.search-box input{border:none;background:transparent;outline:none;flex:1;font-size:13px;}
.topbar-icons{display:flex;align-items:center;gap:12px;margin-left:auto;color:var(--slate-500);}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;}
.btn{padding:9px 16px;border-radius:9px;font-size:12.5px;font-weight:600;border:1px solid var(--slate-300);background:#fff;cursor:pointer;}
.btn-primary{background:var(--navy-900);color:#fff;border-color:var(--navy-900);}
.btn-danger{background:var(--red);color:#fff;border-color:var(--red);}
.head-actions{display:flex;gap:10px;}
.grid-2{display:grid;grid-template-columns:1fr 1.4fr;gap:16px;margin-bottom:16px;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;}
.stat-mini{display:flex;gap:14px;margin-top:12px;}
.stat-mini .box{background:var(--slate-100);padding:8px 14px;border-radius:9px;}
.stat-mini .num{font-size:16px;font-weight:700;}
.stat-mini .label{font-size:11px;color:var(--slate-500);}
.bar-track{height:6px;background:var(--slate-100);border-radius:6px;overflow:hidden;margin:10px 0;}
.bar-fill{height:100%;border-radius:6px;background:var(--indigo-500);}
.perf-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.perf-grid .lbl{font-size:10.5px;text-transform:uppercase;color:var(--slate-500);margin-bottom:4px;}
.perf-grid .val{font-size:17px;font-weight:700;}
.perf-grid .change{font-size:11px;font-weight:600;margin-top:2px;}
.change.up{color:var(--green);}
.change.down{color:var(--red);}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:10.5px;text-transform:uppercase;color:var(--slate-500);padding:10px 12px;border-bottom:1px solid var(--slate-100);}
td{padding:12px;border-bottom:1px solid var(--slate-100);font-size:13px;}
.table-user{display:flex;align-items:center;gap:9px;}
.badge{display:inline-flex;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;}
.badge.active{background:var(--green-bg);color:var(--green);}
.badge.on_leave{background:var(--amber-bg);color:var(--amber);}
.badge.inactive{background:var(--slate-100);color:var(--slate-500);}
.chip-count{background:var(--indigo-50);color:var(--indigo-500);font-weight:700;font-size:11px;padding:3px 9px;border-radius:20px;}
.row-actions{display:flex;gap:4px;}
.icon-btn{border:0;background:none;padding:6px;border-radius:6px;color:var(--slate-500);cursor:pointer;display:grid;place-items:center;}
.icon-btn:hover{background:var(--slate-100);color:var(--slate-900);}
.icon-btn.del:hover{color:var(--red);}
.flash{padding:11px 14px;border-radius:9px;margin-bottom:16px;font-weight:600;font-size:13px;}
.flash.success{background:var(--green-bg);color:var(--green);}
.flash.error{background:var(--red-bg);color:var(--red);}
dialog{border:0;border-radius:16px;padding:0;width:min(520px,94vw);}
dialog::backdrop{background:rgba(15,23,42,.45);}
.dlg{padding:22px;}
.dlg h3{margin:0 0 16px;font-size:18px;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.form-grid .full{grid-column:span 2;}
.field{display:grid;gap:5px;}
.field label{font-size:12.5px;font-weight:600;}
.field input,.field select{border:1px solid var(--slate-300);border-radius:8px;padding:9px 11px;font:inherit;background:#fff;width:100%;}
.field small{color:var(--slate-500);font-size:11.5px;}
.dlg-foot{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;}
@media (max-width:900px){.grid-2,.perf-grid{grid-template-columns:1fr;}}
@media (max-width:560px){.form-grid{grid-template-columns:1fr;}.form-grid .full{grid-column:auto;}}
</style>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand"><img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40"><div><div class="brand-title">AutoPartFlow</div><div class="brand-sub">Admin Workspace</div></div></div>
        <nav aria-label="Admin navigation">
<a class="nav-link" href="<?= url('admin/dashboard') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8v8H3V3Zm10 0h8v5h-8V3ZM3 13h8v8H3v-8Zm10-3h8v11h-8V10Z"/></svg>Dashboard</a>
<a class="nav-link" href="<?= url('admin/reports') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h2v16h15v2H4V3Zm5 8h3v6H9v-6Zm5-5h3v11h-3V6Z"/></svg>Reports</a>
<a class="nav-link" href="<?= url('admin/users') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Z"/></svg>User Management</a>
<a class="nav-link active" aria-current="page" href="<?= url('admin/employees') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Zm16-7h4v7h-4v-7Z"/></svg>Employees</a>
<a class="nav-link" href="<?= url('admin/notifications') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>Notifications</a>
<a class="nav-link" href="<?= url('admin/settings') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v2H3V5Zm4 6h10v2H7v-2Zm3 6h4v2h-4v-2Z"/></svg>Settings</a>
</nav>
        <div class="sidebar-footer">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div>
            <div><div class="name"><?= e($_SESSION['full_name'] ?? 'Alex Rivera') ?></div><div class="role">Admin</div></div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            AutoPartFlow
            <div class="search-box"><input type="text" id="empSearch" placeholder="Search employees..."></div>
            <div class="topbar-icons"><a href="<?= url('admin/notifications') ?>" aria-label="Notifications">Notifications</a> <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></div></div>
        </header>

        <div class="content">
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <div class="page-head">
                <div>
                    <h1>Employees</h1>
                    <p>Manage team performance, attendance, and assignments.</p>
                </div>
                <div class="head-actions">
                    <button class="btn" type="button" id="exportBtn">Export</button>
                    <button class="btn btn-primary" type="button" id="addBtn">+ New Employee</button>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h3 style="margin-top:0;">Today's Attendance</h3>
                    <div style="font-size:28px;font-weight:700;">42 <span style="font-size:14px;font-weight:500;color:var(--slate-500);">/ 45 Present</span></div>
                    <div class="bar-track"><div class="bar-fill" style="width:93%"></div></div>
                    <div class="stat-mini">
                        <div class="box"><div class="num" style="color:var(--amber);">2</div><div class="label">On Leave</div></div>
                        <div class="box"><div class="num" style="color:var(--red);">1</div><div class="label">Late</div></div>
                    </div>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">Team Performance (Q3)</h3>
                    <div class="perf-grid">
                        <div><div class="lbl">Total Sales Volume</div><div class="val">Rs. 1.2M</div><div class="change up">↑ +12% vs Q2</div></div>
                        <div><div class="lbl">Avg Order Fulfillment</div><div class="val">4.2 Hrs</div><div class="change down">↓ -15% vs Q2</div></div>
                        <div><div class="lbl">Customer Satisfaction</div><div class="val">98%</div><div class="change">— No change</div></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Personnel Directory</h3>
                <div class="table-wrap">
                <table>
                    <thead><tr><th>Employee</th><th>Role</th><th>Performance Score</th><th>Base Salary</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody id="empRows">
                    <?php foreach ($employees as $emp): $score = min(100, $emp['commission_rate'] * 10); ?>
                        <tr>
                            <td><div class="table-user"><div class="avatar"><?= strtoupper(substr($emp['full_name'], 0, 1)) ?></div><div><strong><?= e($emp['full_name']) ?></strong><br><span style="color:var(--slate-500);font-size:11.5px;">ID: <?= e($emp['employee_code']) ?></span></div></div></td>
                            <td><?= e($emp['designation']) ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="bar-track" style="width:70px;margin:0;"><div class="bar-fill" style="width:<?= $score ?>%;background:<?= $score >= 85 ? 'var(--green)' : 'var(--indigo-500)' ?>;"></div></div>
                                    <span style="font-size:12px;font-weight:600;"><?= round($score) ?>/100</span>
                                </div>
                            </td>
                            <td>Rs. <?= number_format($emp['base_salary']) ?></td>
                            <td><span class="badge <?= e($emp['status']) ?>"><?= e($statusLabels[$emp['status']] ?? $emp['status']) ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="icon-btn" data-edit="<?= $emp['id'] ?>" title="Edit">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4v16h16v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                    </button>
                                    <button type="button" class="icon-btn del" data-del="<?= $emp['id'] ?>" title="Delete">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$employees): ?><tr><td colspan="6">No employees found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add / Edit employee -->
<dialog id="empDlg">
    <form method="post" id="empForm" class="dlg" action="<?= url('admin/employees/store') ?>">
        <input type="hidden" name="id" id="fId">
        <h3 id="dlgTitle">New employee</h3>
        <div class="form-grid">
            <div class="field full">
                <label for="fName">Full name</label>
                <input id="fName" name="full_name" required maxlength="100">
            </div>
            <div class="field">
                <label for="fCode">Employee ID</label>
                <input id="fCode" name="employee_code" required maxlength="30">
            </div>
            <div class="field">
                <label for="fRole">Role</label>
                <input id="fRole" name="designation" required maxlength="60" placeholder="e.g. Sales Executive">
            </div>
            <div class="field">
                <label for="fSalary">Base salary (Rs.)</label>
                <input id="fSalary" name="base_salary" type="number" min="0" step="0.01" required>
            </div>
            <div class="field">
                <label for="fRate">Commission rate (%)</label>
                <input id="fRate" name="commission_rate" type="number" min="0" max="10" step="0.1" required>
                <small>Performance score = rate × 10</small>
            </div>
            <div class="field full">
                <label for="fStatus">Status</label>
                <select id="fStatus" name="status">
                    <?php foreach ($statusLabels as $val => $label): ?>
                        <option value="<?= $val ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="dlg-foot">
            <button type="button" class="btn" id="cancelBtn">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveBtn">Add employee</button>
        </div>
    </form>
</dialog>

<!-- Delete confirm -->
<dialog id="delDlg">
    <form method="post" class="dlg" action="<?= url('admin/employees/delete') ?>">
        <input type="hidden" name="id" id="delId">
        <h3>Delete employee?</h3>
        <p id="delText" style="margin:0;color:var(--slate-500);"></p>
        <div class="dlg-foot">
            <button type="button" class="btn" id="delCancel">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete employee</button>
        </div>
    </form>
</dialog>

<script>
const EMPLOYEES  = <?= json_encode($employees, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const STORE_URL  = <?= json_encode(url('admin/employees/store')) ?>;
const UPDATE_URL = <?= json_encode(url('admin/employees/update')) ?>;

const empDlg  = document.getElementById('empDlg');
const empForm = document.getElementById('empForm');
const delDlg  = document.getElementById('delDlg');
const findEmp = id => EMPLOYEES.find(e => e.id === Number(id));

// ---- Add / Edit
function openForm(emp) {
    empForm.action = emp ? UPDATE_URL : STORE_URL;
    document.getElementById('dlgTitle').textContent = emp ? 'Edit employee' : 'New employee';
    document.getElementById('saveBtn').textContent  = emp ? 'Save changes' : 'Add employee';
    document.getElementById('fId').value     = emp ? emp.id : '';
    document.getElementById('fName').value   = emp ? emp.full_name : '';
    document.getElementById('fCode').value   = emp ? emp.employee_code : '';
    document.getElementById('fRole').value   = emp ? emp.designation : '';
    document.getElementById('fSalary').value = emp ? emp.base_salary : '';
    document.getElementById('fRate').value   = emp ? emp.commission_rate : '';
    document.getElementById('fStatus').value = emp ? emp.status : 'active';
    empDlg.showModal();
}
document.getElementById('addBtn').onclick = () => openForm(null);
document.getElementById('cancelBtn').onclick = () => empDlg.close();

// ---- Edit / Delete buttons
document.getElementById('empRows').addEventListener('click', e => {
    const b = e.target.closest('button');
    if (!b) return;
    if (b.dataset.edit) openForm(findEmp(b.dataset.edit));
    if (b.dataset.del) {
        const emp = findEmp(b.dataset.del);
        document.getElementById('delId').value = emp.id;
        document.getElementById('delText').textContent =
            `${emp.full_name} (ID: ${emp.employee_code}) will be removed permanently. This can't be undone.`;
        delDlg.showModal();
    }
});
document.getElementById('delCancel').onclick = () => delDlg.close();

// ---- Search
document.getElementById('empSearch').addEventListener('input', e => {
    const q = e.target.value.trim().toLowerCase();
    document.querySelectorAll('#empRows tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ---- Export CSV
document.getElementById('exportBtn').onclick = () => {
    const head = ['Employee ID', 'Full name', 'Role', 'Base salary', 'Commission rate', 'Status'];
    const rows = EMPLOYEES.map(e => [e.employee_code, e.full_name, e.designation, e.base_salary, e.commission_rate, e.status]);
    const csv = [head, ...rows].map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], {type: 'text/csv'}));
    a.download = 'employees.csv';
    a.click();
};
</script>
</body>
</html>