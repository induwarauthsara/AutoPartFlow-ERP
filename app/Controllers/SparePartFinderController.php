<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\VehicleFinder;

class SparePartFinderController extends Controller
{
    public function index(): void
    {
        $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
        header('Location: ' . url('catalog' . $queryString), true, 302);
        exit;
    }


    /**
     * Load models belonging to a vehicle brand.
     */
    public function models(): void
    {
        $brandId = (int) $this->input('brand_id', 0);

        if ($brandId <= 0) {
            $this->json([
                'status' => 'error',
                'message' => 'Invalid vehicle brand.'
            ], 422);

            return;
        }

        $this->json([
            'status' => 'success',
            'models' => (new VehicleFinder())->getModels($brandId)
        ]);
    }


    /**
     * Load engines belonging to a vehicle model.
     */
    public function engines(): void
    {
        $modelId = (int) $this->input('model_id', 0);

        if ($modelId <= 0) {
            $this->json([
                'status' => 'error',
                'message' => 'Invalid vehicle model.'
            ], 422);

            return;
        }

        $this->json([
            'status' => 'success',
            'engines' => (new VehicleFinder())->getEngines($modelId)
        ]);
    }
}