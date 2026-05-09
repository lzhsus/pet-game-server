<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = self::config();

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        self::$connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return self::$connection;
    }

    private static function config(): array
    {
        $configFile = dirname(__DIR__, 2) . '/config/database.php';

        if (file_exists($configFile)) {
            return require $configFile;
        }

        return [
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'weapp',
            'username' => 'admin',
            'password' => 'admin123',
            'charset' => 'utf8mb4',
        ];
    }
}
