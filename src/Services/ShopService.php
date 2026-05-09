<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\JsonGameRepository;

class ShopService
{
    public function __construct(private JsonGameRepository $repository)
    {
    }

    public function list(): array
    {
        return [
            ['id' => 101, 'name' => '高级猫粮', 'price_coin' => 30, 'type' => 'food'],
            ['id' => 102, 'name' => '香氛沐浴露', 'price_coin' => 40, 'type' => 'clean'],
            ['id' => 103, 'name' => '逗猫棒', 'price_coin' => 50, 'type' => 'toy'],
        ];
    }

    public function buy(int $userId, int $goodsId): array
    {
        $goods = null;

        foreach ($this->list() as $item) {
            if ((int) $item['id'] === $goodsId) {
                $goods = $item;
            }
        }

        if (!$goods) {
            return ['success' => false, 'message' => '商品不存在'];
        }

        $data = $this->repository->all();

        foreach ($data['users'] as $userIndex => $user) {
            if ((int) $user['id'] !== $userId) {
                continue;
            }

            if ((int) $user['coin'] < (int) $goods['price_coin']) {
                return ['success' => false, 'message' => '金币不足'];
            }

            $data['users'][$userIndex]['coin'] -= (int) $goods['price_coin'];
            $data['items'][] = [
                'id' => time(),
                'user_id' => $userId,
                'name' => $goods['name'],
                'count' => 1,
            ];

            $this->repository->save($data);

            return ['success' => true, 'message' => '购买成功', 'goods' => $goods];
        }

        return ['success' => false, 'message' => '用户不存在'];
    }
}
