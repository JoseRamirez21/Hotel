<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel_las_vegas');
define('DB_USER', 'root');       // Cambiar en producción
define('DB_PASS', '');           // Cambiar en producción
define('DB_CHARSET', 'utf8mb4');

function conectar(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Error de conexión a la base de datos.']));
        }
    }
    return $pdo;
}