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
        $this->router->get('/checkout', 'OrderController@checkout');
        $this->router->post('/checkout/place', 'OrderController@placeOrder');
        $this->router->get('/track-order', 'OrderController@track');
        $this->router->post('/track-order', 'OrderController@track');

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
        $this->router->post('/admin/employees/store', 'AdminController@employeeStore');
        $this->router->post('/admin/employees/update', 'AdminController@employeeUpdate');
        $this->router->post('/admin/employees/delete', 'AdminController@employeeDelete');
        $this->router->get('/admin/reports', 'AdminController@reports');
        $this->router->get('/admin/notifications', 'AdminController@notifications');
        $this->router->post('/admin/notifications/store', 'AdminController@notificationStore');
        $this->router->post('/admin/notifications/read', 'AdminController@notificationRead');
        $this->router->post('/admin/notifications/read-all', 'AdminController@notificationReadAll');
        $this->router->post('/admin/notifications/delete', 'AdminController@notificationDelete');
        $this->router->get('/admin/settings', 'AdminController@settings');
        $this->router->post('/admin/settings/save', 'AdminController@settingsSave');
        // Inventory / Store Workspace
        $this->router->get('/inventory', 'InventoryController@index');
    }

    public function run(): void
    {
        $rawUri = $_GET['url'] ?? '/';
        $uri = ($rawUri === '') ? '/' : $rawUri;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $this->router->dispatch($uri, $method);
    }
}
