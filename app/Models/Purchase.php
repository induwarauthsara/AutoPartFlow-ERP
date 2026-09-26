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

    public function recentOrders(int $limit = 8): array
    {
        $limit = max(1, min($limit, 50));
        $stmt = $this->db->query(
            "SELECT
                po.id,
                po.po_number,
                po.order_date,
                po.expected_date,
                po.status,
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
