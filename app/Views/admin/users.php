<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Management | AutoPartFlow</title>
<style>
:root{
  --bg:#f4f5f9; --surface:#ffffff; --box:#f1f3f7; --text:#111827; --muted:#6b7280;
  --line:#eceef3; --accent:#5157d9; --link:#3f51d9; --btn:#111827;
  --ok:#16a34a; --ok-soft:#dcfce7; --bad:#dc2626; --bad-soft:#fde8e8;
  --key-soft:#eef0fb; --plus:#7c5ce0; --plus-soft:#ede9fe;
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:"Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif;font-size:15px}
button,input,select{font:inherit;color:inherit}
button{cursor:pointer}

/* ---- Sidebar (same as dashboard) ---- */
.app-shell{display:flex;min-height:100vh}
.sidebar{width:250px;flex-shrink:0;background:linear-gradient(180deg,#0b1220,#101a30);color:#cbd5e1;padding:20px 14px;position:sticky;top:0;height:100vh;overflow-y:auto}
.sidebar .brand{display:flex;align-items:center;gap:10px;padding:6px 8px 22px;color:#fff;font-weight:700}
.brand-title{font-size:16px}
.brand-sub{font-size:12px;color:#64748b;font-weight:500}
.nav-link{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;color:#b7c0dd;font-size:13.5px;font-weight:500;margin-bottom:2px;text-decoration:none}
.nav-link:hover{background:rgba(255,255,255,.06);color:#fff}
.nav-link.active{background:#4f5bd5;color:#fff}
.nav-icon{width:18px;height:18px;fill:currentColor;flex-shrink:0}
.main{flex:1;min-width:0}
.menu-btn{display:none;border:0;background:none;font-size:22px;padding:0 4px}
.overlay{display:none}

/* ---- Page ---- */
.topbar{background:var(--surface);display:flex;align-items:center;gap:1rem;padding:15px 30px}
.search{flex:0 1 475px;display:flex;align-items:center;gap:.7rem;background:var(--box);border-radius:10px;padding:10px 16px}
.search input{border:0;background:transparent;outline:none;width:100%;font-size:16px}
.spacer{flex:1}
.top-icons{display:flex;gap:12px;font-size:18px}
.avatar{width:36px;height:36px;border-radius:50%;background:var(--accent);color:#fff;display:grid;place-items:center;font-weight:600;flex-shrink:0}

.page{padding:32px 30px 40px;display:grid;gap:22px;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr) minmax(0,1.6fr);align-items:start}
.head{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem}
.head h1{margin:0;font-size:28px;font-weight:700}
.head p{margin:8px 0 0;color:var(--muted)}
.btn{border:0;border-radius:8px;padding:11px 20px;font-weight:700;background:var(--btn);color:#fff;white-space:nowrap;font-size:16px}
.btn.ghost{background:var(--box);color:var(--text)}
.btn.danger{background:var(--bad)}
.link{border:0;background:none;color:var(--link);font-weight:500;padding:0;font-size:15px}

.card{background:var(--surface);border-radius:16px;padding:26px 25px;box-shadow:0 1px 3px rgba(17,24,39,.05)}
.card h2{margin:0 0 20px;font-size:19px;font-weight:600;display:flex;justify-content:space-between;align-items:center;gap:1rem}
.users-card{grid-column:1 / span 2}
.log-card{grid-column:3}

.donut{display:grid;place-items:center;position:relative;margin:0 auto 18px;width:184px;height:184px}
.donut svg{transform:rotate(-90deg)}
.donut .c{position:absolute;text-align:center}
.donut .c b{display:block;font-size:24px}
.donut .c span{font-size:14px;color:var(--muted)}
.legend{display:flex;justify-content:center;gap:16px;color:var(--muted);font-size:14px}

.roles{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.role{border:1px solid transparent;background:var(--box);border-radius:12px;padding:16px 6px;text-align:center}
.role b{display:block;font-size:21px}
.role span{font-size:13px;color:var(--muted)}
.role[aria-pressed="true"]{border-color:var(--accent)}

.table-wrap{overflow-x:auto}
.table-wrap table{width:100%;border-collapse:collapse;min-width:620px}
.table-wrap th{text-align:left;font-size:13px;font-weight:600;color:var(--muted);text-transform:uppercase;padding:10px 15px;border-bottom:1px solid var(--line)}
.table-wrap td{padding:16px 15px;border-bottom:1px solid var(--line);vertical-align:middle;font-size:15px}
.who{display:flex;align-items:center;gap:12px}
.who .avatar{width:38px;height:38px}
.who b{display:block;font-weight:600}
.who small{color:var(--muted);font-size:13px}
.pill{border-radius:999px;padding:4px 12px;font-size:14px;font-weight:600;border:0}
.pill.on{background:var(--ok-soft);color:var(--ok)}
.pill.off{background:var(--box);color:var(--muted)}
.actions{display:flex;gap:4px}
.icon-btn{border:0;background:none;padding:6px;border-radius:6px;color:var(--muted);display:grid;place-items:center}
.icon-btn:hover{background:var(--box)}
.icon-btn.del:hover{color:var(--bad)}
.empty{text-align:center;color:var(--muted);padding:24px}

.log{list-style:none;margin:0;padding:0;max-height:420px;overflow-y:auto}
.log li{display:flex;gap:12px;padding:14px 0;border-bottom:1px solid var(--line)}
.log li:last-child{border-bottom:0}
.log .ic{width:34px;height:34px;flex-shrink:0;border-radius:8px;display:grid;place-items:center;font-size:15px}
.ic.update{background:var(--key-soft)}
.ic.delete{background:var(--bad-soft);color:var(--bad)}
.ic.create{background:var(--plus-soft);color:var(--plus);font-weight:700}
.log small{color:var(--muted);display:block;font-size:13px;margin-top:2px}

dialog{border:0;border-radius:16px;padding:0;width:min(440px,92vw)}
dialog::backdrop{background:rgba(17,24,39,.45)}
.dlg{padding:24px}
.dlg h3{margin:0 0 16px;font-size:20px}
.field{display:grid;gap:5px;margin-bottom:12px}
.field label{font-size:14px;font-weight:600}
.field input,.field select{border:1px solid var(--line);background:var(--box);border-radius:8px;padding:10px 12px;width:100%}
.err{color:var(--bad);font-size:13px;min-height:1em}
.dlg-foot{display:flex;justify-content:flex-end;gap:10px;margin-top:10px}
.toggle{display:flex;align-items:center;gap:8px;font-size:14px}

.toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);background:var(--btn);color:#fff;padding:10px 16px;border-radius:8px;opacity:0;pointer-events:none;transition:opacity .2s;z-index:50}
.toast.show{opacity:1}

@media (max-width:1300px){
  .page{grid-template-columns:1fr 1fr}
  .head,.users-card,.log-card{grid-column:span 2}
}
@media (max-width:860px){
  .sidebar{position:fixed;left:0;top:0;z-index:40;transform:translateX(-100%);transition:transform .2s}
  .sidebar.open{transform:none}
  .overlay.show{display:block;position:fixed;inset:0;background:rgba(15,23,42,.4);z-index:30}
  .menu-btn{display:block}
}
@media (max-width:700px){
  .page{grid-template-columns:1fr;padding:20px 16px}
  .head,.users-card,.log-card{grid-column:auto}
  .roles{grid-template-columns:repeat(2,1fr)}
  .topbar{padding:12px 16px}
</style>
<link rel="stylesheet" href="<?= asset('css/shared.css') ?>">
<link rel="icon" href="<?= asset('images/logo-icon.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<div class="app-shell">
    <?php require APP_PATH . '/Views/admin/partials/sidebar.php'; ?>
  <div class="overlay" id="overlay"></div>

  <div class="main">
    <header class="topbar">
      <button class="menu-btn" id="menuBtn" type="button" aria-label="Open menu">☰</button>
      <div class="search">
        <span>🔍</span>
        <input id="q" type="search" placeholder="Search users, roles...">
      </div>
      <div class="spacer"></div>
      <div class="top-icons"><span>🔔</span><span>📅</span></div>
      <a href="<?= url('profile') ?>" class="avatar" title="Edit Profile — <?= e($_SESSION['full_name'] ?? 'Admin') ?>" style="text-decoration:none;cursor:pointer;"><?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?></a>
    </header>

    <main class="page">
      <section class="head">
        <div>
          <h1>User Management</h1>
          <p>Control system access, roles, and review audit logs.</p>
        </div>
        <button class="btn" id="addBtn">+ Add User</button>
      </section>

      <section class="card">
        <h2>Active Users</h2>
        <div class="donut">
          <svg width="184" height="184" viewBox="0 0 184 184">
            <circle cx="92" cy="92" r="84" fill="none" stroke="var(--box)" stroke-width="12"/>
            <circle id="arc" cx="92" cy="92" r="84" fill="none" stroke="var(--accent)" stroke-width="12" stroke-dasharray="0 999"/>
          </svg>
          <div class="c"><b id="total">0</b><span>Total</span></div>
        </div>
        <div class="legend">
          <span>● Active (<span id="nAct">0</span>)</span>
          <span>○ Inactive (<span id="nIn">0</span>)</span>
        </div>
      </section>

      <section class="card">
        <h2>Role Distribution <button class="link" id="manageRoles">Manage Roles</button></h2>
        <div class="roles" id="roles"></div>
      </section>

      <section class="card users-card">
        <h2>System Users</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody id="rows"></tbody>
          </table>
        </div>
      </section>

      <section class="card log-card">
        <h2>Security Audit Log <button class="link" id="exportBtn">⬇ Export CSV</button></h2>
        <ul class="log" id="log"></ul>
      </section>
    </main>
  </div>
</div>

<dialog id="userDlg">
  <form class="dlg" id="userForm" novalidate>
    <h3 id="dlgTitle">Add user</h3>
    <div class="field">
      <label for="fName">Full name</label>
      <input id="fName" autocomplete="off">
      <span class="err" id="eName"></span>
    </div>
    <div class="field">
      <label for="fEmail">Email</label>
      <input id="fEmail" type="email" autocomplete="off">
      <span class="err" id="eEmail"></span>
    </div>
    <div class="field">
      <label for="fRole">Role</label>
      <select id="fRole"></select>
    </div>
    <label class="toggle"><input type="checkbox" id="fActive" checked> Account is active</label>
    <div class="dlg-foot">
      <button type="button" class="btn ghost" id="cancelBtn">Cancel</button>
      <button type="submit" class="btn" id="saveBtn">Add user</button>
    </div>
  </form>
</dialog>

<dialog id="delDlg">
  <div class="dlg">
    <h3>Delete user?</h3>
    <p id="delText" style="margin:0 0 16px;color:var(--muted)"></p>
    <div class="dlg-foot">
      <button class="btn ghost" id="delCancel">Cancel</button>
      <button class="btn danger" id="delConfirm">Delete user</button>
    </div>
  </div>
</dialog>

<div class="toast" id="toast"></div>

<script>
// Role cards shown on the page (same 4 as the design)
const CARD_ROLES = [
  {role:"Administrator",   label:"Administrators"},
  {role:"Manager",         label:"Managers"},
  {role:"Warehouse Staff", label:"Warehouse Staff"},
  {role:"Sales/Support",   label:"Sales/Support"}
];
const ALL_ROLES = ["Business Owner", ...CARD_ROLES.map(r => r.role)];
const KEY = "um-state-v2";

let state = load() || {
  users:[{id:uid(),name:"System Administrator",email:"admin@smartauto.lk",role:"Business Owner",active:true,lastLogin:Date.now()}],
  log:[]
};
let roleFilter = null, editingId = null, deletingId = null;

function uid(){return Math.random().toString(36).slice(2,10)}
function load(){try{return JSON.parse(localStorage.getItem(KEY))}catch(e){return null}}
function save(){try{localStorage.setItem(KEY,JSON.stringify(state))}catch(e){}}
function esc(s){return String(s).replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]))}
function fmtDate(t){return t?new Date(t).toLocaleString("en-US",{month:"short",day:"numeric",hour:"numeric",minute:"2-digit"}):"Never"}
function ago(t){
  const m=Math.round((Date.now()-t)/60000);
  if(m<1)return "Just now"; if(m<60)return m+" mins ago";
  const h=Math.round(m/60); if(h<24)return h+(h===1?" hour ago":" hours ago");
  return fmtDate(t);
}
function audit(type,text){
  state.log.unshift({type,text,at:Date.now(),by:"Admin"});
  state.log = state.log.slice(0,200);
}

function render(){
  const q = document.getElementById("q").value.trim().toLowerCase();
  const list = state.users.filter(u =>
    (!roleFilter || u.role === roleFilter) &&
    (!q || [u.name,u.email,u.role].some(v => v.toLowerCase().includes(q)))
  );

  // Active users donut
  const total = state.users.length, act = state.users.filter(u => u.active).length;
  document.getElementById("total").textContent = total;
  document.getElementById("nAct").textContent = act;
  document.getElementById("nIn").textContent = total - act;
  const C = 2*Math.PI*84;
  document.getElementById("arc").setAttribute("stroke-dasharray", `${C*(total?act/total:0)} ${C}`);

  // Role cards
  document.getElementById("roles").innerHTML = CARD_ROLES.map(r => {
    const n = state.users.filter(u => u.role === r.role).length;
    return `<button class="role" data-role="${esc(r.role)}" aria-pressed="${roleFilter===r.role}"><b>${n}</b><span>${esc(r.label)}</span></button>`;
  }).join("");

  // Users table
  document.getElementById("rows").innerHTML = list.length ? list.map(u => `
    <tr>
      <td><div class="who"><div class="avatar">${esc((u.name[0]||"?").toUpperCase())}</div><div><b>${esc(u.name)}</b><small>${esc(u.email)}</small></div></div></td>
      <td>${esc(u.role)}</td>
      <td><button class="pill ${u.active?"on":"off"}" data-toggle="${u.id}" title="Click to change">${u.active?"Active":"Inactive"}</button></td>
      <td>${fmtDate(u.lastLogin)}</td>
      <td><div class="actions">
        <button class="icon-btn" data-edit="${u.id}" title="Edit">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4v16h16v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>
        <button class="icon-btn del" data-del="${u.id}" title="Delete">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
      </div></td>
    </tr>`).join("")
    : `<tr><td colspan="5" class="empty">No users found.</td></tr>`;

  // Audit log
  const icons = {update:"🔑", delete:"⚠", create:"+"};
  document.getElementById("log").innerHTML = state.log.length ? state.log.slice(0,50).map(l => `
    <li><div class="ic ${l.type}">${icons[l.type]}</div>
    <div><div><b>${esc(l.by)}</b> ${esc(l.text)}</div><small>${ago(l.at)}</small></div></li>`).join("")
    : `<li class="empty" style="display:block">No activity yet.</li>`;
}

// ---- Add / Edit
const dlg = document.getElementById("userDlg");
document.getElementById("fRole").innerHTML = ALL_ROLES.map(r => `<option>${esc(r)}</option>`).join("");

function openForm(u){
  editingId = u ? u.id : null;
  document.getElementById("dlgTitle").textContent = u ? "Edit user" : "Add user";
  document.getElementById("saveBtn").textContent = u ? "Save changes" : "Add user";
  document.getElementById("fName").value = u ? u.name : "";
  document.getElementById("fEmail").value = u ? u.email : "";
  document.getElementById("fRole").value = u ? u.role : "Sales/Support";
  document.getElementById("fActive").checked = u ? u.active : true;
  document.getElementById("eName").textContent = "";
  document.getElementById("eEmail").textContent = "";
  dlg.showModal();
}

document.getElementById("userForm").addEventListener("submit", e => {
  e.preventDefault();
  const name = document.getElementById("fName").value.trim();
  const email = document.getElementById("fEmail").value.trim().toLowerCase();
  const role = document.getElementById("fRole").value;
  const active = document.getElementById("fActive").checked;

  let ok = true;
  document.getElementById("eName").textContent = name ? "" : (ok=false, "Enter the user's full name.");
  let emailErr = "";
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) emailErr = "Enter a valid email address.";
  else if(state.users.some(u => u.email===email && u.id!==editingId)) emailErr = "Another user already has this email.";
  if(emailErr) ok = false;
  document.getElementById("eEmail").textContent = emailErr;
  if(!ok) return;

  if(editingId){
    const u = state.users.find(x => x.id === editingId);
    const changes = [];
    if(u.role !== role) changes.push(`role to ${role}`);
    if(u.active !== active) changes.push(active ? "status to Active" : "status to Inactive");
    if(u.name !== name || u.email !== email) changes.push("profile details");
    Object.assign(u, {name,email,role,active});
    if(changes.length) audit("update", `changed ${changes.join(", ")} for ${email}.`);
    toast("Changes saved");
  } else {
    state.users.push({id:uid(),name,email,role,active,lastLogin:null});
    audit("create", `created new user account ${email}.`);
    toast("User added");
  }
  save(); dlg.close(); render();
});
document.getElementById("cancelBtn").onclick = () => dlg.close();
document.getElementById("addBtn").onclick = () => openForm(null);

// ---- Delete
const delDlg = document.getElementById("delDlg");
document.getElementById("delCancel").onclick = () => delDlg.close();
document.getElementById("delConfirm").onclick = () => {
  const u = state.users.find(x => x.id === deletingId);
  if(u){
    state.users = state.users.filter(x => x.id !== deletingId);
    audit("delete", `deleted user account ${u.email}.`);
    save(); toast("User deleted"); render();
  }
  delDlg.close();
};

// ---- Table clicks
document.getElementById("rows").addEventListener("click", e => {
  const b = e.target.closest("button"); if(!b) return;
  if(b.dataset.edit) openForm(state.users.find(u => u.id === b.dataset.edit));
  if(b.dataset.del){
    const u = state.users.find(x => x.id === b.dataset.del);
    if(u.role==="Business Owner" && state.users.filter(x => x.role==="Business Owner").length===1){
      toast("You can't delete the only Business Owner."); return;
    }
    deletingId = u.id;
    document.getElementById("delText").textContent = `${u.name} (${u.email}) will lose access. This can't be undone.`;
    delDlg.showModal();
  }
  if(b.dataset.toggle){
    const u = state.users.find(x => x.id === b.dataset.toggle);
    u.active = !u.active;
    audit("update", `changed status to ${u.active?"Active":"Inactive"} for ${u.email}.`);
    save(); render();
  }
});

// ---- Role filter + search
document.getElementById("roles").addEventListener("click", e => {
  const b = e.target.closest(".role"); if(!b) return;
  roleFilter = roleFilter === b.dataset.role ? null : b.dataset.role;
  render();
});
document.getElementById("manageRoles").onclick = () => { roleFilter = null; render(); };
document.getElementById("q").addEventListener("input", render);

// ---- Export CSV (downloads a file)
document.getElementById("exportBtn").onclick = () => {
  const rows = [["Time","User","Action"], ...state.log.map(l => [new Date(l.at).toLocaleString(), l.by, l.text])];
  const csv = rows.map(r => r.map(v => `"${String(v).replace(/"/g,'""')}"`).join(",")).join("\n");
  const a = document.createElement("a");
  a.href = URL.createObjectURL(new Blob([csv], {type:"text/csv"}));
  a.download = "audit-log.csv";
  a.click();
};

// ---- Mobile sidebar menu
const sidebar = document.getElementById("admin-sidebar") || document.getElementById("sidebar");
const overlay = document.getElementById("overlay");
function closeMenu(){ if (sidebar) sidebar.classList.remove("open"); if (overlay) overlay.classList.remove("show"); }
const menuBtn = document.getElementById("menuBtn");
if (menuBtn && sidebar && overlay) {
  menuBtn.onclick = () => { sidebar.classList.add("open"); overlay.classList.add("show"); };
  overlay.onclick = closeMenu;
}

let tt;
function toast(m){
  const t = document.getElementById("toast");
  t.textContent = m; t.classList.add("show");
  clearTimeout(tt); tt = setTimeout(() => t.classList.remove("show"), 2500);
}

render();
</script>
</body>
</html>