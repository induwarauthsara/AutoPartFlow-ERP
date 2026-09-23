<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class InventoryController extends Controller
{
    public function __construct()
    {
        if (empty($_SESSION['user_id']) || !in_array($_SESSION['role_slug'] ?? '', ['store_manager', 'owner'], true)) {
            $this->setFlash('error', 'Sign in with a store manager account to open inventory.');
            $this->redirect('/login');
        }
    }

    public function index(): void
    {
        $this->view('inventory/index', [
            'title' => 'Inventory Management',
        ], 'inventory');
    }
}
