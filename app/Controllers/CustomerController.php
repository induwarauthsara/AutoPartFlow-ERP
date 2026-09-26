<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class CustomerController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $this->requireRole('shop_customer', 'Please sign in with a shop customer account to access the customer dashboard.');
        $this->db = Database::getConnection();
    }

    public function dashboard(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $customerId = (int) ($_SESSION['customer_id'] ?? 0);

        // Fetch customer record linked to this user
        $stmt = $this->db->prepare(
            "SELECT c.*, s.shop_name, s.registration_no, s.credit_limit, s.credit_balance, s.payment_terms_days
             FROM users u
             LEFT JOIN customers c ON (c.email = u.email OR (u.phone IS NOT NULL AND c.phone = u.phone))
             LEFT JOIN shops s ON s.customer_id = c.id
             WHERE u.id = :user_id AND c.deleted_at IS NULL
             ORDER BY c.id DESC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);
        $customer = $stmt->fetch() ?: [];

        if (!empty($customer['id'])) {
            $customerId = (int) $customer['id'];
            $_SESSION['customer_id'] = $customerId;
        }

        // If not found in customers table, fallback from users session
        if (empty($customer) || empty($customer['customer_code'])) {
            $userStmt = $this->db->prepare("SELECT full_name, email, phone FROM users WHERE id = :id");
            $userStmt->execute(['id' => $userId]);
            $userData = $userStmt->fetch() ?: [];

            $customer = [
                'id' => $customerId > 0 ? $customerId : null,
                'name' => $userData['full_name'] ?? ($_SESSION['full_name'] ?? 'Customer'),
                'contact_person' => $userData['full_name'] ?? ($_SESSION['full_name'] ?? 'Customer'),
                'shop_name' => ($userData['full_name'] ?? ($_SESSION['full_name'] ?? 'Customer')) . ' Auto Care',
                'customer_code' => 'CUS-' . str_pad((string) $userId, 5, '0', STR_PAD_LEFT),
                'email' => $userData['email'] ?? ($_SESSION['email'] ?? ''),
                'phone' => $userData['phone'] ?? '',
                'address' => '',
                'city' => '',
                'credit_limit' => 300000.00,
                'credit_balance' => 0.00,
                'payment_terms_days' => 30,
            ];
        }

        // Calculate order statistics for stats cards
        $stats = [
            'total_orders' => 0,
            'active_orders' => 0,
            'completed_orders' => 0,
            'total_value' => 0.0,
        ];
        $recentOrders = [];

        if (!empty($customer['id'])) {
            $statsStmt = $this->db->prepare(
                "SELECT COUNT(*) AS total_orders,
                        SUM(CASE WHEN status IN ('pending','confirmed','processing','ready') THEN 1 ELSE 0 END) AS active_orders,
                        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS completed_orders,
                        COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END), 0) AS total_value
                 FROM orders
                 WHERE customer_id = :customer_id AND deleted_at IS NULL"
            );
            $statsStmt->execute(['customer_id' => $customer['id']]);
            $statsData = $statsStmt->fetch();
            if ($statsData) {
                $stats = [
                    'total_orders' => (int) ($statsData['total_orders'] ?? 0),
                    'active_orders' => (int) ($statsData['active_orders'] ?? 0),
                    'completed_orders' => (int) ($statsData['completed_orders'] ?? 0),
                    'total_value' => (float) ($statsData['total_value'] ?? 0.0),
                ];
            }

            $ordersStmt = $this->db->prepare(
                "SELECT id, order_number, order_date, status, payment_status, total_amount,
                        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = orders.id) AS item_count
                 FROM orders
                 WHERE customer_id = :customer_id AND deleted_at IS NULL
                 ORDER BY order_date DESC, id DESC LIMIT 10"
            );
            $ordersStmt->execute(['customer_id' => $customer['id']]);
            $recentOrders = $ordersStmt->fetchAll();
        }

        $this->view('customer/dashboard', [
            'title'        => 'Customer Dashboard | AutoPartFlow',
            'customer'     => $customer,
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
            'orders'       => $recentOrders,
            'flash'        => $this->getFlash(),
        ], 'public');
    }
}
