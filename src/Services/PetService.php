<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\JsonGameRepository;

class PetService
{
    private array $config;

    public function __construct(private JsonGameRepository $repository)
    {
        $this->config = require dirname(__DIR__) . '/Config/game.php';
    }

    public function getPet(int $userId): array
    {
        $data = $this->repository->all();

        foreach ($data['pets'] as $pet) {
            if ((int) $pet['user_id'] === $userId) {
                return $pet;
            }
        }

        return $data['pets'][0];
    }

    public function action(int $userId, string $action): array
    {
        $data = $this->repository->all();

        foreach ($data['pets'] as $index => $pet) {
            if ((int) $pet['user_id'] !== $userId) {
                continue;
            }

            $config = $this->config['pet_actions'][$action] ?? null;

            if (!$config) {
                return $pet;
            }

            $field = $config['field'];
            $value = (int) $config['value'];

            $pet[$field] += $value;
            $pet[$field] = min($pet[$field], $this->config['max_status_value']);

            $pet['exp'] += $this->config['pet_action_reward_exp'];

            if ($pet['exp'] >= $this->config['pet_level_exp_base']) {
                $pet['level'] += 1;
                $pet['exp'] = 0;
            }

            $data['pets'][$index] = $pet;

            foreach ($data['users'] as $userIndex => $user) {
                if ((int) $user['id'] === $userId) {
                    $user['coin'] += $this->config['pet_action_reward_coin'];
                    $data['users'][$userIndex] = $user;
                }
            }

            $this->repository->save($data);

            return $pet;
        }

        return [];
    }
}
