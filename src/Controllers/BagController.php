<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Repositories\JsonGameRepository;
use App\Services\BagService;

class BagController
{
    public function list(): void
    {
        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        $service = new BagService(new JsonGameRepository());

        Response::success([
            'items' => $service->list($userId),
        ]);
    }
}
