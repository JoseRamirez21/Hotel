<?php
// modulos/habitaciones/index.php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/funciones.php';

requierePermiso('habitaciones', 'ver');

$titulo_pagina = 'Habitaciones';
$modulo_actual = 'habitaciones';

$pdo = conectar();

// Traer todas las habitaciones con su tipo
$habitaciones = $pdo->query("
    SELECT h.id, h.numero, h.piso, h.estado,
           t.nombre AS tipo, t.precio_base
    FROM habitaciones h
    JOIN tipos_habitacion t ON t.id = h.tipo_id
    WHERE h.activo = 1
    ORDER BY h.piso, h.numero
")->fetchAll();

// Agrupar por piso
$pisos = [];
foreach ($habitaciones as $h) {
    $pisos[$h['piso']][] = $h;
}
ksort($pisos);

// Conteo por estado para métricas
$conteo = ['disponible'=>0,'ocupada'=>0,'limpieza'=>0,'mantenimiento'=>0,'bloqueada'=>0];
foreach ($habitaciones as $h) {
    $conteo[$h['estado']] = ($conteo[$h['estado']] ?? 0) + 1;
}
$total      = count($habitaciones);
$ocupacion  = $total > 0 ? round(($conteo['ocupada'] / $total) * 100) : 0;

include '../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">🛏 Habitaciones</h1>
    <p class="page-subtitle">Mapa de disponibilidad en tiempo real</p>
  </div>
  <div class="d-flex gap-2">
    <?php if (puede('habitaciones', 'crear')): ?>
      <a href="nueva.php" class="btn-hlv">+ Nueva habitación</a>
    <?php endif; ?>
    <button class="btn-hlv-outline" onclick="location.reload()">↺ Actualizar</button>
  </div>
</div>

<!-- MÉTRICAS RÁPIDAS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3 col-lg-2">
    <div class="metric-card text-center">
      <div class="metric-label">Disponibles</div>
      <div class="metric-value" style="color:#166534"><?= $conteo['disponible'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-lg-2">
    <div class="metric-card text-center">
      <div class="metric-label">Ocupadas</div>
      <div class="metric-value" style="color:#991b1b"><?= $conteo['ocupada'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-lg-2">
    <div class="metric-card text-center">
      <div class="metric-label">Limpieza</div>
      <div class="metric-value" style="color:#1e40af"><?= $conteo['limpieza'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-lg-2">
    <div class="metric-card text-center">
      <div class="metric-label">Mantenimiento</div>
      <div class="metric-value" style="color:#92400e"><?= $conteo['mantenimiento'] ?></div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-4">
    <div class="metric-card">
      <div class="metric-label">Ocupación hoy</div>
      <div class="d-flex align-items-center gap-2 mt-1">
        <div class="metric-value"><?= $ocupacion ?>%</div>
        <div class="flex-grow-1">
          <div style="background:#f1f5f9;border-radius:4px;height:8px">
            <div style="background:#6366f1;width:<?= $ocupacion ?>%;height:8px;border-radius:4px;transition:width .3s"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- LEYENDA -->
<div class="d-flex flex-wrap gap-3 mb-3" style="font-size:.8rem">
  <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#4ade80;margin-right:4px"></span>Disponible</span>
  <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#f87171;margin-right:4px"></span>Ocupada</span>
  <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#60a5fa;margin-right:4px"></span>Limpieza</span>
  <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#fbbf24;margin-right:4px"></span>Mantenimiento</span>
  <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#cbd5e1;margin-right:4px"></span>Bloqueada</span>
</div>

<!-- MAPA POR PISOS -->
<?php foreach ($pisos as $piso => $habs): ?>
<div class="hlv-card mb-3">
  <div class="hlv-card-header">
    <h6 class="mb-0 fw-semibold" style="color:#475569">
      Piso <?= $piso ?> <span class="text-muted fw-normal" style="font-size:.8rem">(<?= count($habs) ?> habitaciones)</span>
    </h6>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <?php foreach ($habs as $h):
      $cls = 'hab-' . $h['estado'];
      $ico = match($h['estado']) {
        'disponible'    => '🟢',
        'ocupada'       => '🔴',
        'limpieza'      => '🧹',
        'mantenimiento' => '🔧',
        default         => '⬜',
      };
    ?>
    <div class="hab-card <?= $cls ?>"
         onclick="verHabitacion(<?= $h['id'] ?>, '<?= $h['numero'] ?>', '<?= $h['estado'] ?>')"
         title="<?= htmlspecialchars($h['tipo']) ?> — S/ <?= number_format($h['precio_base'],2) ?>">
      <div class="hab-ico"><?= $ico ?></div>
      <div class="hab-num"><?= htmlspecialchars($h['numero']) ?></div>
      <div class="hab-tipo"><?= htmlspecialchars($h['tipo']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<!-- MODAL: Acción rápida sobre habitación -->
<div class="modal fade" id="modalHab" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="border-radius:12px;border:1px solid #e2e8f0">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-semibold" id="modalHabTitulo">Habitación</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted mb-3" id="modalHabEstado" style="font-size:.85rem"></p>
        <div class="d-grid gap-2" id="modalHabAcciones"></div>
      </div>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
function verHabitacion(id, numero, estado) {
  document.getElementById('modalHabTitulo').textContent = 'Habitación ' + numero;
  document.getElementById('modalHabEstado').textContent  = 'Estado: ' + estado;

  const acciones = document.getElementById('modalHabAcciones');
  acciones.innerHTML = '';

  const base = '<?= BASE_URL ?>';

  if (estado === 'disponible') {
    acciones.innerHTML += `<a href="${base}modulos/reservas/nueva.php?hab=${id}" class="btn-hlv w-100 justify-content-center">+ Nueva reserva</a>`;
  }
  if (estado === 'ocupada') {
    acciones.innerHTML += `<a href="${base}modulos/reservas/checkout.php?hab=${id}" class="btn-hlv w-100 justify-content-center">Check-out</a>`;
  }
  acciones.innerHTML += `<a href="${base}modulos/habitaciones/editar.php?id=${id}" class="btn-hlv-outline w-100 justify-content-center mt-1">Ver / Editar</a>`;

  <?php if (puede('habitaciones', 'editar')): ?>
  acciones.innerHTML += `
    <select id="nuevoEstado" class="hlv-input mt-2">
      <option value="">Cambiar estado...</option>
      <option value="disponible">Disponible</option>
      <option value="limpieza">En limpieza</option>
      <option value="mantenimiento">Mantenimiento</option>
      <option value="bloqueada">Bloqueada</option>
    </select>
    <button onclick="cambiarEstado(${id})" class="btn-hlv w-100 justify-content-center mt-1">Confirmar cambio</button>
  `;
  <?php endif; ?>

  new bootstrap.Modal(document.getElementById('modalHab')).show();
}

function cambiarEstado(id) {
  const estado = document.getElementById('nuevoEstado').value;
  if (!estado) return alert('Selecciona un estado.');
  fetch('<?= BASE_URL ?>api/habitaciones.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ accion: 'cambiar_estado', id, estado })
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) location.reload();
    else alert('Error al cambiar estado.');
  });
}
</script>