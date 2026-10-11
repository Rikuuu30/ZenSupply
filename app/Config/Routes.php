<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('about', 'Pages::about', ['as' => 'about']);
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login', 'Auth::attempt', ['filter' => 'csrf', 'as' => 'login.attempt']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf', 'as' => 'logout']);

$routes->group('', ['filter' => 'auth'], static function ($routes): void {
    $routes->get('/', 'Pages::index', ['as' => 'home']);
    $routes->get('customers', 'Customers::index', ['as' => 'customers']);
    $routes->get('customers/new', 'Customers::new', ['as' => 'customers.new']);
    $routes->post('customers', 'Customers::create', ['filter' => 'csrf', 'as' => 'customers.create']);
    $routes->get('customers/(:num)', 'Customers::show/$1', ['as' => 'customers.show']);
    $routes->get('customers/(:num)/edit', 'Customers::edit/$1', ['as' => 'customers.edit']);
    $routes->post('customers/(:num)', 'Customers::update/$1', ['filter' => 'csrf', 'as' => 'customers.update']);
    $routes->post('customers/(:num)/archive', 'Customers::archive/$1', ['filter' => 'csrf', 'as' => 'customers.archive']);
    $routes->post('customers/(:num)/restore', 'Customers::restore/$1', ['filter' => 'csrf', 'as' => 'customers.restore']);

    $routes->get('products', 'Products::index', ['as' => 'products']);
    $routes->get('products/(:num)', 'Products::show/$1', ['as' => 'products.show']);
    $routes->group('products', ['filter' => 'inventory'], static function ($routes): void {
        $routes->get('new', 'Products::new', ['as' => 'products.new']);
        $routes->post('', 'Products::create', ['filter' => 'csrf', 'as' => 'products.create']);
        $routes->get('(:num)/edit', 'Products::edit/$1', ['as' => 'products.edit']);
        $routes->post('(:num)', 'Products::update/$1', ['filter' => 'csrf', 'as' => 'products.update']);
        $routes->post('(:num)/archive', 'Products::archive/$1', ['filter' => 'csrf', 'as' => 'products.archive']);
        $routes->post('(:num)/restore', 'Products::restore/$1', ['filter' => 'csrf', 'as' => 'products.restore']);
    });

    $routes->get('sales/new', 'Sales::new', ['as' => 'sales.new']);
    $routes->post('sales', 'Sales::create', ['filter' => 'csrf', 'as' => 'sales.create']);
    $routes->get('sales', 'Sales::index', ['as' => 'sales']);
    $routes->get('sales/(:num)', 'Sales::show/$1', ['as' => 'sales.show']);

    $routes->group('users', ['filter' => 'admin'], static function ($routes): void {
        $routes->get('', 'Users::index', ['as' => 'users']);
        $routes->get('new', 'Users::new', ['as' => 'users.new']);
        $routes->post('', 'Users::create', ['filter' => 'csrf', 'as' => 'users.create']);
        $routes->get('(:num)', 'Users::show/$1', ['as' => 'users.show']);
        $routes->get('(:num)/edit', 'Users::edit/$1', ['as' => 'users.edit']);
        $routes->post('(:num)', 'Users::update/$1', ['filter' => 'csrf', 'as' => 'users.update']);
        $routes->post('(:num)/archive', 'Users::archive/$1', ['filter' => 'csrf', 'as' => 'users.archive']);
        $routes->post('(:num)/restore', 'Users::restore/$1', ['filter' => 'csrf', 'as' => 'users.restore']);
    });
});
