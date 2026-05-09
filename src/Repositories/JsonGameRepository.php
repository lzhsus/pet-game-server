<?php

declare(strict_types=1);

namespace App\Repositories;

class JsonGameRepository
{
    private string $file;

    public function __construct()
    {
        $this->file = dirname(__DIR__, 2) . '/storage/game-data.json';
    }

    public function all(): array
    {
        if (!file_exists($this->file)) {
            return $this->defaultData();
        }

        $content = file_get_contents($this->file);

        if (!$content) {
            return $this->defaultData();
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : $this->defaultData();
    }

    public function save(array $data): void
    {
        file_put_contents(
            $this->file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function defaultData(): array
    {
        return [
            'users' => [
                [
                    'id' => 1,
                    'nickname' => '玩家001',
                    'level' => 1,
                    'exp' => 0,
                    'coin' => 100,
                    'diamond' => 20,
                ],
            ],
            'pets' => [
                [
                    'id' => 1,
                    'user_id' => 1,
                    'name' => '布丁',
                    'type' => 'cat',
                    'level' => 1,
                    'exp' => 0,
                    'hunger' => 60,
                    'clean' => 60,
                    'mood' => 60,
                ],
            ],
            'tasks' => [],
            'items' => [],
        ];
    }
}
