<?php
// includes/header.php
// Requiere que config/session.php ya haya sido incluido y requiereLogin() llamado
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $titulo_pagina ?? HOTEL_NOMBRE ?> — <?= HOTEL_NOMBRE ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/estilos.css">
</head>
<body>

<!-- NAVBAR TOP -->
<nav class="navbar navbar-expand-lg navbar-dark hlv-navbar px-3 py-2">
  <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="<?= BASE_URL ?>modulos/dashboard/index.php">
    <span style="font-size:1.3rem">🏨</span>
    <span><?= HOTEL_NOMBRE ?></span>
    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">★★★</span>
  </a>

  <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navMenu">
    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

      <!-- Notificaciones (placeholder) -->
      <li class="nav-item">
        <a class="nav-link position-relative" href="#">
          🔔
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">3</span>
        </a>
      </li>

      <!-- Usuario -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
          <div class="avatar-circle">
            <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
          </div>
          <span class="d-none d-lg-inline" style="font-size:.875rem">
            <?= htmlspecialchars($_SESSION['nombre']) ?>
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
          <li>
            <span class="dropdown-item-text text-muted" style="font-size:.78rem">
              <?= ucfirst($_SESSION['rol']) ?>
            </span>
          </li>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout.php">Cerrar sesión</a></li>
        </ul>
      </li>
    </ul>
  </div>
</nav>

<!-- LAYOUT PRINCIPAL -->
<div class="d-flex" style="min-height:calc(100vh - 56px)">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="hlv-main flex-grow-1 p-3 p-lg-4">