<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

class Part extends Model
{
    protected string $table = 'products';

    /**
     * Stock lives in `inventory`, not `products`, so every read joins it.
     */
    private const BASE_SELECT = "
        SELECT
            p.id,
            p.product_code,
            p.name,
            p.description,
            p.category_id,
            c.name AS category_name,
            p.brand_id,
            p.unit,
            p.cost_price,
            p.selling_price,
            p.tax_rate,
            COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand,
            COALESCE(i.reorder_level, 0)    AS reorder_level
        FROM products p
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN inventory i ON i.product_id = p.id
        WHERE p.deleted_at IS NULL";

    public function findAll(): array
    {
        $stmt = $this->db->query(self::BASE_SELECT . ' ORDER BY p.id DESC');

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(self::BASE_SELECT . ' AND p.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByProductCode(string $productCode): ?array
    {
        $stmt = $this->db->prepare(self::BASE_SELECT . ' AND p.product_code = :product_code LIMIT 1');
        $stmt->execute(['product_code' => $productCode]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function productCodeExists(string $productCode, ?int $exceptId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE product_code = :product_code";

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
        }

        $params = ['product_code' => $productCode];

        if ($exceptId !== null) {
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        return $stmt->fetch() !== false;
    }

    public function create(array $data): int
    {
        $quantity = (int) ($data['quantity_on_hand'] ?? 0);
        unset($data['quantity_on_hand']);

        $this->db->beginTransaction();

        try {
            $productId = $this->insert($data);

            $stmt = $this->db->prepare(
                'INSERT INTO inventory (product_id, quantity_on_hand, last_stock_in_at)
                 VALUES (:product_id, :quantity, NOW())'
            );
            $stmt->execute(['product_id' => $productId, 'quantity' => $quantity]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $productId;
    }

    public function updatePart(int $id, array $data): bool
    {
        $quantity = (int) ($data['quantity_on_hand'] ?? 0);
        unset($data['quantity_on_hand']);

        $this->db->beginTransaction();

        try {
            $this->update($id, $data);

            $stmt = $this->db->prepare(
                'INSERT INTO inventory (product_id, quantity_on_hand)
                 VALUES (:product_id, :quantity_new)
                 ON DUPLICATE KEY UPDATE quantity_on_hand = :quantity_existing'
            );
            $stmt->execute([
                'product_id'        => $id,
                'quantity_new'      => $quantity,
                'quantity_existing' => $quantity,
            ]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Soft delete: sale_items and stock_movements reference products, so a
     * hard DELETE would fail on those foreign keys.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }
}
