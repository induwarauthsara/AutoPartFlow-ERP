<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Inventory extends Model
{
    protected string $table = 'inventory';

    /**
     * The schema currently supports a single warehouse location.
     */
    public function getLocations(): array
    {
        return ['All Locations', 'Main Warehouse'];
    }

    /**
     * Fetch every inventory item with product details and its latest stock movement.
     *
     * Returns rows shaped for the front-end:
     * id, partNo, name, category, location, bin, qty, reorderLevel, status,
     * lastMovement, unitCost.
     */
    public function getInventoryItems(): array
    {
        $sql = "
            SELECT
                p.id,
                p.product_code,
                p.name,
                p.cost_price,
                p.selling_price,
                c.name AS category,
                i.quantity_on_hand,
                i.quantity_reserved,
                i.quantity_damaged,
                i.reorder_level,
                i.reorder_quantity,
                i.last_stock_in_at,
                i.last_stock_out_at,
                sm.movement_type AS last_movement_type,
                sm.quantity      AS last_movement_qty,
                sm.created_at    AS last_movement_at,
                sm.notes         AS last_movement_notes
            FROM products p
            INNER JOIN inventory i ON i.product_id = p.id
            INNER JOIN categories c ON c.id = p.category_id
            LEFT JOIN stock_movements sm ON sm.id = (
                SELECT sm2.id
                FROM stock_movements sm2
                WHERE sm2.product_id = p.id
                ORDER BY sm2.created_at DESC, sm2.id DESC
                LIMIT 1
            )
            WHERE p.deleted_at IS NULL
            ORDER BY p.name ASC
        ";

        $rows = $this->db->query($sql)->fetchAll();

        return array_map([$this, 'mapItem'], $rows);
    }

    /**
     * Summary KPIs for the inventory dashboard.
     */
    public function getSummary(): array
    {
        $stockValue = (float) $this->db->query(
            "SELECT COALESCE(SUM(i.quantity_on_hand * p.cost_price), 0)
             FROM inventory i
             INNER JOIN products p ON p.id = i.product_id
             WHERE p.deleted_at IS NULL"
        )->fetchColumn();

        $lowStock = (int) $this->db->query(
            "SELECT COUNT(*)
             FROM inventory i
             INNER JOIN products p ON p.id = i.product_id
             WHERE p.deleted_at IS NULL
               AND i.quantity_on_hand <= i.reorder_level"
        )->fetchColumn();

        $incomingPurchases = (int) $this->db->query(
            "SELECT COUNT(*)
             FROM purchase_orders
             WHERE status IN ('pending', 'partial')
               AND deleted_at IS NULL"
        )->fetchColumn();

        return [
            'stockValue'        => $stockValue,
            'lowStock'          => $lowStock,
            'incomingPurchases' => $incomingPurchases,
        ];
    }

    /**
     * Record stock-in adjustment for an existing product.
     */
    public function addStock(int $productId, int $quantity, string $location = 'Main Warehouse', string $notes = '', ?int $userId = null): array
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        $location = $this->normalizeLocation($location);

        $this->db->beginTransaction();
        try {
            $pStmt = $this->db->prepare("SELECT id, product_code, name, cost_price FROM products WHERE id = :id AND deleted_at IS NULL");
            $pStmt->execute(['id' => $productId]);
            $product = $pStmt->fetch();

            if (!$product) {
                throw new \RuntimeException('Product not found.');
            }

            $stmt = $this->db->prepare("SELECT quantity_on_hand, reorder_level FROM inventory WHERE product_id = :product_id FOR UPDATE");
            $stmt->execute(['product_id' => $productId]);
            $inv = $stmt->fetch();

            if (!$inv) {
                $insInv = $this->db->prepare("INSERT INTO inventory (product_id, quantity_on_hand, reorder_level, last_stock_in_at) VALUES (:pid, :qty, 10, NOW())");
                $insInv->execute([
                    'pid' => $productId,
                    'qty' => $quantity,
                ]);
                $qtyBefore = 0;
                $reorderLevel = 10;
                $qtyAfter = $quantity;
            } else {
                $qtyBefore = (int) $inv['quantity_on_hand'];
                $reorderLevel = (int) $inv['reorder_level'];
                $qtyAfter = $qtyBefore + $quantity;

                $upd = $this->db->prepare("
                    UPDATE inventory 
                    SET quantity_on_hand = :qty_after,
                        last_stock_in_at = NOW()
                    WHERE product_id = :pid
                ");
                $upd->execute([
                    'qty_after' => $qtyAfter,
                    'pid'       => $productId,
                ]);
            }

            $costPrice = (float) $product['cost_price'];

            $smStmt = $this->db->prepare("
                INSERT INTO stock_movements (
                    product_id, movement_type, quantity, quantity_before, quantity_after,
                    unit_cost, reference_type, notes, created_by, created_at
                ) VALUES (
                    :product_id, 'adjustment_in', :quantity, :qty_before, :qty_after,
                    :unit_cost, 'manual_adjustment', :notes, :created_by, NOW()
                )
            ");
            $smStmt->execute([
                'product_id' => $productId,
                'quantity'   => $quantity,
                'qty_before' => $qtyBefore,
                'qty_after'  => $qtyAfter,
                'unit_cost'  => $costPrice,
                'notes'      => $notes !== '' ? $notes : 'Stock In via Inventory Management',
                'created_by' => $userId,
            ]);

            $this->db->commit();

            return [
                'id'           => $productId,
                'partNo'       => $product['product_code'],
                'name'         => $product['name'],
                'qty'          => $qtyAfter,
                'location'     => $location,
                'reorderLevel' => $reorderLevel,
                'status'       => $this->deriveStatus($qtyAfter, $reorderLevel),
                'lastMovement' => 'Just now (In)' . ($notes !== '' ? ' — ' . $notes : ''),
                'unitCost'     => $costPrice,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Create a brand new product and register it in the inventory.
     */
    public function createItem(array $data, ?int $userId = null): array
    {
        $code = trim((string) ($data['product_code'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        $categoryId = (int) ($data['category_id'] ?? 0);
        $costPrice = max(0, (float) ($data['cost_price'] ?? 0));
        $sellingPrice = max(0, (float) ($data['selling_price'] ?? 0));
        $initialQty = max(0, (int) ($data['quantity_on_hand'] ?? $data['quantity'] ?? 0));
        $reorderLevel = max(1, (int) ($data['reorder_level'] ?? 10));
        $notes = trim((string) ($data['notes'] ?? ''));
        $location = trim((string) ($data['location'] ?? 'Main Warehouse')) ?: 'Main Warehouse';

        if ($code === '') {
            throw new \InvalidArgumentException('Part number / SKU is required.');
        }
        if ($name === '') {
            throw new \InvalidArgumentException('Product name is required.');
        }
        if ($categoryId <= 0) {
            throw new \InvalidArgumentException('Please select a valid category.');
        }

        $location = $this->normalizeLocation($location);

        $this->db->beginTransaction();
        try {
            $dupStmt = $this->db->prepare("SELECT id FROM products WHERE product_code = :code LIMIT 1");
            $dupStmt->execute(['code' => $code]);
            if ($dupStmt->fetch()) {
                throw new \InvalidArgumentException("An item with part number '{$code}' already exists.");
            }

            $catStmt = $this->db->prepare("SELECT id, name FROM categories WHERE id = :id AND deleted_at IS NULL LIMIT 1");
            $catStmt->execute(['id' => $categoryId]);
            $category = $catStmt->fetch();
            if (!$category) {
                throw new \InvalidArgumentException('Selected category does not exist.');
            }

            $insProd = $this->db->prepare("
                INSERT INTO products (
                    product_code, name, description, category_id, unit, cost_price, selling_price, is_active, created_at
                ) VALUES (
                    :code, :name, :description, :category_id, 'pcs', :cost_price, :selling_price, 1, NOW()
                )
            ");
            $insProd->execute([
                'code'          => $code,
                'name'          => $name,
                'description'   => $notes !== '' ? $notes : null,
                'category_id'   => $categoryId,
                'cost_price'    => $costPrice,
                'selling_price' => $sellingPrice,
            ]);

            $newProductId = (int) $this->db->lastInsertId();

            $insInv = $this->db->prepare("
                INSERT INTO inventory (
                    product_id, quantity_on_hand, quantity_reserved, quantity_damaged,
                    reorder_level, reorder_quantity, last_stock_in_at
                ) VALUES (
                    :pid, :qty, 0, 0, :reorder, 50, :last_in
                )
            ");
            $insInv->execute([
                'pid'     => $newProductId,
                'qty'     => $initialQty,
                'reorder' => $reorderLevel,
                'last_in' => $initialQty > 0 ? date('Y-m-d H:i:s') : null,
            ]);

            if ($initialQty > 0) {
                $smStmt = $this->db->prepare("
                    INSERT INTO stock_movements (
                        product_id, movement_type, quantity, quantity_before, quantity_after,
                        unit_cost, reference_type, notes, created_by, created_at
                    ) VALUES (
                        :product_id, 'adjustment_in', :quantity, 0, :quantity_after,
                        :unit_cost, 'initial_stock', :notes, :created_by, NOW()
                    )
                ");
                $smStmt->execute([
                    'product_id'     => $newProductId,
                    'quantity'       => $initialQty,
                    'quantity_after' => $initialQty,
                    'unit_cost'      => $costPrice,
                    'notes'          => $notes !== '' ? $notes : 'Initial stock setup',
                    'created_by'     => $userId,
                ]);
            }

            $this->db->commit();

            return [
                'id'           => $newProductId,
                'partNo'       => $code,
                'name'         => $name,
                'category'     => $category['name'],
                'location'     => $location,
                'bin'          => '',
                'qty'          => $initialQty,
                'reorderLevel' => $reorderLevel,
                'status'       => $this->deriveStatus($initialQty, $reorderLevel),
                'lastMovement' => $initialQty > 0 ? 'Just now (In)' : 'No movements yet',
                'unitCost'     => $costPrice,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Soft-delete an inventory product while preserving its stock history.
     */
    public function deleteItem(int $productId): array
    {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('Please select a valid inventory item.');
        }

        $stmt = $this->db->prepare(
            "UPDATE products
             SET deleted_at = NOW()
             WHERE id = :id
               AND deleted_at IS NULL"
        );
        $stmt->execute(['id' => $productId]);

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Inventory item not found or already deleted.');
        }

        return ['id' => $productId];
    }

    /**
     * Map a raw database row into the shape the front-end expects.
     */
    private function mapItem(array $row): array
    {
        $qty = (int) $row['quantity_on_hand'];
        $reorder = (int) $row['reorder_level'];

        return [
            'id'           => (int) $row['id'],
            'partNo'       => $row['product_code'],
            'name'         => $row['name'],
            'category'     => $row['category'],
            'location'     => 'Main Warehouse',
            'bin'          => '',
            'qty'          => $qty,
            'reorderLevel' => $reorder,
            'status'       => $this->deriveStatus($qty, $reorder),
            'lastMovement' => $this->formatLastMovement($row),
            'unitCost'     => (float) $row['cost_price'],
        ];
    }

    /**
     * Derive a stock status. Mirrors the front-end logic so the data is
     * self-consistent even before the script runs.
     */
    public function deriveStatus(int $qty, int $reorder): string
    {
        $criticalThreshold = max(1, intdiv($reorder, 2));

        if ($qty <= $criticalThreshold) {
            return 'critical';
        }
        if ($qty <= $reorder) {
            return 'low';
        }

        return 'optimal';
    }

    private function normalizeLocation(string $location): string
    {
        $location = trim($location);

        if ($location === '' || $location === 'Main Warehouse') {
            return 'Main Warehouse';
        }

        throw new \InvalidArgumentException('The selected inventory location is not available.');
    }

    /**
     * Build a human readable "last movement" string from the latest
     * stock movement, falling back to the inventory timestamps.
     */
    private function formatLastMovement(array $row): string
    {
        if (!empty($row['last_movement_at'])) {
            $direction = ((int) $row['last_movement_qty']) >= 0 ? 'In' : 'Out';
            $note = !empty($row['last_movement_notes']) ? ' — ' . $row['last_movement_notes'] : '';

            return $this->relativeTime($row['last_movement_at']) . ' (' . $direction . ')' . $note;
        }

        if (!empty($row['last_stock_in_at'])) {
            return $this->relativeTime($row['last_stock_in_at']) . ' (In)';
        }

        if (!empty($row['last_stock_out_at'])) {
            return $this->relativeTime($row['last_stock_out_at']) . ' (Out)';
        }

        return 'No movements yet';
    }

    /**
     * Convert a datetime into a short relative label.
     */
    private function relativeTime(string $datetime): string
    {
        $time = strtotime($datetime);
        if ($time === false) {
            return $datetime;
        }

        $diff = time() - $time;

        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' min ago';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        }
        if ($diff < 86400 * 7) {
            return floor($diff / 86400) . ' days ago';
        }

        return date('M j, Y', $time);
    }
}