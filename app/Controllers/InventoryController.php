<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inventory;
use App\Models\Product;

class InventoryController extends Controller
{
    private Inventory $inventory;

    public function __construct()
    {
        $this->requireRole(['store_manager', 'owner'], 'Access denied. The Store / Inventory workspace is reserved for Store Managers and Administrators.');
        $this->inventory = new Inventory();
    }

    public function index(): void
    {
        $productModel = new Product();
        $isOwner = ((string) ($_SESSION['role_slug'] ?? '')) === 'owner';

        $this->view('inventory/index', [
            'title'              => $isOwner ? 'Inventory Management | Admin' : 'Inventory Management',
            'inventoryItems'     => $this->inventory->getInventoryItems(),
            'inventorySummary'   => $this->inventory->getSummary(),
            'inventoryLocations' => $this->inventory->getLocations(),
            'categories'         => $productModel->categories(),
            'kpis'               => $this->inventory->kpis(),
            'products'           => $this->inventory->productsList(),
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

            $result = $this->inventory->addStock(
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
                'items'   => $this->inventory->getInventoryItems(),
                'summary' => $this->inventory->getSummary(),
                'kpis'    => $this->inventory->kpis(),
            ];
        });
    }

    public function stockIn(): void
    {
        $this->recordStockIn();
    }

    public function adjust(): void
    {
        $this->api(function (array $data): array {
            $productId = (int) ($data['product_id'] ?? 0);
            $newQty = (int) ($data['quantity'] ?? 0);
            $notes = trim((string) ($data['notes'] ?? ''));
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            $result = $this->inventory->adjustStock($productId, $newQty, $notes, $userId ?: null);
            return [
                'ok' => true,
                'message' => 'Inventory adjusted successfully.',
                'result' => $result,
                'items' => $this->inventory->getInventoryItems(),
                'summary' => $this->inventory->getSummary(),
                'kpis' => $this->inventory->kpis(),
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

            $newItem = $this->inventory->createItem([
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
                'items'   => $this->inventory->getInventoryItems(),
                'summary' => $this->inventory->getSummary(),
                'kpis'    => $this->inventory->kpis(),
            ];
        });
    }

    public function writeOff(): void
    {
        $this->api(function (array $data): array {
            $productId = (int) ($data['product_id'] ?? 0);
            $qty = (int) ($data['quantity'] ?? 0);
            $reason = trim((string) ($data['reason'] ?? $data['notes'] ?? ''));
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            $result = $this->inventory->writeOffStock($productId, $qty, $reason, $userId ?: null);
            return [
                'ok' => true,
                'message' => 'Stock written off successfully.',
                'result' => $result,
                'items' => $this->inventory->getInventoryItems(),
                'summary' => $this->inventory->getSummary(),
                'kpis' => $this->inventory->kpis(),
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
            $this->inventory->deleteItem($productId);

            return [
                'ok'      => true,
                'message' => 'Inventory item deleted successfully.',
                'items'   => $this->inventory->getInventoryItems(),
                'summary' => $this->inventory->getSummary(),
                'kpis'    => $this->inventory->kpis(),
            ];
        });
    }

    public function movements(): void
    {
        $this->json([
            'ok' => true,
            'movements' => $this->inventory->movements(30),
        ]);
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
            return;
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
