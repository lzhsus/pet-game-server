<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

class RewardService
{
    public function __construct(private MySqlGameRepository $repository)
    {
    }

    public function dailySign(int $userId): array
    {
        $coin = 50;
        $diamond = 1;
        $today = date('Y-m-d');

        $created = $this->repository->addSignLog($userId, $today, $coin, $diamond);

        if (!$created) {
            return [
                'received' => false,
                'message' => '今日已签到',
                'coin' => 0,
                'diamond' => 0,
                'week' => $this->week($userId),
            ];
        }

        $user = $this->repository->findUser($userId);
        $this->repository->updateUser($userId, [
            'coin' => (int) $user['coin'] + $coin,
            'diamond' => (int) $user['diamond'] + $diamond,
        ]);

        // 签到成功后推进每日签到任务。
        // pet_tasks.task_type 需要配置为 sign。
        $this->repository->incrementTasksByType($userId, 'sign');

        return [
            'received' => true,
            'message' => '签到成功',
            'coin' => $coin,
            'diamond' => $diamond,
            'week' => $this->week($userId),
        ];
    }

    public function week(int $userId): array
    {
        $today = new \DateTimeImmutable(date('Y-m-d'));
        $weekDay = (int) $today->format('N');
        $weekStart = $today->modify('-' . ($weekDay - 1) . ' days');
        $weekEnd = $weekStart->modify('+6 days');

        $logs = $this->repository->listSignLogsByDateRange(
            $userId,
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d')
        );

        $logMap = [];
        foreach ($logs as $log) {
            $logMap[(string) $log['sign_date']] = $log;
        }

        $labels = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
        $week = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->modify('+' . $i . ' days');
            $dateString = $date->format('Y-m-d');
            $log = $logMap[$dateString] ?? null;

            $week[] = [
                'date' => $dateString,
                'day_no' => $i + 1,
                'label' => $labels[$i],
                'signed' => $log !== null,
                'is_today' => $dateString === $today->format('Y-m-d'),
                'is_future' => $dateString > $today->format('Y-m-d'),
                'reward_coin' => $log ? (int) $log['reward_coin'] : 50,
                'reward_diamond' => $log ? (int) $log['reward_diamond'] : 1,
            ];
        }

        return [
            'today' => $today->format('Y-m-d'),
            'week_start_date' => $weekStart->format('Y-m-d'),
            'week_end_date' => $weekEnd->format('Y-m-d'),
            'today_signed' => isset($logMap[$today->format('Y-m-d')]),
            'week' => $week,
        ];
    }
}
