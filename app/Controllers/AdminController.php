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
        $this->db = Database::getConnection();
    }

    /** GET /admin/dashboard */
    public function dashboard(): void
    {
        $salesByDay = $this->db->query(
            "SELECT DATE_FORMAT(sale_date,'%a') d, SUM(amount) total FROM demo_sales
             WHERE sale_date >= CURDATE() - INTERVAL 6 DAY GROUP BY sale_date ORDER BY sale_date"
        )->fetchAll();

        $lowStock = $this->db->query(
            "SELECT * FROM demo_inventory_alerts WHERE status IN ('critical','low') ORDER BY status LIMIT 6"
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
            "SELECT u.*, r.role_name FROM users u JOIN roles r ON r.role_id = u.role_id
             WHERE u.is_deleted = 0 ORDER BY u.created_at DESC LIMIT 8"
        );
        $rows = $stmt->fetchAll();

        $this->view('admin.users', ['title' => 'User Management - AutoPartFlow', 'rows' => $rows], null);
    }

    /** GET /admin/employees */
    public function employees(): void
    {
        $stmt = $this->db->query(
            "SELECT e.*, u.full_name FROM employees e JOIN users u ON u.user_id = e.user_id
             WHERE e.is_deleted = 0 ORDER BY e.created_at DESC LIMIT 8"
        );
        $rows = $stmt->fetchAll();

        $this->view('admin.employees', ['title' => 'Employee Management - AutoPartFlow', 'rows' => $rows], null);
    }

    /** GET /admin/reports */
    public function reports(): void
    {
        $this->view('admin.reports', ['title' => 'Reports & Analytics - AutoPartFlow'], null);
    }

    /** GET /admin/notifications */
    public function notifications(): void
    {
        $stmt = $this->db->query("SELECT * FROM notifications ORDER BY created_at DESC");
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
}