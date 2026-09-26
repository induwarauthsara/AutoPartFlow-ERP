<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Product extends Model
{
    protected string $table = 'products';

    /**
 * Get catalog products based on search, categories, brands, and sort filters.
 */
public function catalog(array $filters = []): array
{
    $sql = "
        SELECT
            p.id,
            p.product_code,
            p.barcode,
            p.name,
            p.description,
            p.unit,
            p.cost_price,
            p.selling_price,
            p.wholesale_price,
            p.tax_rate,
            p.warranty_months,
            p.specifications,
            p.image_path,
            c.id AS category_id,
            c.name AS category,
            b.id AS brand_id,
            b.name AS brand,
            COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand,
            COALESCE(i.reorder_level, 0) AS reorder_level
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        LEFT JOIN brands b ON b.id = p.brand_id
        LEFT JOIN inventory i ON i.product_id = p.id
        WHERE p.is_active = 1
          AND p.deleted_at IS NULL
          AND c.is_active = 1
          AND c.deleted_at IS NULL
    ";

    $params = [];

    /*
     * Search
     *
     * Use a separate placeholder for every LIKE condition.
     * This avoids PDO HY093 errors caused by reusing :search.
     */
    if (!empty($filters['search'])) {
        $sql .= "
            AND (
                p.name LIKE :search_name
                OR p.product_code LIKE :search_code
                OR p.barcode LIKE :search_barcode
                OR p.description LIKE :search_description
                OR c.name LIKE :search_category
                OR b.name LIKE :search_brand
            )
        ";

        $search = '%' . trim((string) $filters['search']) . '%';

        $params['search_name'] = $search;
        $params['search_code'] = $search;
        $params['search_barcode'] = $search;
        $params['search_description'] = $search;
        $params['search_category'] = $search;
        $params['search_brand'] = $search;
    }

    /*
     * Category filters
     */
    if (!empty($filters['categories'])) {
        $placeholders = [];

        foreach ($filters['categories'] as $index => $category) {
            $key = 'category_' . $index;

            $placeholders[] = ':' . $key;
            $params[$key] = $category;
        }

        $sql .= ' AND c.name IN (' . implode(', ', $placeholders) . ')';
    }

    /*
     * Brand filters
     */
    if (!empty($filters['brands'])) {
        $placeholders = [];

        foreach ($filters['brands'] as $index => $brand) {
            $key = 'brand_' . $index;

            $placeholders[] = ':' . $key;
            $params[$key] = $brand;
        }

        $sql .= ' AND b.name IN (' . implode(', ', $placeholders) . ')';
    }

    /*
     * Sorting
     */
    $sort = $filters['sort'] ?? 'relevance';

    $orderBy = match ($sort) {
        'price_asc'  => 'p.selling_price ASC, p.id ASC',
        'price_desc' => 'p.selling_price DESC, p.id ASC',
        'name_asc'   => 'p.name ASC, p.id ASC',
        default      => 'p.id ASC',
    };

    $sql .= ' ORDER BY ' . $orderBy;

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

    /**
     * Get vehicle compatibility list for a product.
     */
    public function getCompatibility(int $productId): array
    {
        $sql = "
            SELECT
                pc.id,
                pc.product_id,
                pc.vehicle_brand_id,
                pc.vehicle_model_id,
                pc.vehicle_engine_id,
                vb.name AS vehicle_brand,
                vm.name AS vehicle_model,
                ve.engine_code,
                ve.fuel_type,
                ve.transmission,
                COALESCE(pc.year_from, ve.year_from) AS year_from,
                COALESCE(pc.year_to, ve.year_to) AS year_to,
                pc.notes
            FROM product_compatibility pc
            LEFT JOIN vehicle_brands vb ON vb.id = pc.vehicle_brand_id
            LEFT JOIN vehicle_models vm ON vm.id = pc.vehicle_model_id
            LEFT JOIN vehicle_engines ve ON ve.id = pc.vehicle_engine_id
            WHERE pc.product_id = :product_id
            ORDER BY vb.name, vm.name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll();
    }

    /**
     * Find single product by its unique product code.
     */
    public function findByCode(string $code): ?array
    {
        $sql = "
            SELECT
                p.*,
                c.name AS category,
                b.name AS brand,
                COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand,
                COALESCE(i.reorder_level, 0) AS reorder_level
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.product_code = :code
              AND p.deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                p.*,
                c.name AS category,
                b.name AS brand,
                COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand,
                COALESCE(i.reorder_level, 0) AS reorder_level
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.id = :id
              AND p.deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Create a new product with initial inventory and optional vehicle compatibility.
     */
    public function createProduct(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $categoryId = (int) ($data['category_id'] ?? 0);
        $sellingPrice = (float) ($data['selling_price'] ?? 0);

        if ($name === '' || $categoryId <= 0 || $sellingPrice <= 0) {
            throw new \InvalidArgumentException('Product name, category, and selling price are required.');
        }

        $this->db->beginTransaction();
        try {
            $next = (int) $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM products FOR UPDATE')->fetchColumn();
            $code = !empty($data['product_code']) ? trim((string) $data['product_code']) : ('PRD-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT));
            $barcode = !empty($data['barcode']) ? trim((string) $data['barcode']) : null;
            $brandId = !empty($data['brand_id']) ? (int) $data['brand_id'] : null;
            $costPrice = (float) ($data['cost_price'] ?? 0);
            $wholesalePrice = !empty($data['wholesale_price']) ? (float) $data['wholesale_price'] : null;
            $warrantyMonths = (int) ($data['warranty_months'] ?? 12);
            $description = trim((string) ($data['description'] ?? ''));
            $imagePath = trim((string) ($data['image_path'] ?? ''));
            $initialStock = (int) ($data['initial_stock'] ?? 10);
            $reorderLevel = (int) ($data['reorder_level'] ?? 5);

            $stmt = $this->db->prepare(
                'INSERT INTO products (product_code, barcode, name, description, category_id, brand_id, cost_price, selling_price, wholesale_price, warranty_months, image_path, is_active)
                 VALUES (:code, :barcode, :name, :desc, :cat, :brand, :cost, :price, :wholesale, :warranty, :img, 1)'
            );
            $stmt->execute([
                'code' => $code,
                'barcode' => $barcode,
                'name' => $name,
                'desc' => $description ?: null,
                'cat' => $categoryId,
                'brand' => $brandId,
                'cost' => $costPrice,
                'price' => $sellingPrice,
                'wholesale' => $wholesalePrice,
                'warranty' => $warrantyMonths,
                'img' => $imagePath ?: null,
            ]);
            $productId = (int) $this->db->lastInsertId();

            // Create inventory entry
            $invStmt = $this->db->prepare(
                'INSERT INTO inventory (product_id, quantity_on_hand, reorder_level, last_stock_in_at)
                 VALUES (:pid, :qty, :reorder, NOW())'
            );
            $invStmt->execute([
                'pid' => $productId,
                'qty' => $initialStock,
                'reorder' => $reorderLevel,
            ]);

            // Add vehicle compatibility if provided
            if (!empty($data['vehicle_brand_id'])) {
                $compStmt = $this->db->prepare(
                    'INSERT INTO product_compatibility (product_id, vehicle_brand_id, vehicle_model_id, vehicle_engine_id, year_from, year_to, notes)
                     VALUES (:pid, :vbrand, :vmodel, :vengine, :yfrom, :yto, :notes)'
                );
                $compStmt->execute([
                    'pid' => $productId,
                    'vbrand' => (int) $data['vehicle_brand_id'],
                    'vmodel' => !empty($data['vehicle_model_id']) ? (int) $data['vehicle_model_id'] : null,
                    'vengine' => !empty($data['vehicle_engine_id']) ? (int) $data['vehicle_engine_id'] : null,
                    'yfrom' => !empty($data['year_from']) ? (int) $data['year_from'] : null,
                    'yto' => !empty($data['year_to']) ? (int) $data['year_to'] : null,
                    'notes' => trim((string) ($data['compat_notes'] ?? 'Standard fitment')),
                ]);
            }

            $this->db->commit();
            return ['id' => $productId, 'product_code' => $code, 'name' => $name];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(int $id, array $data): bool
    {
        $name = trim((string) ($data['name'] ?? ''));
        $categoryId = (int) ($data['category_id'] ?? 0);
        $sellingPrice = (float) ($data['selling_price'] ?? 0);

        if ($id <= 0 || $name === '' || $categoryId <= 0 || $sellingPrice <= 0) {
            throw new \InvalidArgumentException('Product ID, name, category, and selling price are required.');
        }

        $this->db->beginTransaction();
        try {
            $brandId = !empty($data['brand_id']) ? (int) $data['brand_id'] : null;
            $costPrice = (float) ($data['cost_price'] ?? 0);
            $wholesalePrice = !empty($data['wholesale_price']) ? (float) $data['wholesale_price'] : null;
            $warrantyMonths = (int) ($data['warranty_months'] ?? 12);
            $description = trim((string) ($data['description'] ?? ''));
            $imagePath = trim((string) ($data['image_path'] ?? ''));
            $isActive = isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1;

            $stmt = $this->db->prepare(
                'UPDATE products SET name=:name, description=:desc, category_id=:cat, brand_id=:brand,
                                    cost_price=:cost, selling_price=:price, wholesale_price=:wholesale,
                                    warranty_months=:warranty, image_path=:img, is_active=:active
                 WHERE id=:id AND deleted_at IS NULL'
            );
            $stmt->execute([
                'name' => $name,
                'desc' => $description ?: null,
                'cat' => $categoryId,
                'brand' => $brandId,
                'cost' => $costPrice,
                'price' => $sellingPrice,
                'wholesale' => $wholesalePrice,
                'warranty' => $warrantyMonths,
                'img' => $imagePath ?: null,
                'active' => $isActive,
                'id' => $id,
            ]);

            if (isset($data['reorder_level'])) {
                $reorder = (int) $data['reorder_level'];
                $updInv = $this->db->prepare('UPDATE inventory SET reorder_level = :reorder WHERE product_id = :pid');
                $updInv->execute(['reorder' => $reorder, 'pid' => $id]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete product.
     */
    public function deleteProduct(int $id): bool
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('Valid product ID is required.');
        }

        $stmt = $this->db->prepare('UPDATE products SET deleted_at = NOW(), is_active = 0 WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function saveCompatibility(int $productId, array $data): int
    {
        $vbrandId = (int) ($data['vehicle_brand_id'] ?? 0);
        if ($productId <= 0 || $vbrandId <= 0) {
            throw new \InvalidArgumentException('Product ID and vehicle brand are required.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO product_compatibility (product_id, vehicle_brand_id, vehicle_model_id, vehicle_engine_id, year_from, year_to, notes)
             VALUES (:pid, :vbrand, :vmodel, :vengine, :yfrom, :yto, :notes)'
        );
        $stmt->execute([
            'pid' => $productId,
            'vbrand' => $vbrandId,
            'vmodel' => !empty($data['vehicle_model_id']) ? (int) $data['vehicle_model_id'] : null,
            'vengine' => !empty($data['vehicle_engine_id']) ? (int) $data['vehicle_engine_id'] : null,
            'yfrom' => !empty($data['year_from']) ? (int) $data['year_from'] : null,
            'yto' => !empty($data['year_to']) ? (int) $data['year_to'] : null,
            'notes' => trim((string) ($data['notes'] ?? 'Vehicle fitment mapped')),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteCompatibility(int $compatId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM product_compatibility WHERE id = :id');
        return $stmt->execute(['id' => $compatId]);
    }

    public function vehicleBrands(): array
    {
        return $this->db->query('SELECT id, name, country FROM vehicle_brands WHERE is_active = 1 ORDER BY name')->fetchAll();
    }

    public function vehicleModels(?int $brandId = null): array
    {
        if ($brandId) {
            $stmt = $this->db->prepare('SELECT id, vehicle_brand_id, name, body_type FROM vehicle_models WHERE vehicle_brand_id = :bid AND is_active = 1 ORDER BY name');
            $stmt->execute(['bid' => $brandId]);
            return $stmt->fetchAll();
        }
        return $this->db->query('SELECT id, vehicle_brand_id, name, body_type FROM vehicle_models WHERE is_active = 1 ORDER BY name')->fetchAll();
    }

    public function vehicleEngines(?int $modelId = null): array
    {
        if ($modelId) {
            $stmt = $this->db->prepare('SELECT id, vehicle_model_id, engine_code, displacement_cc, fuel_type, year_from, year_to FROM vehicle_engines WHERE vehicle_model_id = :mid AND is_active = 1 ORDER BY engine_code');
            $stmt->execute(['mid' => $modelId]);
            return $stmt->fetchAll();
        }
        return $this->db->query('SELECT id, vehicle_model_id, engine_code, displacement_cc, fuel_type, year_from, year_to FROM vehicle_engines WHERE is_active = 1 ORDER BY engine_code')->fetchAll();
    }

    /**
     * Find only fields that are safe for the public product-details endpoint.
     * Internal cost_price and wholesale_price are deliberately excluded.
     */
    public function findPublicByCode(string $code): ?array
    {
        $sql = "
            SELECT
                p.id, p.product_code, p.barcode, p.name, p.description,
                p.unit, p.selling_price, p.tax_rate, p.warranty_months,
                p.image_path, p.specifications, p.is_active,
                c.name AS category,
                b.name AS brand,
                COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand,
                COALESCE(i.reorder_level, 0) AS reorder_level
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.product_code = :code
              AND p.is_active = 1
              AND p.deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Fetch active categories.
     */
    public function categories(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, slug FROM categories
             WHERE is_active = 1 AND deleted_at IS NULL
             ORDER BY name"
        );

        return $stmt->fetchAll();
    }

    /**
     * Fetch active brands.
     */
    public function brands(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, slug, country FROM brands
             WHERE is_active = 1 AND deleted_at IS NULL
             ORDER BY name"
        );

        return $stmt->fetchAll();
    }
}
