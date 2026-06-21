<?php
// auth/login.php
require_once '../config/config.php';
require_once '../config/database.php';

session_name(SESSION_NOMBRE);
session_start();

// Si ya inició sesión, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: ../modulos/dashboard/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Por favor ingresa tu correo y contraseña.';
    } else {
        $pdo  = conectar();
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre, u.apellido, u.email, u.password,
                   u.activo, u.foto,
                   r.nombre AS rol
            FROM usuarios u
            JOIN roles r ON r.id = u.rol_id
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $error = 'Correo o contraseña incorrectos.';
        } elseif (!$usuario['activo']) {
            $error = 'Tu cuenta está desactivada. Contacta al administrador.';
        } else {
            // Registrar login exitoso
            $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?")
                ->execute([$usuario['id']]);

            $pdo->prepare("INSERT INTO log_accesos (usuario_id, accion, modulo, ip)
                           VALUES (?, 'login', 'auth', ?)")
                ->execute([$usuario['id'], $_SERVER['REMOTE_ADDR']]);

            // Cargar permisos del rol
            $stmtP = $pdo->prepare("
                SELECT modulo, ver, crear, editar, eliminar
                FROM permisos
                WHERE rol_id = (SELECT rol_id FROM usuarios WHERE id = ?)
            ");
            $stmtP->execute([$usuario['id']]);
            $permisos = [];
            foreach ($stmtP->fetchAll() as $p) {
                $permisos[$p['modulo']] = $p;
            }

            // Guardar sesión
            $_SESSION['usuario_id']  = $usuario['id'];
            $_SESSION['nombre']      = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['email']       = $usuario['email'];
            $_SESSION['rol']         = $usuario['rol'];
            $_SESSION['foto']        = $usuario['foto'];
            $_SESSION['permisos']    = $permisos;
            $_SESSION['login_time']  = time();

            session_regenerate_id(true);
            header('Location: ../modulos/dashboard/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión — <?= HOTEL_NOMBRE ?></title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <style>
    body {
      background: #f4f6fb;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-card {
      background: #fff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 400px;
    }
    .hotel-stars { color: #f59e0b; font-size: 1rem; }
    .logo-circle {
      width: 64px; height: 64px;
      background: #eef2ff;
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; margin: 0 auto 1rem;
    }
    .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
    .btn-login {
      background: #6366f1; color: #fff; border: none;
      border-radius: 8px; padding: .65rem 1.5rem;
      width: 100%; font-size: .95rem; font-weight: 500;
      transition: background .2s;
    }
    .btn-login:hover { background: #4f46e5; color: #fff; }
    .input-group-text { background: #f8fafc; border-right: none; }
    .form-control { border-left: none; }
    .form-control:not(:focus) { border-color: #dee2e6; }
    .toggle-pass { cursor: pointer; background: #f8fafc; border-left: none; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="text-center mb-4">
      <div class="logo-circle">🏨</div>
      <h1 class="h5 fw-semibold mb-1"><?= HOTEL_NOMBRE ?></h1>
      <div class="hotel-stars">★★★</div>
      <p class="text-muted mt-1" style="font-size:.85rem"><?= HOTEL_CIUDAD ?></p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show py-2" role="alert"
           style="font-size:.875rem;border-radius:8px">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="mb-3">
        <label class="form-label fw-medium" style="font-size:.875rem">Correo electrónico</label>
        <div class="input-group">
          <span class="input-group-text"><i>@</i></span>
          <input type="email" name="email" class="form-control"
                 placeholder="usuario@hotel.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 required autocomplete="email">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium" style="font-size:.875rem">Contraseña</label>
        <div class="input-group">
          <span class="input-group-text">🔒</span>
          <input type="password" name="password" id="password"
                 class="form-control" placeholder="••••••••"
                 required autocomplete="current-password">
          <button type="button" class="btn toggle-pass border"
                  onclick="togglePassword()" id="toggle-icon">👁</button>
        </div>
      </div>

      <button type="submit" class="btn-login">Ingresar al sistema</button>
    </form>

    <p class="text-center text-muted mt-3 mb-0" style="font-size:.8rem">
      Sistema de gestión v1.0 · <?= HOTEL_NOMBRE ?>
    </p>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const icon  = document.getElementById('toggle-icon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '🙈';
      } else {
        input.type = 'password';
        icon.textContent = '👁';
      }
    }
  </script>
</body>
</html>