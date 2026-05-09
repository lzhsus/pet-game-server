<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

class RewardService
{
    public function __construct(private MySqlGameRepository $repository)
    {
    }

    public function dailySign(int $userId): array
    {
        $coin = 50;
        $diamond = 1;
        $today = date('Y-m-d');

        $created = $this->repository->addSignLog($userId, $today, $coin, $diamond);

        if (!$created) {
            return [
                'received' => false,
                'message' => '今日已签到',
                'coin' => 0,
                'diamond' => 0,
            ];
        }

        $user = $this->repository->findUser($userId);
        $this->repository->updateUser($userId, [
            'coin' => (int) $user['coin'] + $coin,
            'diamond' => (int) $user['diamond'] + $diamond,
        ]);

        return [
            'received' => true,
            'message' => '签到成功',
            'coin' => $coin,
            'diamond' => $diamond,
        ];
    }
}
