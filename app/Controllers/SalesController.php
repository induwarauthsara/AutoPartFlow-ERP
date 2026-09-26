<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SalesWorkspace;

class SalesController extends Controller
{
    private SalesWorkspace $workspace;
    private ?int $salesRepId;

    public function __construct()
    {
        $this->requireRole('sales_rep', 'Access denied. The Sales workspace is reserved for Sales Representatives.');
        $this->workspace = new SalesWorkspace();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $this->salesRepId = $userId > 0 ? $this->workspace->employeeIdForUser($userId) : null;
    }

    public function dashboard(): void
    {
        $this->view('sales-rep/dashboard/index', [
            'title' => 'Sales Representative Dashboard',
            'dashboard' => $this->workspace->dashboardData($this->salesRepId),
            'customers' => $this->workspace->customers(),
            'orders' => $this->workspace->orders(),
        ], 'sales-rep');
    }

    public function pos(): void
    {
        $this->view('sales-rep/pos/index', [
            'title' => 'Point of Sale (POS)',
            'products' => $this->workspace->products(),
            'customers' => $this->workspace->customerOptions(),
        ], 'sales-rep');
    }

    public function orders(): void
    {
        $this->view('sales-rep/orders/index', [
            'title' => 'Sales Orders',
            'orders' => $this->workspace->orders(),
        ], 'sales-rep');
    }

    public function createOrder(): void
    {
        $this->view('sales-rep/orders/create', [
            'title' => 'Create New Order',
            'products' => $this->workspace->products(),
            'customers' => $this->workspace->customerOptions(),
        ], 'sales-rep');
    }

    public function customers(): void
    {
        $this->view('sales-rep/customers/index', [
            'title' => 'Customer Directory',
            'customers' => $this->workspace->customers(),
        ], 'sales-rep');
    }

    public function saveCustomer(): void
    {
        $this->api(function (array $data): array {
            $result = $this->workspace->saveCustomer($data);
            return ['ok' => true, 'customer' => $result, 'customers' => $this->workspace->customers()];
        });
    }

    public function deleteCustomer(): void
    {
        $this->api(function (array $data): array {
            $id = (int) ($data['id'] ?? 0);
            if ($id === 0 && !empty($data['customer_code'])) {
                $id = $this->workspace->customerIdByCode((string) $data['customer_code']);
            }
            $this->workspace->deleteCustomer($id);
            return ['ok' => true];
        });
    }

    public function saveOrder(): void
    {
        $this->api(function (array $data): array {
            $number = $this->workspace->createOrder(
                (int) ($data['customer_id'] ?? 0),
                is_array($data['items'] ?? null) ? $data['items'] : [],
                trim((string) ($data['notes'] ?? '')),
                $this->salesRepId
            );
            return ['ok' => true, 'orderNumber' => $number, 'redirect' => url('sales/orders')];
        });
    }

    public function updateOrderStatus(): void
    {
        $this->api(function (array $data): array {
            $id = (int) ($data['id'] ?? 0);
            if ($id === 0 && !empty($data['order_number'])) {
                $id = $this->workspace->orderIdByNumber((string) $data['order_number']);
            }
            $this->workspace->updateOrderStatus($id, strtolower((string) ($data['status'] ?? '')));
            return ['ok' => true];
        });
    }

    public function deleteOrder(): void
    {
        $this->api(function (array $data): array {
            $id = (int) ($data['id'] ?? 0);
            if ($id === 0 && !empty($data['order_number'])) {
                $id = $this->workspace->orderIdByNumber((string) $data['order_number']);
            }
            $this->workspace->deleteOrder($id);
            return ['ok' => true];
        });
    }

    public function completeSale(): void
    {
        $this->api(function (array $data): array {
            $result = $this->workspace->completeSale(
                !empty($data['customer_id']) ? (int) $data['customer_id'] : null,
                is_array($data['items'] ?? null) ? $data['items'] : [],
                (float) ($data['discount'] ?? 0),
                (string) ($data['payment_method'] ?? ''),
                (float) ($data['amount_paid'] ?? 0),
                $this->salesRepId,
                (int) $_SESSION['user_id']
            );
            return ['ok' => true, 'sale' => $result];
        });
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
        }
        try {
            $this->json($action($data));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'The operation could not be completed. Please try again.'], 500);
        }
    }
}
