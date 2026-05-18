<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Repositories\MySqlGameRepository;

class AuthController
{
    public function login(): void
    {
        $repository = new MySqlGameRepository();

        // 当前阶段先固定使用 user_id = 1。
        // 数据库为空时，登录接口自动创建用户和默认宠物。
        $userId = 1;
        $user = $repository->findUser($userId);

        if (!$user) {
            $user = $repository->createDefaultUser($userId);
        }

        if (!$repository->findPetByUserId($userId)) {
            $repository->createDefaultPet($userId);
        }

        Response::success([
            'token' => Auth::createToken((int) $user['id']),
            'user' => $user,
        ], 'login success');
    }
}
