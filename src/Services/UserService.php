<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

class UserService
{
    public function __construct(private MySqlGameRepository $repository)
    {
    }

    public function getUser(int $userId): array
    {
        return $this->repository->findUser($userId);
    }

    public function addCoin(int $userId, int $coin): array
    {
        $user = $this->getUser($userId);
        $newCoin = (int) ($user['coin'] ?? 0) + $coin;

        $this->repository->updateUser($userId, [
            'coin' => $newCoin,
        ]);

        return $this->getUser($userId);
    }
}
