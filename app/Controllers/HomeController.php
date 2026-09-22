<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'AutoPartFlow ERP | Enterprise Auto Parts Management',
            'flash' => $this->getFlash(),
        ]);
    }

    public function login(): void
    {
        // Already logged in? Send to the right place instead of showing the form again.
        if (!empty($_SESSION['user_id'])) {
            $this->redirect($this->homeRouteForRole((int) ($_SESSION['role_id'] ?? 0)));
        }

        $this->view('auth/login', [
            'title' => 'Sign In | AutoPartFlow ERP',
            'flash' => $this->getFlash(),
        ], 'main');
    }

    public function doLogin(): void
    {
        $db = Database::getConnection();

        $email = trim($this->input('email', ''));
        $password = (string) $this->input('password', '');

        if ($email === '' || $password === '') {
            $this->setFlash('error', 'Please enter both email and password.');
            $this->redirect('/login');
        }

        $stmt = $db->prepare(
            "SELECT u.id, u.full_name, u.email, u.password_hash, u.role_id, u.is_active, r.slug AS role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->setFlash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        if (!$user['is_active']) {
            $this->setFlash('error', 'Your account is inactive. Contact an administrator.');
            $this->redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['role_id']    = $user['role_id'];
        $_SESSION['role_slug']  = $user['role_slug'];
        $_SESSION['full_name']  = $user['full_name'];

        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")
           ->execute(['id' => $user['id']]);

        $this->setFlash('success', 'Successfully signed in as ' . $user['full_name'] . '.');
        $this->redirect($this->homeRouteForRole((int) $user['role_id']));
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }

    private function homeRouteForRole(int $roleId): string
    {
        // role_id 1 = Business Owner (see database/schema.sql seed data)
        return $roleId === 1 ? '/admin/dashboard' : '/sales';
    }
}
