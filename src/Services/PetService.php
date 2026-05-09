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
        return $this->repository->findPetByUserId($userId);
    }

    public function action(int $userId, string $action): array
    {
        $pet = $this->getPet($userId);

        if (!$pet) {
            return [];
        }

        $config = $this->config['pet_actions'][$action] ?? null;

        if (!$config) {
            return $pet;
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

        if ($newExp >= (int) $this->config['pet_level_exp_base']) {
            $newLevel += 1;
            $newExp = 0;
        }

        $this->repository->updatePet((int) $pet['id'], [
            $field => $newValue,
            'exp' => $newExp,
            'level' => $newLevel,
        ]);

        $user = $this->repository->findUser($userId);
        $this->repository->updateUser($userId, [
            'coin' => (int) $user['coin'] + (int) $this->config['pet_action_reward_coin'],
        ]);

        return $this->getPet($userId);
    }
}
