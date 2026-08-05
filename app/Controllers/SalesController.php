<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class SalesController extends Controller
{
    public function dashboard(): void
    {
        $this->view('sales/dashboard/index', [
            'title' => 'Sales Representative Dashboard',
        ], 'sales');
    }

    public function pos(): void
    {
        $this->view('sales/pos/index', [
            'title' => 'Point of Sale (POS)',
        ], 'sales');
    }

    public function orders(): void
    {
        $this->view('sales/orders/index', [
            'title' => 'Sales Orders',
        ], 'orders');
    }

    public function createOrder(): void
    {
        $this->view('sales/orders/create', [
            'title' => 'Create New Order',
        ], 'orders');
    }

    public function customers(): void
    {
        $this->view('sales/customers/index', [
            'title' => 'Customer Directory',
        ], 'sales');
    }
}
