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
        $this->requireRole('shop_customer', 'Access denied. The Customer Dashboard is reserved for registered Shop Customers.');
        $this->db = Database::getConnection();
    }

    public function dashboard(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        // Fetch customer & shop profile linked to this user
        $stmt = $this->db->prepare(
            "SELECT c.*, s.shop_name, s.registration_no, s.credit_limit, s.credit_balance, s.payment_terms_days
             FROM users u
             LEFT JOIN customers c ON (c.email = u.email OR (u.phone IS NOT NULL AND c.phone = u.phone))
             LEFT JOIN shops s ON s.customer_id = c.id
             WHERE u.id = :user_id AND (c.deleted_at IS NULL OR c.deleted_at IS NULL)
             LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);
        $customer = $stmt->fetch();

        // Fallback profile if record not yet linked in database
        if (!$customer || empty($customer['customer_code'])) {
            $userStmt = $this->db->prepare("SELECT full_name, email, phone FROM users WHERE id = :id");
            $userStmt->execute(['id' => $userId]);
            $userData = $userStmt->fetch();

            $customer = [
                'id' => null,
                'name' => $userData['full_name'] ?? ($_SESSION['full_name'] ?? 'Valued Customer'),
                'shop_name' => ($userData['full_name'] ?? ($_SESSION['full_name'] ?? 'Customer')) . ' Auto Care',
                'customer_code' => 'CUS-' . str_pad((string) $userId, 5, '0', STR_PAD_LEFT),
                'email' => $userData['email'] ?? '',
                'phone' => $userData['phone'] ?? '',
                'credit_limit' => 300000.00,
                'credit_balance' => 0.00,
                'payment_terms_days' => 30,
            ];
        }

        // Fetch recent orders for this customer
        $orders = [];
        if (!empty($customer['id'])) {
            $orderStmt = $this->db->prepare(
                "SELECT o.id, o.order_number, o.total_amount, o.order_status, o.payment_status, o.created_at,
                        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
                 FROM orders o
                 WHERE o.customer_id = :customer_id AND o.deleted_at IS NULL
                 ORDER BY o.id DESC LIMIT 10"
            );
            $orderStmt->execute(['customer_id' => $customer['id']]);
            $orders = $orderStmt->fetchAll();
        }

        $this->view('customer/dashboard', [
            'title'    => 'Customer Dashboard | AutoPartFlow',
            'customer' => $customer,
            'orders'   => $orders,
            'flash'    => $this->getFlash(),
        ], 'main');
    }
}
