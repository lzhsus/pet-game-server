<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

class BagService
{
    public function __construct(private MySqlGameRepository $repository)
    {
    }

    public function list(int $userId): array
    {
        return $this->repository->listBagItems($userId);
    }
}
