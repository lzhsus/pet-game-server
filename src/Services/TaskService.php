<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\JsonGameRepository;

class TaskService
{
    public function __construct(private JsonGameRepository $repository)
    {
    }

    public function list(int $userId): array
    {
        $data = $this->repository->all();

        if (empty($data['tasks'])) {
            $data['tasks'] = [
                ['id' => 1, 'user_id' => $userId, 'title' => '喂食 1 次', 'progress' => 0, 'target' => 1, 'reward_coin' => 20, 'status' => 0],
                ['id' => 2, 'user_id' => $userId, 'title' => '洗澡 1 次', 'progress' => 0, 'target' => 1, 'reward_coin' => 20, 'status' => 0],
                ['id' => 3, 'user_id' => $userId, 'title' => '玩耍 1 次', 'progress' => 0, 'target' => 1, 'reward_coin' => 20, 'status' => 0],
            ];
            $this->repository->save($data);
        }

        return array_values(array_filter($data['tasks'], fn ($task) => (int) $task['user_id'] === $userId));
    }

    public function receive(int $userId, int $taskId): array
    {
        $data = $this->repository->all();
        $reward = 0;

        foreach ($data['tasks'] as $index => $task) {
            if ((int) $task['id'] === $taskId && (int) $task['user_id'] === $userId) {
                $data['tasks'][$index]['progress'] = $task['target'];
                $data['tasks'][$index]['status'] = 1;
                $reward = (int) $task['reward_coin'];
            }
        }

        foreach ($data['users'] as $index => $user) {
            if ((int) $user['id'] === $userId) {
                $data['users'][$index]['coin'] += $reward;
            }
        }

        $this->repository->save($data);

        return ['reward_coin' => $reward, 'tasks' => $this->list($userId)];
    }
}
