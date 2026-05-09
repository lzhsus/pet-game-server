<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\JsonGameRepository;

class RewardService
{
    public function __construct(private JsonGameRepository $repository)
    {
    }

    public function dailySign(int $userId): array
    {
        $data = $this->repository->all();

        foreach ($data['users'] as $index => $user) {
            if ((int) $user['id'] === $userId) {
                $data['users'][$index]['coin'] += 50;
                $data['users'][$index]['diamond'] += 1;

                $this->repository->save($data);

                return [
                    'coin' => 50,
                    'diamond' => 1,
                ];
            }
        }

        return [];
    }
}
