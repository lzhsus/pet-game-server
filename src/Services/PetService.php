<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

class PetService
{
    private array $config;

    public function __construct(private MySqlGameRepository $repository)
    {
        $this->config = require dirname(__DIR__) . '/Config/game.php';
    }

    public function getPet(int $userId): array
    {
        $user = $this->repository->findUser($userId);
        if (!$user) {
            $this->repository->createDefaultUser($userId);
        }

        $pet = $this->repository->findPetByUserId($userId);

        if (!$pet) {
            $pet = $this->repository->createDefaultPet($userId);
        }

        return $this->applyStatusDecay($pet);
    }

    public function action(int $userId, string $action): array
    {
        $user = $this->repository->findUser($userId);
        if (!$user) {
            $user = $this->repository->createDefaultUser($userId);
        }

        $pet = $this->getPet($userId);

        if (!$pet) {
            return [
                'error' => true,
                'message' => '宠物初始化失败',
            ];
        }

        $config = $this->config['pet_actions'][$action] ?? null;

        if (!$config) {
            return $pet;
        }

        $consumeMap = [
            'feed' => [
                'item_type' => 'food',
                'coin_cost' => 10,
            ],
            'bath' => [
                'item_type' => 'clean',
                'coin_cost' => 15,
            ],
            'play' => [
                'item_type' => 'toy',
                'coin_cost' => 20,
            ],
        ];

        $consumeConfig = $consumeMap[$action] ?? null;

        if ($consumeConfig) {
            $bagItem = $this->repository->findUsableBagItemByType(
                $userId,
                $consumeConfig['item_type']
            );

            if ($bagItem) {
                $this->repository->consumeBagItem((int) $bagItem['id']);
            } else {
                $newCoin = (int) $user['coin'] - (int) $consumeConfig['coin_cost'];

                if ($newCoin < 0) {
                    return [
                        'error' => true,
                        'message' => '金币不足',
                    ];
                }

                $this->repository->updateUser($userId, [
                    'coin' => $newCoin,
                ]);

                $user['coin'] = $newCoin;
            }
        }

        $field = $config['field'];
        if ($field === 'clean') {
            $field = 'clean_value';
        }

        $newValue = min(
            (int) $pet[$field] + (int) $config['value'],
            (int) $this->config['max_status_value']
        );

        $newExp = (int) $pet['exp'] + (int) $this->config['pet_action_reward_exp'];
        $newLevel = (int) $pet['level'];
        $levelExpBase = max((int) $this->config['pet_level_exp_base'], 1);

        while ($newExp >= $levelExpBase) {
            $newLevel += 1;
            $newExp -= $levelExpBase;
        }

        $this->repository->updatePet((int) $pet['id'], [
            $field => $newValue,
            'exp' => $newExp,
            'level' => $newLevel,
        ]);

        // 宠物操作成功后，同步推进每日任务进度。
        // feed / bath / play 必须和 pet_tasks.task_type 保持一致。
        $this->repository->incrementTasksByType($userId, $action);

        return $this->getPet($userId);
    }

    private function applyStatusDecay(array $pet): array
    {
        $updatedAt = strtotime((string) ($pet['updated_at'] ?? ''));

        if (!$updatedAt) {
            return $pet;
        }

        $elapsedSeconds = time() - $updatedAt;
        $decayMinutes = (int) ($this->config['status_decay_minutes'] ?? 1);
        $elapsedUnits = intdiv(max($elapsedSeconds, 0), max($decayMinutes, 1) * 60);

        if ($elapsedUnits <= 0) {
            return $pet;
        }

        $decayValues = $this->config['status_decay_values'] ?? [];
        $updates = [];

        foreach ($decayValues as $field => $value) {
            $updates[$field] = max(
                (int) $pet[$field] - ((int) $value * $elapsedUnits),
                0
            );
        }

        if (!$updates) {
            return $pet;
        }

        $this->repository->updatePet((int) $pet['id'], $updates);

        return array_merge($pet, $updates);
    }
}
