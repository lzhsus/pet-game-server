<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\JsonGameRepository;

class BagService
{
    public function __construct(private JsonGameRepository $repository)
    {
    }

    public function list(int $userId): array
    {
        $data = $this->repository->all();

        if (empty($data['items'])) {
            $data['items'] = [
                ['id' => 1, 'user_id' => $userId, 'name' => '小鱼干', 'count' => 5],
                ['id' => 2, 'user_id' => $userId, 'name' => '沐浴露', 'count' => 2],
                ['id' => 3, 'user_id' => $userId, 'name' => '玩具球', 'count' => 1],
            ];

            $this->repository->save($data);
        }

        return array_values(array_filter($data['items'], fn ($item) => (int) $item['user_id'] === $userId));
    }
}
