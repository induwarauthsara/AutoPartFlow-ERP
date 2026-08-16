<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/icons.php';

$db = Database::getInstance();
$auth = new Auth();

$uid = (int) ($_GET['uid'] ?? $_POST['uid'] ?? 0);
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$success = false;

function validResetUser(Database $db, int $uid, string $token): ?array
{
    if (!$uid || !$token) return null;
    $user = $db->selectOne("SELECT user_id, reset_token, reset_token_expires FROM users WHERE user_id = ? AND is_deleted = 0", 'i', [$uid]);
    if (!$user || !$user['reset_token'] || !$user['reset_token_expires']) return null;
    if (strtotime($user['reset_token_expires']) < time()) return null;
    if (!password_verify($token, $user['reset_token'])) return null;
    return $user;
}

$user = validResetUser($db, $uid, $token);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (!$user) {
        $errors[] = 'This reset link is invalid or has expired. Please request a new one.';
    } else {
        $v = new Validator($_POST);
        $v->required('password')->minLength('password', 8)
          ->required('confirm_password')->matches('confirm_password', 'password', 'Passwords do not match.');

        if ($v->fails()) {
            $errors[] = $v->firstError();
        } else {
            $db->execute(
                "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?",
                'si',
                [password_hash($_POST['password'], PASSWORD_DEFAULT), $uid]
            );
            $auth->logActivity($uid, 'Reset password via forgot-password link', 'auth', 'success');
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - <?= APP_NAME ?></title>
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
            <span class="tag">Choose a strong new password.</span>
        </div>
        <div class="auth-form">
            <div class="auth-icon-circle"><?= icon('lock') ?></div>
            <h2>Reset Your Password</h2>
            <p class="subtitle">Create a new password for your <?= APP_NAME ?> account.</p>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= icon('alert-triangle') ?><span><?= htmlspecialchars($error) ?></span></div>
            <?php endforeach; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= icon('check-circle') ?><span>Your password has been updated. You can now sign in.</span></div>
                <a class="btn btn-primary" href="/login.php" style="width:100%;justify-content:center;">Go to Sign In</a>
            <?php elseif (!$user): ?>
                <div class="alert alert-error"><?= icon('alert-triangle') ?><span>This reset link is invalid or has expired.</span></div>
                <a class="btn btn-outline" href="/forgot_password.php" style="width:100%;justify-content:center;">Request a New Link</a>
            <?php else: ?>
                <form method="POST" action="/reset_password.php" novalidate>
                    <input type="hidden" name="csrf" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="uid" value="<?= htmlspecialchars((string) $uid) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input class="form-control" type="password" id="password" name="password" placeholder="At least 8 characters" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input class="form-control" type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required minlength="8">
                    </div>
                    <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;">Update Password</button>
                </form>
            <?php endif; ?>

            <div class="auth-footer-note">
                <a class="link-sm" href="/login.php">Back to sign in</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
