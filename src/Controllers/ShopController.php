<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\MySqlGameRepository;
use App\Services\ShopService;

class ShopController
{
    private function service(): ShopService
    {
        return new ShopService(new MySqlGameRepository());
    }

    public function list(): void
    {
        Response::success([
            'goods' => $this->service()->list(),
        ]);
    }

    public function buy(): void
    {
        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        $params = Request::json();
        $goodsId = (int) ($params['goods_id'] ?? 0);

        Response::success(
            $this->service()->buy($userId, $goodsId)
        );
    }
}
