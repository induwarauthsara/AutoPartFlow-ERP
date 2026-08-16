<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/icons.php';

$auth = new Auth();
if ($auth->isLoggedIn()) {
    header('Location: /views/dashboard.php');
    exit;
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $v = new Validator($_POST);
        $v->required('email')->email('email')->required('password');

        if ($v->fails()) {
            $errors[] = $v->firstError();
        } else {
            $oldEmail = trim($_POST['email']);
            $result = $auth->attemptLogin($oldEmail, $_POST['password'], isset($_POST['remember']));
            if ($result['success']) {
                header('Location: /views/dashboard.php');
                exit;
            }
            $errors[] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - <?= APP_NAME ?></title>
<link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-visual">
            <svg class="warehouse-rack" viewBox="0 0 400 460" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <g stroke="#ffffff" stroke-width="2" fill="none">
                    <rect x="30" y="40" width="110" height="380"/><line x1="30" y1="130" x2="140" y2="130"/><line x1="30" y1="220" x2="140" y2="220"/><line x1="30" y1="310" x2="140" y2="310"/>
                    <rect x="260" y="40" width="110" height="380"/><line x1="260" y1="130" x2="370" y2="130"/><line x1="260" y1="220" x2="370" y2="220"/><line x1="260" y1="310" x2="370" y2="310"/>
                    <rect x="48" y="55" width="30" height="22" fill="#ffffff" fill-opacity=".25"/><rect x="90" y="55" width="30" height="22" fill="#ffffff" fill-opacity=".18"/>
                    <rect x="48" y="145" width="30" height="22" fill="#ffffff" fill-opacity=".2"/><rect x="90" y="235" width="30" height="22" fill="#ffffff" fill-opacity=".2"/>
                    <rect x="278" y="55" width="30" height="22" fill="#ffffff" fill-opacity=".22"/><rect x="320" y="145" width="30" height="22" fill="#ffffff" fill-opacity=".18"/>
                </g>
            </svg>
            <span class="tag">Real-time inventory. Zero guesswork.</span>
        </div>
        <div class="auth-form">
            <div class="logo-row">
                <div class="brand-mark" style="width:30px;height:30px;font-size:12px;"><?= substr(APP_NAME,0,2) ?></div>
                <strong style="font-size:15px;"><?= APP_NAME ?></strong>
            </div>
            <h2>Welcome Back</h2>
            <p class="subtitle">Please sign in to your account.</p>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= icon('alert-triangle') ?><span><?= htmlspecialchars($error) ?></span></div>
            <?php endforeach; ?>
            <?php if ($flash = Session::flash('error')): ?>
                <div class="alert alert-error"><?= icon('alert-triangle') ?><span><?= htmlspecialchars($flash) ?></span></div>
            <?php endif; ?>
            <?php if ($flash = Session::flash('success')): ?>
                <div class="alert alert-success"><?= icon('check-circle') ?><span><?= htmlspecialchars($flash) ?></span></div>
            <?php endif; ?>

            <form method="POST" action="/login.php" novalidate>
                <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input class="form-control" type="email" id="email" name="email" placeholder="name@company.com" value="<?= htmlspecialchars($oldEmail) ?>" required autofocus>
                </div>
                <div class="form-group">
                    <div class="row-between">
                        <label for="password" style="margin-bottom:0;">Password</label>
                        <a class="link-sm" href="/forgot_password.php">Forgot Password?</a>
                    </div>
                    <div class="input-wrap">
                        <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="toggle-eye" onclick="togglePassword()" aria-label="Show password">
                            <span id="eye-icon"><?= icon('eye') ?></span>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-row">
                        <input type="checkbox" name="remember"> Remember me for 30 days
                    </label>
                </div>
                <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;">Sign In</button>
            </form>

            <div class="auth-footer-note">
                Need an account? <a class="link-sm" href="mailto:admin@autopartflow.com">Contact Administrator</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(){
    const input = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
}
</script>
</body>
</html>
