<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

/**
 * 宠物核心业务服务。
 *
 * 这个类主要负责 3 件事：
 * 1. 获取宠物信息。
 * 2. 处理喂食 / 洗澡 / 玩耍等宠物操作。
 * 3. 根据离线时间自动衰减宠物状态。
 *
 * 宠物有 3 个核心状态：
 * - hunger：饥饿值，越高代表越饱。
 * - clean_value：清洁值，越高代表越干净。
 * - mood：心情值，越高代表越开心。
 *
 * 状态值统一控制在 0 ~ max_status_value 之间。
 * 当前配置在 src/Config/game.php。
 */
class PetService
{
    private array $config;

    public function __construct(private MySqlGameRepository $repository)
    {
        // 读取游戏配置，例如：状态最大值、衰减周期、操作增加值、升级经验等。
        $this->config = require dirname(__DIR__) . '/Config/game.php';
    }

    /**
     * 获取宠物信息。
     *
     * 这里做了两个兜底：
     * 1. 如果用户不存在，自动创建默认用户。
     * 2. 如果宠物不存在，自动创建默认宠物。
     *
     * 最后会调用 applyStatusDecay()，根据 updated_at 计算离线时间，
     * 自动扣减 hunger / clean_value / mood。
     */
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

    /**
     * 执行宠物操作。
     *
     * $action 当前支持：
     * - feed：喂食，提升 hunger。
     * - bath：洗澡，提升 clean_value。
     * - play：玩耍，提升 mood。
     *
     * 操作流程：
     * 1. 确保用户和宠物存在。
     * 2. 根据操作类型找到配置。
     * 3. 优先消耗背包道具。
     * 4. 背包没有道具时，扣金币。
     * 5. 增加对应宠物状态。
     * 6. 增加宠物经验，并处理升级。
     * 7. 推进每日任务进度。
     */
    public function action(int $userId, string $action): array
    {
        $user = $this->repository->findUser($userId);
        if (!$user) {
            $user = $this->repository->createDefaultUser($userId);
        }

        // getPet() 内部会顺手处理状态衰减，所以这里拿到的是已经更新过状态的宠物数据。
        $pet = $this->getPet($userId);

        if (!$pet) {
            return [
                'error' => true,
                'message' => '宠物初始化失败',
            ];
        }

        // 从配置表里读取本次操作影响哪个状态，以及增加多少。
        // 例如 feed => hunger + 1。
        $config = $this->config['pet_actions'][$action] ?? null;

        if (!$config) {
            return $pet;
        }

        // 消耗配置：每种操作优先使用背包道具；没有道具时再扣金币。
        // feed 对应 food，道具不存在时扣 10 金币。
        // bath 对应 clean，道具不存在时扣 15 金币。
        // play 对应 toy，道具不存在时扣 20 金币。
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
                // 有可用道具：消耗 1 个背包物品。
                $this->repository->consumeBagItem((int) $bagItem['id']);
            } else {
                // 没有可用道具：改为扣金币。
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

        // 配置里为了更接近前端语义，bath 写的是 clean。
        // 但是数据库字段名叫 clean_value，所以这里统一转换。
        $field = $config['field'];
        if ($field === 'clean') {
            $field = 'clean_value';
        }

        // 增加对应状态，并限制最大值不超过 max_status_value。
        // 例如当前 hunger=99，喂食 +1，最高也只能到 100。
        $newValue = min(
            (int) $pet[$field] + (int) $config['value'],
            (int) $this->config['max_status_value']
        );

        // 每次操作成功，宠物获得经验。
        // 当前经验达到 pet_level_exp_base 后自动升级，并保留多余经验。
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

        // 重新读取宠物信息，让前端拿到最新状态、等级和经验。
        return $this->getPet($userId);
    }

    /**
     * 根据时间自动衰减宠物状态。
     *
     * 这个方法是宠物养成系统的核心之一。
     *
     * 计算方式：
     * 1. 读取宠物 updated_at。
     * 2. 用当前时间减去 updated_at，得到距离上次更新过去了多少秒。
     * 3. 根据 status_decay_minutes 计算经过了几个衰减周期。
     * 4. 每经过 1 个周期，就从 hunger / clean_value / mood 中扣除配置值。
     *
     * 当前配置：
     * - status_decay_minutes = 180，表示每 3 小时衰减一次。
     * - hunger 每 3 小时减少 5。
     * - clean_value 每 3 小时减少 3。
     * - mood 每 3 小时减少 4。
     *
     * 例子：
     * - 如果玩家 6 小时没打开游戏，就是 2 个衰减周期。
     * - hunger 会减少 5 * 2 = 10。
     * - clean_value 会减少 3 * 2 = 6。
     * - mood 会减少 4 * 2 = 8。
     *
     * 注意：状态最低不会小于 0。
     */
    private function applyStatusDecay(array $pet): array
    {
        $updatedAt = strtotime((string) ($pet['updated_at'] ?? ''));

        if (!$updatedAt) {
            return $pet;
        }

        $elapsedSeconds = time() - $updatedAt;
        $decayMinutes = (int) ($this->config['status_decay_minutes'] ?? 1);

        // 计算完整经过了几个衰减周期。
        // intdiv 是整数除法，不足一个周期不会衰减。
        // 例：周期是 180 分钟，离线 179 分钟，衰减次数是 0。
        // 例：周期是 180 分钟，离线 360 分钟，衰减次数是 2。
        $elapsedUnits = intdiv(max($elapsedSeconds, 0), max($decayMinutes, 1) * 60);

        if ($elapsedUnits <= 0) {
            return $pet;
        }

        $decayValues = $this->config['status_decay_values'] ?? [];
        $updates = [];

        foreach ($decayValues as $field => $value) {
            // 每个状态按照：当前值 - 单次衰减值 * 衰减周期数。
            // max(..., 0) 保证状态值不会变成负数。
            $updates[$field] = max(
                (int) $pet[$field] - ((int) $value * $elapsedUnits),
                0
            );
        }

        if (!$updates) {
            return $pet;
        }

        // 写回数据库。由于 updated_at 会自动更新，下一次衰减会从当前时间重新开始计算。
        $this->repository->updatePet((int) $pet['id'], $updates);

        // 返回合并后的宠物数据，避免前端拿到旧状态。
        return array_merge($pet, $updates);
    }
}
