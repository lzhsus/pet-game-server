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
        $matchedTask = null;

        foreach ($tasks as $task) {
            if ((int) $task['id'] === $taskId) {
                $matchedTask = $task;
                break;
            }
        }

        if (!$matchedTask) {
            return [
                'error' => true,
                'message' => '任务不存在',
                'tasks' => $this->list($userId),
            ];
        }

        if ((int) $matchedTask['status'] === 2) {
            return [
                'error' => true,
                'message' => '奖励已领取',
                'tasks' => $this->list($userId),
            ];
        }

        if ((int) $matchedTask['progress'] < (int) $matchedTask['target_value']) {
            return [
                'error' => true,
                'message' => '任务未完成',
                'tasks' => $this->list($userId),
            ];
        }

        $rewardCoin = (int) $matchedTask['reward_coin'];
        $this->repository->saveUserTask(
            $userId,
            $taskId,
            (int) $matchedTask['target_value'],
            2
        );

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
