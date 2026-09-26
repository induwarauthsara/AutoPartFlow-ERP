<?php
/** @var array $rows */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'User Management') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-50:#eef0fd;--slate-900:#0f172a;--slate-500:#64748b;--slate-100:#f1f5f9;--slate-300:#cbd5e1;--bg:#f5f6fb;--radius-lg:16px;--shadow:0 1px 3px rgba(15,23,42,.06);--green:#16a34a;--green-bg:#e7f8ee;--red:#dc2626;--red-bg:#fdecec;}
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
.topbar{background:#fff;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;gap:16px;padding:12px 24px;}
.search-box{flex:1;max-width:380px;display:flex;align-items:center;gap:8px;background:var(--slate-100);border-radius:9px;padding:8px 12px;color:var(--slate-500);}
.search-box input{border:none;background:transparent;outline:none;flex:1;font-size:13px;}
.topbar-icons{display:flex;align-items:center;gap:14px;margin-left:auto;color:var(--slate-500);}
.content{padding:24px;}
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.page-head h1{font-size:22px;margin:0 0 4px;}
.page-head p{margin:0;color:var(--slate-500);font-size:13px;}
.btn{padding:9px 16px;border-radius:9px;font-size:12.5px;font-weight:600;border:1px solid var(--slate-300);background:#fff;cursor:pointer;}
.btn-primary{background:var(--navy-900);color:#fff;border-color:var(--navy-900);}
.grid-2{display:grid;grid-template-columns:1fr 1.6fr;gap:16px;margin-bottom:16px;}
.card{background:#fff;border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:20px;border:1px solid #eef0f5;margin-bottom:16px;}
.card-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;}
.card-head h3{margin:0;font-size:15px;}
.card-head a{font-size:12px;color:var(--indigo-500);text-decoration:none;font-weight:600;}
.donut-wrap{display:flex;flex-direction:column;align-items:center;gap:10px;}
.donut-center{font-size:20px;font-weight:700;}
.legend{font-size:11.5px;color:var(--slate-500);}
.role-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;}
.role-box{background:var(--slate-100);border-radius:10px;padding:12px;text-align:center;}
.role-num{font-size:17px;font-weight:700;}
.role-label{font-size:10.5px;color:var(--slate-500);}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:10.5px;text-transform:uppercase;color:var(--slate-500);padding:10px 12px;border-bottom:1px solid var(--slate-100);}
td{padding:12px;border-bottom:1px solid var(--slate-100);font-size:13px;}
.table-user{display:flex;align-items:center;gap:9px;}
.badge{display:inline-flex;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;}
.badge.active{background:var(--green-bg);color:var(--green);}
.badge.suspended{background:var(--red-bg);color:var(--red);}
.log-item{display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--slate-100);font-size:12.5px;}
.log-item:last-child{border-bottom:none;}
.log-icon{width:28px;height:28px;border-radius:8px;background:var(--indigo-50);color:var(--indigo-500);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.log-icon.red{background:var(--red-bg);color:var(--red);}
.log-ip{color:var(--slate-500);font-size:11px;}
@media (max-width:900px){.grid-2{grid-template-columns:1fr;}.role-grid{grid-template-columns:repeat(2,1fr);}}
</style>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<meta name="base-url" content="<?= url() ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand"><img class="app-logo" src="<?= asset('images/logo-icon.png') ?>" alt="AutoPartFlow" width="40" height="40"><div><div class="brand-title">AutoPartFlow</div><div class="brand-sub">Admin Workspace</div></div></div>
        <nav aria-label="Admin navigation">
<a class="nav-link" href="<?= url('admin/dashboard') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h8v8H3V3Zm10 0h8v5h-8V3ZM3 13h8v8H3v-8Zm10-3h8v11h-8V10Z"/></svg>Dashboard</a>
<a class="nav-link" href="<?= url('admin/reports') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h2v16h15v2H4V3Zm5 8h3v6H9v-6Zm5-5h3v11h-3V6Z"/></svg>Reports</a>
<a class="nav-link active" aria-current="page" href="<?= url('admin/users') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Z"/></svg>User Management</a>
<a class="nav-link" href="<?= url('admin/employees') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM2 20v-2c0-3 3.5-5 7-5s7 2 7 5v2H2Zm16-7h4v7h-4v-7Z"/></svg>Employees</a>
<a class="nav-link" href="<?= url('admin/notifications') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2Zm7-5-2-2v-5a5 5 0 0 0-4-5V3h-2v2a5 5 0 0 0-4 5v5l-2 2v2h14v-2Z"/></svg>Notifications</a>
<a class="nav-link" href="<?= url('admin/settings') ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v2H3V5Zm4 6h10v2H7v-2Zm3 6h4v2h-4v-2Z"/></svg>Settings</a>
<a class="nav-link" href="<?= url() ?>"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>Public Home</a>
<a class="nav-link" href="<?= url('logout') ?>" style="color:#fca5a5;"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>Sign Out</a>
</nav>
        <div class="sidebar-footer">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)) ?></div>
            <div><div class="name"><?= e($_SESSION['full_name'] ?? 'Sarah Jenkins') ?></div><div class="role">Admin</div></div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="search-box"> <input type="text" placeholder="Search users, roles..."></div>
            <div class="topbar-icons"><a href="<?= url('admin/notifications') ?>" aria-label="Notifications">Notifications</a> <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)) ?></div></div>
        </header>

        <div class="content">
            <div class="page-head">
                <div>
                    <h1>User Management</h1>
                    <p>Control system access, roles, and review audit logs.</p>
                </div>
                <button class="btn btn-primary" onclick="document.getElementById('addUserModal').style.display='flex'">+ Add User</button>

            <div class="grid-2">
                <div class="card">
                    <div class="card-head"><h3>Active Users</h3></div>
                    <div class="donut-wrap">
                        <div style="width:150px;height:150px;position:relative;">
                            <canvas id="userDonut" style="width:100%;height:100%;"></canvas>
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                <div class="donut-center"><?= count($rows) ?></div>
                                <div style="color:var(--slate-500);font-size:11px;">Total</div>
                            </div>
                        </div>
                        <div class="legend">● Active (<?= count($rows) ?>) &nbsp;&nbsp; ○ Inactive (0)</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-head"><h3>Role Distribution</h3><a href="#">Manage Roles</a></div>
                    <div class="role-grid">
                        <div class="role-box"><div class="role-num">12</div><div class="role-label">Administrators</div></div>
                        <div class="role-box"><div class="role-num">45</div><div class="role-label">Managers</div></div>
                        <div class="role-box"><div class="role-num">115</div><div class="role-label">Warehouse Staff</div></div>
                        <div class="role-box"><div class="role-num">76</div><div class="role-label">Sales/Support</div></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><h3>System Users</h3></div>
                <table>
                    <thead><tr><th>User</th><th>Role</th><th>Department</th><th>Status</th><th>Last Login</th><th style="text-align:right;">Actions</th></tr></thead>
                    <tbody id="users-table-body">
                    <?php foreach ($rows as $u): ?>
                        <tr id="user-row-<?= (int) $u['id'] ?>">
                            <td><div class="table-user"><div class="avatar"><?= strtoupper(substr($u['full_name'],0,1)) ?></div><div><strong><?= e($u['full_name']) ?></strong><br><span style="color:var(--slate-500);font-size:11.5px;"><?= e($u['email']) ?></span></div></div></td>
                            <td><?= e($u['role_name']) ?></td>
                            <td><?= e(ucfirst($u['department'] ?? '—')) ?></td>
                            <td><span class="badge <?= $u['is_active'] ? 'active' : 'suspended' ?>"><?= $u['is_active'] ? 'Active' : 'Suspended' ?></span></td>
                            <td><?= $u['last_login_at'] ? date('M j, g:i A', strtotime($u['last_login_at'])) : 'Never' ?></td>
                            <td style="text-align:right;white-space:nowrap;">
                                <button type="button" class="btn-action btn-edit-user" data-user='<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>' title="Edit user" style="border:none;background:transparent;cursor:pointer;color:var(--indigo-500);padding:4px 6px;border-radius:6px;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <?php if (($u['id'] ?? 0) !== ($_SESSION['user_id'] ?? 0)): ?>
                                    <button type="button" class="btn-action btn-delete-user" data-id="<?= (int) $u['id'] ?>" data-name="<?= e($u['full_name']) ?>" title="Deactivate user" style="border:none;background:transparent;cursor:pointer;color:var(--red);padding:4px 6px;border-radius:6px;margin-left:4px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="6">No users found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="card-head"><h3>Security Audit Log</h3><a href="#">Export CSV</a></div>
                <div class="log-item">
                    <div class="log-icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3a5 5 0 1 0 4 8h5v3h3v-3h2V8H13a5 5 0 0 0-5-5Zm-2 5a2 2 0 1 1 4 0 2 2 0 0 1-4 0Z"/></svg></div>
                    <div><strong>Admin</strong> logged in and reviewed system user accounts.<div class="log-ip">IP: <?= e($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?> · Just now</div></div>
                </div>
                <div class="log-item">
                    <div class="log-icon red"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 21 12 2l11 19H1Zm10-4h2v2h-2v-2Zm0-7h2v5h-2v-5Z"/></svg></div>
                    <div>Failed login attempt for user demo@autopartflow.com.<div class="log-ip">IP: 45.33.12.98 (External) · 1 hour ago</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add / Edit User Modal Dialog -->
<div id="userModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);align-items:center;justify-content:center;z-index:999;padding:16px;">
    <div class="card" style="width:520px;max-width:96vw;max-height:90vh;overflow-y:auto;box-shadow:0 10px 25px rgba(0,0,0,.2);margin:0;">
        <div class="card-head">
            <h3 id="userModalTitle">Add New User</h3>
            <span style="cursor:pointer;font-size:18px;color:var(--slate-500);" onclick="closeUserModal()">✕</span>
        </div>
        <form id="userForm" style="display:flex;flex-direction:column;gap:12px;">
            <input type="hidden" id="modal-user-id" name="id" value="0">
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Full Name *</label>
                    <input type="text" id="modal-user-name" name="full_name" required style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Username</label>
                    <input type="text" id="modal-user-uname" name="username" placeholder="Auto-generated if empty" style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Email Address *</label>
                    <input type="email" id="modal-user-email" name="email" required style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Phone Number</label>
                    <input type="tel" id="modal-user-phone" name="phone" placeholder="07XXXXXXXX" style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">System Role *</label>
                    <select id="modal-user-role" name="role_id" required style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;background:#fff;">
                        <?php foreach (($roles ?? []) as $r): ?>
                            <option value="<?= (int) $r['id'] ?>"><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Department</label>
                    <select id="modal-user-dept" name="department" style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;background:#fff;">
                        <option value="sales">Sales</option>
                        <option value="store">Store / Inventory</option>
                        <option value="admin">Administration</option>
                        <option value="delivery">Delivery</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Designation / Job Title</label>
                    <input type="text" id="modal-user-desig" name="designation" placeholder="e.g. Sales Representative" style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Base Salary (Rs.)</label>
                    <input type="number" id="modal-user-salary" name="base_salary" step="0.01" min="0" placeholder="0.00" style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Password <span id="pwd-hint" style="font-weight:normal;color:var(--slate-500);">(Min 6 chars)</span></label>
                    <input type="password" id="modal-user-password" name="password" style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:600;margin-bottom:4px;color:var(--slate-500);">Account Status</label>
                    <select id="modal-user-active" name="is_active" style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid var(--slate-300);font-size:13px;background:#fff;">
                        <option value="1">Active</option>
                        <option value="0">Suspended</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:14px;border-top:1px solid var(--slate-100);padding-top:14px;">
                <button type="button" class="btn" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="userSubmitBtn">Save User</button>
            </div>
        </form>
    </div>
</div>

<div id="admin-toast" style="position:fixed;bottom:24px;right:24px;background:var(--navy-900);color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:500;box-shadow:0 4px 14px rgba(0,0,0,.2);display:none;z-index:9999;"></div>

<script src="<?= asset('js/admin-charts.js') ?>"></script>
<script>
drawDonut('userDonut', <?= count($rows) ?>, Math.max(<?= count($rows) ?>, 1));

function showAdminToast(msg) {
    const toast = document.getElementById('admin-toast');
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3200);
}

