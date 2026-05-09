<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

class TaskService
{
    public function __construct(private MySqlGameRepository $repository)
    {
    }

    public function list(int $userId): array
    {
        return $this->repository->listTasks($userId);
    }

    public function receive(int $userId, int $taskId): array
    {
        $tasks = $this->repository->listTasks($userId);
        $rewardCoin = 0;

        foreach ($tasks as $task) {
            if ((int) $task['id'] === $taskId) {
                $rewardCoin = (int) $task['reward_coin'];
                $this->repository->saveUserTask(
                    $userId,
                    $taskId,
                    (int) $task['target_value'],
                    1
                );
            }
        }

        if ($rewardCoin > 0) {
            $user = $this->repository->findUser($userId);
            $this->repository->updateUser($userId, [
                'coin' => (int) $user['coin'] + $rewardCoin,
            ]);
        }

        return [
            'reward_coin' => $rewardCoin,
            'tasks' => $this->list($userId),
        ];
    }
}
