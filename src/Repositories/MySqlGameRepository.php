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
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch() ?: [];
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

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function findPetByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM pets WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: [];
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

        $sql = 'UPDATE pets SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function listBagItems(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM bag_items WHERE user_id = :user_id ORDER BY id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findUsableBagItemByType(int $userId, string $type): array
    {
        $stmt = $this->db->prepare('SELECT * FROM bag_items WHERE user_id = :user_id AND item_type = :item_type AND item_count > 0 ORDER BY id ASC LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'item_type' => $type,
        ]);
        return $stmt->fetch() ?: [];
    }

    public function consumeBagItem(int $bagItemId, int $count = 1): void
    {
        $stmt = $this->db->prepare('UPDATE bag_items SET item_count = GREATEST(item_count - :count, 0) WHERE id = :id');
        $stmt->execute([
            'id' => $bagItemId,
            'count' => $count,
        ]);
    }

    public function listShopGoods(): array
    {
        $stmt = $this->db->query('SELECT * FROM shop_goods WHERE status = 1 ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public function addBagItem(int $userId, int $itemId, string $name, string $type, int $count = 1): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO bag_items (user_id, item_id, item_name, item_type, item_count) VALUES (:user_id, :item_id, :item_name, :item_type, :item_count)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'item_id' => $itemId,
            'item_name' => $name,
            'item_type' => $type,
            'item_count' => $count,
        ]);
    }

    public function listTasks(int $userId): array
    {
        $sql = 'SELECT t.id, t.title, t.task_type, t.target_value, t.reward_coin, t.reward_exp, COALESCE(ut.progress, 0) AS progress, COALESCE(ut.status, 0) AS status
                FROM tasks t
                LEFT JOIN user_tasks ut ON ut.task_id = t.id AND ut.user_id = :user_id
                WHERE t.status = 1
                ORDER BY t.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function saveUserTask(int $userId, int $taskId, int $progress, int $status): void
    {
        $sql = 'INSERT INTO user_tasks (user_id, task_id, progress, status)
                VALUES (:user_id, :task_id, :progress, :status)
                ON DUPLICATE KEY UPDATE progress = VALUES(progress), status = VALUES(status)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'task_id' => $taskId,
            'progress' => $progress,
            'status' => $status,
        ]);
    }

    public function addSignLog(int $userId, string $date, int $coin, int $diamond): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO user_sign_logs (user_id, sign_date, reward_coin, reward_diamond) VALUES (:user_id, :sign_date, :reward_coin, :reward_diamond)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'sign_date' => $date,
            'reward_coin' => $coin,
            'reward_diamond' => $diamond,
        ]);

        return $stmt->rowCount() > 0;
    }
}
