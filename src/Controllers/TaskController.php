<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\JsonGameRepository;
use App\Services\TaskService;

class TaskController
{
    private function service(): TaskService
    {
        return new TaskService(new JsonGameRepository());
    }

    public function list(): void
    {
        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        Response::success([
            'tasks' => $this->service()->list($userId),
        ]);
    }

    public function receive(): void
    {
        $userId = Auth::userId();

        if ($userId <= 0) {
            Response::error('unauthorized', 401, 401);
        }

        $params = Request::json();
        $taskId = (int) ($params['task_id'] ?? 0);

        Response::success(
            $this->service()->receive($userId, $taskId)
        );
    }
}
