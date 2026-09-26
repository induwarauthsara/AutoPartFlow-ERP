<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

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

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.product_code LIKE :search OR p.barcode LIKE :search OR p.description LIKE :search OR c.name LIKE :search OR b.name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['categories'])) {
            $placeholders = [];
            foreach ($filters['categories'] as $index => $category) {
                $key = 'category_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $category;
            }
            $sql .= ' AND c.name IN (' . implode(', ', $placeholders) . ')';
        }

        if (!empty($filters['brands'])) {
            $placeholders = [];
            foreach ($filters['brands'] as $index => $brand) {
                $key = 'brand_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $brand;
            }
            $sql .= ' AND b.name IN (' . implode(', ', $placeholders) . ')';
        }

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
