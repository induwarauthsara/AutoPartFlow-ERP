<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class VehicleFinder extends Model
{
    protected string $table = 'vehicle_brands';

    public function getBrands(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, country
             FROM vehicle_brands
             WHERE is_active = 1
             ORDER BY name"
        );
        return $stmt->fetchAll();
    }

    public function getModels(int $brandId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, body_type
             FROM vehicle_models
             WHERE vehicle_brand_id = :brand_id
               AND is_active = 1
             ORDER BY name"
        );
        $stmt->execute(['brand_id' => $brandId]);
        return $stmt->fetchAll();
    }

    public function getEngines(int $modelId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, engine_code, displacement_cc, fuel_type, transmission, year_from, year_to
             FROM vehicle_engines
             WHERE vehicle_model_id = :model_id
               AND is_active = 1
             ORDER BY engine_code, year_from"
        );
        $stmt->execute(['model_id' => $modelId]);
        return $stmt->fetchAll();
    }

    public function findCompatibleParts(int $brandId, int $modelId, int $engineId, int $year): array
{
    $stmt = $this->db->prepare(
        "SELECT DISTINCT
            p.id,
            p.product_code,
            p.name,
            p.description,
            p.unit,
            p.selling_price,
            p.warranty_months,
            b.name AS brand_name,
            c.name AS category_name,
            COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand,
            COALESCE(i.quantity_reserved, 0) AS quantity_reserved,
            pc.year_from AS compatibility_year_from,
            pc.year_to AS compatibility_year_to,
            pc.notes AS compatibility_notes
         FROM product_compatibility pc
         INNER JOIN products p ON p.id = pc.product_id
         LEFT JOIN brands b ON b.id = p.brand_id
         INNER JOIN categories c ON c.id = p.category_id
         LEFT JOIN inventory i ON i.product_id = p.id
         WHERE p.is_active = 1
           AND p.deleted_at IS NULL
           AND (pc.vehicle_brand_id IS NULL OR pc.vehicle_brand_id = :brand_id)
           AND (pc.vehicle_model_id IS NULL OR pc.vehicle_model_id = :model_id)
           AND (pc.vehicle_engine_id IS NULL OR pc.vehicle_engine_id = :engine_id)
           AND (pc.year_from IS NULL OR :year_from_check >= pc.year_from)
           AND (pc.year_to IS NULL OR :year_to_check <= pc.year_to)
         ORDER BY p.name"
    );

    $stmt->execute([
        'brand_id' => $brandId,
        'model_id' => $modelId,
        'engine_id' => $engineId,
        'year_from_check' => $year,
        'year_to_check' => $year,
    ]);

    return $stmt->fetchAll();
}

    public function getVehicleSelection(int $brandId, int $modelId, int $engineId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                vb.id AS brand_id, vb.name AS brand_name,
                vm.id AS model_id, vm.name AS model_name,
                ve.id AS engine_id, ve.engine_code, ve.year_from, ve.year_to
             FROM vehicle_engines ve
             INNER JOIN vehicle_models vm ON vm.id = ve.vehicle_model_id
             INNER JOIN vehicle_brands vb ON vb.id = vm.vehicle_brand_id
             WHERE vb.id = :brand_id
               AND vm.id = :model_id
               AND ve.id = :engine_id
               AND vb.is_active = 1
               AND vm.is_active = 1
               AND ve.is_active = 1
             LIMIT 1"
        );
        $stmt->execute([
            'brand_id' => $brandId,
            'model_id' => $modelId,
            'engine_id' => $engineId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
