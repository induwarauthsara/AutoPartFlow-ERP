<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Delivery extends Model
{
    protected string $table = 'deliveries';

    public function getForOrderAndCustomer(string $orderNumber, int $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                d.id,
                d.delivery_number,
                d.status AS delivery_status,
                d.scheduled_date,
                d.delivered_at,
                d.delivery_address,
                d.recipient_name,
                d.recipient_phone,
                d.notes,
                o.order_number,
                o.order_date,
                o.status AS order_status,
                o.total_amount
             FROM deliveries d
             INNER JOIN orders o ON o.id = d.order_id
             WHERE o.order_number = :order_number
               AND o.customer_id = :customer_id
               AND o.deleted_at IS NULL
             ORDER BY d.id DESC
             LIMIT 1"
        );
        $stmt->execute([
            'order_number' => $orderNumber,
            'customer_id' => $customerId,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getForOrderAndPhone(string $orderNumber, string $phone): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                d.id,
                d.delivery_number,
                d.status AS delivery_status,
                d.scheduled_date,
                d.delivered_at,
                d.delivery_address,
                d.recipient_name,
                d.recipient_phone,
                d.notes,
                o.order_number,
                o.order_date,
                o.status AS order_status,
                o.total_amount
             FROM deliveries d
             INNER JOIN orders o ON o.id = d.order_id
             INNER JOIN customers c ON c.id = o.customer_id
             WHERE o.order_number = :order_number
               AND c.phone = :phone
               AND o.deleted_at IS NULL
             ORDER BY d.id DESC
             LIMIT 1"
        );
        $stmt->execute([
            'order_number' => $orderNumber,
            'phone' => $phone,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function assignDelivery(int $deliveryId, int $employeeId, ?string $scheduledDate): array
    {
        $employeeStmt = $this->db->prepare(
            "SELECT id FROM employees WHERE id = :id AND department = 'delivery' AND deleted_at IS NULL LIMIT 1"
        );
        $employeeStmt->execute(['id' => $employeeId]);
        if (!$employeeStmt->fetch()) {
            return ['success' => false, 'message' => 'Selected employee is not an active delivery representative.'];
        }

        $stmt = $this->db->prepare(
            'UPDATE deliveries
             SET delivery_rep_id = :delivery_rep_id, scheduled_date = :scheduled_date, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            'delivery_rep_id' => $employeeId,
            'scheduled_date' => $scheduledDate,
            'id' => $deliveryId,
        ]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Delivery not found or assignment was unchanged.'];
        }

        return ['success' => true, 'message' => 'Delivery assigned successfully.'];
    }

    public function updateStatus(int $deliveryId, string $status, int $userId): array
    {
        $allowed = ['pending', 'in_transit', 'delivered', 'failed', 'returned'];
        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'message' => 'Invalid delivery status.'];
        }

        try {
            $this->db->beginTransaction();

            $find = $this->db->prepare('SELECT id, status FROM deliveries WHERE id = :id FOR UPDATE');
            $find->execute(['id' => $deliveryId]);
            $delivery = $find->fetch();
            if (!$delivery) {
                throw new \RuntimeException('Delivery not found.');
            }

            $deliveredAt = $status === 'delivered' ? 'CURRENT_TIMESTAMP' : 'NULL';
            $update = $this->db->prepare(
                "UPDATE deliveries
                 SET status = :status, delivered_at = {$deliveredAt}, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $update->execute(['status' => $status, 'id' => $deliveryId]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Delivery status updated successfully.'];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Delivery status could not be updated.'];
        }
    }


}
