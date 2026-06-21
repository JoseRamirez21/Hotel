<?php
// includes/sidebar.php
$modulo_actual = $modulo_actual ?? '';
?>
<aside class="hlv-sidebar d-none d-lg-flex flex-column">

  <nav class="flex-grow-1 py-2">

    <!-- Dashboard: todos lo ven -->
    <a href="<?= BASE_URL ?>modulos/dashboard/index.php"
       class="sidebar-link <?= $modulo_actual === 'dashboard' ? 'active' : '' ?>">
      <span class="sidebar-icon">📊</span> Dashboard
    </a>

    <div class="sidebar-section">Operación</div>

    <?php if (puede('reservas', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/reservas/index.php"
       class="sidebar-link <?= $modulo_actual === 'reservas' ? 'active' : '' ?>">
      <span class="sidebar-icon">📅</span> Reservas
    </a>
    <?php endif; ?>

    <?php if (puede('habitaciones', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/habitaciones/index.php"
       class="sidebar-link <?= $modulo_actual === 'habitaciones' ? 'active' : '' ?>">
      <span class="sidebar-icon">🛏</span> Habitaciones
    </a>
    <?php endif; ?>

    <?php if (puede('clientes', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/clientes/index.php"
       class="sidebar-link <?= $modulo_actual === 'clientes' ? 'active' : '' ?>">
      <span class="sidebar-icon">👥</span> Huéspedes
    </a>
    <?php endif; ?>

    <div class="sidebar-section">Administración</div>

    <?php if (puede('facturacion', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/facturacion/index.php"
       class="sidebar-link <?= $modulo_actual === 'facturacion' ? 'active' : '' ?>">
      <span class="sidebar-icon">🧾</span> Facturación
    </a>
    <?php endif; ?>

    <?php if (puede('caja', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/caja/index.php"
       class="sidebar-link <?= $modulo_actual === 'caja' ? 'active' : '' ?>">
      <span class="sidebar-icon">💰</span> Caja
    </a>
    <?php endif; ?>

    <?php if (puede('inventario', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/inventario/index.php"
       class="sidebar-link <?= $modulo_actual === 'inventario' ? 'active' : '' ?>">
      <span class="sidebar-icon">📦</span> Inventario
    </a>
    <?php endif; ?>

    <?php if (puede('personal', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/personal/index.php"
       class="sidebar-link <?= $modulo_actual === 'personal' ? 'active' : '' ?>">
      <span class="sidebar-icon">👤</span> Personal
    </a>
    <?php endif; ?>

    <div class="sidebar-section">Análisis</div>

    <?php if (puede('reportes', 'ver')): ?>
    <a href="<?= BASE_URL ?>modulos/reportes/index.php"
       class="sidebar-link <?= $modulo_actual === 'reportes' ? 'active' : '' ?>">
      <span class="sidebar-icon">📈</span> Reportes
    </a>
    <?php endif; ?>

    <?php if ($_SESSION['rol'] === 'administrador'): ?>
    <div class="sidebar-section">Sistema</div>
    <a href="<?= BASE_URL ?>modulos/usuarios/index.php"
       class="sidebar-link <?= $modulo_actual === 'usuarios' ? 'active' : '' ?>">
      <span class="sidebar-icon">🔐</span> Usuarios
    </a>
    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <span style="font-size:.75rem;color:#94a3b8">v1.0 · <?= HOTEL_NOMBRE ?></span>
  </div>
</aside>