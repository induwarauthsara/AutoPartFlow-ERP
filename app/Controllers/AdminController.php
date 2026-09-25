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
        $period = (string) ($_GET['period'] ?? 'monthly');
        if (!in_array($period, ['daily', 'monthly', 'ytd'], true)) {
            $period = 'monthly';
        }

        $today = new \DateTimeImmutable('today');

        // ---- Cards walata kaalaya (period eka anuwa wenas wenawa)
        if ($period === 'daily') {
            $from = $today;
            $to = $today;
            $prevFrom = $today->modify('-1 day');
            $prevTo = $prevFrom;
            $compareText = 'vs yesterday';
            $periodLabel = 'Today (' . $today->format('j M Y') . ')';
        } elseif ($period === 'ytd') {
            $from = $today->modify('first day of january this year');
            $to = $today;
            $prevFrom = $from->modify('-1 year');
            $prevTo = $today->modify('-1 year');
            $compareText = 'vs same period last year';
            $periodLabel = 'Year to date (' . $from->format('j M') . ' – ' . $today->format('j M Y') . ')';
        } else {
            $from = $today->modify('first day of this month');
            $to = $today;
            $prevFrom = $today->modify('first day of last month');
            $prevTo = $prevFrom->modify('last day of this month');
            $compareText = 'vs last month';
            $periodLabel = 'This month (' . $from->format('j M') . ' – ' . $today->format('j M Y') . ')';
        }

        $statsStmt = $this->db->prepare(
            "SELECT COALESCE(SUM(gross_sales), 0)        AS gross,
                    COALESCE(SUM(total_transactions), 0) AS tx,
                    COALESCE(SUM(total_discounts), 0)    AS disc
             FROM v_daily_sales_summary
             WHERE sale_day BETWEEN ? AND ?"
        );
        $statsStmt->execute([$from->format('Y-m-d'), $to->format('Y-m-d')]);
        $cur = $statsStmt->fetch();
        $statsStmt->execute([$prevFrom->format('Y-m-d'), $prevTo->format('Y-m-d')]);
        $prev = $statsStmt->fetch();

        $curGross = (float) $cur['gross'];  $prevGross = (float) $prev['gross'];
        $curTx    = (float) $cur['tx'];     $prevTx    = (float) $prev['tx'];
        $curDisc  = (float) $cur['disc'];   $prevDisc  = (float) $prev['disc'];
        $curAvg   = $curTx > 0 ? $curGross / $curTx : 0.0;
        $prevAvg  = $prevTx > 0 ? $prevGross / $prevTx : 0.0;

        $pct = fn(float $c, float $p): ?float => $p > 0 ? (($c - $p) / $p) * 100 : null;

        $cards = [
            ['icon' => 'Rs', 'label' => 'Gross Revenue',   'value' => 'Rs. ' . number_format($curGross, 2), 'change' => $pct($curGross, $prevGross), 'goodWhenUp' => true],
            ['icon' => '#',  'label' => 'Transactions',    'value' => number_format($curTx),                'change' => $pct($curTx, $prevTx),       'goodWhenUp' => true],
            ['icon' => 'Avg','label' => 'Average Sale',    'value' => 'Rs. ' . number_format($curAvg, 2),   'change' => $pct($curAvg, $prevAvg),     'goodWhenUp' => true],
            ['icon' => '%',  'label' => 'Discounts Given', 'value' => 'Rs. ' . number_format($curDisc, 2),  'change' => $pct($curDisc, $prevDisc),   'goodWhenUp' => false],
        ];

        // ---- Chart eka: hama welema pasugiya masa 6 (period eka anuwa wenas wenne nae)
        $chartKeys = [];
        $monthStart = $today->modify('first day of this month');
        for ($i = 5; $i >= 0; $i--) {
            $m = $monthStart->modify("-{$i} month");
            $chartKeys[$m->format('Y-m')] = $m->format('M');
        }
        $chartRows = $this->db->query(
            "SELECT DATE_FORMAT(sale_day, '%Y-%m') AS k, SUM(gross_sales) AS total
             FROM v_daily_sales_summary
             WHERE sale_day >= DATE_FORMAT(CURDATE() - INTERVAL 5 MONTH, '%Y-%m-01')
             GROUP BY k"
        )->fetchAll();
        $totals = array_column($chartRows, 'total', 'k');
        $chartValues = [];
        foreach (array_keys($chartKeys) as $k) {
            $chartValues[] = (float) ($totals[$k] ?? 0);
        }

        $performance = $this->db->query("SELECT * FROM v_employee_sales_performance ORDER BY total_revenue DESC LIMIT 5")->fetchAll();

        $this->view('admin.reports', [
            'title'       => 'Reports & Analytics - AutoPartFlow',
            'performance' => $performance,
            'period'      => $period,
            'periodLabel' => $periodLabel,
            'compareText' => $compareText,
            'cards'       => $cards,
            'chartTitle'  => 'Sales (Last 6 Months)',
            'chartLabels' => array_values($chartKeys),
            'chartValues' => $chartValues,
        ], null);
    }

    /** GET /admin/notifications */
    public function notifications(): void
    {
        $groups = $this->notifGroups();
        $type = (string) ($_GET['type'] ?? 'all');
        if (!isset($groups[$type])) {
            $type = 'all';
        }
        $status = (string) ($_GET['status'] ?? 'all');
        if (!in_array($status, ['all', 'unread', 'read'], true)) {
            $status = 'all';
        }

        $where = [];
        $params = [];
        if ($groups[$type]) {
            $where[] = 'type IN (' . implode(',', array_fill(0, count($groups[$type]), '?')) . ')';
            $params = array_merge($params, $groups[$type]);
        }
        if ($status === 'unread') {
            $where[] = 'is_read = 0';
        } elseif ($status === 'read') {
            $where[] = 'is_read = 1';
        }

        $sql = 'SELECT * FROM notifications'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY created_at DESC, id DESC LIMIT 50';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $unreadCount = (int) $this->db->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn();

        $flash = $_SESSION['notif_flash'] ?? null;
        unset($_SESSION['notif_flash']);

        $this->view('admin.notifications', [
            'title'        => 'Notification Center - AutoPartFlow',
            'rows'         => $rows,
            'filterType'   => $type,
            'filterStatus' => $status,
            'unreadCount'  => $unreadCount,
            'flash'        => $flash,
        ], null);
    }

    /** POST /admin/notifications/store */
    public function notificationStore(): void
    {
        $types = ['low_stock', 'new_order', 'pending_order', 'credit_due', 'purchase_arrival', 'delivery_update', 'system'];
        $type = (string) ($_POST['type'] ?? 'system');
        $title = trim((string) ($_POST['title'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $linkUrl = trim((string) ($_POST['link_url'] ?? ''));

        if (!in_array($type, $types, true)) {
            $this->notifBack('error', 'Choose a valid notification type.');
        }
        if ($title === '' || $message === '') {
            $this->notifBack('error', 'Enter a title and a message.');
        }
        if ($linkUrl !== '' && !preg_match('#^(/|https?://)#i', $linkUrl)) {
            $this->notifBack('error', 'The link must start with / or http.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO notifications (type, title, message, link_url) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$type, substr($title, 0, 200), $message, $linkUrl !== '' ? substr($linkUrl, 0, 255) : null]);

        $this->notifBack('success', 'Notification created.');
    }

    /** POST /admin/notifications/read  (read <-> unread) */
    public function notificationRead(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $this->db->prepare(
            'UPDATE notifications SET read_at = IF(is_read = 0, NOW(), NULL), is_read = 1 - is_read WHERE id = ?'
        );
        $stmt->execute([$id]);
        $this->notifBack(null, '');
    }

    /** POST /admin/notifications/read-all */
    public function notificationReadAll(): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0');
        $stmt->execute();
        $count = $stmt->rowCount();
        $this->notifBack('success', $count === 1 ? '1 notification marked as read.' : "{$count} notifications marked as read.");
    }

    /** POST /admin/notifications/delete */
    public function notificationDelete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $this->db->prepare('DELETE FROM notifications WHERE id = ?');
        $stmt->execute([$id]);
        $this->notifBack('success', 'Notification deleted.');
    }

    private function notifGroups(): array
    {
        return [
            'all'       => [],
            'critical'  => ['low_stock', 'credit_due'],
            'orders'    => ['new_order', 'pending_order', 'delivery_update'],
            'inventory' => ['low_stock', 'purchase_arrival'],
            'system'    => ['system'],
        ];
    }

    private function notifBack(?string $type, string $msg): void
    {
        if ($type !== null) {
            $_SESSION['notif_flash'] = ['type' => $type, 'msg' => $msg];
        }
        $q = [];
        $t = (string) ($_POST['return_type'] ?? 'all');
        $s = (string) ($_POST['return_status'] ?? 'all');
        if ($t !== 'all' && isset($this->notifGroups()[$t])) {
            $q['type'] = $t;
        }
        if (in_array($s, ['unread', 'read'], true)) {
            $q['status'] = $s;
        }
        $this->redirect('/admin/notifications' . ($q ? '?' . http_build_query($q) : ''));
        exit;
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
