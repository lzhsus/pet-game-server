<?php

declare(strict_types=1);

namespace App;

class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success(array $data = [], string $message = 'success'): void
    {
        self::json([
            'code' => 0,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message, int $code = 1, int $status = 400): void
    {
        self::json([
            'code' => $code,
            'message' => $message,
            'data' => null,
        ], $status);
    }
}
