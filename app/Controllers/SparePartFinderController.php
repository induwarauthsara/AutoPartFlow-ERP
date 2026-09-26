<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\VehicleFinder;

class SparePartFinderController extends Controller
{
    public function index(): void
    {
        $finder = new VehicleFinder();
        $brandId = (int) $this->input('brand_id', 0);
        $modelId = (int) $this->input('model_id', 0);
        $engineId = (int) $this->input('engine_id', 0);
        $year = (int) $this->input('year', 0);
        $results = [];
        $selection = null;
        $error = null;

        if ($brandId && $modelId && $engineId && $year >= 1900 && $year <= ((int) date('Y') + 1)) {
            $selection = $finder->getVehicleSelection($brandId, $modelId, $engineId);
            if ($selection) {
                $minYear = (int) $selection['year_from'];
                $maxYear = $selection['year_to'] !== null ? (int) $selection['year_to'] : ((int) date('Y') + 1);
                if ($year < $minYear || $year > $maxYear) {
                    $error = "The selected engine is available for model years {$minYear}–{$maxYear}.";
                } else {
                    $results = $finder->findCompatibleParts($brandId, $modelId, $engineId, $year);
                }
            } else {
                $error = 'The selected vehicle combination could not be verified.';
            }
        }

        $this->view('parts/finder', [
            'title' => 'Spare Part Finder | AutoPartFlow',
            'brands' => $finder->getBrands(),
            'brandId' => $brandId,
            'modelId' => $modelId,
            'engineId' => $engineId,
            'year' => $year,
            'selection' => $selection,
            'results' => $results,
            'error' => $error,
        ], 'public');
    }

    public function models(): void
    {
        $brandId = (int) $this->input('brand_id', 0);
        if ($brandId <= 0) {
            $this->json(['status' => 'error', 'message' => 'Invalid vehicle brand.'], 422);
            return;
        }

        $this->json(['status' => 'success', 'models' => (new VehicleFinder())->getModels($brandId)]);
    }

    public function engines(): void
    {
        $modelId = (int) $this->input('model_id', 0);
        if ($modelId <= 0) {
            $this->json(['status' => 'error', 'message' => 'Invalid vehicle model.'], 422);
            return;
        }

        $this->json(['status' => 'success', 'engines' => (new VehicleFinder())->getEngines($modelId)]);
    }
}
