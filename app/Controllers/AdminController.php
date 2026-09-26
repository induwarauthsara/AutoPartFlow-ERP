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
        $isLoginRoute = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/login');

        if (!$isLoginRoute) {
            if (empty($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'owner') {
                header('Location: /admin/login');
                exit;
            }
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
        $stmt = $this->db->query(
            "SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL ORDER BY u.created_at DESC LIMIT 8"
        );
        $rows = $stmt->fetchAll();

        $this->view('admin.users', ['title' => 'User Management - AutoPartFlow', 'rows' => $rows], null);
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

    /** GET /admin/suppliers */
    public function suppliers(): void
    {
        $supplierModel = new \App\Models\Supplier();

        $this->view('admin.suppliers', [
            'title'    => 'Supplier Management - AutoPartFlow',
            'rows'     => $supplierModel->allWithSummary(),
            'summary'  => $supplierModel->summary(),
            'csrfToken' => csrf_token(),
        ], null);
    }

    /** GET /admin/purchases */
    public function purchases(): void
    {
        $purchaseModel = new \App\Models\Purchase();
        $orders = $purchaseModel->recentOrders();
        $isMock = !$orders;

        $this->view('admin.purchases', [
            'title'   => 'Purchase Management - AutoPartFlow',
            'summary' => $isMock ? [
                'pendingApproval' => 12,
                'inTransit' => 8,
                'receivedThisWeek' => 45,
                'fulfillmentRate' => 98,
            ] : $purchaseModel->summary(),
            'orders'  => $orders ?: $purchaseModel->mockOrders(),
            'isMock'  => $isMock,
            'csrfToken' => csrf_token(),
        ], null);
    }

    /** POST /admin/purchases/status */
    public function updatePurchaseStatus(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);
        $data = is_array($data) ? $data : $_POST;
        $token = (string) ($data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

        if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            $this->json(['ok' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 419);
        }

        try {
            (new \App\Models\Purchase())->updateStatus((int) ($data['order_id'] ?? 0), (string) ($data['status'] ?? ''));
            $this->json(['ok' => true, 'message' => 'Purchase order status updated.']);
        } catch (\InvalidArgumentException $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'The purchase order status could not be updated.'], 500);
        }
    }

    /** POST /admin/suppliers */
    public function createSupplier(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);
        $data = is_array($data) ? $data : $_POST;
        $token = (string) ($data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

        if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            $this->json(['ok' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 419);
        }

        try {
            $supplierModel = new \App\Models\Supplier();
            $supplier = $supplierModel->create($data);

            $this->json([
                'ok'      => true,
                'message' => "Supplier '{$supplier['company_name']}' created successfully.",
                'supplier' => $supplier,
                'summary' => $supplierModel->summary(),
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'The supplier could not be saved. Please try again.'], 500);
        }
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
        if (!empty($_SESSION['user_id']) && ($_SESSION['role_slug'] ?? '') === 'owner') {
            $this->redirect('/admin/dashboard');
        }
        $this->view('admin.login', ['title' => 'Login - AutoPartFlow', 'flash' => $this->getFlash()], null);
    }

    /** POST /admin/login */
    public function doLogin(): void
    {
        $db = Database::getConnection();
        $email = trim($this->input('email', ''));
        $password = (string) $this->input('password', '');

        $stmt = $db->prepare(
            "SELECT u.id, u.full_name, u.password_hash, u.role_id, u.is_active, r.slug AS role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash']) || $user['role_slug'] !== 'owner') {
            $this->setFlash('error', 'Invalid email or password.');
            $this->redirect('/admin/login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_slug'] = $user['role_slug'];
        $_SESSION['full_name'] = $user['full_name'];

        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")->execute(['id' => $user['id']]);

        $this->redirect('/admin/dashboard');
    }
}