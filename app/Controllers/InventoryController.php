<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Inventory;

class InventoryController extends Controller
{
    private Inventory $inventory;

    public function __construct()
    {
        $this->requireRole('store_manager', 'Access denied. The Store / Inventory workspace is reserved for Store Managers.');
        $this->inventory = new Inventory();
    }

    public function index(): void
    {
        $this->view('inventory/index', [
            'title' => 'Inventory Management',
            'inventoryItems' => $this->inventory->items(),
            'kpis' => $this->inventory->kpis(),
            'products' => $this->inventory->productsList(),
        ], 'inventory');
    }

    public function stockIn(): void
    {
        $this->api(function (array $data): array {
            $productId = (int) ($data['product_id'] ?? 0);
            $qty = (int) ($data['quantity'] ?? 0);
            $unitCost = !empty($data['unit_cost']) ? (float) $data['unit_cost'] : null;
            $notes = trim((string) ($data['notes'] ?? ''));
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            $result = $this->inventory->stockIn($productId, $qty, $unitCost, $notes, $userId ?: null);
            return [
                'ok' => true,
                'message' => 'Stock added successfully.',
                'result' => $result,
                'items' => $this->inventory->items(),
                'kpis' => $this->inventory->kpis(),
            ];
        });
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
                'items' => $this->inventory->items(),
                'kpis' => $this->inventory->kpis(),
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
                'items' => $this->inventory->items(),
                'kpis' => $this->inventory->kpis(),
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

    private function api(callable $action): void
    {
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