function openAddUserModal() {
    document.getElementById('userForm').reset();
    document.getElementById('modal-user-id').value = '0';
    document.getElementById('userModalTitle').textContent = 'Add New Staff User';
    document.getElementById('pwd-hint').textContent = '(Min 6 chars, required)';
    document.getElementById('modal-user-password').required = true;
    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

function openEditUserModal(user) {
    document.getElementById('userForm').reset();
    document.getElementById('modal-user-id').value = user.id;
    document.getElementById('userModalTitle').textContent = 'Edit User — ' + user.full_name;
    document.getElementById('modal-user-name').value = user.full_name || '';
    document.getElementById('modal-user-uname').value = user.username || '';
    document.getElementById('modal-user-email').value = user.email || '';
    document.getElementById('modal-user-phone').value = user.phone || '';
    document.getElementById('modal-user-role').value = user.role_id || '1';
    document.getElementById('modal-user-dept').value = user.department || 'sales';
    document.getElementById('modal-user-desig').value = user.designation || '';
    document.getElementById('modal-user-salary').value = user.base_salary || '0';
    document.getElementById('modal-user-active').value = user.is_active ? '1' : '0';
    document.getElementById('modal-user-password').value = '';
    document.getElementById('modal-user-password').required = false;
    document.getElementById('pwd-hint').textContent = '(Leave empty to keep current)';
    document.getElementById('userModal').style.display = 'flex';
}

document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
        try {
            const user = JSON.parse(btn.getAttribute('data-user'));
            openEditUserModal(user);
        } catch(e) {
            console.error(e);
        }
    });
});

