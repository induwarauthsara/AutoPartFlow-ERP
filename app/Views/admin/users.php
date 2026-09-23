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
                    <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $u): ?>
                        <tr>
                            <td><div class="table-user"><div class="avatar"><?= strtoupper(substr($u['full_name'],0,1)) ?></div><div><strong><?= e($u['full_name']) ?></strong><br><span style="color:var(--slate-500);font-size:11.5px;"><?= e($u['email']) ?></span></div></div></td>
                            <td><?= e($u['role_name']) ?></td>
                            <td><span class="badge <?= $u['is_active'] ? 'active' : 'suspended' ?>"><?= $u['is_active'] ? 'Active' : 'Suspended' ?></span></td>
                            <td><?= $u['last_login_at'] ? date('M j, g:i A', strtotime($u['last_login_at'])) : 'Never' ?></td>
                            <td><a href="#" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;color:var(--slate-500);text-decoration:none;" onmouseover="this.style.background='var(--slate-100)'" onmouseout="this.style.background='transparent'" title="Edit user"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="5">No users found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="card-head"><h3>Security Audit Log</h3><a href="#">Export CSV</a></div>
                <div class="log-item">
                    <div class="log-icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3a5 5 0 1 0 4 8h5v3h3v-3h2V8H13a5 5 0 0 0-5-5Zm-2 5a2 2 0 1 1 4 0 2 2 0 0 1-4 0Z"/></svg></div>
                    <div><strong>Admin</strong> changed permissions for role Warehouse Staff.<div class="log-ip">IP: 192.168.1.45 · 10 mins ago</div></div>
                </div>
                <div class="log-item">
                    <div class="log-icon red"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 21 12 2l11 19H1Zm10-4h2v2h-2v-2Zm0-7h2v5h-2v-5Z"/></svg></div>
                    <div>Failed login attempt for user alice.s@autopartflow.com.<div class="log-ip">IP: 45.33.12.98 (External) · 1 hour ago</div></div>
                </div>
                <div class="log-item">
                    <div class="log-icon"><svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M11 4h2v7h7v2h-7v7h-2v-7H4v-2h7V4Z"/></svg></div>
                    <div>System Admin created new user account mike.t@autopartflow.com.<div class="log-ip">IP: 10.0.0.5 · Yesterday, 16:30</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="addUserModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);align-items:center;justify-content:center;z-index:50;">
    <div class="card" style="width:400px;max-width:92vw;">
        <div class="card-head"><h3>Add New User</h3><span style="cursor:pointer;" onclick="document.getElementById('addUserModal').style.display='none'">✕</span></div>
        <p style="color:var(--slate-500);font-size:13px;">User creation is not available yet.</p>
        <button class="btn" style="width:100%;" onclick="document.getElementById('addUserModal').style.display='none'">Close</button>
    </div>
</div>
<script src="<?= asset('js/admin-charts.js') ?>"></script>
<script>
drawDonut('userDonut', <?= count($rows) ?>, Math.max(<?= count($rows) ?>, 1));
</script>
</body>
</html>