<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDOException;

class Order extends Model
{
    protected string $table = 'orders';

    /**
     * Generate next order sequence number (e.g. ORD-00001).
     */
    public function getNextOrderNumber(): string
    {
        try {
            $stmt = $this->db->prepare("SELECT prefix, current_value, pad_length FROM sequences WHERE seq_type = 'order' FOR UPDATE");
            $stmt->execute();
            $seq = $stmt->fetch();

            if ($seq) {
                $nextVal = (int) $seq['current_value'] + 1;
                $update = $this->db->prepare("UPDATE sequences SET current_value = :val WHERE seq_type = 'order'");
                $update->execute(['val' => $nextVal]);

                return $seq['prefix'] . str_pad((string) $nextVal, (int) $seq['pad_length'], '0', STR_PAD_LEFT);
            }
        } catch (PDOException $e) {
            // Fallback if sequence table locking fails
        }

        return 'ORD-' . date('Y') . '-' . str_pad((string) rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get or create customer by phone and name.
     */
    public function getOrCreateCustomer(string $name, string $phone, string $address): int
    {
        // Check if customer with this phone number already exists
        $stmt = $this->db->prepare("SELECT id FROM customers WHERE phone = :phone AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['phone' => $phone]);
        $existing = $stmt->fetch();

        if ($existing) {
            return (int) $existing['id'];
        }

        // Generate customer code
        $seqStmt = $this->db->query("SELECT MAX(id) as max_id FROM customers");
        $maxRow = $seqStmt->fetch();
        $nextId = (int) ($maxRow['max_id'] ?? 0) + 1;
        $customerCode = 'CUS-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);

        $insertStmt = $this->db->prepare("
            INSERT INTO customers (customer_code, customer_type, name, phone, address, is_active)
            VALUES (:code, 'walking', :name, :phone, :address, 1)
        ");
        $insertStmt->execute([
            'code'    => $customerCode,
            'name'    => $name,
            'phone'   => $phone,
            'address' => $address,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Place order transactionally.
     */
    public function createOrderWithItems(array $customerData, array $items, string $paymentMethod): array
    {
        try {
            $this->db->beginTransaction();

            $customerId = $this->getOrCreateCustomer(
                $customerData['fullName'],
                $customerData['phoneNumber'],
                $customerData['deliveryAddress']
            );

            $orderNumber = $this->getNextOrderNumber();

            // Calculate item subtotals
            $subtotal = 0.00;
            $orderItems = [];

            $productModel = new Product();

            foreach ($items as $item) {
                $code = $item['code'] ?? '';
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $product = $productModel->findByCode($code);

                if ($product) {
                    $unitPrice = (float) $product['selling_price'];
                    $lineTotal = $unitPrice * $qty;
                    $subtotal += $lineTotal;

                    $orderItems[] = [
                        'product_id' => (int) $product['id'],
                        'quantity'   => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                } else {
                    // Fallback using submitted item price
                    $unitPrice = (float) ($item['price'] ?? 0);
                    $lineTotal = $unitPrice * $qty;
                    $subtotal += $lineTotal;

                    $orderItems[] = [
                        'product_id' => 1, // Fallback default seed product ID
                        'quantity'   => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }
            }

            $deliveryFee = 350.00;
            $taxAmount = 0.00;
            $totalAmount = $subtotal + $deliveryFee;

            $notes = "Payment Method: " . ($paymentMethod === 'card' ? 'Card Payment (Online)' : 'Cash on Delivery');

            // Insert into orders
            $orderStmt = $this->db->prepare("
                INSERT INTO orders (
                    order_number, customer_id, order_source, status,
                    subtotal, discount_amount, tax_amount, total_amount,
                    payment_status, delivery_address, notes
                ) VALUES (
                    :order_number, :customer_id, 'shop_portal', 'pending',
                    :subtotal, 0.00, :tax_amount, :total_amount,
                    'unpaid', :delivery_address, :notes
                )
            ");

            $orderStmt->execute([
                'order_number'     => $orderNumber,
                'customer_id'      => $customerId,
                'subtotal'         => $subtotal,
                'tax_amount'       => $taxAmount,
                'total_amount'     => $totalAmount,
                'delivery_address' => $customerData['deliveryAddress'],
                'notes'            => $notes,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            // Insert order items
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantity, unit_price, discount_amount, tax_rate, line_total
                ) VALUES (
                    :order_id, :product_id, :quantity, :unit_price, 0.00, 0.00, :line_total
                )
            ");

            foreach ($orderItems as $oi) {
                $itemStmt->execute([
                    'order_id'   => $orderId,
                    'product_id' => $oi['product_id'],
                    'quantity'   => $oi['quantity'],
                    'unit_price' => $oi['unit_price'],
                    'line_total' => $oi['line_total'],
                ]);
            }

            $this->db->commit();

            return [
                'success'            => true,
                'order_id'           => $orderId,
                'order_number'       => $orderNumber,
                'subtotal'           => $subtotal,
                'delivery_fee'       => $deliveryFee,
                'total_amount'       => $totalAmount,
                'estimated_delivery' => 'Tomorrow by 2PM',
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ];
        }
    }
}
