<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function cart(): void
    {
        $this->view('orders/cart', [
            'title' => 'Shopping Cart | AutoPartFlow',
        ], 'public');
    }

    public function checkout(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('error', 'Please sign in to your customer account to confirm and place your order.');
            $this->redirect('/login?redirect=' . rawurlencode('/checkout'));
            return;
        }

        $orderModel = new Order();
        $customer = $this->currentCustomer($orderModel);

        $this->view('orders/checkout', [
            'title' => 'Checkout | AutoPartFlow',
            'customer' => $customer,
        ], null);
    }

    /**
     * Customer order management page.
     * Signed-in shop customers see their order list; guests can verify one order
     * with order number + phone number.
     */
    public function orders(): void
    {
        $orderModel = new Order();
        $customer = $this->currentCustomer($orderModel);
        $orderNumber = trim((string) $this->input('order_number', ''));
        $phone = trim((string) $this->input('phone', ''));
        $order = null;
        $orders = [];
        $message = null;

        if ($customer) {
            $orders = $orderModel->getOrdersForCustomer((int) $customer['id']);
            if ($orderNumber !== '') {
                $order = $orderModel->findOrderForCustomer($orderNumber, (int) $customer['id']);
                if (!$order) {
                    $message = 'That order could not be found in your account.';
                }
            }
        } elseif ($orderNumber !== '' || $phone !== '') {
            if ($orderNumber === '' || $phone === '') {
                $message = 'Enter both the order number and phone number to manage a guest order.';
            } else {
                $order = $orderModel->findOrderForPhone($orderNumber, $phone);
                if (!$order) {
                    $message = 'We could not verify that order. Check the order number and phone number.';
                }
            }
        }

        $this->view('orders/manage', [
            'title' => 'My Orders | AutoPartFlow',
            'customer' => $customer,
            'orders' => $orders,
            'order' => $order,
            'orderNumber' => $orderNumber,
            'phone' => $phone,
            'message' => $message,
            'flash' => $this->getFlash(),
        ], 'public');
    }

    public function placeOrder(): void
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            $data = $_POST;
        }

        $csrfToken = (string) ($data['csrf_token'] ?? '');
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
            $this->json(['status' => 'error', 'message' => 'Your session expired. Refresh the checkout page and try again.'], 419);
            return;
        }

        if (empty($_SESSION['user_id'])) {
            $this->json(['status' => 'error', 'message' => 'Please sign in to your customer account to place an order.'], 401);
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $deliveryAddress = trim((string) ($data['deliveryAddress'] ?? ''));
        $paymentMethod = trim((string) ($data['paymentMethod'] ?? ''));
        $items = $data['items'] ?? [];

        if ($deliveryAddress === '') {
            $this->json(['status' => 'error', 'message' => 'Delivery address is required.'], 422);
            return;
        }

        if ($paymentMethod !== 'cod') {
            $this->json(['status' => 'error', 'message' => 'Only Cash on Delivery is currently supported.'], 422);
            return;
        }

        if (!is_array($items) || $items === []) {
            $this->json(['status' => 'error', 'message' => 'Your cart is empty. Add at least one product before checkout.'], 422);
            return;
        }

        if (count($items) > 50) {
            $this->json(['status' => 'error', 'message' => 'Your cart contains too many different products.'], 422);
            return;
        }

        $orderModel = new Order();
        $accountCustomer = $orderModel->findCustomerForUser($userId);

        if (!$accountCustomer) {
            $this->json(['status' => 'error', 'message' => 'Your customer account could not be linked. Please contact support.'], 403);
            return;
        }

        // Account identity is authoritative
        $fullName = trim((string) ($accountCustomer['name'] ?? ''));
        if ($fullName === '') {
            $fullName = trim((string) ($_SESSION['full_name'] ?? 'Shop Customer'));
        }

        $phoneNumber = trim((string) ($accountCustomer['phone'] ?? ''));
        $inputPhone  = trim((string) ($data['phoneNumber'] ?? ''));

        // If phone is missing in user/customer profile, update it from input
        if ($phoneNumber === '' && $inputPhone !== '') {
            if (preg_match('/^(?:07\d{8}|0\d{9}|\+94\d{9})$/', $inputPhone)) {
                $phoneNumber = $inputPhone;
                $db = \App\Core\Database::getConnection();
                $db->prepare('UPDATE customers SET phone = :phone WHERE id = :id')->execute(['phone' => $phoneNumber, 'id' => $accountCustomer['id']]);
                $db->prepare('UPDATE users SET phone = :phone WHERE id = :id')->execute(['phone' => $phoneNumber, 'id' => $userId]);
            }
        }

        if ($phoneNumber === '' || !preg_match('/^(?:07\d{8}|0\d{9}|\+94\d{9})$/', $phoneNumber)) {
            $this->json(['status' => 'error', 'message' => 'Valid contact phone number is required (e.g., 0712345678).'], 422);
            return;
        }

        $email = (string) ($accountCustomer['email'] ?? ($_SESSION['email'] ?? ''));

        // Save delivery address to customer record if empty
        if (empty($accountCustomer['address']) && $deliveryAddress !== '') {
            $db = \App\Core\Database::getConnection();
            $db->prepare('UPDATE customers SET address = :addr WHERE id = :id')->execute(['addr' => $deliveryAddress, 'id' => $accountCustomer['id']]);
        }

        $result = $orderModel->createOrderWithItems(
            [
                'fullName' => $fullName,
                'phoneNumber' => $phoneNumber,
                'deliveryAddress' => $deliveryAddress,
                'userId' => $userId,
                'email' => $email,
            ],
            $items,
            $paymentMethod
        );

        if (!$result['success']) {
            $status = str_contains(strtolower((string) ($result['message'] ?? '')), 'available') ? 409 : 422;
            $this->json(['status' => 'error', 'message' => $result['message'] ?? 'Failed to place order.'], $status);
            return;
        }

        $this->json([
            'status' => 'success',
            'order_number' => $result['order_number'],
            'total_amount' => $result['total_amount'],
            'estimated_delivery' => $result['estimated_delivery'],
            'message' => 'Order placed successfully!',
        ]);
    }

    public function updateOrder(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Your session expired. Please try again.');
            $this->redirect('/orders');
        }

        $orderNumber = trim((string) $this->input('order_number', ''));
        $phone = trim((string) $this->input('phone', ''));
        $address = trim((string) $this->input('delivery_address', ''));
        $orderModel = new Order();
        $customer = $this->currentCustomer($orderModel);

        if ($customer) {
            $order = $orderModel->findOrderForCustomer($orderNumber, (int) $customer['id']);
        } else {
            $order = $orderModel->findOrderForPhone($orderNumber, $phone);
        }

        if (!$order) {
            $this->setFlash('error', 'Order could not be verified.');
            $this->redirect('/orders');
        }

        $result = $orderModel->updateDeliveryAddress((int) $order['id'], (int) $order['customer_id'], $address);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);

        $query = 'order_number=' . rawurlencode($orderNumber);
        if (!$customer) {
            $query .= '&phone=' . rawurlencode($phone);
        }
        $this->redirect('/orders?' . $query);
    }

    public function cancelOrder(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Your session expired. Please try again.');
            $this->redirect('/orders');
        }

        $orderNumber = trim((string) $this->input('order_number', ''));
        $phone = trim((string) $this->input('phone', ''));
        $orderModel = new Order();
        $customer = $this->currentCustomer($orderModel);

        if ($customer) {
            $order = $orderModel->findOrderForCustomer($orderNumber, (int) $customer['id']);
        } else {
            $order = $orderModel->findOrderForPhone($orderNumber, $phone);
        }

        if (!$order) {
            $this->setFlash('error', 'Order could not be verified.');
            $this->redirect('/orders');
        }

        $result = $orderModel->cancelOrder((int) $order['id'], (int) $order['customer_id']);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);

        $query = 'order_number=' . rawurlencode($orderNumber);
        if (!$customer) {
            $query .= '&phone=' . rawurlencode($phone);
        }
        $this->redirect('/orders?' . $query);
    }

    public function track(): void
    {
        $orderNumber = trim((string) $this->input('order_number', ''));
        $phone = trim((string) $this->input('phone', ''));
        $orderModel = new Order();
        $customer = $this->currentCustomer($orderModel);

        if ($orderNumber === '') {
            $this->view('orders/track', [
                'title' => 'Track Order | AutoPartFlow',
                'order' => null,
                'orderNumber' => '',
                'phone' => '',
                'customer' => $customer,
                'searched' => false,
                'message' => null,
            ], 'public');
            return;
        }

        if (!$customer && $phone === '') {
            $message = 'For guest tracking, enter the phone number used when placing the order.';
            $this->view('orders/track', [
                'title' => "Track Order #{$orderNumber} | AutoPartFlow",
                'order' => null, 'orderNumber' => $orderNumber, 'phone' => '',
                'customer' => null, 'searched' => true, 'message' => $message,
            ], 'public');
            return;
        }

        $order = $customer
            ? $orderModel->trackOrderForCustomer($orderNumber, (int) $customer['id'])
            : $orderModel->trackOrderForPhone($orderNumber, $phone);

        if ($this->input('format') === 'json' || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            if ($order) {
                $delivery = $order['delivery'] ?? null;
                $this->json([
                    'status' => 'success',
                    'order' => [
                        'order_number' => $order['order_number'],
                        'order_date' => $order['order_date'],
                        'status' => $order['status'],
                        'total_amount' => $order['total_amount'],
                        'payment_status' => $order['payment_status'],
                        'items' => $order['items'],
                        'delivery' => $delivery ? [
                            'delivery_number' => $delivery['delivery_number'],
                            'status' => $delivery['delivery_status'],
                            'scheduled_date' => $delivery['scheduled_date'],
                            'delivered_at' => $delivery['delivered_at'],
                            ] : null,
                    ],
                ]);
            }
            $this->json(['status' => 'error', 'message' => 'Order could not be verified.'], 404);
        }

        $this->view('orders/track', [
            'title' => "Track Order #{$orderNumber} | AutoPartFlow",
            'order' => $order,
            'orderNumber' => $orderNumber,
            'phone' => $phone,
            'customer' => $customer,
            'searched' => true,
            'message' => $order ? null : 'Order could not be verified. Check the order number and phone number.',
        ], 'public');
    }

    private function currentCustomer(Order $orderModel): ?array
    {
        $userId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
        $role = (string) ($_SESSION['role_slug'] ?? '');

        if ($userId <= 0 || $role !== 'shop_customer') {
            return null;
        }

        $customer = $orderModel->findCustomerForUser($userId);
        if (!$customer) {
            unset($_SESSION['customer_id']);
        }
        return $customer;
    }
}
