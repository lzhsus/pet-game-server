<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public static function json(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function header(string $name): string
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if (!empty($_SERVER[$serverKey])) {
            return (string) $_SERVER[$serverKey];
        }

        if (strtolower($name) === 'authorization') {
            if (!empty($_SERVER['Authorization'])) {
                return (string) $_SERVER['Authorization'];
            }

            if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                return (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                foreach ($headers as $key => $value) {
                    if (strtolower($key) === 'authorization') {
                        return (string) $value;
                    }
                }
            }
        }

        return '';
    }

    public static function bearerToken(): string
    {
        $authorization = self::header('Authorization');
        if (str_starts_with($authorization, 'Bearer ')) {
            return trim(substr($authorization, 7));
        }

        return '';
    }
}
