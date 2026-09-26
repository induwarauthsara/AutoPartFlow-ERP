<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class VehicleFinder extends Model
{
    protected string $table = 'vehicle_brands';


    /**
     * Get active vehicle brands.
     */
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


    /**
     * Get active models belonging to a brand.
     */
    public function getModels(int $brandId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                id,
                name,
                body_type
             FROM vehicle_models
             WHERE vehicle_brand_id = :brand_id
               AND is_active = 1
             ORDER BY name"
        );

        $stmt->execute([
            'brand_id' => $brandId
        ]);

        return $stmt->fetchAll();
    }


    /**
     * Get active engines belonging to a model.
     */
    public function getEngines(int $modelId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                id,
                engine_code,
                displacement_cc,
                fuel_type,
                transmission,
                year_from,
                year_to
             FROM vehicle_engines
             WHERE vehicle_model_id = :model_id
               AND is_active = 1
             ORDER BY engine_code, year_from"
        );

        $stmt->execute([
            'model_id' => $modelId
        ]);

        return $stmt->fetchAll();
    }


    /**
     * Get vehicle information from an engine.
     *
     * Engine -> Model -> Brand
     */
    public function getEngineVehicle(int $engineId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ve.id AS engine_id,
                ve.engine_code,
                ve.year_from,
                ve.year_to,

                vm.id AS model_id,
                vm.name AS model_name,

                vb.id AS brand_id,
                vb.name AS brand_name

             FROM vehicle_engines ve

             INNER JOIN vehicle_models vm
                ON vm.id = ve.vehicle_model_id

             INNER JOIN vehicle_brands vb
                ON vb.id = vm.vehicle_brand_id

             WHERE ve.id = :engine_id
               AND ve.is_active = 1
               AND vm.is_active = 1
               AND vb.is_active = 1

             LIMIT 1"
        );

        $stmt->execute([
            'engine_id' => $engineId
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }


    /**
     * Get vehicle information from a model.
     *
     * Model -> Brand
     */
    public function getModelVehicle(int $modelId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                vm.id AS model_id,
                vm.name AS model_name,

                vb.id AS brand_id,
                vb.name AS brand_name

             FROM vehicle_models vm

             INNER JOIN vehicle_brands vb
                ON vb.id = vm.vehicle_brand_id

             WHERE vm.id = :model_id
               AND vm.is_active = 1
               AND vb.is_active = 1

             LIMIT 1"
        );

        $stmt->execute([
            'model_id' => $modelId
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }


    /**
     * Get vehicle information from a brand.
     */
    public function getBrandVehicle(int $brandId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                id AS brand_id,
                name AS brand_name

             FROM vehicle_brands

             WHERE id = :brand_id
               AND is_active = 1

             LIMIT 1"
        );

        $stmt->execute([
            'brand_id' => $brandId
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }


    /**
     * Find compatible spare parts.
     *
     * Brand, model, engine and year are all OPTIONAL.
     *
     * NULL compatibility values in product_compatibility mean
     * that the compatibility value is universal.
     */
    public function findCompatibleParts(
        ?int $brandId,
        ?int $modelId,
        ?int $engineId,
        ?int $year
    ): array {

        $sql = "
            SELECT DISTINCT

                p.id,
                p.product_code,
                p.name,
                p.description,
                p.unit,
                p.selling_price,
                p.warranty_months,

                b.name AS brand_name,
                c.name AS category_name,

                COALESCE(i.quantity_on_hand, 0)
                    AS quantity_on_hand,

                COALESCE(i.quantity_reserved, 0)
                    AS quantity_reserved,

                pc.year_from
                    AS compatibility_year_from,

                pc.year_to
                    AS compatibility_year_to,

                pc.notes
                    AS compatibility_notes

            FROM product_compatibility pc

            INNER JOIN products p
                ON p.id = pc.product_id

            LEFT JOIN brands b
                ON b.id = p.brand_id

            INNER JOIN categories c
                ON c.id = p.category_id

            LEFT JOIN inventory i
                ON i.product_id = p.id

            WHERE p.is_active = 1
              AND p.deleted_at IS NULL
        ";

        $params = [];


        /*
         * BRAND
         *
         * If no brand was selected, don't add a brand condition.
         */
        if ($brandId !== null) {

            $sql .= "
                AND (
                    pc.vehicle_brand_id IS NULL
                    OR pc.vehicle_brand_id = :brand_id
                )
            ";

            $params['brand_id'] = $brandId;
        }


        /*
         * MODEL
         */
        if ($modelId !== null) {

            $sql .= "
                AND (
                    pc.vehicle_model_id IS NULL
                    OR pc.vehicle_model_id = :model_id
                )
            ";

            $params['model_id'] = $modelId;
        }


        /*
         * ENGINE
         */
        if ($engineId !== null) {

            $sql .= "
                AND (
                    pc.vehicle_engine_id IS NULL
                    OR pc.vehicle_engine_id = :engine_id
                )
            ";

            $params['engine_id'] = $engineId;
        }


        /*
         * YEAR
         *
         * Use TWO different placeholders.
         *
         * This avoids PDO HY093 errors caused by using
         * the same named parameter more than once.
         */
        if ($year !== null) {

            $sql .= "
                AND (
                    pc.year_from IS NULL
                    OR pc.year_from <= :year_from
                )

                AND (
                    pc.year_to IS NULL
                    OR pc.year_to >= :year_to
                )
            ";

            $params['year_from'] = $year;
            $params['year_to'] = $year;
        }


        $sql .= "
            ORDER BY p.name
        ";


        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll();
    }


    /**
     * Get a complete vehicle selection.
     *
     * Used when brand + model + engine are all selected.
     */
    public function getVehicleSelection(
        int $brandId,
        int $modelId,
        int $engineId
    ): ?array {

        $stmt = $this->db->prepare(
            "SELECT

                vb.id AS brand_id,
                vb.name AS brand_name,

                vm.id AS model_id,
                vm.name AS model_name,

                ve.id AS engine_id,
                ve.engine_code,
                ve.year_from,
                ve.year_to

             FROM vehicle_engines ve

             INNER JOIN vehicle_models vm
                ON vm.id = ve.vehicle_model_id

             INNER JOIN vehicle_brands vb
                ON vb.id = vm.vehicle_brand_id

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
            'engine_id' => $engineId
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }
}