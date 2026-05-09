<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\JsonGameRepository;

class UserService
{
    public function __construct(private JsonGameRepository $repository)
    {
    }

    public function getUser(int $userId): array
    {
        $data = $this->repository->all();

        foreach ($data['users'] as $user) {
            if ((int) $user['id'] === $userId) {
                return $user;
            }
        }

        return $data['users'][0];
    }

    public function saveUser(array $user): array
    {
        $data = $this->repository->all();

        foreach ($data['users'] as $index => $item) {
            if ((int) $item['id'] === (int) $user['id']) {
                $data['users'][$index] = $user;
                $this->repository->save($data);
                return $user;
            }
        }

        $data['users'][] = $user;
        $this->repository->save($data);

        return $user;
    }
}
