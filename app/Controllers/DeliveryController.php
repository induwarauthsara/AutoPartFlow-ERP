<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Delivery;
use App\Models\Order;

class DeliveryController extends Controller
{
    public function status(): void
    {
        $orderNumber = trim((string) $this->input('order_number', ''));
        $phone = trim((string) $this->input('phone', ''));
        $delivery = null;
        $error = null;

        if ($orderNumber !== '') {
            $orderModel = new Order();
            $customer = $this->currentCustomer($orderModel);
            $deliveryModel = new Delivery();

            if ($customer) {
                $delivery = $deliveryModel->getForOrderAndCustomer($orderNumber, (int) $customer['id']);
            } elseif ($phone !== '') {
                $delivery = $deliveryModel->getForOrderAndPhone($orderNumber, $phone);
            } else {
                $error = 'Enter the phone number used when placing the order.';
            }

            if (!$delivery && !$error) {
                $error = 'No delivery record was found for that verified order.';
            }
        }

        $this->view('orders/delivery-status', [
            'title' => 'Delivery Status | AutoPartFlow',
            'orderNumber' => $orderNumber,
            'phone' => $phone,
            'delivery' => $delivery,
            'error' => $error,
            'customer' => $this->currentCustomer(new Order()),
        ], 'public');
    }


    /**
     * Staff-only delivery assignment. The assigned employee must belong to the delivery department.
     */
    public function assign(): void
    {
        $role = (string) ($_SESSION['role_slug'] ?? '');
        if (!in_array($role, ['owner', 'store_manager'], true)) {
            $this->json(['status' => 'error', 'message' => 'You are not authorized to assign deliveries.'], 403);
            return;
        }

        $data = $_POST;
        if (empty($data)) {
            $raw = json_decode(file_get_contents('php://input'), true);
            $data = is_array($raw) ? $raw : [];
        }

        $token = (string) ($data['csrf_token'] ?? '');
        if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            $this->json(['status' => 'error', 'message' => 'Invalid or expired security token.'], 419);
            return;
        }

        $deliveryId = (int) ($data['delivery_id'] ?? 0);
        $employeeId = (int) ($data['delivery_rep_id'] ?? 0);
        $scheduledDate = trim((string) ($data['scheduled_date'] ?? ''));
        if ($deliveryId <= 0 || $employeeId <= 0) {
            $this->json(['status' => 'error', 'message' => 'Delivery and delivery representative are required.'], 422);
            return;
        }
        if ($scheduledDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $scheduledDate)) {
            $this->json(['status' => 'error', 'message' => 'Invalid scheduled date.'], 422);
            return;
        }

        $deliveryModel = new Delivery();
        $result = $deliveryModel->assignDelivery($deliveryId, $employeeId, $scheduledDate !== '' ? $scheduledDate : null);
        $this->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Staff-only delivery status update. Status changes are persisted in both
     * the delivery row and the delivery status history table.
     */
    public function updateStatus(): void
    {
        $role = (string) ($_SESSION['role_slug'] ?? '');
        if (!in_array($role, ['owner', 'store_manager'], true)) {
            $this->json(['status' => 'error', 'message' => 'You are not authorized to update delivery status.'], 403);
            return;
        }

        $data = $_POST;
        if (empty($data)) {
            $raw = json_decode(file_get_contents('php://input'), true);
            $data = is_array($raw) ? $raw : [];
        }

        $token = (string) ($data['csrf_token'] ?? '');
        if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            $this->json(['status' => 'error', 'message' => 'Invalid or expired security token.'], 419);
            return;
        }

        $deliveryId = (int) ($data['delivery_id'] ?? 0);
        $status = trim((string) ($data['status'] ?? ''));
        $allowed = ['pending', 'in_transit', 'delivered', 'failed', 'returned'];
        if ($deliveryId <= 0 || !in_array($status, $allowed, true)) {
            $this->json(['status' => 'error', 'message' => 'Invalid delivery status data.'], 422);
            return;
        }

        $deliveryModel = new Delivery();
        $result = $deliveryModel->updateStatus($deliveryId, $status, (int) ($_SESSION['user_id'] ?? 0));
        $this->json($result, $result['success'] ? 200 : 422);
    }

    private function currentCustomer(Order $orderModel): ?array
    {
        $userId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
        $role = (string) ($_SESSION['role_slug'] ?? '');
        if ($userId <= 0 || $role !== 'shop_customer') return null;
        return $orderModel->findCustomerForUser($userId);
    }
}
