<?php

declare(strict_types=1);

namespace App\Core;

class App
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // Landing & Auth
        $this->router->get('/', 'HomeController@index');
        $this->router->get('/login', 'HomeController@login');
        $this->router->post('/login', 'HomeController@doLogin');
        $this->router->get('/logout', 'HomeController@logout');

        // Sales Representative Workspace
        $this->router->get('/sales', 'SalesController@dashboard');
        $this->router->get('/sales/pos', 'SalesController@pos');
        $this->router->get('/sales/orders', 'SalesController@orders');
        $this->router->get('/sales/orders/create', 'SalesController@createOrder');
        $this->router->get('/sales/customers', 'SalesController@customers');

                // Admin & BI (Chathumi's module)
        $this->router->get('/admin/dashboard', 'AdminController@dashboard');
        $this->router->get('/admin/users', 'AdminController@users');
        $this->router->get('/admin/employees', 'AdminController@employees');
        $this->router->get('/admin/reports', 'AdminController@reports');
        $this->router->get('/admin/notifications', 'AdminController@notifications');
        $this->router->get('/admin/settings', 'AdminController@settings');
    }

    public function run(): void
    {
        $rawUri = $_GET['url'] ?? '/';
        $uri = ($rawUri === '') ? '/' : $rawUri;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $this->router->dispatch($uri, $method);
    }
}