document.querySelectorAll('.btn-delete-user').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        if (!confirm('Are you sure you want to deactivate account for ' + name + '?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const baseUrl = (document.querySelector('meta[name="base-url"]')?.content || '/').replace(/\/$/, '');

        fetch(baseUrl + '/admin/users/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ id: Number(id), csrf_token: csrfToken })
        }).then(res => res.json())
        .then(json => {
            if (json.ok) {
                showAdminToast(json.message);
                const row = document.getElementById('user-row-' + id);
                if (row) {
                    const badge = row.querySelector('.badge');
                    if (badge) {
                        badge.className = 'badge suspended';
                        badge.textContent = 'Suspended';
                    }
                }
                setTimeout(() => { location.reload(); }, 1000);
            } else {
                alert(json.message || 'Failed to deactivate user.');
            }
        }).catch(() => {
            alert('Network error deactivating user.');
        });
    });
});

document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const baseUrl = (document.querySelector('meta[name="base-url"]')?.content || '/').replace(/\/$/, '');

    const payload = {
        id: Number(document.getElementById('modal-user-id').value || 0),
        full_name: document.getElementById('modal-user-name').value.trim(),
        username: document.getElementById('modal-user-uname').value.trim(),
        email: document.getElementById('modal-user-email').value.trim(),
        phone: document.getElementById('modal-user-phone').value.trim(),
        role_id: Number(document.getElementById('modal-user-role').value),
        department: document.getElementById('modal-user-dept').value,
        designation: document.getElementById('modal-user-desig').value.trim(),
        base_salary: Number(document.getElementById('modal-user-salary').value || 0),
        password: document.getElementById('modal-user-password').value,
        is_active: Number(document.getElementById('modal-user-active').value),
        csrf_token: csrfToken
    };

    const submitBtn = document.getElementById('userSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    fetch(baseUrl + '/admin/users/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(payload)
    }).then(res => res.json())
    .then(json => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save User';
        if (json.ok) {
            closeUserModal();
            showAdminToast(json.message);
            setTimeout(() => { location.reload(); }, 900);
        } else {
            alert(json.message || 'Failed to save user.');
        }
    }).catch(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save User';
        alert('Network error saving user.');
    });
});

// Hook top "+ Add User" button
document.querySelector('.page-head .btn-primary').onclick = openAddUserModal;
</script>
</body>
</html>