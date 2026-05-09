<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Repositories\JsonGameRepository;
use App\Services\UserService;

class AuthController
{
    public function login(): void
    {
        $repository = new JsonGameRepository();
        $userService = new UserService($repository);
        $user = $userService->getUser(1);

        Response::success([
            'token' => Auth::createToken((int) $user['id']),
            'user' => $user,
        ], 'login success');
    }
}
