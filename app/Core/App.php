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

        // User Profile Management (Common to all authenticated roles)
        $this->router->get('/profile', 'ProfileController@index');
        $this->router->post('/profile', 'ProfileController@updateProfile');
        $this->router->post('/profile/update', 'ProfileController@updateProfile');
        $this->router->post('/profile/password', 'ProfileController@changePassword');

        // Catalog & Spare Part Finder
        $this->router->get('/catalog', 'CatalogController@index');
        $this->router->get('/catalog/compatibility', 'CatalogController@compatibility');
        $this->router->get('/catalog/details', 'CatalogController@details');
        $this->router->post('/catalog/parts/save', 'CatalogController@savePart');
        $this->router->post('/catalog/parts/delete', 'CatalogController@deletePart');
        $this->router->post('/catalog/compatibility/save', 'CatalogController@saveCompatibility');
        $this->router->post('/catalog/compatibility/delete', 'CatalogController@deleteCompatibility');
        $this->router->get('/catalog/vehicle-data', 'CatalogController@vehicleData');
        $this->router->get('/finder', 'SparePartFinderController@index');
        $this->router->get('/finder/models', 'SparePartFinderController@models');
        $this->router->get('/finder/engines', 'SparePartFinderController@engines');

        // Cart, Checkout & Orders
        $this->router->get('/cart', 'OrderController@cart');
        $this->router->get('/checkout', 'OrderController@checkout');
        $this->router->post('/checkout/place', 'OrderController@placeOrder');
        $this->router->get('/orders', 'OrderController@orders');
        $this->router->get('/my-orders', 'OrderController@orders');
        $this->router->post('/orders/update', 'OrderController@updateOrder');
        $this->router->post('/orders/cancel', 'OrderController@cancelOrder');
        $this->router->get('/track-order', 'OrderController@track');
        $this->router->post('/track-order', 'OrderController@track');

        // Deliveries & Public Pages
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

        // Admin & BI Workspace
        $this->router->get('/admin/login', 'AdminController@loginPage');
        $this->router->post('/admin/login', 'AdminController@doLogin');       
        $this->router->get('/admin/dashboard', 'AdminController@dashboard');
        $this->router->get('/admin/users', 'AdminController@users');
        $this->router->post('/admin/users/save', 'AdminController@saveUser');
        $this->router->post('/admin/users/delete', 'AdminController@deleteUser');
        $this->router->get('/admin/employees', 'AdminController@employees');
        $this->router->post('/admin/employees/store', 'AdminController@employeeStore');
        $this->router->post('/admin/employees/update', 'AdminController@employeeUpdate');
        $this->router->post('/admin/employees/delete', 'AdminController@employeeDelete');
        $this->router->get('/admin/suppliers', 'AdminController@suppliers');
        $this->router->post('/admin/suppliers', 'AdminController@createSupplier');
        $this->router->get('/admin/purchases', 'AdminController@purchases');
        $this->router->post('/admin/purchases/status', 'AdminController@updatePurchaseStatus');
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
        $this->router->post('/inventory/stock-in', 'InventoryController@stockIn');
        $this->router->post('/inventory/adjust', 'InventoryController@adjust');
        $this->router->post('/inventory/write-off', 'InventoryController@writeOff');
        $this->router->get('/inventory/movements', 'InventoryController@movements');
        $this->router->post('/inventory/add-item', 'InventoryController@addItem');
        $this->router->post('/inventory/delete-item', 'InventoryController@deleteItem');

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
