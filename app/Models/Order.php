<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDOException;

class Order extends Model
{
    protected string $table = 'orders';

    /**
     * Generate the next order number from the configured sequence.
     */
    public function getNextOrderNumber(): string
    {
        $stmt = $this->db->prepare("SELECT prefix, current_value, pad_length FROM sequences WHERE seq_type = 'order' FOR UPDATE");
        $stmt->execute();
        $seq = $stmt->fetch();

        if ($seq) {
            $nextValue = (int) $seq['current_value'] + 1;
            $update = $this->db->prepare("UPDATE sequences SET current_value = :value WHERE seq_type = 'order'");
            $update->execute(['value' => $nextValue]);

            return $seq['prefix'] . str_pad((string) $nextValue, (int) $seq['pad_length'], '0', STR_PAD_LEFT);
        }

        return 'ORD-' . date('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Find the shop customer belonging to the currently signed-in user.
     * Registration stores the user's email in customers, so no schema change is required.
     */
    public function findCustomerForUser(int $userId): ?array
    {
        $sessionCustomerId = !empty($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : 0;
        if ($sessionCustomerId > 0) {
            $stmt = $this->db->prepare(
                "SELECT c.id, c.customer_code, c.customer_type, c.name, c.phone, c.email, c.address
                 FROM customers c
                 INNER JOIN users u ON u.id = :user_id AND u.email = c.email
                 WHERE c.id = :customer_id
                   AND c.customer_type = 'shop'
                   AND c.deleted_at IS NULL
                   AND c.is_active = 1
                   AND u.deleted_at IS NULL
                 LIMIT 1"
            );
            $stmt->execute(['user_id' => $userId, 'customer_id' => $sessionCustomerId]);
            $customer = $stmt->fetch();
            if ($customer) {
                return $customer;
            }
        }

        // Backward-compatible recovery for accounts created before customer_id
        // was stored in the login session.
        $stmt = $this->db->prepare(
            "SELECT c.id, c.customer_code, c.customer_type, c.name, c.phone, c.email, c.address
             FROM customers c
             INNER JOIN users u ON u.email = c.email
             WHERE u.id = :user_id
               AND u.deleted_at IS NULL
               AND c.customer_type = 'shop'
               AND c.deleted_at IS NULL
               AND c.is_active = 1
             ORDER BY c.id DESC
             LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);
        $customer = $stmt->fetch();

        if ($customer) {
            $_SESSION['customer_id'] = (int) $customer['id'];
            return $customer;
        }

        // Check by phone if email did not match
        $stmt = $this->db->prepare(
            "SELECT c.id, c.customer_code, c.customer_type, c.name, c.phone, c.email, c.address
             FROM customers c
             INNER JOIN users u ON u.id = :user_id AND u.phone IS NOT NULL AND u.phone != '' AND u.phone = c.phone
             WHERE u.id = :user_id
               AND u.deleted_at IS NULL
               AND c.customer_type = 'shop'
               AND c.deleted_at IS NULL
               AND c.is_active = 1
             ORDER BY c.id DESC
             LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);
        $customer = $stmt->fetch();

        if ($customer) {
            $_SESSION['customer_id'] = (int) $customer['id'];
            return $customer;
        }

        // Automatic creation/linking if customer record doesn't exist yet for signed-in user
        $uStmt = $this->db->prepare("SELECT id, full_name, email, phone FROM users WHERE id = :id AND deleted_at IS NULL");
        $uStmt->execute(['id' => $userId]);
        $user = $uStmt->fetch();
        if ($user) {
            $code = 'CUS-' . str_pad((string) $userId, 5, '0', STR_PAD_LEFT);
            $ins = $this->db->prepare(
                "INSERT INTO customers (customer_code, customer_type, name, contact_person, phone, email, is_active)
                 VALUES (:code, 'shop', :name, :name, :phone, :email, 1)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone)"
            );
            $ins->execute([
                'code' => $code,
                'name' => $user['full_name'],
                'phone' => !empty($user['phone']) ? $user['phone'] : null,
                'email' => $user['email'],
            ]);
            $cId = (int) $this->db->lastInsertId();
            if ($cId === 0) {
                $sel = $this->db->prepare("SELECT id FROM customers WHERE email = :email LIMIT 1");
                $sel->execute(['email' => $user['email']]);
                $cId = (int) ($sel->fetchColumn() ?: 0);
            }
            if ($cId > 0) {
                $sh = $this->db->prepare("INSERT IGNORE INTO shops (customer_id, shop_name) VALUES (:cid, :sname)");
                $sh->execute(['cid' => $cId, 'sname' => $user['full_name'] . ' Auto Care']);
                $_SESSION['customer_id'] = $cId;
                return [
                    'id' => $cId,
                    'customer_code' => $code,
                    'customer_type' => 'shop',
                    'name' => $user['full_name'],
                    'phone' => $user['phone'] ?? '',
                    'email' => $user['email'],
                    'address' => '',
                ];
            }
        }

        return null;
    }

    /**
     * Get or create a guest/walking customer by phone.
     */
    public function getOrCreateCustomer(string $name, string $phone, string $address, ?string $email = null): int
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM customers
             WHERE phone = :phone AND deleted_at IS NULL
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['phone' => $phone]);
        $existing = $stmt->fetch();

        if ($existing) {
            $update = $this->db->prepare(
                "UPDATE customers
                 SET name = :name,
                     address = :address,
                     email = COALESCE(:email, email),
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $update->execute([
                'name' => $name,
                'address' => $address,
                'email' => $email !== '' ? $email : null,
                'id' => (int) $existing['id'],
            ]);

            return (int) $existing['id'];
        }

        $seqStmt = $this->db->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM customers");
        $nextId = (int) $seqStmt->fetchColumn();
        $customerCode = 'CUS-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);

        $insert = $this->db->prepare(
            "INSERT INTO customers
                (customer_code, customer_type, name, phone, email, address, is_active)
             VALUES
                (:code, 'walking', :name, :phone, :email, :address, 1)"
        );
        $insert->execute([
            'code' => $customerCode,
            'name' => $name,
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'address' => $address,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Create a customer order transactionally.
     * Product identity, current selling price and available stock are always read from MySQL.
     */
    public function createOrderWithItems(array $customerData, array $items, string $paymentMethod): array
    {
        if ($paymentMethod !== 'cod') {
            return ['success' => false, 'message' => 'Only Cash on Delivery is currently supported.'];
        }

        if ($items === []) {
            return ['success' => false, 'message' => 'Your cart is empty. Add at least one product before checkout.'];
        }

        try {
            $this->db->beginTransaction();

            $customerId = 0;
            if (!empty($customerData['userId'])) {
                $accountCustomer = $this->findCustomerForUser((int) $customerData['userId']);
                if ($accountCustomer) {
                    $customerId = (int) $accountCustomer['id'];
                    $updateCustomer = $this->db->prepare(
                        "UPDATE customers
                         SET name = :name, phone = :phone, address = :address, updated_at = CURRENT_TIMESTAMP
                         WHERE id = :id"
                    );
                    $updateCustomer->execute([
                        'name' => $customerData['fullName'],
                        'phone' => $customerData['phoneNumber'],
                        'address' => $customerData['deliveryAddress'],
                        'id' => $customerId,
                    ]);
                }
            }

            if ($customerId === 0) {
                $customerId = $this->getOrCreateCustomer(
                    $customerData['fullName'],
                    $customerData['phoneNumber'],
                    $customerData['deliveryAddress'],
                    $customerData['email'] ?? null
                );
            }

            $orderNumber = $this->getNextOrderNumber();
            $subtotal = 0.00;
            $orderItems = [];

            $productStmt = $this->db->prepare(
                "SELECT
                    p.id,
                    p.product_code,
                    p.name,
                    p.selling_price,
                    COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand,
                    COALESCE(i.quantity_reserved, 0) AS quantity_reserved
                 FROM products p
                 LEFT JOIN inventory i ON i.product_id = p.id
                 WHERE p.product_code = :code
                   AND p.is_active = 1
                   AND p.deleted_at IS NULL
                 LIMIT 1
                 FOR UPDATE"
            );

            if (count($items) > 50) {
                throw new \RuntimeException('Your cart contains too many different products.');
            }

            // Browser/localStorage values are untrusted. Product name, price and
            // totals are deliberately ignored; only a valid product code + quantity
            // are accepted and authoritative values are loaded from MySQL.
            $normalizedItems = [];
            foreach ($items as $item) {
                if (!is_array($item)) {
                    throw new \RuntimeException('One or more cart items are invalid.');
                }

                $code = trim((string) ($item['code'] ?? ''));
                $rawQty = $item['qty'] ?? null;
                $qty = filter_var($rawQty, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 99],
                ]);

                if (!preg_match('/^[A-Za-z0-9._-]{2,50}$/', $code) || $qty === false) {
                    throw new \RuntimeException('One or more cart items are invalid.');
                }

                $normalizedItems[$code] = ($normalizedItems[$code] ?? 0) + (int) $qty;
                if ($normalizedItems[$code] > 99) {
                    throw new \RuntimeException('A product quantity cannot exceed 99 units.');
                }
            }

            foreach ($normalizedItems as $code => $qty) {
                $productStmt->execute(['code' => $code]);
                $product = $productStmt->fetch();

                if (!$product) {
                    throw new \RuntimeException("Product {$code} is no longer available.");
                }

                $available = max(0, (int) $product['quantity_on_hand'] - (int) $product['quantity_reserved']);
                if ($qty > $available) {
                    throw new \RuntimeException("Only {$available} unit(s) of {$product['name']} are currently available.");
                }

                $unitPrice = (float) $product['selling_price'];
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => (int) $product['id'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $deliveryFee = 350.00;
            $taxAmount = 0.00;
            $totalAmount = $subtotal + $deliveryFee;
            $notes = 'Payment Method: Cash on Delivery';

            $orderStmt = $this->db->prepare(
                "INSERT INTO orders (
                    order_number, customer_id, order_source, status,
                    subtotal, discount_amount, tax_amount, total_amount,
                    payment_status, delivery_address, notes
                ) VALUES (
                    :order_number, :customer_id, 'shop_portal', 'pending',
                    :subtotal, 0.00, :tax_amount, :total_amount,
                    'unpaid', :delivery_address, :notes
                )"
            );
            $orderStmt->execute([
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'delivery_address' => $customerData['deliveryAddress'],
                'notes' => $notes,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                "INSERT INTO order_items (
                    order_id, product_id, quantity, unit_price, discount_amount, tax_rate, line_total
                ) VALUES (
                    :order_id, :product_id, :quantity, :unit_price, 0.00, 0.00, :line_total
                )"
            );

            foreach ($orderItems as $orderItem) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $orderItem['product_id'],
                    'quantity' => $orderItem['quantity'],
                    'unit_price' => $orderItem['unit_price'],
                    'line_total' => $orderItem['line_total'],
                ]);
            }

            // Create the delivery record as part of the same transaction so every
            // successfully placed shop-portal order has a customer delivery record.
            $deliverySeq = $this->db->prepare(
                "SELECT prefix, current_value, pad_length
                 FROM sequences
                 WHERE seq_type = 'delivery'
                 FOR UPDATE"
            );
            $deliverySeq->execute();
            $sequence = $deliverySeq->fetch();

            if (!$sequence) {
                throw new \RuntimeException('Delivery sequence is not configured.');
            }

            $nextDeliveryValue = (int) $sequence['current_value'] + 1;
            $updateDeliverySeq = $this->db->prepare(
                "UPDATE sequences SET current_value = :value WHERE seq_type = 'delivery'"
            );
            $updateDeliverySeq->execute(['value' => $nextDeliveryValue]);
            $deliveryNumber = $sequence['prefix'] . str_pad(
                (string) $nextDeliveryValue,
                (int) $sequence['pad_length'],
                '0',
                STR_PAD_LEFT
            );

            $deliveryStmt = $this->db->prepare(
                "INSERT INTO deliveries (
                    delivery_number, order_id, customer_id, status,
                    delivery_address, recipient_name, recipient_phone, notes
                ) VALUES (
                    :delivery_number, :order_id, :customer_id, 'pending',
                    :delivery_address, :recipient_name, :recipient_phone, :notes
                )"
            );
            $deliveryStmt->execute([
                'delivery_number' => $deliveryNumber,
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'delivery_address' => $customerData['deliveryAddress'],
                'recipient_name' => $customerData['fullName'],
                'recipient_phone' => $customerData['phoneNumber'],
                'notes' => 'Created automatically for online customer order.',
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'estimated_delivery' => 'Delivery date will be confirmed by the store.',
            ];
        } catch (PDOException|\RuntimeException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all active orders for a customer account.
     */
    public function getOrdersForCustomer(int $customerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, order_number, order_date, status, subtotal, total_amount, payment_status, delivery_address
             FROM orders
             WHERE customer_id = :customer_id
               AND deleted_at IS NULL
             ORDER BY order_date DESC, id DESC"
        );
        $stmt->execute(['customer_id' => $customerId]);

        return $stmt->fetchAll();
    }

    /**
     * Find one order for a customer using the order number and verified phone number.
     * This is the guest-management fallback when no shop account is signed in.
     */
    public function findOrderForPhone(string $orderNumber, string $phone): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                o.id,
                o.order_number,
                o.order_date,
                o.status,
                o.subtotal,
                o.discount_amount,
                o.tax_amount,
                o.total_amount,
                o.payment_status,
                o.delivery_address,
                c.id AS customer_id,
                c.name AS customer_name,
                c.phone AS customer_phone,
                c.email AS customer_email
             FROM orders o
             INNER JOIN customers c ON c.id = o.customer_id
             WHERE o.order_number = :order_number
               AND c.phone = :phone
               AND o.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute(['order_number' => $orderNumber, 'phone' => $phone]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        return $this->attachItems($order);
    }

    /**
     * Get one order by customer id.
     */
    public function findOrderForCustomer(string $orderNumber, int $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                o.id,
                o.order_number,
                o.order_date,
                o.status,
                o.subtotal,
                o.discount_amount,
                o.tax_amount,
                o.total_amount,
                o.payment_status,
                o.delivery_address,
                c.id AS customer_id,
                c.name AS customer_name,
                c.phone AS customer_phone,
                c.email AS customer_email
             FROM orders o
             INNER JOIN customers c ON c.id = o.customer_id
             WHERE o.order_number = :order_number
               AND o.customer_id = :customer_id
               AND o.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([
            'order_number' => $orderNumber,
            'customer_id' => $customerId,
        ]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        return $this->attachItems($order);
    }

    /**
     * Update the delivery address of an order while it is still pending.
     */
    public function updateDeliveryAddress(int $orderId, int $customerId, string $address): array
    {
        $address = trim($address);
        if ($address === '') {
            return ['success' => false, 'message' => 'Delivery address is required.'];
        }

        $stmt = $this->db->prepare(
            "UPDATE orders
             SET delivery_address = :address
             WHERE id = :id
               AND customer_id = :customer_id
               AND status = 'pending'
               AND deleted_at IS NULL"
        );
        $stmt->execute([
            'address' => $address,
            'id' => $orderId,
            'customer_id' => $customerId,
        ]);

        if ($stmt->rowCount() === 0) {
            return [
                'success' => false,
                'message' => 'Only pending orders can be updated.'
            ];
        }

        return ['success' => true, 'message' => 'Delivery address updated successfully.'];
    }

    /**
     * Cancel an order without physically deleting the transaction.
     * The cancelled row remains available for audit/history.
     */
    public function cancelOrder(int $orderId, int $customerId): array
    {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET status = 'cancelled'
             WHERE id = :id
               AND customer_id = :customer_id
               AND status IN ('pending', 'confirmed')
               AND deleted_at IS NULL"
        );
        $stmt->execute([
            'id' => $orderId,
            'customer_id' => $customerId,
        ]);

        if ($stmt->rowCount() === 0) {
            return [
                'success' => false,
                'message' => 'This order can no longer be cancelled.'
            ];
        }

        $deliveryStmt = $this->db->prepare(
            "UPDATE deliveries
             SET status = 'returned', updated_at = CURRENT_TIMESTAMP
             WHERE order_id = :order_id
               AND status = 'pending'"
        );
        $deliveryStmt->execute(['order_id' => $orderId]);

        return ['success' => true, 'message' => 'Order cancelled successfully.'];
    }

    /**
     * Secure tracking lookup for a signed-in shop customer.
     */
    public function trackOrderForCustomer(string $orderNumber, int $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                o.id, o.order_number, o.order_date, o.status, o.order_source,
                o.subtotal, o.total_amount, o.payment_status, o.delivery_address, o.notes,
                c.name AS customer_name, c.phone AS customer_phone
             FROM orders o
             INNER JOIN customers c ON c.id = o.customer_id
             WHERE o.order_number = :order_number
               AND o.customer_id = :customer_id
               AND o.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute(['order_number' => $orderNumber, 'customer_id' => $customerId]);
        $order = $stmt->fetch();
        return $order ? $this->attachItems($order) : null;
    }

    /**
     * Secure guest tracking lookup. The order number alone is never sufficient.
     */
    public function trackOrderForPhone(string $orderNumber, string $phone): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                o.id, o.order_number, o.order_date, o.status, o.order_source,
                o.subtotal, o.total_amount, o.payment_status, o.delivery_address, o.notes,
                c.name AS customer_name, c.phone AS customer_phone
             FROM orders o
             INNER JOIN customers c ON c.id = o.customer_id
             WHERE o.order_number = :order_number
               AND c.phone = :phone
               AND o.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute(['order_number' => $orderNumber, 'phone' => $phone]);
        $order = $stmt->fetch();
        return $order ? $this->attachItems($order) : null;
    }

    private function attachItems(array $order): array
    {
        $itemStmt = $this->db->prepare(
            "SELECT
                oi.quantity,
                oi.unit_price,
                oi.line_total,
                p.name AS product_name,
                p.product_code
             FROM order_items oi
             INNER JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.id"
        );
        $itemStmt->execute(['order_id' => $order['id']]);
        $order['items'] = $itemStmt->fetchAll();

        $deliveryStmt = $this->db->prepare(
            "SELECT delivery_number, status AS delivery_status, scheduled_date, delivered_at,
                    delivery_address, recipient_name, recipient_phone
             FROM deliveries
             WHERE order_id = :order_id
             ORDER BY id DESC
             LIMIT 1"
        );
        $deliveryStmt->execute(['order_id' => $order['id']]);
        $order['delivery'] = $deliveryStmt->fetch() ?: null;
        return $order;
    }
}
