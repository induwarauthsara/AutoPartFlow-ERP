<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class InventoryController extends Controller
{
    public function index(): void
    {
        $this->view('inventory/index', [
            'title' => 'Inventory Management',
        ], 'inventory');
    }
}
