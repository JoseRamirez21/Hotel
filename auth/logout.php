<?php
// auth/logout.php
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/database.php';

if (isset($_SESSION['usuario_id'])) {
    // Registrar cierre de sesión
    try {
        $pdo = conectar();
        $pdo->prepare("INSERT INTO log_accesos (usuario_id, accion, modulo, ip)
                        VALUES (?, 'logout', 'auth', ?)")
            ->execute([$_SESSION['usuario_id'], $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception $e) {}
}

session_unset();
session_destroy();

header('Location: ' . BASE_URL . 'auth/login.php?sesion=cerrada');
exit;