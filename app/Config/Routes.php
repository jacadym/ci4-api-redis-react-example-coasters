<?php

use App\Controllers\API\Coaster;
use App\Controllers\API\Wagon;
use App\Controllers\Home;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', [Home::class, 'index']);

/**
 * API
 */
$routes->group('api', static function ($routes) {
    $routes->get('coasters', [Coaster::class, 'index']);
    $routes->post('coasters', [Coaster::class, 'new']);
    $routes->put('coasters/(:num)', [Coaster::class, 'update']);
    $routes->post('coasters/(:num)/wagons', [Wagon::class, 'add']);
    $routes->delete('coasters/(:num)/wagons/(:num)', [Wagon::class, 'delete']);
});
