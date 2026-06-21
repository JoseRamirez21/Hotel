<?php
// modulos/reservas/index.php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/funciones.php';

requierePermiso('reservas', 'ver');

$titulo_pagina = 'Reservas';
$modulo_actual = 'reservas';

$pdo = conectar();

// Filtros
$buscar  = trim($_GET['q']      ?? '');
$estado  = $_GET['estado']      ?? '';
$fecha_i = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_f = $_GET['fecha_hasta'] ?? date('Y-m-t');

$where  = ['r.fecha_entrada BETWEEN :fi AND :ff'];
$params = [':fi' => $fecha_i, ':ff' => $fecha_f];

if ($estado) {
    $where[]          = 'r.estado = :estado';
    $params[':estado'] = $estado;
}
if ($buscar) {
    $where[]         = "(c.nombre LIKE :q OR c.apellido LIKE :q OR c.numero_documento LIKE :q OR h.numero LIKE :q)";
    $params[':q']    = "%$buscar%";
}

$sql = "
    SELECT r.id, r.fecha_entrada, r.fecha_salida, r.noches,
           r.precio_total, r.estado, r.canal,
           CONCAT(c.nombre, ' ', c.apellido) AS huesped,
           c.numero_documento,
           h.numero AS habitacion,
           t.nombre AS tipo_hab,
           CONCAT(u.nombre, ' ', u.apellido) AS registrado_por
    FROM reservas r
    JOIN clientes c     ON c.id = r.cliente_id
    JOIN habitaciones h ON h.id = r.habitacion_id
    JOIN tipos_habitacion t ON t.id = h.tipo_id
    JOIN usuarios u     ON u.id = r.usuario_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY r.fecha_entrada DESC
    LIMIT 200
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

// Conteo por estado (para los filtros rápidos)
$conteos = $pdo->query("
    SELECT estado, COUNT(*) AS n FROM reservas
    WHERE fecha_entrada BETWEEN '$fecha_i' AND '$fecha_f'
    GROUP BY estado
")->fetchAll(PDO::FETCH_KEY_PAIR);

include '../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">📅 Reservas</h1>
    <p class="page-subtitle"><?= count($reservas) ?> resultado(s) encontrados</p>
  </div>
  <?php if (puede('reservas', 'crear')): ?>
    <a href="nueva.php" class="btn-hlv">+ Nueva reserva</a>
  <?php endif; ?>
</div>

<!-- FILTROS RÁPIDOS POR ESTADO -->
<div class="d-flex flex-wrap gap-2 mb-3">
  <?php
  $estados_filtro = [
    ''           => 'Todas',
    'pendiente'  => 'Pendiente',
    'confirmada' => 'Confirmada',
    'checkin'    => 'En hotel',
    'checkout'   => 'Check-out',
    'cancelada'  => 'Cancelada',
    'no_show'    => 'No show',
  ];
  foreach ($estados_filtro as $val => $label):
    $activo = ($estado === $val) ? 'btn-hlv' : 'btn-hlv-outline';
    $n = $val ? ($conteos[$val] ?? 0) : array_sum($conteos);
  ?>
    <a href="?estado=<?= $val ?>&fecha_desde=<?= $fecha_i ?>&fecha_hasta=<?= $fecha_f ?>&q=<?= urlencode($buscar) ?>"
       class="<?= $activo ?> btn-hlv-sm">
      <?= $label ?> <span style="opacity:.7">(<?= $n ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<!-- BUSCADOR Y RANGO DE FECHAS -->
<div class="hlv-card mb-3">
  <form method="GET" class="row g-2 align-items-end">
    <input type="hidden" name="estado" value="<?= htmlspecialchars($estado) ?>">
    <div class="col-12 col-md-4">
      <label class="hlv-form-label">Buscar huésped / habitación</label>
      <input type="text" name="q" class="hlv-input" placeholder="Nombre, DNI, hab..."
             value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="hlv-form-label">Desde</label>
      <input type="date" name="fecha_desde" class="hlv-input" value="<?= $fecha_i ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="hlv-form-label">Hasta</label>
      <input type="date" name="fecha_hasta" class="hlv-input" value="<?= $fecha_f ?>">
    </div>
    <div class="col-12 col-md-2">
      <button type="submit" class="btn-hlv w-100 justify-content-center">Buscar</button>
    </div>
  </form>
</div>

<!-- TABLA DE RESERVAS -->
<div class="hlv-card">
  <div class="table-responsive">
    <table class="hlv-table table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Huésped</th>
          <th>Habitación</th>
          <th>Entrada</th>
          <th>Salida</th>
          <th>Noches</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reservas)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">No hay reservas en este período.</td></tr>
        <?php endif; ?>
        <?php foreach ($reservas as $r): ?>
        <tr>
          <td class="text-muted" style="font-size:.78rem">#<?= $r['id'] ?></td>
          <td>
            <div style="font-weight:500"><?= htmlspecialchars($r['huesped']) ?></div>
            <div style="font-size:.75rem;color:#94a3b8"><?= htmlspecialchars($r['numero_documento']) ?></div>
          </td>
          <td>
            <span style="font-weight:600"><?= htmlspecialchars($r['habitacion']) ?></span>
            <div style="font-size:.75rem;color:#94a3b8"><?= htmlspecialchars($r['tipo_hab']) ?></div>
          </td>
          <td><?= date('d/m/Y', strtotime($r['fecha_entrada'])) ?></td>
          <td><?= date('d/m/Y', strtotime($r['fecha_salida'])) ?></td>
          <td class="text-center"><?= $r['noches'] ?></td>
          <td style="font-weight:500"><?= MONEDA ?> <?= number_format($r['precio_total'], 2) ?></td>
          <td>
            <span class="estado-badge badge-<?= $r['estado'] ?>">
              <?= ucfirst(str_replace('_',' ',$r['estado'])) ?>
            </span>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="ver.php?id=<?= $r['id'] ?>" class="btn-hlv btn-hlv-sm" title="Ver">👁</a>
              <?php if ($r['estado'] === 'confirmada' && puede('reservas', 'editar')): ?>
                <a href="checkin.php?id=<?= $r['id'] ?>" class="btn-hlv btn-hlv-sm" title="Check-in">✅</a>
              <?php endif; ?>
              <?php if ($r['estado'] === 'checkin' && puede('reservas', 'editar')): ?>
                <a href="checkout.php?id=<?= $r['id'] ?>" class="btn-hlv btn-hlv-sm" title="Check-out">🏁</a>
              <?php endif; ?>
              <?php if (puede('reservas', 'editar') && !in_array($r['estado'], ['checkout','cancelada'])): ?>
                <a href="editar.php?id=<?= $r['id'] ?>" class="btn-hlv-outline btn-hlv-sm" title="Editar">✏️</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>