<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class PingController
{
    public function index(): void
    {
        Response::success([
            'server' => 'pet-game-server',
            'status' => 'ok',
            'time' => date('Y-m-d H:i:s'),
        ], 'pong');
    }
}
