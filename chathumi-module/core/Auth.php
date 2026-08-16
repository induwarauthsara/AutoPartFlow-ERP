<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/../config/constants.php';

class Auth
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function isLoggedIn(): bool
    {
        return Session::has('user_id');
    }

    public function currentUser(): ?array
    {
        if (!$this->isLoggedIn()) return null;
        return $this->db->selectOne(
            "SELECT u.user_id, u.full_name, u.email, u.profile_image, u.role_id, r.role_name
             FROM users u JOIN roles r ON r.role_id = u.role_id
             WHERE u.user_id = ? AND u.is_deleted = 0",
            'i',
            [Session::get('user_id')]
        );
    }

    /**
     * Redirect to login if not authenticated, or to a "not authorised"
     * page if authenticated but the role isn't allowed on this page.
     * Call at the top of every protected view/controller.
     */
    public function requireRole(array $allowedRoleIds): array
    {
        if (!$this->isLoggedIn()) {
            Session::flash('error', 'Please sign in to continue.');
            header('Location: /login.php');
            exit;
        }
        $user = $this->currentUser();
        if (!$user || !in_array((int) $user['role_id'], $allowedRoleIds, true)) {
            http_response_code(403);
            echo '<h2 style="font-family:sans-serif;text-align:center;margin-top:80px;">403 — You do not have access to this page.</h2>';
            exit;
        }
        return $user;
    }

    public function attemptLogin(string $email, string $password, bool $remember = false): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user = $this->db->selectOne(
            "SELECT * FROM users WHERE email = ? AND is_deleted = 0",
            's',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->logActivity(null, 'Failed login attempt for ' . $email, 'auth', 'failed');
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        if ($user['status'] !== 'active') {
            $this->logActivity($user['user_id'], 'Login blocked — account ' . $user['status'], 'auth', 'failed');
            return ['success' => false, 'message' => 'Your account is ' . $user['status'] . '. Contact an administrator.'];
        }

        session_regenerate_id(true);
        Session::set('user_id', $user['user_id']);
        Session::set('role_id', $user['role_id']);

        $this->db->execute(
            "UPDATE users SET last_login = NOW(), last_login_ip = ? WHERE user_id = ?",
            'si',
            [$ip, $user['user_id']]
        );

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->db->execute("UPDATE users SET remember_token = ? WHERE user_id = ?", 'si', [password_hash($token, PASSWORD_DEFAULT), $user['user_id']]);
            setcookie('remember_me', $user['user_id'] . ':' . $token, time() + (REMEMBER_ME_DAYS * 86400), '/', '', false, true);
        }

        $this->logActivity($user['user_id'], 'Logged in', 'auth', 'success');
        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        $userId = Session::get('user_id');
        if ($userId) {
            $this->logActivity($userId, 'Logged out', 'auth', 'success');
            $this->db->execute("UPDATE users SET remember_token = NULL WHERE user_id = ?", 'i', [$userId]);
        }
        setcookie('remember_me', '', time() - 3600, '/');
        Session::destroy();
    }

    public function logActivity(?int $userId, string $action, string $module = 'general', string $status = 'success'): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->db->execute(
            "INSERT INTO activity_logs (user_id, action, module, ip_address, status) VALUES (?,?,?,?,?)",
            'issss',
            [$userId, $action, $module, $ip, $status]
        );
    }
}
