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
        // Home
        $this->router->get('/', 'HomeController@index');

        // Parts CRUD
        $this->router->get('/parts', 'PartController@index');
        $this->router->get('/parts/create', 'PartController@create');
        $this->router->post('/parts/store', 'PartController@store');
        $this->router->get('/parts/edit/{id}', 'PartController@edit');
        $this->router->post('/parts/update/{id}', 'PartController@update');
        $this->router->post('/parts/delete/{id}', 'PartController@delete');
    }

    public function run(): void
    {
        $uri = $_GET['url'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $this->router->dispatch($uri, $method);
    }
}
