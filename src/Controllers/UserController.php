<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Repositories\JsonGameRepository;
use App\Services\UserService;

class UserController
{
    public function profile(): void
    {
        $repository = new JsonGameRepository();
        $service = new UserService($repository);

        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        Response::success([
            'user' => $service->getUser($userId),
        ]);
    }
}
