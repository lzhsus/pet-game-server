<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class MySqlGameRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM pet_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch() ?: [];
    }

    public function createDefaultUser(int $userId = 1): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pet_users (id, nickname, coin, diamond) VALUES (:id, :nickname, 1000, 100)
             ON DUPLICATE KEY UPDATE id = id'
        );

        $stmt->execute([
            'id' => $userId,
            'nickname' => '玩家' . str_pad((string) $userId, 3, '0', STR_PAD_LEFT),
        ]);

        return $this->findUser($userId);
    }

    public function updateUser(int $userId, array $data): void
    {
        $fields = [];
        $params = ['id' => $userId];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = :{$key}";
            $params[$key] = $value;
        }

        if (!$fields) {
            return;
        }

        $sql = 'UPDATE pet_users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function findPetByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM pet_pets WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: [];
    }

    public function createDefaultPet(int $userId): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pet_pets (user_id, name, type, level, exp, hunger, clean_value, mood) VALUES (:user_id, :name, :type, 1, 0, 60, 60, 60)
             ON DUPLICATE KEY UPDATE user_id = user_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'name' => '布丁',
            'type' => 'cat',
        ]);

        return $this->findPetByUserId($userId);
    }

    public function updatePet(int $petId, array $data): void
    {
        $fields = [];
        $params = ['id' => $petId];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = :{$key}";
            $params[$key] = $value;
        }

        if (!$fields) {
            return;
        }

        $sql = 'UPDATE pet_pets SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function listBagItems(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM pet_bag_items WHERE user_id = :user_id ORDER BY id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findUsableBagItemByType(int $userId, string $type): array
    {
        $stmt = $this->db->prepare('SELECT * FROM pet_bag_items WHERE user_id = :user_id AND item_type = :item_type AND item_count > 0 ORDER BY id ASC LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'item_type' => $type,
        ]);
        return $stmt->fetch() ?: [];
    }

    public function consumeBagItem(int $bagItemId, int $count = 1): void
    {
        $stmt = $this->db->prepare('UPDATE pet_bag_items SET item_count = GREATEST(item_count - :count, 0) WHERE id = :id');
        $stmt->execute([
            'id' => $bagItemId,
            'count' => $count,
        ]);
    }

    public function listShopGoods(): array
    {
        $stmt = $this->db->query('SELECT * FROM pet_shop_goods WHERE status = 1 ORDER BY sort ASC, id ASC');
        return $stmt->fetchAll();
    }

    public function addBagItem(int $userId, array $goods, int $count = 1): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pet_bag_items
                (user_id, item_id, item_name, item_type, item_count, hunger_value, clean_value, mood_value, exp_value)
             VALUES
                (:user_id, :item_id, :item_name, :item_type, :item_count, :hunger_value, :clean_value, :mood_value, :exp_value)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'item_id' => (int) $goods['id'],
            'item_name' => (string) $goods['goods_name'],
            'item_type' => (string) $goods['goods_type'],
            'item_count' => $count,
            'hunger_value' => (int) ($goods['hunger_value'] ?? 0),
            'clean_value' => (int) ($goods['clean_value'] ?? 0),
            'mood_value' => (int) ($goods['mood_value'] ?? 0),
            'exp_value' => (int) ($goods['exp_value'] ?? 0),
        ]);
    }

    public function listTasks(int $userId): array
    {
        $today = date('Y-m-d');
        $sql = 'SELECT t.id, t.title, t.task_type, t.target_value, t.reward_coin, t.reward_exp, COALESCE(ut.progress, 0) AS progress, COALESCE(ut.status, 0) AS status
                FROM pet_tasks t
                LEFT JOIN pet_user_tasks ut ON ut.task_id = t.id AND ut.user_id = :user_id AND ut.task_date = :task_date
                WHERE t.status = 1
                ORDER BY t.sort ASC, t.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'task_date' => $today,
        ]);
        return $stmt->fetchAll();
    }

    public function saveUserTask(int $userId, int $taskId, int $progress, int $status): void
    {
        $today = date('Y-m-d');
        $sql = 'INSERT INTO pet_user_tasks (user_id, task_id, task_date, progress, status)
                VALUES (:user_id, :task_id, :task_date, :progress, :status)
                ON DUPLICATE KEY UPDATE progress = VALUES(progress), status = VALUES(status)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'task_id' => $taskId,
            'task_date' => $today,
            'progress' => $progress,
            'status' => $status,
        ]);
    }

    public function incrementTasksByType(int $userId, string $taskType, int $step = 1): void
    {
        $tasks = $this->listTasks($userId);

        foreach ($tasks as $task) {
            if ((string) $task['task_type'] !== $taskType) {
                continue;
            }

            if ((int) $task['status'] >= 1) {
                continue;
            }

            $targetValue = max((int) $task['target_value'], 1);
            $newProgress = min((int) $task['progress'] + $step, $targetValue);
            $newStatus = $newProgress >= $targetValue ? 1 : 0;

            $this->saveUserTask($userId, (int) $task['id'], $newProgress, $newStatus);
        }
    }

    public function addSignLog(int $userId, string $date, int $coin, int $diamond): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO pet_user_sign_logs (user_id, sign_date, reward_coin, reward_diamond) VALUES (:user_id, :sign_date, :reward_coin, :reward_diamond)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'sign_date' => $date,
            'reward_coin' => $coin,
            'reward_diamond' => $diamond,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function listSignLogsByDateRange(int $userId, string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM pet_user_sign_logs WHERE user_id = :user_id AND sign_date BETWEEN :start_date AND :end_date ORDER BY sign_date ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $stmt->fetchAll();
    }
}
