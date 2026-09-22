<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function checkout(): void
    {
        $productModel = new Product();
        $sampleProducts = $productModel->catalog(['limit' => 4]);

        $this->view('orders/checkout', [
            'title'          => 'Checkout | AutoPartFlow',
            'sampleProducts' => $sampleProducts,
        ], null); // Render as standalone clean layout matching the Stitch checkout mockup
    }

    public function placeOrder(): void
    {
        // Parse request payload (JSON or Form POST)
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            $data = $_POST;
        }

        $fullName        = trim((string) ($data['fullName'] ?? ''));
        $phoneNumber     = trim((string) ($data['phoneNumber'] ?? ''));
        $deliveryAddress = trim((string) ($data['deliveryAddress'] ?? ''));
        $paymentMethod   = trim((string) ($data['paymentMethod'] ?? 'cod'));
        $items           = $data['items'] ?? [];

        // Validation
        if (empty($fullName)) {
            $this->json(['status' => 'error', 'message' => 'Full Name is required.'], 422);
            return;
        }

        // Sri Lankan phone regex (07XXXXXXXX or standard 10 digits)
        if (!preg_match('/^(?:07\d{8}|0\d{9}|\+94\d{9})$/', $phoneNumber)) {
            $this->json(['status' => 'error', 'message' => 'Valid 10-digit phone number is required (e.g., 0712345678).'], 422);
            return;
        }

        if (empty($deliveryAddress)) {
            $this->json(['status' => 'error', 'message' => 'Delivery address is required.'], 422);
            return;
        }

        if (empty($items) || !is_array($items)) {
            // Default to single sample product if empty
            $items = [
                ['code' => 'PRD-00001', 'name' => 'Front Brake Pad Set - Brembo', 'price' => 4500.00, 'qty' => 1]
            ];
        }

        $orderModel = new Order();
        $result = $orderModel->createOrderWithItems(
            [
                'fullName'        => $fullName,
                'phoneNumber'     => $phoneNumber,
                'deliveryAddress' => $deliveryAddress,
            ],
            $items,
            $paymentMethod
        );

        if ($result['success']) {
            $this->json([
                'status'             => 'success',
                'order_number'       => $result['order_number'],
                'total_amount'       => $result['total_amount'],
                'estimated_delivery' => $result['estimated_delivery'],
                'message'            => 'Order placed successfully!',
            ]);
        } else {
            $this->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Failed to place order. Please try again.',
            ], 500);
        }
    }

    public function track(): void
    {
        $orderNumber = trim((string) $this->input('order_number', ''));

        if (empty($orderNumber)) {
            $this->view('orders/track', [
                'title'       => 'Track Order | AutoPartFlow',
                'order'       => null,
                'orderNumber' => '',
                'searched'    => false,
            ], 'public');
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->trackOrder($orderNumber);

        if ($this->input('format') === 'json' || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            if ($order) {
                $this->json(['status' => 'success', 'order' => $order]);
            } else {
                $this->json(['status' => 'error', 'message' => "No order found with number: {$orderNumber}"], 404);
            }
            return;
        }

        $this->view('orders/track', [
            'title'       => "Track Order #{$orderNumber} | AutoPartFlow",
            'order'       => $order,
            'orderNumber' => $orderNumber,
            'searched'    => true,
        ], 'public');
    }
}
