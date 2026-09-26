<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class SalesWorkspace extends Model
{
    protected string $table = 'customers';

    public function employeeIdForUser(int $userId): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM employees WHERE user_id = :user_id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public function orderIdByNumber(string $number): int
    {
        $stmt = $this->db->prepare('SELECT id FROM orders WHERE order_number = :num AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['num' => $number]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function customerIdByCode(string $code): int
    {
        $stmt = $this->db->prepare('SELECT id FROM customers WHERE customer_code = :code AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['code' => $code]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function products(): array
    {
        $rows = $this->db->query(
            "SELECT p.id, p.product_code sku, p.name, p.description, p.selling_price price,
                    c.name category, COALESCE(SUM(i.quantity_on_hand - i.quantity_reserved), 0) stock,
                    COALESCE(MAX(i.reorder_level), 0) reorder_level
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN inventory i ON i.product_id = p.id
             WHERE p.deleted_at IS NULL AND p.is_active = 1 AND c.is_active = 1
             GROUP BY p.id, p.product_code, p.name, p.description, p.selling_price, c.name
             ORDER BY p.name"
        )->fetchAll();

        return array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'sku' => $row['sku'],
            'code' => $row['sku'],
            'name' => $row['name'],
            'shortName' => $row['name'],
            'description' => $row['description'] ?? '',
            'category' => $row['category'],
            'stock' => max(0, (int) $row['stock']),
            'reorderLevel' => (int) $row['reorder_level'],
            'price' => (float) $row['price'],
            'icon' => 'sales-icon-inventory',
        ], $rows);
    }

    public function customers(): array
    {
        $rows = $this->db->query(
            "SELECT c.*, COALESCE(s.credit_balance, 0) outstanding,
                    COALESCE(s.payment_terms_days, 30) payment_terms_days,
                    COALESCE(stats.order_count, 0) order_count,
                    COALESCE(stats.revenue, 0) revenue
             FROM customers c
             LEFT JOIN shops s ON s.customer_id = c.id AND s.deleted_at IS NULL
             LEFT JOIN (
                 SELECT customer_id, COUNT(*) order_count, SUM(total_amount) revenue
                 FROM (
                     SELECT customer_id, total_amount FROM orders WHERE deleted_at IS NULL
                     UNION ALL
                     SELECT customer_id, total_amount FROM sales WHERE deleted_at IS NULL
                 ) activity GROUP BY customer_id
             ) stats ON stats.customer_id = c.id
             WHERE c.deleted_at IS NULL ORDER BY c.created_at DESC"
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $purchases = $this->customerPurchases((int) $row['id']);
            $name = (string) $row['name'];
            $result[] = [
                'databaseId' => (int) $row['id'],
                'id' => $row['customer_code'],
                'type' => $row['customer_type'],
                'name' => $name,
                'initials' => $this->initials($name),
                'phone' => $row['phone'] ?? '',
                'email' => $row['email'] ?? '',
                'address' => trim(implode(', ', array_filter([$row['address'] ?? '', $row['city'] ?? '']))),
                'active' => (bool) $row['is_active'],
                'accountSince' => date('F Y', strtotime((string) $row['created_at'])),
                'outstanding' => (float) $row['outstanding'],
                'overdue' => (float) $row['outstanding'] > 0,
                'highVolume' => (float) $row['revenue'] >= 1000000,
                'ytdRevenue' => (float) $row['revenue'],
                'averageOrder' => (int) $row['order_count'] > 0 ? (float) $row['revenue'] / (int) $row['order_count'] : 0,
                'orderCount' => (int) $row['order_count'],
                'returnRate' => 0.0,
                'purchases' => $purchases,
            ];
        }
        return $result;
    }

    private function customerPurchases(int $customerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT reference, activity_date, total, status, items FROM (
                SELECT o.order_number reference, o.order_date activity_date, o.total_amount total, o.status,
                       GROUP_CONCAT(CONCAT(oi.quantity, '× ', p.name) SEPARATOR ', ') items
                FROM orders o JOIN order_items oi ON oi.order_id = o.id JOIN products p ON p.id = oi.product_id
                WHERE o.customer_id = :order_customer AND o.deleted_at IS NULL
                GROUP BY o.id, o.order_number, o.order_date, o.total_amount, o.status
                UNION ALL
                SELECT s.invoice_number reference, s.sale_date activity_date, s.total_amount total, 'delivered' status,
                       GROUP_CONCAT(CONCAT(si.quantity, '× ', p.name) SEPARATOR ', ') items
                FROM sales s JOIN sale_items si ON si.sale_id = s.id JOIN products p ON p.id = si.product_id
                WHERE s.customer_id = :sale_customer AND s.deleted_at IS NULL
                GROUP BY s.id, s.invoice_number, s.sale_date, s.total_amount
             ) purchases ORDER BY activity_date DESC LIMIT 8"
        );
        $stmt->execute(['order_customer' => $customerId, 'sale_customer' => $customerId]);
        return array_map(static fn(array $row): array => [
            'id' => $row['reference'],
            'date' => substr((string) $row['activity_date'], 0, 10),
            'items' => $row['items'] ?? '',
            'total' => (float) $row['total'],
            'status' => ucfirst((string) $row['status']),
        ], $stmt->fetchAll());
    }

    public function customerOptions(): array
    {
        $options = [];
        foreach ($this->customers() as $customer) {
            $options[$customer['id']] = [
                'databaseId' => $customer['databaseId'],
                'name' => $customer['name'],
                'detail' => ($customer['address'] ?: 'No address') . ' · ' . ($customer['type'] === 'shop' ? 'Trade customer' : 'Walk-in customer'),
                'initials' => $customer['initials'],
                'type' => $customer['type'],
            ];
        }
        return $options;
    }

    public function orders(): array
    {
        $rows = $this->db->query(
            "SELECT o.*, c.name customer_name, c.customer_type,
                    COALESCE(u.full_name, 'Unassigned') rep_name
             FROM orders o
             JOIN customers c ON c.id = o.customer_id
             LEFT JOIN employees e ON e.id = o.sales_rep_id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE o.deleted_at IS NULL ORDER BY o.order_date DESC"
        )->fetchAll();
        $result = [];
        $itemsStmt = $this->db->prepare(
            'SELECT p.name, p.product_code sku, oi.quantity, oi.line_total total
             FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id'
        );
        foreach ($rows as $row) {
            $itemsStmt->execute(['order_id' => $row['id']]);
            $items = array_map(static fn(array $item): array => [
                'name' => $item['name'], 'sku' => $item['sku'], 'quantity' => (int) $item['quantity'], 'total' => (float) $item['total'],
            ], $itemsStmt->fetchAll());
            $result[] = [
                'databaseId' => (int) $row['id'],
                'id' => $row['order_number'],
                'customer' => $row['customer_name'],
                'initials' => $this->initials((string) $row['customer_name']),
                'accountType' => $row['customer_type'] === 'shop' ? 'Trade Account' : 'Walk-in Customer',
                'date' => substr((string) $row['order_date'], 0, 10),
                'time' => date('g:i A', strtotime((string) $row['order_date'])),
                'status' => ucfirst((string) $row['status']),
                'total' => (float) $row['total_amount'],
                'paymentStatus' => ucfirst((string) $row['payment_status']),
                'rep' => $row['rep_name'],
                'items' => $items,
            ];
        }
        return $result;
    }

    public function saveCustomer(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $type = in_array($data['customer_type'] ?? '', ['shop', 'walking'], true) ? $data['customer_type'] : 'walking';
        if ($name === '' || $phone === '') {
            throw new \InvalidArgumentException('Name and phone number are required.');
        }
        $email = trim((string) ($data['email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Enter a valid email address.');
        }

        $id = (int) ($data['id'] ?? 0);
        if ($id === 0 && !empty($data['customer_code'])) {
            $id = $this->customerIdByCode((string) $data['customer_code']);
        }
        $this->db->beginTransaction();
        try {
            if ($id > 0) {
                $stmt = $this->db->prepare(
                    'UPDATE customers SET customer_type=:type, name=:name, phone=:phone, email=:email, address=:address, city=:city
                     WHERE id=:id AND deleted_at IS NULL'
                );
                $stmt->execute(['type' => $type, 'name' => $name, 'phone' => $phone, 'email' => $email ?: null,
                    'address' => trim((string) ($data['address'] ?? '')) ?: null, 'city' => trim((string) ($data['city'] ?? '')) ?: null, 'id' => $id]);
            } else {
                $next = (int) $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM customers FOR UPDATE')->fetchColumn();
                $code = 'CUS-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
                $stmt = $this->db->prepare(
                    'INSERT INTO customers (customer_code, customer_type, name, phone, email, address, city)
                     VALUES (:code, :type, :name, :phone, :email, :address, :city)'
                );
                $stmt->execute(['code' => $code, 'type' => $type, 'name' => $name, 'phone' => $phone, 'email' => $email ?: null,
                    'address' => trim((string) ($data['address'] ?? '')) ?: null, 'city' => trim((string) ($data['city'] ?? '')) ?: null]);
                $id = (int) $this->db->lastInsertId();
            }

            if ($type === 'shop') {
                $shop = $this->db->prepare(
                    'INSERT INTO shops (customer_id, shop_name) VALUES (:customer_id, :shop_name)
                     ON DUPLICATE KEY UPDATE shop_name = VALUES(shop_name), deleted_at = NULL'
                );
                $shop->execute(['customer_id' => $id, 'shop_name' => $name]);
            } else {
                $shop = $this->db->prepare('UPDATE shops SET deleted_at = NOW() WHERE customer_id = :customer_id');
                $shop->execute(['customer_id' => $id]);
            }
            $this->db->commit();
            return ['id' => $id];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteCustomer(int $id): void
    {
        $check = $this->db->prepare(
            'SELECT (SELECT COUNT(*) FROM orders WHERE customer_id=:orders_id AND deleted_at IS NULL) +
                    (SELECT COUNT(*) FROM sales WHERE customer_id=:sales_id AND deleted_at IS NULL)'
        );
        $check->execute(['orders_id' => $id, 'sales_id' => $id]);
        if ((int) $check->fetchColumn() > 0) {
            throw new \RuntimeException('Customers with sales or order history cannot be deleted. Mark them inactive instead.');
        }
        $stmt = $this->db->prepare('UPDATE customers SET deleted_at=NOW(), is_active=0 WHERE id=:id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    public function createOrder(int $customerId, array $items, string $notes, ?int $salesRepId): string
    {
        if ($customerId < 1 || !$items) {
            throw new \InvalidArgumentException('Select a customer and add at least one product.');
        }
        $this->db->beginTransaction();
        try {
            $number = $this->nextNumber('order');
            $validated = $this->validateItems($items, true);
            $subtotal = array_sum(array_column($validated, 'line_total'));
            $order = $this->db->prepare(
                "INSERT INTO orders (order_number, customer_id, sales_rep_id, order_source, subtotal, total_amount, notes)
                 VALUES (:number, :customer_id, :sales_rep_id, 'rep_field', :subtotal, :total, :notes)"
            );
            $order->execute(['number' => $number, 'customer_id' => $customerId, 'sales_rep_id' => $salesRepId,
                'subtotal' => $subtotal, 'total' => $subtotal, 'notes' => $notes ?: null]);
            $orderId = (int) $this->db->lastInsertId();
            $line = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, unit_price, tax_rate, line_total)
                 VALUES (:order_id, :product_id, :quantity, :unit_price, :tax_rate, :line_total)'
            );
            foreach ($validated as $item) {
                $line->execute(['order_id' => $orderId] + $item);
                $reserve = $this->db->prepare('UPDATE inventory SET quantity_reserved = quantity_reserved + :quantity WHERE id=:inventory_id');
                $reserve->execute(['quantity' => $item['quantity'], 'inventory_id' => $item['inventory_id']]);
            }
            $this->db->commit();
            return $number;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus(int $id, string $status): void
    {
        $allowed = ['processing', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid order status.');
        }
        $this->db->beginTransaction();
        try {
            $order = $this->db->prepare('SELECT status FROM orders WHERE id=:id AND deleted_at IS NULL FOR UPDATE');
            $order->execute(['id' => $id]);
            $current = $order->fetchColumn();
            if ($current === false) {
                throw new \RuntimeException('Order not found.');
            }
            $valid = ($current === 'pending' && in_array($status, ['processing', 'cancelled'], true)) ||
                     ($current === 'processing' && in_array($status, ['delivered', 'cancelled'], true));
            if (!$valid) {
                throw new \RuntimeException('That status change is not allowed.');
            }
            if ($status === 'delivered') {
                $this->consumeOrderStock($id);
            } elseif ($status === 'cancelled') {
                $this->releaseOrderStock($id);
            }
            $stmt = $this->db->prepare('UPDATE orders SET status=:status WHERE id=:id');
            $stmt->execute(['status' => $status, 'id' => $id]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteOrder(int $id): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT status FROM orders WHERE id=:id AND deleted_at IS NULL FOR UPDATE');
            $stmt->execute(['id' => $id]);
            $status = $stmt->fetchColumn();
            if ($status === false) {
                throw new \RuntimeException('Order not found.');
            }
            if ($status === 'delivered') {
                throw new \RuntimeException('Delivered orders are retained as sales history and cannot be deleted.');
            }
            if ($status !== 'cancelled') {
                $this->releaseOrderStock($id);
            }
            $delete = $this->db->prepare("UPDATE orders SET status='cancelled', deleted_at=NOW() WHERE id=:id");
            $delete->execute(['id' => $id]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function completeSale(?int $customerId, array $items, float $discount, string $method, float $paid, ?int $salesRepId, int $userId): array
    {
        if (!in_array($method, ['cash', 'card', 'bank_transfer', 'credit'], true)) {
            throw new \InvalidArgumentException('Select a supported payment method.');
        }
        $this->db->beginTransaction();
        try {
            if (!$customerId) {
                $customerId = (int) $this->db->query("SELECT id FROM customers WHERE customer_type='walking' AND deleted_at IS NULL ORDER BY id LIMIT 1 FOR UPDATE")->fetchColumn();
                if (!$customerId) {
                    $next = (int) $this->db->query('SELECT COALESCE(MAX(id),0)+1 FROM customers FOR UPDATE')->fetchColumn();
                    $walkIn = $this->db->prepare("INSERT INTO customers (customer_code, customer_type, name) VALUES (:code, 'walking', 'Walk-in Customer')");
                    $walkIn->execute(['code' => 'CUS-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT)]);
                    $customerId = (int) $this->db->lastInsertId();
                }
            }
            $validated = $this->validateItems($items, false);
            $subtotal = array_sum(array_column($validated, 'line_total'));
            $discount = max(0, min($discount, $subtotal));
            $total = $subtotal - $discount;
            if ($method !== 'credit' && $paid < $total) {
                throw new \InvalidArgumentException('Amount paid is less than the sale total.');
            }
            $amountPaid = $method === 'credit' ? min($paid, $total) : $total;
            $change = $method === 'credit' ? 0 : max(0, $paid - $total);
            $paymentStatus = $amountPaid >= $total ? 'paid' : ($amountPaid > 0 ? 'partial' : 'unpaid');
            $invoice = $this->nextNumber('invoice');
            $saleNumber = $this->nextNumber('sale');
            $sale = $this->db->prepare(
                'INSERT INTO sales (invoice_number, customer_id, sales_rep_id, sale_type, payment_method, subtotal,
                 discount_amount, total_amount, amount_paid, change_amount, payment_status, created_by)
                 VALUES (:invoice, :customer_id, :sales_rep_id, :sale_type, :method, :subtotal, :discount,
                 :total, :paid, :change_amount, :payment_status, :created_by)'
            );
            $sale->execute(['invoice' => $invoice, 'customer_id' => $customerId, 'sales_rep_id' => $salesRepId,
                'sale_type' => $method === 'credit' ? 'credit' : 'pos', 'method' => $method, 'subtotal' => $subtotal,
                'discount' => $discount, 'total' => $total, 'paid' => $amountPaid, 'change_amount' => $change,
                'payment_status' => $paymentStatus, 'created_by' => $userId]);
            $saleId = (int) $this->db->lastInsertId();
            $line = $this->db->prepare(
                'INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, cost_price, tax_rate, line_total)
                 VALUES (:sale_id, :product_id, :quantity, :unit_price, :cost_price, :tax_rate, :line_total)'
            );
            foreach ($validated as $item) {
                $line->execute(['sale_id' => $saleId, 'product_id' => $item['product_id'], 'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'], 'cost_price' => $item['cost_price'], 'tax_rate' => $item['tax_rate'], 'line_total' => $item['line_total']]);
                $stock = $this->db->prepare('UPDATE inventory SET quantity_on_hand=quantity_on_hand-:quantity WHERE id=:inventory_id');
                $stock->execute(['quantity' => $item['quantity'], 'inventory_id' => $item['inventory_id']]);
            }
            if ($amountPaid > 0) {
                $payment = $this->db->prepare(
                    "INSERT INTO payments (payment_number, payable_type, payable_id, customer_id, amount, payment_method, received_by)
                     VALUES (:number, 'sale', :sale_id, :customer_id, :amount, :method, :received_by)"
                );
                $payment->execute(['number' => $this->nextNumber('payment'), 'sale_id' => $saleId, 'customer_id' => $customerId,
                    'amount' => $amountPaid, 'method' => $method, 'received_by' => $userId]);
            }
            $this->db->commit();
            return ['invoiceNumber' => $invoice, 'saleNumber' => $saleNumber, 'subtotal' => $subtotal,
                'discount' => $discount, 'total' => $total, 'paid' => $amountPaid, 'change' => $change];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function dashboardData(?int $salesRepId): array
    {
        $where = $salesRepId ? ' AND s.sales_rep_id = :rep_id' : '';
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(s.total_amount),0) total, COUNT(*) count FROM sales s WHERE DATE(s.sale_date)=CURDATE() AND s.deleted_at IS NULL{$where}");
        $stmt->execute($salesRepId ? ['rep_id' => $salesRepId] : []);
        $today = $stmt->fetch() ?: ['total' => 0, 'count' => 0];
        $orders = $this->orders();
        return [
            'todaySales' => (float) $today['total'],
            'ordersToday' => (int) $today['count'],
            'pendingOrders' => count(array_filter($orders, static fn(array $order): bool => in_array($order['status'], ['Pending', 'Processing'], true))),
            'recentSales' => array_slice($orders, 0, 5),
        ];
    }

    private function validateItems(array $items, bool $includeReserved): array
    {
        $product = $this->db->prepare(
            'SELECT p.id product_id, p.selling_price unit_price, p.cost_price, p.tax_rate,
                    i.id inventory_id, i.quantity_on_hand, i.quantity_reserved
             FROM products p JOIN inventory i ON i.product_id=p.id
             WHERE p.id=:id AND p.deleted_at IS NULL AND p.is_active=1 ORDER BY i.id LIMIT 1 FOR UPDATE'
        );
        $validated = [];
        foreach ($items as $raw) {
            $id = (int) ($raw['id'] ?? $raw['product_id'] ?? 0);
            $quantity = (int) ($raw['quantity'] ?? 0);
            if ($id < 1 || $quantity < 1) {
                throw new \InvalidArgumentException('Every line item needs a valid product and quantity.');
            }
            $product->execute(['id' => $id]);
            $row = $product->fetch();
            if (!$row) {
                throw new \RuntimeException('One of the selected products is unavailable.');
            }
            $available = (int) $row['quantity_on_hand'] - ($includeReserved ? (int) $row['quantity_reserved'] : 0);
            if ($quantity > $available) {
                throw new \RuntimeException('Requested quantity exceeds available stock.');
            }
            $validated[] = [
                'product_id' => (int) $row['product_id'], 'inventory_id' => (int) $row['inventory_id'], 'quantity' => $quantity,
                'unit_price' => (float) $row['unit_price'], 'cost_price' => (float) $row['cost_price'], 'tax_rate' => (float) $row['tax_rate'],
                'line_total' => round((float) $row['unit_price'] * $quantity, 2),
            ];
        }
        return $validated;
    }

    private function nextNumber(string $type): string
    {
        $stmt = $this->db->prepare('SELECT prefix, current_value, pad_length FROM sequences WHERE seq_type=:type FOR UPDATE');
        $stmt->execute(['type' => $type]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new \RuntimeException('Number sequence is not configured.');
        }
        $next = (int) $row['current_value'] + 1;
        $update = $this->db->prepare('UPDATE sequences SET current_value=:value WHERE seq_type=:type');
        $update->execute(['value' => $next, 'type' => $type]);
        return $row['prefix'] . str_pad((string) $next, (int) $row['pad_length'], '0', STR_PAD_LEFT);
    }

    private function releaseOrderStock(int $orderId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE inventory i JOIN order_items oi ON oi.product_id=i.product_id
             SET i.quantity_reserved=GREATEST(0, i.quantity_reserved-oi.quantity) WHERE oi.order_id=:order_id'
        );
        $stmt->execute(['order_id' => $orderId]);
    }

    private function consumeOrderStock(int $orderId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE inventory i JOIN order_items oi ON oi.product_id=i.product_id
             SET i.quantity_on_hand=i.quantity_on_hand-oi.quantity,
                 i.quantity_reserved=GREATEST(0, i.quantity_reserved-oi.quantity)
             WHERE oi.order_id=:order_id AND i.quantity_on_hand>=oi.quantity'
        );
        $stmt->execute(['order_id' => $orderId]);
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        return strtoupper(implode('', array_map(static fn(string $part): string => substr($part, 0, 1), array_slice($parts, 0, 2)))) ?: 'CU';
    }
}
