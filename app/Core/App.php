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
        $this->router->get('/register', 'HomeController@register');
        $this->router->post('/register', 'HomeController@doRegister');
        $this->router->get('/logout', 'HomeController@logout');
        $this->router->get('/catalog', 'CatalogController@index');
        $this->router->get('/catalog/compatibility', 'CatalogController@compatibility');
        $this->router->get('/catalog/details', 'CatalogController@details');
        $this->router->post('/catalog/parts/save', 'CatalogController@savePart');
        $this->router->post('/catalog/parts/delete', 'CatalogController@deletePart');
        $this->router->post('/catalog/compatibility/save', 'CatalogController@saveCompatibility');
        $this->router->post('/catalog/compatibility/delete', 'CatalogController@deleteCompatibility');
        $this->router->get('/catalog/vehicle-data', 'CatalogController@vehicleData');
        $this->router->get('/checkout', 'OrderController@checkout');
        $this->router->post('/checkout/place', 'OrderController@placeOrder');

        // Sales Representative Workspace
        $this->router->get('/sales', 'SalesController@dashboard');
        $this->router->get('/sales/pos', 'SalesController@pos');
        $this->router->get('/sales/orders', 'SalesController@orders');
        $this->router->get('/sales/orders/create', 'SalesController@createOrder');
        $this->router->get('/sales/customers', 'SalesController@customers');
        $this->router->post('/sales/customers/save', 'SalesController@saveCustomer');
        $this->router->post('/sales/customers/delete', 'SalesController@deleteCustomer');
        $this->router->post('/sales/orders/save', 'SalesController@saveOrder');
        $this->router->post('/sales/orders/status', 'SalesController@updateOrderStatus');
        $this->router->post('/sales/orders/delete', 'SalesController@deleteOrder');
        $this->router->post('/sales/pos/complete', 'SalesController@completeSale');

                // Admin & BI (Chathumi's module)
        $this->router->get('/admin/login', 'AdminController@loginPage');
        $this->router->post('/admin/login', 'AdminController@doLogin');       
        $this->router->get('/admin/dashboard', 'AdminController@dashboard');
        $this->router->get('/admin/users', 'AdminController@users');
        $this->router->post('/admin/users/save', 'AdminController@saveUser');
        $this->router->post('/admin/users/delete', 'AdminController@deleteUser');
        $this->router->get('/admin/employees', 'AdminController@employees');
        $this->router->get('/admin/reports', 'AdminController@reports');
        $this->router->get('/admin/notifications', 'AdminController@notifications');
        $this->router->get('/admin/settings', 'AdminController@settings');
        // Inventory / Store Workspace (Sashik's module)
        $this->router->get('/inventory', 'InventoryController@index');
        $this->router->post('/inventory/stock-in', 'InventoryController@stockIn');
        $this->router->post('/inventory/adjust', 'InventoryController@adjust');
        $this->router->post('/inventory/write-off', 'InventoryController@writeOff');
        $this->router->get('/inventory/movements', 'InventoryController@movements');

        // Shop Customer Workspace (B2B Portal)
        $this->router->get('/customer', 'CustomerController@dashboard');
        $this->router->get('/customer/dashboard', 'CustomerController@dashboard');
    }

    public function run(): void
    {
        $rawUri = $_GET['url'] ?? '/';
        $uri = ($rawUri === '') ? '/' : $rawUri;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $this->router->dispatch($uri, $method);
    }
}
