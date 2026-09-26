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
        $this->router->get('/finder', 'SparePartFinderController@index');
        $this->router->get('/finder/models', 'SparePartFinderController@models');
        $this->router->get('/finder/engines', 'SparePartFinderController@engines');
        $this->router->get('/cart', 'OrderController@cart');
        $this->router->get('/checkout', 'OrderController@checkout');
        $this->router->get('/orders', 'OrderController@orders');
        $this->router->get('/my-orders', 'OrderController@orders');
        $this->router->post('/orders/update', 'OrderController@updateOrder');
        $this->router->post('/orders/cancel', 'OrderController@cancelOrder');
        $this->router->post('/checkout/place', 'OrderController@placeOrder');
        $this->router->get('/track-order', 'OrderController@track');
        $this->router->post('/track-order', 'OrderController@track');
        $this->router->get('/delivery-status', 'DeliveryController@status');
        $this->router->post('/delivery/assign', 'DeliveryController@assign');
        $this->router->post('/delivery/status', 'DeliveryController@updateStatus');
        $this->router->get('/help', 'HomeController@help');
        $this->router->get('/privacy', 'HomeController@privacy');
        $this->router->get('/terms', 'HomeController@terms');

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
        $this->router->get('/admin/employees', 'AdminController@employees');
        $this->router->get('/admin/suppliers', 'AdminController@suppliers');
        $this->router->get('/admin/purchases', 'AdminController@purchases');
        $this->router->post('/admin/purchases/status', 'AdminController@updatePurchaseStatus');
        $this->router->post('/admin/suppliers', 'AdminController@createSupplier');
        $this->router->get('/admin/reports', 'AdminController@reports');
        $this->router->get('/admin/notifications', 'AdminController@notifications');
        $this->router->get('/admin/settings', 'AdminController@settings');
        // Inventory / Store Workspace
        $this->router->get('/inventory', 'InventoryController@index');
        $this->router->post('/inventory/stock-in', 'InventoryController@recordStockIn');
        $this->router->post('/inventory/add-item', 'InventoryController@addItem');
        $this->router->post('/inventory/delete-item', 'InventoryController@deleteItem');
    }

    public function run(): void
    {
        $rawUri = $_GET['url'] ?? '/';
        $uri = ($rawUri === '') ? '/' : $rawUri;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $this->router->dispatch($uri, $method);
    }
}
