<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

/**
 * 每日任务业务服务。
 *
 * 任务系统分成两张表：
 * 1. pet_tasks：任务配置表，配置有哪些任务、目标次数、奖励金币、奖励经验。
 * 2. pet_user_tasks：用户每日任务进度表，记录某个用户某一天的任务进度和领取状态。
 *
 * 任务状态 status：
 * - 0：未完成，按钮显示“去完成”。
 * - 1：已完成但未领取，按钮显示“领取”。
 * - 2：已领取，按钮显示“已完成”。
 *
 * 奖励规则：
 * - reward_coin 加到 pet_users.coin。
 * - reward_exp 加到 pet_pets.exp，也就是宠物经验，不是用户经验。
 */
class TaskService
{
    public function __construct(private MySqlGameRepository $repository)
    {
    }

    /**
     * 获取每日任务列表。
     *
     * listTasks() 内部会把任务配置 pet_tasks 和当天用户进度 pet_user_tasks 合并。
     * 如果用户当天还没有任务记录，会默认返回 progress=0、status=0。
     */
    public function list(int $userId): array
    {
        return $this->repository->listTasks($userId);
    }

    /**
     * 领取任务奖励。
     *
     * 领取前会做 3 个检查：
     * 1. 任务是否存在。
     * 2. 奖励是否已经领取过。
     * 3. 当前进度是否达到目标值。
     *
     * 领取成功后：
     * 1. 把 pet_user_tasks.status 改成 2，表示已领取。
     * 2. 把任务金币 reward_coin 加到用户金币 pet_users.coin。
     * 3. 把任务经验 reward_exp 加到宠物经验 pet_pets.exp。
     * 4. 如果宠物经验达到升级要求，就自动升级。
     */
    public function receive(int $userId, int $taskId): array
    {
        $tasks = $this->repository->listTasks($userId);
        $matchedTask = null;

        // 在当天任务列表里找到用户要领取的任务。
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

        // status=2 表示已经领取过，防止重复领取奖励。
        if ((int) $matchedTask['status'] === 2) {
            return [
                'error' => true,
                'message' => '奖励已领取',
                'tasks' => $this->list($userId),
            ];
        }

        // progress 没达到 target_value，说明任务还没完成，不能领取。
        if ((int) $matchedTask['progress'] < (int) $matchedTask['target_value']) {
            return [
                'error' => true,
                'message' => '任务未完成',
                'tasks' => $this->list($userId),
            ];
        }

        $rewardCoin = (int) $matchedTask['reward_coin'];
        $rewardExp = (int) $matchedTask['reward_exp'];

        // 标记任务已领取。
        // progress 固定写成 target_value，避免出现已领取但进度显示不足的情况。
        $this->repository->saveUserTask(
            $userId,
            $taskId,
            (int) $matchedTask['target_value'],
            2
        );

        // 金币奖励加到用户表。
        $user = $this->repository->findUser($userId);
        if ($user) {
            $this->repository->updateUser($userId, [
                'coin' => (int) $user['coin'] + $rewardCoin,
            ]);
        }

        // 经验奖励加到宠物表。
        // 注意：用户表已经不再保存 level / exp，宠物才是成长主体。
        $pet = $this->repository->findPetByUserId($userId);
        if ($pet && $rewardExp > 0) {
            $level = (int) $pet['level'];
            $exp = (int) $pet['exp'] + $rewardExp;
            $levelExpBase = 100;

            // 当前规则：每满 100 点经验升 1 级。
            // 如果一次获得大量经验，可以连续升级，并保留剩余经验。
            while ($exp >= $levelExpBase) {
                $level += 1;
                $exp -= $levelExpBase;
            }

            $this->repository->updatePet((int) $pet['id'], [
                'level' => $level,
                'exp' => $exp,
            ]);
        }

        // 返回最新用户、宠物、任务列表，方便前端直接刷新页面。
        return [
            'reward_coin' => $rewardCoin,
            'reward_exp' => $rewardExp,
            'user' => $this->repository->findUser($userId),
            'pet' => $this->repository->findPetByUserId($userId),
            'tasks' => $this->list($userId),
        ];
    }
}
