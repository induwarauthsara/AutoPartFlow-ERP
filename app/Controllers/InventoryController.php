<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inventory;
use App\Models\Product;

class InventoryController extends Controller
{
    public function __construct()
    {
        if (empty($_SESSION['user_id']) || !in_array($_SESSION['role_slug'] ?? '', ['store_manager', 'owner'], true)) {
            $isApi = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) || $this->isPost();
            if ($isApi) {
                $this->json(['ok' => false, 'message' => 'Unauthorized or session expired. Please sign in.'], 401);
            }
            $this->setFlash('error', 'Sign in with a store manager account to open inventory.');
            $this->redirect('/login');
        }
    }

    public function index(): void
    {
        $inventory = new Inventory();
        $productModel = new Product();

        $this->view('inventory/index', [
            'title'              => 'Inventory Management',
            'inventoryItems'     => $inventory->getInventoryItems(),
            'inventorySummary'   => $inventory->getSummary(),
            'inventoryLocations' => $inventory->getLocations(),
            'categories'         => $productModel->categories(),
        ], 'inventory');
    }

    /**
     * POST /inventory/stock-in
     * Record stock-in adjustment for an existing item.
     */
    public function recordStockIn(): void
    {
        $this->api(function (array $data): array {
            $productId = (int) ($data['product_id'] ?? 0);
            $quantity  = (int) ($data['quantity'] ?? 0);
            $location  = trim((string) ($data['location'] ?? 'Main Warehouse'));
            $notes     = trim((string) ($data['notes'] ?? ''));

            if ($productId <= 0) {
                throw new \InvalidArgumentException('Please select a valid product.');
            }
            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be greater than zero.');
            }

            $inventory = new Inventory();
            $result = $inventory->addStock(
                $productId,
                $quantity,
                $location !== '' ? $location : 'Main Warehouse',
                $notes,
                isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null
            );

            return [
                'ok'      => true,
                'message' => "Successfully added {$quantity} units to {$result['partNo']}.",
                'item'    => $result,
                'summary' => $inventory->getSummary(),
            ];
        });
    }

    /**
     * POST /inventory/add-item
     * Register a new item in the catalog & inventory.
     */
    public function addItem(): void
    {
        $this->api(function (array $data): array {
            $productCode  = trim((string) ($data['product_code'] ?? ''));
            $name         = trim((string) ($data['name'] ?? ''));
            $categoryId   = (int) ($data['category_id'] ?? 0);
            $costPrice    = (float) ($data['cost_price'] ?? 0);
            $sellingPrice = (float) ($data['selling_price'] ?? 0);
            $quantity     = (int) ($data['quantity_on_hand'] ?? $data['quantity'] ?? 0);
            $reorderLevel = (int) ($data['reorder_level'] ?? 10);
            $location     = trim((string) ($data['location'] ?? 'Main Warehouse'));
            $notes        = trim((string) ($data['notes'] ?? ''));

            if ($productCode === '') {
                throw new \InvalidArgumentException('Part number / SKU is required.');
            }
            if ($name === '') {
                throw new \InvalidArgumentException('Product name is required.');
            }
            if ($categoryId <= 0) {
                throw new \InvalidArgumentException('Please select a category.');
            }
            if ($costPrice < 0 || $sellingPrice < 0) {
                throw new \InvalidArgumentException('Prices cannot be negative.');
            }
            if ($quantity < 0) {
                throw new \InvalidArgumentException('Initial stock cannot be negative.');
            }

            $inventory = new Inventory();
            $newItem = $inventory->createItem([
                'product_code'     => $productCode,
                'name'             => $name,
                'category_id'      => $categoryId,
                'cost_price'       => $costPrice,
                'selling_price'    => $sellingPrice,
                'quantity_on_hand' => $quantity,
                'reorder_level'    => $reorderLevel > 0 ? $reorderLevel : 10,
                'location'         => $location,
                'notes'            => $notes,
            ], isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);

            return [
                'ok'      => true,
                'message' => "Item '{$name}' ({$productCode}) created successfully.",
                'item'    => $newItem,
                'summary' => $inventory->getSummary(),
            ];
        });
    }

    /**
     * POST /inventory/delete-item
     * Soft-delete an inventory item from the catalog.
     */
    public function deleteItem(): void
    {
        $this->api(function (array $data): array {
            $productId = (int) ($data['product_id'] ?? 0);
            $inventory = new Inventory();
            $inventory->deleteItem($productId);

            return [
                'ok'      => true,
                'message' => 'Inventory item deleted successfully.',
                'summary' => $inventory->getSummary(),
            ];
        });
    }

    /**
     * Unified JSON API runner with CSRF verification and error handling.
     */
    private function api(callable $action): void
    {
        $raw = (string) file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }

        $token = (string) ($data['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
            $this->json(['ok' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 419);
        }

        try {
            $this->json($action($data));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage() ?: 'The operation could not be completed. Please try again.'], 500);
        }
    }
}
