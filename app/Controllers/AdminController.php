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
        /** POST /admin/employees/store */
    public function employeeStore(): void
    {
        $data = $this->employeeInput();
        $this->validateEmployee($data);

        $check = $this->db->prepare("SELECT id FROM employees WHERE employee_code = ?");
        $check->execute([$data['employee_code']]);
        if ($check->fetch()) {
            $this->employeeBack('error', 'Employee ID ' . $data['employee_code'] . ' is already used.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO employees (full_name, employee_code, designation, base_salary, commission_rate, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['full_name'], $data['employee_code'], $data['designation'],
            $data['base_salary'], $data['commission_rate'], $data['status'],
        ]);

        $this->employeeBack('success', $data['full_name'] . ' was added.');
    }

    /** POST /admin/employees/update */
    public function employeeUpdate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $data = $this->employeeInput();
        $this->validateEmployee($data);

        $check = $this->db->prepare("SELECT id FROM employees WHERE employee_code = ? AND id <> ?");
        $check->execute([$data['employee_code'], $id]);
        if ($check->fetch()) {
            $this->employeeBack('error', 'Employee ID ' . $data['employee_code'] . ' is already used.');
        }

        $stmt = $this->db->prepare(
            "UPDATE employees SET full_name = ?, employee_code = ?, designation = ?,
             base_salary = ?, commission_rate = ?, status = ? WHERE id = ?"
        );
        $stmt->execute([
            $data['full_name'], $data['employee_code'], $data['designation'],
            $data['base_salary'], $data['commission_rate'], $data['status'], $id,
        ]);

        $this->employeeBack('success', 'Changes to ' . $data['full_name'] . ' were saved.');
    }

    /** POST /admin/employees/delete */
    public function employeeDelete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $this->db->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);

        $this->employeeBack('success', 'Employee was deleted.');
    }

    private function employeeInput(): array
    {
        return [
            'full_name'       => trim((string) ($_POST['full_name'] ?? '')),
            'employee_code'   => trim((string) ($_POST['employee_code'] ?? '')),
            'designation'     => trim((string) ($_POST['designation'] ?? '')),
            'base_salary'     => (float) ($_POST['base_salary'] ?? 0),
            'commission_rate' => (float) ($_POST['commission_rate'] ?? 0),
            'status'          => (string) ($_POST['status'] ?? 'active'),
        ];
    }

    private function validateEmployee(array $d): void
    {
        if ($d['full_name'] === '' || $d['employee_code'] === '' || $d['designation'] === '') {
            $this->employeeBack('error', 'Fill in the name, employee ID and role.');
        }
        if ($d['base_salary'] < 0) {
            $this->employeeBack('error', 'Base salary cannot be negative.');
        }
        if ($d['commission_rate'] < 0 || $d['commission_rate'] > 10) {
            $this->employeeBack('error', 'Commission rate must be between 0 and 10.');
        }
        if (!in_array($d['status'], ['active', 'on_leave', 'inactive'], true)) {
            $this->employeeBack('error', 'Choose a valid status.');
        }
    }

    private function employeeBack(string $type, string $msg): void
    {
        $_SESSION['emp_flash'] = ['type' => $type, 'msg' => $msg];
        $this->redirect('/admin/employees');
        exit;
    }

    /** POST /admin/settings/save */
    public function settingsSave(): void
    {
        $fields = [
            'business_name'         => 'text',
            'business_email'        => 'email',
            'business_phone'        => 'text',
            'business_address'      => 'text',
            'invoice_prefix'        => 'text',
            'invoice_next_number'   => 'int',
            'invoice_due_days'      => 'int',
            'invoice_footer_note'   => 'text',
            'tax_enabled'           => 'bool',
            'tax_name'              => 'text',
            'tax_rate'              => 'percent',
            'tax_registration_no'   => 'text',
            'backup_frequency'      => ['off', 'daily', 'weekly', 'monthly'],
            'backup_retention_days' => 'int',
            'currency'              => ['LKR', 'USD'],
            'date_format'           => ['Y-m-d', 'd/m/Y', 'm/d/Y'],
            'low_stock_threshold'   => 'int',
        ];

        $tab = preg_replace('/[^a-z]/', '', (string) ($_POST['active_tab'] ?? 'business'));
        $clean = [];

        foreach ($fields as $key => $type) {
            $raw = trim((string) ($_POST[$key] ?? ''));
            $label = ucfirst(str_replace('_', ' ', $key));

            if (is_array($type)) {
                if (!in_array($raw, $type, true)) {
                    $this->settingsBack($tab, 'error', "Choose a valid value for {$label}.");
                }
                $clean[$key] = $raw;
                continue;
            }

            switch ($type) {
                case 'bool':
                    $clean[$key] = isset($_POST[$key]) ? '1' : '0';
                    break;
                case 'email':
                    if ($raw !== '' && !filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                        $this->settingsBack($tab, 'error', 'Enter a valid contact email.');
                    }
                    $clean[$key] = $raw;
                    break;
                case 'int':
                    if ($raw === '' || !ctype_digit($raw)) {
                        $this->settingsBack($tab, 'error', "{$label} must be a whole number.");
                    }
                    $clean[$key] = $raw;
                    break;
                case 'percent':
                    if (!is_numeric($raw) || (float) $raw < 0 || (float) $raw > 100) {
                        $this->settingsBack($tab, 'error', "{$label} must be between 0 and 100.");
                    }
                    $clean[$key] = number_format((float) $raw, 2, '.', '');
                    break;
                default:
                    $clean[$key] = substr($raw, 0, 500);
            }
        }

        if ($clean['business_name'] === '') {
            $this->settingsBack('business', 'error', 'Company name cannot be empty.');
        }

        $find   = $this->db->prepare("SELECT 1 FROM settings WHERE setting_key = ?");
        $update = $this->db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $insert = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");

        $this->db->beginTransaction();
        foreach ($clean as $key => $value) {
            $find->execute([$key]);
            if ($find->fetchColumn()) {
                $update->execute([$value, $key]);
            } else {
                $insert->execute([$key, $value]);
            }
            $find->closeCursor();
        }
        $this->db->commit();

        $this->settingsBack($tab, 'success', 'Settings saved.');
    }

    private function settingsBack(string $tab, string $type, string $msg): void
    {
        $_SESSION['settings_flash'] = ['type' => $type, 'msg' => $msg];
        $this->redirect('/admin/settings?tab=' . $tab);
        exit;
    }
}
