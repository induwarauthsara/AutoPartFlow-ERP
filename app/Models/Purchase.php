<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Purchase extends Model
{
    protected string $table = 'purchase_orders';

    public function summary(): array
    {
        $row = $this->db->query(
            "SELECT
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_approval,
                SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) AS in_transit,
                SUM(CASE WHEN status = 'received' AND received_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN 1 ELSE 0 END) AS received_this_week,
                COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END), 0) AS committed_value,
                COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN amount_paid ELSE 0 END), 0) AS paid_value
             FROM purchase_orders
             WHERE deleted_at IS NULL"
        )->fetch() ?: [];

        $ordered = (int) $this->db->query(
            "SELECT COALESCE(SUM(quantity_ordered), 0)
             FROM purchase_order_items poi
             INNER JOIN purchase_orders po ON po.id = poi.purchase_order_id
             WHERE po.deleted_at IS NULL AND po.status <> 'cancelled'"
        )->fetchColumn();

        $received = (int) $this->db->query(
            "SELECT COALESCE(SUM(quantity_received), 0)
             FROM purchase_order_items poi
             INNER JOIN purchase_orders po ON po.id = poi.purchase_order_id
             WHERE po.deleted_at IS NULL AND po.status <> 'cancelled'"
        )->fetchColumn();

        return [
            'pendingApproval' => (int) ($row['pending_approval'] ?? 0),
            'inTransit'       => (int) ($row['in_transit'] ?? 0),
            'receivedThisWeek' => (int) ($row['received_this_week'] ?? 0),
            'fulfillmentRate' => $ordered > 0 ? round(($received / $ordered) * 100) : 0,
            'committedValue'  => (float) ($row['committed_value'] ?? 0),
            'outstandingValue' => max(0, (float) ($row['committed_value'] ?? 0) - (float) ($row['paid_value'] ?? 0)),
        ];
    }

    public function recentOrders(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query(
            "SELECT
                po.id,
                po.po_number,
                po.order_date,
                po.expected_date,
                CASE
                    WHEN po.status = 'pending' THEN 'preparing'
                    WHEN po.status = 'draft'   THEN 'preparing'
                    WHEN po.status = 'partial' THEN 'in_transit'
                    ELSE po.status
                END AS status,
                po.total_amount,
                s.company_name AS supplier_name,
                COUNT(poi.id) AS item_count
             FROM purchase_orders po
             INNER JOIN suppliers s ON s.id = po.supplier_id
             LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
             WHERE po.deleted_at IS NULL
             GROUP BY po.id, po.po_number, po.order_date, po.expected_date,
                      po.status, po.total_amount, s.company_name
             ORDER BY po.order_date DESC, po.id DESC
             LIMIT {$limit}"
        );

        return $stmt->fetchAll();
    }

    public function mockOrders(): array
    {
        return [
            ['id' => 0, 'po_number' => 'PO-2026-1042', 'order_date' => '2026-09-24', 'status' => 'preparing', 'total_amount' => 142500, 'supplier_name' => 'Bosch Auto Parts', 'item_count' => 6],
            ['id' => 0, 'po_number' => 'PO-2026-1041', 'order_date' => '2026-09-22', 'status' => 'ready', 'total_amount' => 89005, 'supplier_name' => 'Denso Corporation', 'item_count' => 4],
            ['id' => 0, 'po_number' => 'PO-2026-1040', 'order_date' => '2026-09-20', 'status' => 'in_transit', 'total_amount' => 21000, 'supplier_name' => 'ACDelco', 'item_count' => 3],
            ['id' => 0, 'po_number' => 'PO-2026-1039', 'order_date' => '2026-09-18', 'status' => 'received', 'total_amount' => 345000, 'supplier_name' => 'Brembo Brakes', 'item_count' => 8],
        ];
    }

    public function create(array $data, ?int $userId = null): array
    {
        $supplierId = (int) ($data['supplier_id'] ?? 0);
        if ($supplierId <= 0) {
            throw new \InvalidArgumentException('Please select a valid supplier.');
        }

        $sStmt = $this->db->prepare("SELECT id, company_name FROM suppliers WHERE id = ? AND deleted_at IS NULL");
        $sStmt->execute([$supplierId]);
        $supplier = $sStmt->fetch();
        if (!$supplier) {
            throw new \InvalidArgumentException('Selected supplier does not exist.');
        }

        $orderDate = !empty($data['order_date']) ? (string) $data['order_date'] : date('Y-m-d');
        $expectedDate = !empty($data['expected_date']) ? (string) $data['expected_date'] : null;
        $notes = trim((string) ($data['notes'] ?? ''));
        $rawItems = is_array($data['items'] ?? null) ? $data['items'] : [];

        if (empty($rawItems)) {
            throw new \InvalidArgumentException('Please add at least one item to the purchase order.');
        }

        $this->db->beginTransaction();
        try {
            // Sequence for purchase_order
            $seqStmt = $this->db->prepare("SELECT prefix, current_value, pad_length FROM sequences WHERE seq_type = 'purchase_order' FOR UPDATE");
            $seqStmt->execute();
            $seq = $seqStmt->fetch();
            if ($seq) {
                $nextVal = (int) $seq['current_value'] + 1;
                $this->db->prepare("UPDATE sequences SET current_value = :val WHERE seq_type = 'purchase_order'")->execute(['val' => $nextVal]);
                $poNumber = $seq['prefix'] . str_pad((string) $nextVal, (int) $seq['pad_length'], '0', STR_PAD_LEFT);
            } else {
                $poNumber = 'PO-' . date('Y') . '-' . str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            }

            $subtotal = 0.0;
            $items = [];

            foreach ($rawItems as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? $item['quantity_ordered'] ?? 0);
                $unitCost = (float) ($item['unit_cost'] ?? 0);

                if ($productId <= 0 || $qty <= 0 || $unitCost < 0) {
                    continue;
                }

                $pStmt = $this->db->prepare("SELECT id, name, product_code FROM products WHERE id = ? AND deleted_at IS NULL");
                $pStmt->execute([$productId]);
                $product = $pStmt->fetch();
                if (!$product) {
                    continue;
                }

                $lineTotal = round($qty * $unitCost, 2);
                $subtotal += $lineTotal;

                $items[] = [
                    'product_id'       => $productId,
                    'name'             => $product['name'],
                    'product_code'     => $product['product_code'],
                    'quantity_ordered' => $qty,
                    'unit_cost'        => $unitCost,
                    'line_total'       => $lineTotal,
                ];
            }

            if (empty($items)) {
                throw new \InvalidArgumentException('All line items were invalid. Please verify product and quantity.');
            }

            $discount = (float) ($data['discount_amount'] ?? 0);
            $totalAmount = max(0, $subtotal - $discount);
            $statusCandidate = (string) ($data['status'] ?? 'pending');
            $status = in_array($statusCandidate, ['draft', 'pending', 'partial', 'received'], true) ? $statusCandidate : 'pending';

            $insertPo = $this->db->prepare(
                "INSERT INTO purchase_orders
                    (po_number, supplier_id, order_date, expected_date, status, subtotal, discount_amount, total_amount, notes, created_by)
                 VALUES
                    (:po_number, :supplier_id, :order_date, :expected_date, :status, :subtotal, :discount_amount, :total_amount, :notes, :created_by)"
            );
            $insertPo->execute([
                'po_number'       => $poNumber,
                'supplier_id'     => $supplierId,
                'order_date'      => $orderDate,
                'expected_date'   => $expectedDate,
                'status'          => $status,
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'total_amount'    => $totalAmount,
                'notes'           => $notes ?: null,
                'created_by'      => $userId,
            ]);
            $poId = (int) $this->db->lastInsertId();

            $insertItem = $this->db->prepare(
                "INSERT INTO purchase_order_items
                    (purchase_order_id, product_id, quantity_ordered, quantity_received, unit_cost, line_total)
                 VALUES
                    (:po_id, :product_id, :qty, :qty_rec, :unit_cost, :line_total)"
            );

            foreach ($items as $itm) {
                $insertItem->execute([
                    'po_id'       => $poId,
                    'product_id'  => $itm['product_id'],
                    'qty'         => $itm['quantity_ordered'],
                    'qty_rec'     => $status === 'received' ? $itm['quantity_ordered'] : 0,
                    'unit_cost'   => $itm['unit_cost'],
                    'line_total'  => $itm['line_total'],
                ]);
            }

            $this->db->commit();

            return [
                'id'            => $poId,
                'po_number'     => $poNumber,
                'supplier_id'   => $supplierId,
                'supplier_name' => $supplier['company_name'],
                'order_date'    => $orderDate,
                'expected_date' => $expectedDate,
                'status'        => $status === 'pending' ? 'preparing' : $status,
                'total_amount'  => $totalAmount,
                'item_count'    => count($items),
                'items'         => $items,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getOrder(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT po.*, s.company_name AS supplier_name, s.supplier_code, s.phone AS supplier_phone, s.email AS supplier_email,
                    u.full_name AS creator_name
             FROM purchase_orders po
             INNER JOIN suppliers s ON s.id = po.supplier_id
             LEFT JOIN users u ON u.id = po.created_by
             WHERE po.id = :id AND po.deleted_at IS NULL"
        );
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $itemStmt = $this->db->prepare(
            "SELECT poi.*, p.name AS product_name, p.product_code, p.unit
             FROM purchase_order_items poi
             INNER JOIN products p ON p.id = poi.product_id
             WHERE poi.purchase_order_id = :id
             ORDER BY poi.id ASC"
        );
        $itemStmt->execute(['id' => $orderId]);
        $order['items'] = $itemStmt->fetchAll();

        return $order;
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $statusMap = [
            'preparing'  => 'pending',
            'ready'      => 'partial',
            'in_transit' => 'partial',
            'received'   => 'received',
        ];

        if ($orderId <= 0 || !isset($statusMap[$status])) {
            throw new \InvalidArgumentException('Invalid purchase order status update.');
        }

        $stmt = $this->db->prepare(
            "UPDATE purchase_orders
             SET status = :status,
                 received_at = CASE WHEN :status_received = 'received' THEN COALESCE(received_at, NOW()) ELSE received_at END
             WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([
            'status'          => $statusMap[$status],
            'status_received' => $status,
            'id'              => $orderId,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Purchase order not found or status is unchanged.');
        }
    }
}
