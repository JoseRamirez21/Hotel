<?php
// config/session.php
// Incluir este archivo al inicio de CADA módulo protegido

require_once __DIR__ . '/config.php';

session_name(SESSION_NOMBRE);
session_start();

/**
 * Verifica que el usuario haya iniciado sesión.
 * Si no, redirige al login.
 */
function requiereLogin(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }

    // Verificar tiempo de sesión
    if (isset($_SESSION['login_time']) &&
        (time() - $_SESSION['login_time']) > SESSION_TIEMPO) {
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login.php?expirado=1');
        exit;
    }
}

/**
 * Verifica que el usuario tenga permiso para una acción.
 * Ejemplo: requierePermiso('reservas', 'crear')
 */
function requierePermiso(string $modulo, string $accion = 'ver'): void {
    requiereLogin();

    if ($_SESSION['rol'] === 'administrador') return; // admin pasa siempre

    $permisos = $_SESSION['permisos'] ?? [];

    if (!isset($permisos[$modulo]) || !$permisos[$modulo][$accion]) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

/**
 * Retorna true/false si el usuario puede hacer una acción.
 * Usar en vistas para mostrar/ocultar botones.
 * Ejemplo: if (puede('inventario', 'crear')) { ... }
 */
function puede(string $modulo, string $accion = 'ver'): bool {
    if (!isset($_SESSION['usuario_id'])) return false;
    if ($_SESSION['rol'] === 'administrador') return true;

    $permisos = $_SESSION['permisos'] ?? [];
    return isset($permisos[$modulo]) && (bool)$permisos[$modulo][$accion];
}

/**
 * Registra una acción en el log de auditoría.
 */
function registrarLog(string $accion, string $modulo = '', string $detalle = ''): void {
    if (!isset($_SESSION['usuario_id'])) return;

    try {
        require_once __DIR__ . '/database.php';
        $pdo = conectar();
        $pdo->prepare("INSERT INTO log_accesos (usuario_id, accion, modulo, ip, detalle)
                        VALUES (?, ?, ?, ?, ?)")
            ->execute([
                $_SESSION['usuario_id'],
                $accion,
                $modulo,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $detalle,
            ]);
    } catch (Exception $e) {
        // Silencioso — no interrumpir el flujo por fallo de log
    }
}