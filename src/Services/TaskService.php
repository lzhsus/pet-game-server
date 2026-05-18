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
        $rewardExp = (int) $matchedTask['reward_exp'];

        $this->repository->saveUserTask(
            $userId,
            $taskId,
            (int) $matchedTask['target_value'],
            2
        );

        $user = $this->repository->findUser($userId);
        if ($user) {
            $this->repository->updateUser($userId, [
                'coin' => (int) $user['coin'] + $rewardCoin,
            ]);
        }

        $pet = $this->repository->findPetByUserId($userId);
        if ($pet && $rewardExp > 0) {
            $level = (int) $pet['level'];
            $exp = (int) $pet['exp'] + $rewardExp;
            $levelExpBase = 100;

            while ($exp >= $levelExpBase) {
                $level += 1;
                $exp -= $levelExpBase;
            }

            $this->repository->updatePet((int) $pet['id'], [
                'level' => $level,
                'exp' => $exp,
            ]);
        }

        return [
            'reward_coin' => $rewardCoin,
            'reward_exp' => $rewardExp,
            'user' => $this->repository->findUser($userId),
            'pet' => $this->repository->findPetByUserId($userId),
            'tasks' => $this->list($userId),
        ];
    }
}
