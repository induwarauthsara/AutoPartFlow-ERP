<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class AdminController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $isLoginRoute = str_ends_with(rtrim($uri, '/'), '/admin/login');

        if (!$isLoginRoute) {
            $this->requireRole('owner', 'Access denied. The Admin workspace is reserved for Business Owners.');
        }

        $this->db = Database::getConnection();
    }

    /** GET /admin/dashboard */
    public function dashboard(): void
    {
        $salesByDay = $this->db->query(
            "SELECT DATE_FORMAT(sale_day,'%a') d, gross_sales total FROM v_daily_sales_summary
             WHERE sale_day >= CURDATE() - INTERVAL 6 DAY ORDER BY sale_day"
        )->fetchAll();

        $lowStock = $this->db->query(
            "SELECT * FROM v_low_stock_products ORDER BY quantity_on_hand ASC LIMIT 6"
        )->fetchAll();

        $labels = $salesByDay ? array_column($salesByDay, 'd') : ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        $values = $salesByDay ? array_map('floatval', array_column($salesByDay, 'total')) : [4200,5100,3900,6200,7300,8100,6700];

        $this->view('admin.dashboard', [
            'title' => 'Owner Dashboard - AutoPartFlow',
            'labels' => $labels,
            'values' => $values,
            'lowStock' => $lowStock,
        ], null);
    }

    /** GET /admin/users */
    public function users(): void
    {
        $userModel = new \App\Models\User();
        $rows = $userModel->allUsers();
        $roles = $userModel->allRoles();

        $this->view('admin.users', [
            'title' => 'User Management - AutoPartFlow',
            'rows' => $rows,
            'roles' => $roles,
        ], null);
    }

    /** POST /admin/users/save */
    public function saveUser(): void
    {
        $this->api(function (array $data): array {
            $userModel = new \App\Models\User();
            $operatorId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
            $res = $userModel->saveUser($data, $operatorId);
            return [
                'ok' => true,
                'message' => 'User ' . ($res['action'] === 'updated' ? 'updated' : 'created') . ' successfully.',
                'user' => $res,
                'users' => $userModel->allUsers(),
            ];
        });
    }

    /** POST /admin/users/delete */
    public function deleteUser(): void
    {
        $this->api(function (array $data): array {
            $userModel = new \App\Models\User();
            $id = (int) ($data['id'] ?? 0);
            $operatorId = (int) ($_SESSION['user_id'] ?? 0);
            $userModel->deleteUser($id, $operatorId);
            return [
                'ok' => true,
                'message' => 'User account deactivated successfully.',
                'users' => $userModel->allUsers(),
            ];
        });
    }

    /** GET /admin/employees */
    public function employees(): void
    {
        $stmt = $this->db->query(
            "SELECT e.*, u.full_name FROM employees e JOIN users u ON u.id = e.user_id
             WHERE e.deleted_at IS NULL ORDER BY e.created_at DESC LIMIT 8"
        );
        $rows = $stmt->fetchAll();

        $this->view('admin.employees', ['title' => 'Employee Management - AutoPartFlow', 'rows' => $rows], null);
    }

    /** GET /admin/reports */
    public function reports(): void
    {
        $performance = $this->db->query("SELECT * FROM v_employee_sales_performance ORDER BY total_revenue DESC LIMIT 5")->fetchAll();

        $this->view('admin.reports', [
            'title' => 'Reports & Analytics - AutoPartFlow',
            'performance' => $performance,
        ], null);
    }

    /** GET /admin/notifications */
    public function notifications(): void
    {
        $stmt = $this->db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
        $rows = $stmt->fetchAll();

        $this->view('admin.notifications', ['title' => 'Notification Center - AutoPartFlow', 'rows' => $rows], null);
    }

    /** GET /admin/settings */
    public function settings(): void
    {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($stmt->fetchAll() as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }

        $this->view('admin.settings', ['title' => 'Settings - AutoPartFlow', 'settings' => $settings], null);
    }
        /** GET /admin/login */
    public function loginPage(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect($this->dashboardUrlForRole((string) ($_SESSION['role_slug'] ?? '')));
        }
        $this->view('admin.login', ['title' => 'Login - AutoPartFlow', 'flash' => $this->getFlash()], null);
    }

    /** POST /admin/login */
    public function doLogin(): void
    {
        $db = Database::getConnection();
        $identity = trim((string) $this->input('username', $this->input('email', '')));
        $password = (string) $this->input('password', '');

        if ($identity === '' || $password === '') {
            $this->setFlash('error', 'Please enter your username or email and password.');
            $this->redirect('/admin/login');
        }

        $stmt = $db->prepare(
            "SELECT u.id, u.full_name, u.email, u.password_hash, u.role_id, u.is_active, r.slug AS role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE (u.email = :email_identity OR u.username = :username_identity) AND u.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([
            'email_identity'    => $identity,
            'username_identity' => $identity,
        ]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->setFlash('error', 'Invalid username/email or password.');
            $this->redirect('/admin/login');
        }

        if ($user['role_slug'] !== 'owner') {
            $this->setFlash('error', 'Access restricted to Business Owners only. Please sign in via the general login page.');
            $this->redirect('/login');
        }

        if (!$user['is_active']) {
            $this->setFlash('error', 'Your account is inactive. Contact an administrator.');
            $this->redirect('/admin/login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role_id'] = (int) $user['role_id'];
        $_SESSION['role_slug'] = (string) $user['role_slug'];
        $_SESSION['full_name'] = (string) $user['full_name'];

        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")->execute(['id' => $user['id']]);

        $this->setFlash('success', 'Successfully signed in as ' . $user['full_name'] . '.');
        $this->redirect('/admin/dashboard');
    }

    private function api(callable $action): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $token = (string) ($data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            $this->json(['ok' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 419);
            return;
        }

        try {
            $this->json($action($data));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }
}