<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Part;

class HomeController extends Controller
{
    public function index(): void
    {
        $partModel = new Part();

        $this->view('home/index', [
            'title'      => 'Dashboard',
            'totalParts' => count($partModel->findAll()),
            'flash'      => $this->getFlash(),
        ]);
    }
}
