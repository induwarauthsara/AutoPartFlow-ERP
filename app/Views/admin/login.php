<?php
/** @var array $flash */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Login') ?></title>
<style>
:root{--navy-900:#0b1220;--navy-800:#101a30;--indigo-500:#4f5bd5;--indigo-600:#3f4ac2;--slate-900:#0f172a;--slate-500:#64748b;--slate-300:#cbd5e1;--slate-100:#f1f5f9;--bg:#f5f6fb;--red:#dc2626;--red-bg:#fdecec;--green:#16a34a;--green-bg:#e7f8ee;}
*{box-sizing:border-box;}
body{margin:0;font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--slate-900);font-size:14px;}
.auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
.auth-card{width:100%;max-width:900px;background:#fff;border-radius:20px;box-shadow:0 4px 16px rgba(15,23,42,.08);display:flex;overflow:hidden;border:1px solid #eef0f5;}
.auth-visual{flex:1;background:linear-gradient(180deg,rgba(11,18,32,0) 45%,rgba(11,18,32,.65)),linear-gradient(160deg,#1c2544,#3a4a86);min-height:460px;display:flex;align-items:flex-end;padding:26px;color:#fff;}
.auth-visual .tag{font-size:13px;font-weight:600;background:rgba(255,255,255,.15);padding:6px 12px;border-radius:20px;}
.auth-form{flex:1;padding:46px 44px;display:flex;flex-direction:column;justify-content:center;}
.brand-mark{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,var(--indigo-500),#7c8cf0);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;}
.logo-row{display:flex;align-items:center;gap:9px;margin-bottom:22px;}
h2{margin:0 0 6px;font-size:22px;}
.subtitle{color:var(--slate-500);margin:0 0 26px;font-size:13.5px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;}
.form-control{width:100%;padding:10px 12px;border:1px solid var(--slate-300);border-radius:8px;background:#fff;}
.row-between{display:flex;justify-content:space-between;align-items:center;}
.link-sm{font-size:12.5px;font-weight:600;color:var(--indigo-500);text-decoration:none;}
.btn-primary{width:100%;padding:11px;border:none;border-radius:9px;background:var(--indigo-500);color:#fff;font-weight:600;font-size:14px;cursor:pointer;}
.alert{padding:12px 14px;border-radius:9px;font-size:13px;margin-bottom:16px;}
.alert-error{background:var(--red-bg);color:#a11414;}
.alert-success{background:var(--green-bg);color:#0f7a37;}
.auth-footer-note{text-align:center;margin-top:22px;padding-top:18px;border-top:1px solid var(--slate-100);font-size:13px;color:var(--slate-500);}
</style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-visual"><span class="tag">Real-time inventory. Zero guesswork.</span></div>
        <div class="auth-form">
            <div class="logo-row">
                <div class="brand-mark">AP</div>
                <strong>AutoPartFlow</strong>
            </div>
            <h2>Welcome Back</h2>
            <p class="subtitle">Please sign in to your account.</p>

            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= url('admin/login') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Email Address</label>
                    <input class="form-control" type="email" name="email" placeholder="name@company.com" required>
                </div>
                <div class="form-group">
                    <div class="row-between">
                        <label style="margin-bottom:0;">Password</label>
                    </div>
                    <input class="form-control" type="password" name="password" placeholder="••••••••" required>
                </div>
                <button class="btn-primary" type="submit">Sign In</button>
            </form>
            <div class="auth-footer-note">AutoPartFlow ERP — Admin Panel</div>
        </div>
    </div>
</div>
</body>
</html>
