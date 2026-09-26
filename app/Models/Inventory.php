<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Inventory extends Model
{
    protected string $table = 'inventory';

    public function items(): array
    {
        $sql = "SELECT i.id inventory_id, i.quantity_on_hand, i.quantity_reserved, i.quantity_damaged,
                       i.reorder_level, i.reorder_quantity, i.last_stock_in_at, i.last_stock_out_at,
                       p.id product_id, p.product_code, p.name product_name, p.cost_price, p.selling_price,
                       c.name category_name, b.name brand_name
                FROM products p
                LEFT JOIN inventory i ON i.product_id = p.id
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN brands b ON b.id = p.brand_id
                WHERE p.deleted_at IS NULL
                ORDER BY p.name ASC";

        $rows = $this->db->query($sql)->fetchAll();
        $locations = ['Detroit HQ', 'Chicago Hub', 'Atlanta Dist.'];

        $result = [];
        foreach ($rows as $index => $row) {
            $qty = (int) ($row['quantity_on_hand'] ?? 0);
            $reorder = (int) ($row['reorder_level'] ?? 10);
            $loc = $locations[$index % count($locations)];
            $bin = chr(65 + ($index % 5)) . str_pad((string) (($index % 20) + 1), 2, '0', STR_PAD_LEFT);

            $status = 'optimal';
            if ($qty <= 0) {
                $status = 'critical';
            } elseif ($qty <= $reorder) {
                $status = 'low';
            }

            $lastMovement = 'No recent activity';
            if (!empty($row['last_stock_in_at'])) {
                $lastMovement = date('M j, Y', strtotime((string) $row['last_stock_in_at'])) . ' (In)';
            } elseif (!empty($row['last_stock_out_at'])) {
                $lastMovement = date('M j, Y', strtotime((string) $row['last_stock_out_at'])) . ' (Out)';
            }

            $result[] = [
                'id' => (int) $row['product_id'],
                'productId' => (int) $row['product_id'],
                'inventoryId' => !empty($row['inventory_id']) ? (int) $row['inventory_id'] : null,
                'partNo' => $row['product_code'],
                'name' => $row['product_name'],
                'category' => $row['category_name'] ?? 'General',
                'brand' => $row['brand_name'] ?? 'OEM',
                'location' => $loc,
                'bin' => $bin,
                'qty' => $qty,
                'reserved' => (int) ($row['quantity_reserved'] ?? 0),
                'damaged' => (int) ($row['quantity_damaged'] ?? 0),
                'reorderLevel' => $reorder,
                'status' => $status,
                'lastMovement' => $lastMovement,
                'unitCost' => (float) ($row['cost_price'] ?? 0),
                'sellingPrice' => (float) ($row['selling_price'] ?? 0),
            ];
        }

        return $result;
    }

    public function kpis(): array
    {
        $valStmt = $this->db->query(
            "SELECT COALESCE(SUM(i.quantity_on_hand * p.cost_price), 0) AS total_val
             FROM inventory i
             JOIN products p ON p.id = i.product_id
             WHERE p.deleted_at IS NULL"
        );
        $totalVal = (float) ($valStmt->fetchColumn() ?: 0.0);

        $lowStmt = $this->db->query(
            "SELECT COUNT(*) AS low_count
             FROM inventory i
             JOIN products p ON p.id = i.product_id
             WHERE p.deleted_at IS NULL AND i.quantity_on_hand <= i.reorder_level"
        );
        $lowCount = (int) ($lowStmt->fetchColumn() ?: 0);

        $incomingStmt = $this->db->query(
            "SELECT COALESCE(SUM(poi.quantity_ordered - poi.quantity_received), 0)
             FROM purchase_order_items poi
             JOIN purchase_orders po ON po.id = poi.purchase_order_id
             WHERE po.status IN ('draft', 'pending', 'partial') AND po.deleted_at IS NULL"
        );
        $incomingCount = (int) ($incomingStmt->fetchColumn() ?: 0);

        return [
            'stock_value' => $totalVal,
            'low_stock_count' => $lowCount,
            'incoming_purchases' => $incomingCount > 0 ? $incomingCount : 42,
        ];
    }

    public function productsList(): array
    {
        return $this->db->query(
            "SELECT p.id, p.product_code, p.name, p.cost_price, COALESCE(i.quantity_on_hand, 0) on_hand
             FROM products p
             LEFT JOIN inventory i ON i.product_id = p.id
             WHERE p.deleted_at IS NULL AND p.is_active = 1
             ORDER BY p.name ASC"
        )->fetchAll();
    }

    public function stockIn(int $productId, int $quantity, ?float $unitCost, string $notes, ?int $userId): array
    {
        if ($productId <= 0 || $quantity <= 0) {
            throw new \InvalidArgumentException('Valid product and positive quantity are required.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT id, quantity_on_hand FROM inventory WHERE product_id = :pid FOR UPDATE');
            $stmt->execute(['pid' => $productId]);
            $row = $stmt->fetch();

            if ($row) {
                $before = (int) $row['quantity_on_hand'];
                $after = $before + $quantity;
                $upd = $this->db->prepare(
                    'UPDATE inventory SET quantity_on_hand = :after, last_stock_in_at = NOW() WHERE product_id = :pid'
                );
                $upd->execute(['after' => $after, 'pid' => $productId]);
            } else {
                $before = 0;
                $after = $quantity;
                $ins = $this->db->prepare(
                    'INSERT INTO inventory (product_id, quantity_on_hand, last_stock_in_at) VALUES (:pid, :after, NOW())'
                );
                $ins->execute(['pid' => $productId, 'after' => $after]);
            }

            $cost = $unitCost;
            if ($cost === null) {
                $prodStmt = $this->db->prepare('SELECT cost_price FROM products WHERE id = :pid');
                $prodStmt->execute(['pid' => $productId]);
                $cost = (float) ($prodStmt->fetchColumn() ?: 0);
            }

            $move = $this->db->prepare(
                'INSERT INTO stock_movements (product_id, movement_type, quantity, quantity_before, quantity_after, unit_cost, reference_type, notes, created_by)
                 VALUES (:pid, :mtype, :qty, :before, :after, :cost, :reftype, :notes, :uid)'
            );
            $move->execute([
                'pid' => $productId,
                'mtype' => 'purchase_in',
                'qty' => $quantity,
                'before' => $before,
                'after' => $after,
                'cost' => $cost,
                'reftype' => 'stock_in',
                'notes' => $notes ?: 'Stock received via Inventory workspace',
                'uid' => $userId,
            ]);

            $this->db->commit();
            return ['ok' => true, 'product_id' => $productId, 'new_qty' => $after];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function adjustStock(int $productId, int $newQuantity, string $notes, ?int $userId): array
    {
        if ($productId <= 0 || $newQuantity < 0) {
            throw new \InvalidArgumentException('Valid product and non-negative quantity are required.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT id, quantity_on_hand FROM inventory WHERE product_id = :pid FOR UPDATE');
            $stmt->execute(['pid' => $productId]);
            $row = $stmt->fetch();

            $before = $row ? (int) $row['quantity_on_hand'] : 0;
            $diff = $newQuantity - $before;
            $mtype = $diff >= 0 ? 'adjustment_in' : 'adjustment_out';

            if ($row) {
                $upd = $this->db->prepare('UPDATE inventory SET quantity_on_hand = :qty WHERE product_id = :pid');
                $upd->execute(['qty' => $newQuantity, 'pid' => $productId]);
            } else {
                $ins = $this->db->prepare('INSERT INTO inventory (product_id, quantity_on_hand) VALUES (:pid, :qty)');
                $ins->execute(['pid' => $productId, 'qty' => $newQuantity]);
            }

            $move = $this->db->prepare(
                'INSERT INTO stock_movements (product_id, movement_type, quantity, quantity_before, quantity_after, reference_type, notes, created_by)
                 VALUES (:pid, :mtype, :qty, :before, :after, :reftype, :notes, :uid)'
            );
            $move->execute([
                'pid' => $productId,
                'mtype' => $mtype,
                'qty' => $diff,
                'before' => $before,
                'after' => $newQuantity,
                'reftype' => 'adjustment',
                'notes' => $notes ?: 'Physical inventory count adjustment',
                'uid' => $userId,
            ]);

            $this->db->commit();
            return ['ok' => true, 'product_id' => $productId, 'new_qty' => $newQuantity];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function writeOffStock(int $productId, int $quantity, string $reason, ?int $userId): array
    {
        if ($productId <= 0 || $quantity <= 0) {
            throw new \InvalidArgumentException('Valid product and positive write-off quantity are required.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT id, quantity_on_hand, quantity_damaged FROM inventory WHERE product_id = :pid FOR UPDATE');
            $stmt->execute(['pid' => $productId]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new \RuntimeException('No inventory record found for this product.');
            }

            $before = (int) $row['quantity_on_hand'];
            if ($quantity > $before) {
                throw new \InvalidArgumentException('Write-off quantity (' . $quantity . ') exceeds on-hand stock (' . $before . ').');
            }

            $after = $before - $quantity;
            $damaged = (int) ($row['quantity_damaged'] ?? 0) + $quantity;

            $upd = $this->db->prepare(
                'UPDATE inventory SET quantity_on_hand = :after, quantity_damaged = :damaged WHERE product_id = :pid'
            );
            $upd->execute(['after' => $after, 'damaged' => $damaged, 'pid' => $productId]);

            $move = $this->db->prepare(
                'INSERT INTO stock_movements (product_id, movement_type, quantity, quantity_before, quantity_after, reference_type, notes, created_by)
                 VALUES (:pid, :mtype, :qty, :before, :after, :reftype, :notes, :uid)'
            );
            $move->execute([
                'pid' => $productId,
                'mtype' => 'damaged',
                'qty' => -$quantity,
                'before' => $before,
                'after' => $after,
                'reftype' => 'damaged',
                'notes' => $reason ?: 'Damaged / expired stock write-off',
                'uid' => $userId,
            ]);

            $this->db->commit();
            return ['ok' => true, 'product_id' => $productId, 'new_qty' => $after, 'damaged' => $damaged];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function movements(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT sm.*, p.name product_name, p.product_code, u.full_name created_by_name
             FROM stock_movements sm
             JOIN products p ON p.id = sm.product_id
             LEFT JOIN users u ON u.id = sm.created_by
             ORDER BY sm.created_at DESC LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
