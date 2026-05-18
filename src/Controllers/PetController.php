<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Repositories\MySqlGameRepository;
use App\Services\PetService;
use App\Services\UserService;

class PetController
{
    private function services(): array
    {
        $repository = new MySqlGameRepository();
        return [new PetService($repository), new UserService($repository)];
    }

    public function profile(): void
    {
        [$petService] = $this->services();
        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        Response::success([
            'pet' => $petService->getPet($userId),
        ]);
    }

    public function feed(): void
    {
        $this->action('feed');
    }

    public function bath(): void
    {
        $this->action('bath');
    }

    public function play(): void
    {
        $this->action('play');
    }

    private function action(string $type): void
    {
        [$petService, $userService] = $this->services();
        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        $pet = $petService->action($userId, $type);

        if (($pet['error'] ?? false) === true) {
            Response::error((string) ($pet['message'] ?? '操作失败'), 400, 200);
        }

        Response::success([
            'pet' => $pet,
            'user' => $userService->getUser($userId),
        ]);
    }
}
