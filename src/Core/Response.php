<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function success(array $data = [], string $message = 'success'): void
    {
        self::json([
            'code' => 0,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message = 'error', int $code = 500, int $httpCode = 500): void
    {
        http_response_code($httpCode);

        self::json([
            'code' => $code,
            'message' => $message,
            'data' => [],
        ]);
    }

    private static function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        exit;
    }
}
