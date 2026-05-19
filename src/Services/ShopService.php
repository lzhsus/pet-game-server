<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MySqlGameRepository;

/**
 * 商城业务服务。
 *
 * 当前商城只负责两件事：
 * 1. 返回上架商品列表。
 * 2. 用户购买商品，扣金币后把商品放入背包。
 *
 * 商品属性字段说明：
 * - hunger_value：使用后增加饥饿值。
 * - clean_value：使用后增加清洁值。
 * - mood_value：使用后增加心情值。
 * - exp_value：使用后增加宠物经验。
 *
 * 购买时会把这些属性一起写入 pet_bag_items，
 * 这样背包里的物品就是一个“商品快照”。
 */
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

        $priceCoin = (int) $goods['price_coin'];

        if ((int) $user['coin'] < $priceCoin) {
            return ['success' => false, 'message' => '金币不足'];
        }

        $this->repository->updateUser($userId, [
            'coin' => (int) $user['coin'] - $priceCoin,
        ]);

        $this->repository->addBagItem(
            $userId,
            $goods,
            (int) ($goods['item_count'] ?? 1)
        );

        return [
            'success' => true,
            'message' => '购买成功',
            'goods' => $goods,
            'user' => $this->repository->findUser($userId),
            'bag' => $this->repository->listBagItems($userId),
        ];
    }
}
