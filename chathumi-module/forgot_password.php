<?php
/**
 * Forgot Password — step 1
 * Since the project rules forbid external APIs (no SMS/Email gateway),
 * this generates a secure one-time reset link and — for demo/dev
 * purposes — displays it on screen / logs it, instead of emailing it.
 * In a real deployment you would plug in your mail server here.
 */
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/icons.php';

$db = Database::getInstance();
$errors = [];
$devResetLink = null; // shown only in this demo build instead of emailing

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $v = new Validator($_POST);
        $v->required('email')->email('email');

        if ($v->fails()) {
            $errors[] = $v->firstError();
        } else {
            $email = trim($_POST['email']);
            $user = $db->selectOne("SELECT user_id FROM users WHERE email = ? AND is_deleted = 0", 's', [$email]);

            // Always show the same success message whether or not the
            // account exists, so the form can't be used to enumerate emails.
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+' . RESET_TOKEN_TTL_MINUTES . ' minutes'));
                $db->execute(
                    "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?",
                    'ssi',
                    [password_hash($token, PASSWORD_DEFAULT), $expires, $user['user_id']]
                );
                $devResetLink = '/reset_password.php?uid=' . $user['user_id'] . '&token=' . $token;
            }
            Session::flash('reset_requested', '1');
        }
    }
}

$requested = Session::flash('reset_requested');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password - <?= APP_NAME ?></title>
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
            <span class="tag">We'll get you back in, securely.</span>
        </div>
        <div class="auth-form">
            <a class="link-sm" href="/login.php" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:18px;color:var(--slate-500);">
                <?= icon('arrow-left') ?> Back to sign in
            </a>

            <div class="auth-icon-circle"><?= icon('key') ?></div>
            <h2>Forgot Password?</h2>
            <p class="subtitle">No worries. Enter the email linked to your account and we'll send you a secure link to reset it.</p>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= icon('alert-triangle') ?><span><?= htmlspecialchars($error) ?></span></div>
            <?php endforeach; ?>

            <?php if ($requested): ?>
                <div class="alert alert-success">
                    <?= icon('check-circle') ?>
                    <span>If an account exists for that email, a password reset link has been generated. It expires in <?= RESET_TOKEN_TTL_MINUTES ?> minutes.</span>
                </div>
                <?php if ($devResetLink): ?>
                    <div class="alert alert-info">
                        <?= icon('info') ?>
                        <span>
                            <strong>Developer preview</strong> (no email gateway is configured in this build):<br>
                            <a class="link-sm" href="<?= htmlspecialchars($devResetLink) ?>"><?= htmlspecialchars($devResetLink) ?></a>
                        </span>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <form method="POST" action="/forgot_password.php" novalidate>
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input class="form-control" type="email" id="email" name="email" placeholder="name@company.com" required autofocus>
                    </div>
                    <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;">Send Reset Link</button>
                </form>
            <?php endif; ?>

            <div class="auth-footer-note">
                Remembered it? <a class="link-sm" href="/login.php">Sign in instead</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
