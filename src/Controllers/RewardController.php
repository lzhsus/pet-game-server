<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Repositories\JsonGameRepository;
use App\Services\RewardService;

class RewardController
{
    public function dailySign(): void
    {
        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        $service = new RewardService(new JsonGameRepository());

        Response::success([
            'reward' => $service->dailySign($userId),
        ]);
    }
}
