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
        $totalParts = count($partModel->findAll());

        $this->view('home/index', [
            'title'      => 'Dashboard',
            'totalParts' => $totalParts,
            'flash'      => $this->getFlash(),
        ]);
    }
}
