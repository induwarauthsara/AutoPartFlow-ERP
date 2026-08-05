<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'AutoPartFlow ERP | Enterprise Auto Parts Management',
            'flash' => $this->getFlash(),
        ]);
    }

    public function login(): void
    {
        $this->view('auth/login', [
            'title' => 'Sign In | AutoPartFlow ERP',
            'flash' => $this->getFlash(),
        ], 'main');
    }

    public function doLogin(): void
    {
        $this->setFlash('success', 'Successfully signed in as Sales Representative.');
        $this->redirect('/sales');
    }
}
