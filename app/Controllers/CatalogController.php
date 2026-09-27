<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class CatalogController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $selectedCategories = $this->input('category', []);
        $selectedBrands = $this->input('brand', []);

        $selectedCategories = is_array($selectedCategories) ? $selectedCategories : [$selectedCategories];
        $selectedBrands = is_array($selectedBrands) ? $selectedBrands : [$selectedBrands];

        $vBrandId = (int) ($this->input('vehicle_brand_id') ?: $this->input('brand_id', 0));
        $vModelId = (int) ($this->input('vehicle_model_id') ?: $this->input('model_id', 0));
        $vEngineId = (int) ($this->input('vehicle_engine_id') ?: $this->input('engine_id', 0));
        $vYear = (int) $this->input('year', 0);

        $currentYear = (int) date('Y');
        if ($vYear !== 0 && ($vYear < 1900 || $vYear > ($currentYear + 1))) {
            $vYear = 0;
        }

        $hasVehicleFilter = ($vBrandId > 0 || $vModelId > 0 || $vEngineId > 0 || $vYear > 0);

        $filters = [
            'search'            => trim((string) $this->input('q', '')),
            'categories'        => array_values(array_filter($selectedCategories)),
            'brands'            => array_values(array_filter($selectedBrands)),
            'sort'              => (string) $this->input('sort', 'relevance'),
            'vehicle_brand_id'  => $vBrandId > 0 ? $vBrandId : null,
            'vehicle_model_id'  => $vModelId > 0 ? $vModelId : null,
            'vehicle_engine_id' => $vEngineId > 0 ? $vEngineId : null,
            'vehicle_year'      => $vYear > 0 ? $vYear : null,
        ];

        $products = $this->productModel->catalog($filters);

        // Vehicle info lookup for display
        $vehicleSelectionTitle = '';
        $selectedBrandName = '';
        $selectedModelName = '';
        $selectedEngineCode = '';

        if ($hasVehicleFilter) {
            $finder = new \App\Models\VehicleFinder();
            if ($vEngineId > 0) {
                $veh = $finder->getEngineVehicle($vEngineId);
                if ($veh) {
                    $selectedBrandName = $veh['brand_name'] ?? '';
                    $selectedModelName = $veh['model_name'] ?? '';
                    $selectedEngineCode = $veh['engine_code'] ?? '';
                }
            } elseif ($vModelId > 0) {
                $veh = $finder->getModelVehicle($vModelId);
                if ($veh) {
                    $selectedBrandName = $veh['brand_name'] ?? '';
                    $selectedModelName = $veh['model_name'] ?? '';
                }
            } elseif ($vBrandId > 0) {
                $veh = $finder->getBrandVehicle($vBrandId);
                if ($veh) {
                    $selectedBrandName = $veh['brand_name'] ?? '';
                }
            }

            $titleParts = [];
            if ($vYear > 0) {
                $titleParts[] = (string) $vYear;
            }
            if (!empty($selectedBrandName)) {
                $titleParts[] = $selectedBrandName;
            }
            if (!empty($selectedModelName)) {
                $titleParts[] = $selectedModelName;
            }
            if (!empty($selectedEngineCode)) {
                $titleParts[] = '(' . $selectedEngineCode . ')';
            }

            $vehicleSelectionTitle = implode(' ', $titleParts);
        }

        // Product Catalog image fallback map
        $imageMap = [
            'PRD-00001' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7ayr4--XmcXIdj2_0j20NXNhwrNFVFHPhCw6RsqQzlx5S9U0D7O2rVc9ojrLLKdEKNRs_zVwHUqXJ27qp0gKJyX_jCNLzmZ5uNGoqmVVexNpeSpo7V6sga3SXzMhmANQkAmz2OjyAo-Dff3zk1sshDzp6foTQUI_o3PWFatdXSTJ6xV7rRlLA8AJj4o5ePetMhF_M02G8T_r04Hln6nW8W11Z-uQr_sbIIhz9GsTWqTzK_eJWRUk3IQ',
            'PRD-00002' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDXVa7PI7qpTpWwjgiCBwpq8ISA56sfTdR6HuO9PHQnt54ZoUHSSbDb3LknhC7yy_nJZgMspkICqPGjvbb4Pp0b_1yODnIhsnuB8rDnkJCm2sR516fd9O9b7syPNg0FxtsXpKH3yyiwigE59xh_MojH9cNCCOyzREH4v80JbGmmr3zCGZq7rj6ZZHJ5-PBTitAe7zb37bbkmOzlf_6V2i4OFKnXBMQEFFHZlPN9sDBpu-mfJBIxkG9Y9g',
            'PRD-00003' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDbNtYLS-IoL-F11aS1DyKBafmPcMgfW4tiNId1TA526e0jZHa5T43u3hk0hQYCoOPb16i2_2iIXRpcs_pL1dwOqWfb9S98LcLbXg9xMlZCg_sapW6i9xm6kRlazc3l5_OSJw_qRKQL-mV_Cy2pGxzSy6jo97qQe0F_am0tjT8Wm8xGhCiuEs11JMgUwwwizmSgen-MZfkzNUTa_OSDuphOigU7itdLhvTr1v-nVO6Ksi1g2pVNGVIymA',
            'PRD-00004' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDp1g1Jjfz_qJpnd44qiiNhUd-JxBd0WpkvzZIlluvXVLDIAFFXJNX-heHxAvAiQbo9WQsPS0fPW6FuW6b2rLw-839uh_ScmLlbK19JvReOfXmVLh7nuQrJdhuOamhwnmEZ1XEf4r01b5akqxzLcmhDmt0gB_ypQAo-EKOlykWUQP0AXMqJIdrqT6r-4quCr6EgfVBkPHmwuMwomLxTGuGaY-Q2h5K9PA5hG2ow1VEoEOSLnuzb4DweDA',
            'PRD-00005' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBDARRJpu8-yk_D7G5BU-lm8TOrhf_WsDfk91WFhOHxaWdBYJoDvCGxHu505DRZLBE1Dzw946jk5UOz8hn1QmTRD54LpGCjMvv0xZi86BIP5rF14-xGYxVVxcVA0LTZQfl_zI6SHAxZZ1KJdajLLVLtmJsPlLm9ZFq1nHHbNAwwrhbHzYxRXZn54op1dWWakqJlTo-MqNmCHShHIf39M3XfQDqBHGLbCtevRzUItz90QinjTJw2CUw90g',
            'PRD-00006' => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=400&q=80',
            'PRD-00007' => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=400&q=80',
            'PRD-00008' => 'https://images.unsplash.com/photo-1615906655593-ad0386982a0f?auto=format&fit=crop&w=400&q=80',
        ];

        foreach ($products as &$product) {
            $product['display_image'] = !empty($product['image_path']) ? $product['image_path'] : ($imageMap[$product['product_code']] ?? '');
            $qty = (int) ($product['quantity_on_hand'] ?? 0);
            $reorder = (int) ($product['reorder_level'] ?? 0);
            $product['stock_status'] = $qty <= 0 ? 'Out of Stock' : ($qty <= $reorder ? 'Low Stock' : 'In Stock');
        }
        unset($product);

        $this->view('catalog/index', [
            'title' => 'Product Catalog & Spare Part Finder | AutoPartFlow',
            'products' => $products,
            'categories' => $this->productModel->categories(),
            'brands' => $this->productModel->brands(),
            'vehicleBrands' => $this->productModel->vehicleBrands(),
            'vehicleModels' => $vBrandId > 0 ? $this->productModel->vehicleModels($vBrandId) : [],
            'vehicleEngines' => $vModelId > 0 ? $this->productModel->vehicleEngines($vModelId) : [],
            'filters' => $filters,
            'vehicleFilter' => [
                'brand_id'  => $vBrandId,
                'model_id'  => $vModelId,
                'engine_id' => $vEngineId,
                'year'      => $vYear,
            ],
            'vehicleSelectionTitle' => $vehicleSelectionTitle,
            'hasVehicleFilter' => $hasVehicleFilter,
            'canManageCatalog' => in_array((string) ($_SESSION['role_slug'] ?? ''), ['owner', 'store_manager'], true),
        ], 'public');
    }

    public function compatibility(): void
    {
        $productId = (int) $this->input('product_id', 0);
        $productCode = (string) $this->input('product_code', '');

        if ($productId === 0 && !empty($productCode)) {
            $product = $this->productModel->findByCode($productCode);
            if ($product) {
                $productId = (int) $product['id'];
            }
        }

        if ($productId <= 0) {
            $this->json(['status' => 'error', 'message' => 'Invalid product identifier.'], 400);
            return;
        }

        $compatibilities = $this->productModel->getCompatibility($productId);
        $this->json([
            'status' => 'success',
            'product_id' => $productId,
            'compatibility' => $compatibilities,
        ]);
    }

    public function details(): void
    {
        $code = trim((string) $this->input('code', ''));
        if (empty($code)) {
            $this->json(['status' => 'error', 'message' => 'Product code is required.'], 400);
            return;
        }

        $product = $this->productModel->findPublicByCode($code) ?? $this->productModel->findByCode($code);

        if (!$product) {
            $this->json(['status' => 'error', 'message' => 'Product not found.'], 404);
            return;
        }

        $qty = (int) ($product['quantity_on_hand'] ?? 0);
        $reorder = (int) ($product['reorder_level'] ?? 0);
        $product['stock_status'] = $qty <= 0 ? 'Out of Stock' : ($qty <= $reorder ? 'Low Stock' : 'In Stock');
        $compatibilities = $this->productModel->getCompatibility((int) $product['id']);
        $product['compatibility'] = $compatibilities;

        $this->json([
            'status' => 'success',
            'product' => $product,
        ]);
    }

    public function savePart(): void
    {
        $this->api(function (array $data): array {
            $id = (int) ($data['id'] ?? 0);
            if ($id > 0) {
                $this->productModel->updateProduct($id, $data);
                return ['ok' => true, 'message' => 'Spare part updated successfully.', 'id' => $id];
            }

            $res = $this->productModel->createProduct($data);
            return ['ok' => true, 'message' => 'Spare part created successfully.', 'product' => $res];
        });
    }

    public function deletePart(): void
    {
        $this->api(function (array $data): array {
            $id = (int) ($data['id'] ?? 0);
            $this->productModel->deleteProduct($id);
            return ['ok' => true, 'message' => 'Spare part archived and deactivated successfully.'];
        });
    }

    public function saveCompatibility(): void
    {
        $this->api(function (array $data): array {
            $productId = (int) ($data['product_id'] ?? 0);
            $newId = $this->productModel->saveCompatibility($productId, $data);
            return ['ok' => true, 'message' => 'Vehicle compatibility mapped successfully.', 'id' => $newId];
        });
    }

    public function deleteCompatibility(): void
    {
        $this->api(function (array $data): array {
            $id = (int) ($data['id'] ?? 0);
            $this->productModel->deleteCompatibility($id);
            return ['ok' => true, 'message' => 'Compatibility link removed.'];
        });
    }

    public function vehicleData(): void
    {
        $brandId = !empty($_GET['brand_id']) ? (int) $_GET['brand_id'] : (!empty($_GET['vehicle_brand_id']) ? (int) $_GET['vehicle_brand_id'] : null);
        $modelId = !empty($_GET['model_id']) ? (int) $_GET['model_id'] : (!empty($_GET['vehicle_model_id']) ? (int) $_GET['vehicle_model_id'] : null);

        if ($modelId) {
            $engines = $this->productModel->vehicleEngines($modelId);
            $this->json(['ok' => true, 'status' => 'success', 'engines' => $engines]);
            return;
        }

        if ($brandId) {
            $models = $this->productModel->vehicleModels($brandId);
            $this->json(['ok' => true, 'status' => 'success', 'models' => $models]);
            return;
        }

        $this->json([
            'ok' => true,
            'status' => 'success',
            'brands' => $this->productModel->vehicleBrands(),
            'models' => $this->productModel->vehicleModels(),
        ]);
    }

    private function api(callable $action): void
    {
        if (empty($_SESSION['user_id']) || !in_array((string) ($_SESSION['role_slug'] ?? ''), ['owner', 'store_manager'], true)) {
            $this->json(['ok' => false, 'message' => 'Only Business Owners and Store Managers can change the catalog.'], 403);
            return;
        }
        $data = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $token = (string) ($data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            $this->json(['ok' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 419);
            return;
        }

        try {
            $this->json($action($data));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }
}
