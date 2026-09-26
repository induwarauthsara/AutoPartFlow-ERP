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

        $currentYear = (int) date('Y');
        $hasSearchCriteria = (
            $brandId > 0 ||
            $modelId > 0 ||
            $engineId > 0 ||
            $year > 0
        );

        /*
         * Validate year only when the user entered one.
         */
        if (
            $year !== 0 &&
            ($year < 1900 || $year > ($currentYear + 1))
        ) {
            $error = 'Please enter a valid vehicle year.';
        }

        /*
         * Do not search if all four fields are empty.
         */
        if (!$error && $hasSearchCriteria) {

            /*
             * If an engine was selected, use the database to determine
             * its actual model and brand.
             */
            if ($engineId > 0) {

                $vehicle = $finder->getEngineVehicle($engineId);

                if (!$vehicle) {
                    $error = 'The selected engine could not be verified.';
                } else {
                    /*
                     * Prevent invalid combinations such as:
                     * Toyota + Honda Model + Toyota Engine.
                     */
                    if (
                        ($modelId > 0 &&
                            (int) $vehicle['model_id'] !== $modelId) ||
                        ($brandId > 0 &&
                            (int) $vehicle['brand_id'] !== $brandId)
                    ) {
                        $error = 'The selected vehicle combination is invalid.';
                    } else {
                        $selection = $vehicle;
                    }
                }

            /*
             * If model was selected without an engine.
             */
            } elseif ($modelId > 0) {

                $vehicle = $finder->getModelVehicle($modelId);

                if (!$vehicle) {
                    $error = 'The selected vehicle model could not be verified.';
                } else {
                    /*
                     * Make sure the selected model belongs to the
                     * selected make.
                     */
                    if (
                        $brandId > 0 &&
                        (int) $vehicle['brand_id'] !== $brandId
                    ) {
                        $error = 'The selected vehicle combination is invalid.';
                    } else {
                        $selection = $vehicle;
                    }
                }

            /*
             * Only make was selected.
             */
            } elseif ($brandId > 0) {

                $vehicle = $finder->getBrandVehicle($brandId);

                if (!$vehicle) {
                    $error = 'The selected vehicle make could not be verified.';
                } else {
                    $selection = $vehicle;
                }
            }

            /*
             * Run the compatibility search.
             *
             * Any of the four values can be NULL.
             */
            if (!$error) {
                $results = $finder->findCompatibleParts(
                    $brandId > 0 ? $brandId : null,
                    $modelId > 0 ? $modelId : null,
                    $engineId > 0 ? $engineId : null,
                    $year > 0 ? $year : null
                );
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

            'hasSearchCriteria' => $hasSearchCriteria,

        ], 'public');
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