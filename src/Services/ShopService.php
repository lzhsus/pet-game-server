<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

class ShopService
{
    public function __construct(private MySqlGameRepository $repository)
    {
    }

    public function list(): array
    {
        return $this->repository->listShopGoods();
    }

    public function buy(int $userId, int $goodsId): array
    {
        $goods = null;

        foreach ($this->list() as $item) {
            if ((int) $item['id'] === $goodsId) {
                $goods = $item;
                break;
            }
        }

        if (!$goods) {
            return ['success' => false, 'message' => '商品不存在'];
        }

        $user = $this->repository->findUser($userId);

        if (!$user) {
            return ['success' => false, 'message' => '用户不存在'];
        }

        if ((int) $user['coin'] < (int) $goods['price_coin']) {
            return ['success' => false, 'message' => '金币不足'];
        }

        $this->repository->updateUser($userId, [
            'coin' => (int) $user['coin'] - (int) $goods['price_coin'],
        ]);

        $this->repository->addBagItem(
            $userId,
            (int) $goods['id'],
            (string) $goods['goods_name'],
            (string) $goods['goods_type'],
            1
        );

        return [
            'success' => true,
            'message' => '购买成功',
            'goods' => $goods,
        ];
    }
}
