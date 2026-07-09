<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection === null) {

            try {

                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    config('database.host'),
                    config('database.port'),
                    config('database.database'),
                    config('database.charset')
                );

                self::$connection = new PDO(
                    $dsn,
                    config('database.username'),
                    config('database.password'),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );

            } catch (PDOException $e) {

                die('Error de conexión: ' . $e->getMessage());

            }
        }

        return self::$connection;
    }
}