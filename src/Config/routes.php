<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\PetController;
use App\Controllers\TaskController;
use App\Controllers\BagController;
use App\Controllers\ShopController;
use App\Controllers\RewardController;
use App\Controllers\PingController;

return function ($router) {
    $router->get('/api/ping', [PingController::class, 'index']);

    $router->post('/api/auth/login', [AuthController::class, 'login']);

    $router->get('/api/user/profile', [UserController::class, 'profile']);

    $router->get('/api/pet/profile', [PetController::class, 'profile']);
    $router->post('/api/pet/feed', [PetController::class, 'feed']);
    $router->post('/api/pet/bath', [PetController::class, 'bath']);
    $router->post('/api/pet/play', [PetController::class, 'play']);

    $router->get('/api/task/list', [TaskController::class, 'list']);
    $router->post('/api/task/receive', [TaskController::class, 'receive']);

    $router->get('/api/bag/list', [BagController::class, 'list']);

    $router->get('/api/shop/list', [ShopController::class, 'list']);
    $router->post('/api/shop/buy', [ShopController::class, 'buy']);

    $router->post('/api/reward/sign', [RewardController::class, 'dailySign']);
};
