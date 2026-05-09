<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function createToken(int $userId): string
    {
        return base64_encode('pet-game-user-' . $userId);
    }

    public static function userId(): int
    {
        $token = Request::bearerToken();

        if (!$token) {
            return 0;
        }

        $decoded = base64_decode($token);

        if (!$decoded) {
            return 0;
        }

        if (!str_contains($decoded, 'pet-game-user-')) {
            return 0;
        }

        return (int) str_replace('pet-game-user-', '', $decoded);
    }
}
