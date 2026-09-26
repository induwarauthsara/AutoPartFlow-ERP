<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class CustomerController extends Controller
{
    public function dashboard(): void
    {
        if (empty($_SESSION['user_id']) || (string) ($_SESSION['role_slug'] ?? '') !== 'shop_customer') {
            $this->setFlash('error', 'Please sign in with a customer account to open the customer dashboard.');
            $this->redirect('/login');
        }

        $db = Database::getConnection();
        $customerId = (int) ($_SESSION['customer_id'] ?? 0);

        // Recover the customer link for older sessions/accounts using the signed-in user's email.
        if ($customerId <= 0) {
            $stmt = $db->prepare(
                "SELECT c.id
                 FROM customers c
                 INNER JOIN users u ON u.email = c.email
                 WHERE u.id = :user_id
                   AND c.customer_type = 'shop'
                   AND c.is_active = 1
                   AND c.deleted_at IS NULL
                 ORDER BY c.id DESC LIMIT 1"
            );
            $stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
            $customerId = (int) ($stmt->fetchColumn() ?: 0);
            if ($customerId > 0) {
                $_SESSION['customer_id'] = $customerId;
            }
        }

        if ($customerId <= 0) {
            $this->setFlash('error', 'Your customer profile is not linked correctly. Please contact an administrator.');
            $this->redirect('/logout');
        }

        $profileStmt = $db->prepare(
            "SELECT c.id, c.customer_code, c.name, c.contact_person, c.phone, c.email, c.address, c.city,
                    s.shop_name, s.credit_limit, s.credit_balance
             FROM customers c
             LEFT JOIN shops s ON s.customer_id = c.id AND s.deleted_at IS NULL
             WHERE c.id = :id AND c.deleted_at IS NULL LIMIT 1"
        );
        $profileStmt->execute(['id' => $customerId]);
        $customer = $profileStmt->fetch() ?: [];

        $statsStmt = $db->prepare(
            "SELECT COUNT(*) AS total_orders,
                    SUM(CASE WHEN status IN ('pending','confirmed','processing','ready') THEN 1 ELSE 0 END) AS active_orders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS completed_orders,
                    COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END), 0) AS total_value
             FROM orders
             WHERE customer_id = :customer_id AND deleted_at IS NULL"
        );
        $statsStmt->execute(['customer_id' => $customerId]);
        $stats = $statsStmt->fetch() ?: [];

        $ordersStmt = $db->prepare(
            "SELECT order_number, order_date, status, payment_status, total_amount
             FROM orders
             WHERE customer_id = :customer_id AND deleted_at IS NULL
             ORDER BY order_date DESC, id DESC LIMIT 5"
        );
        $ordersStmt->execute(['customer_id' => $customerId]);

        $this->view('customer/dashboard', [
            'title' => 'Customer Dashboard | AutoPartFlow',
            'customer' => $customer,
            'stats' => $stats,
            'recentOrders' => $ordersStmt->fetchAll(),
            'flash' => $this->getFlash(),
        ], 'public');
    }
}
