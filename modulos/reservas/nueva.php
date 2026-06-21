<?php
// modulos/reservas/nueva.php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../includes/funciones.php';

requierePermiso('reservas', 'crear');

$titulo_pagina = 'Nueva Reserva';
$modulo_actual = 'reservas';

$pdo = conectar();

$error  = '';
$exito  = '';
$hab_id = (int)($_GET['hab'] ?? 0);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id   = (int)$_POST['cliente_id'];
    $habitacion_id= (int)$_POST['habitacion_id'];
    $fecha_entrada = $_POST['fecha_entrada'];
    $fecha_salida  = $_POST['fecha_salida'];
    $adultos       = (int)$_POST['adultos'];
    $ninos         = (int)($_POST['ninos'] ?? 0);
    $canal         = $_POST['canal'] ?? 'directa';
    $notas         = trim($_POST['notas'] ?? '');

    // Validaciones básicas
    if (!$cliente_id || !$habitacion_id || !$fecha_entrada || !$fecha_salida) {
        $error = 'Completa todos los campos obligatorios.';
    } elseif ($fecha_salida <= $fecha_entrada) {
        $error = 'La fecha de salida debe ser posterior a la entrada.';
    } else {
        // Calcular noches y precio
        $noches = (int)((strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400);

        $hab = $pdo->prepare("SELECT h.*, t.precio_base, t.precio_fin_semana
                               FROM habitaciones h
                               JOIN tipos_habitacion t ON t.id = h.tipo_id
                               WHERE h.id = ?");
        $hab->execute([$habitacion_id]);
        $habitacion = $hab->fetch();

        if (!$habitacion) {
            $error = 'Habitación no encontrada.';
        } else {
            // Verificar disponibilidad
            $conflict = $pdo->prepare("
                SELECT COUNT(*) FROM reservas
                WHERE habitacion_id = ?
                  AND estado NOT IN ('cancelada','checkout','no_show')
                  AND fecha_entrada < ? AND fecha_salida > ?
            ");
            $conflict->execute([$habitacion_id, $fecha_salida, $fecha_entrada]);

            if ($conflict->fetchColumn() > 0) {
                $error = 'La habitación no está disponible en las fechas seleccionadas.';
            } else {
                // Calcular precio (simple: precio_base × noches)
                $precio_noche = $habitacion['precio_base'];
                $precio_total = $precio_noche * $noches;

                $pdo->prepare("
                    INSERT INTO reservas
                      (cliente_id, habitacion_id, usuario_id, fecha_entrada, fecha_salida,
                       noches, adultos, ninos, precio_noche, precio_total, canal, notas, estado)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'confirmada')
                ")->execute([
                    $cliente_id, $habitacion_id, $_SESSION['usuario_id'],
                    $fecha_entrada, $fecha_salida, $noches,
                    $adultos, $ninos, $precio_noche, $precio_total, $canal, $notas
                ]);

                $nueva_id = $pdo->lastInsertId();
                registrarLog('crear_reserva', 'reservas', "Reserva #$nueva_id");
                header("Location: ver.php?id=$nueva_id&nuevo=1");
                exit;
            }
        }
    }
}

// Cargar habitaciones disponibles
$habitaciones = $pdo->query("
    SELECT h.id, h.numero, h.piso, t.nombre AS tipo, t.precio_base, t.capacidad
    FROM habitaciones h
    JOIN tipos_habitacion t ON t.id = h.tipo_id
    WHERE h.activo = 1 AND h.estado = 'disponible'
    ORDER BY h.piso, h.numero
")->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">📅 Nueva reserva</h1>
    <p class="page-subtitle">Completa los datos para registrar la reserva</p>
  </div>
  <a href="index.php" class="btn-hlv-outline">← Volver</a>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger mb-3" style="border-radius:8px;font-size:.875rem"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="nueva.php" id="form-reserva">
<div class="row g-3">

  <!-- COLUMNA IZQUIERDA: Cliente + Fechas -->
  <div class="col-12 col-lg-7">

    <!-- Búsqueda de cliente -->
    <div class="hlv-card mb-3">
      <h6 class="fw-semibold mb-3" style="color:#374151">👤 Huésped</h6>

      <div class="mb-2">
        <label class="hlv-form-label">Buscar cliente existente</label>
        <div class="d-flex gap-2">
          <input type="text" id="buscar-cliente" class="hlv-input" placeholder="Nombre o DNI/Pasaporte...">
          <button type="button" class="btn-hlv" onclick="buscarCliente()">Buscar</button>
        </div>
        <div id="resultados-cliente" class="mt-2"></div>
      </div>

      <div style="border-top:1px solid #f1f5f9;padding-top:12px;margin-top:4px">
        <a href="../clientes/nuevo.php?retorno=reserva" class="btn-hlv-outline btn-hlv-sm">
          + Registrar nuevo huésped
        </a>
      </div>

      <input type="hidden" name="cliente_id" id="cliente_id" value="<?= (int)($_POST['cliente_id'] ?? 0) ?>">
      <div id="cliente-seleccionado" class="mt-2 p-2 rounded" style="background:#f0fdf4;display:none;font-size:.85rem"></div>
    </div>

    <!-- Fechas y detalles -->
    <div class="hlv-card mb-3">
      <h6 class="fw-semibold mb-3" style="color:#374151">📆 Fechas de estadía</h6>
      <div class="row g-3">
        <div class="col-6">
          <label class="hlv-form-label">Fecha de entrada *</label>
          <input type="date" name="fecha_entrada" id="fecha_entrada" class="hlv-input"
                 min="<?= date('Y-m-d') ?>"
                 value="<?= htmlspecialchars($_POST['fecha_entrada'] ?? date('Y-m-d')) ?>"
                 onchange="calcularTotal()" required>
        </div>
        <div class="col-6">
          <label class="hlv-form-label">Fecha de salida *</label>
          <input type="date" name="fecha_salida" id="fecha_salida" class="hlv-input"
                 min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                 value="<?= htmlspecialchars($_POST['fecha_salida'] ?? date('Y-m-d', strtotime('+1 day'))) ?>"
                 onchange="calcularTotal()" required>
        </div>
        <div class="col-4">
          <label class="hlv-form-label">Adultos *</label>
          <input type="number" name="adultos" class="hlv-input" min="1" max="6"
                 value="<?= (int)($_POST['adultos'] ?? 1) ?>" required>
        </div>
        <div class="col-4">
          <label class="hlv-form-label">Niños</label>
          <input type="number" name="ninos" class="hlv-input" min="0" max="4"
                 value="<?= (int)($_POST['ninos'] ?? 0) ?>">
        </div>
        <div class="col-4">
          <label class="hlv-form-label">Canal</label>
          <select name="canal" class="hlv-input">
            <option value="directa"   <?= ($_POST['canal']??'')=='directa'   ?'selected':'' ?>>Directa</option>
            <option value="telefono"  <?= ($_POST['canal']??'')=='telefono'  ?'selected':'' ?>>Teléfono</option>
            <option value="web"       <?= ($_POST['canal']??'')=='web'       ?'selected':'' ?>>Web</option>
            <option value="agencia"   <?= ($_POST['canal']??'')=='agencia'   ?'selected':'' ?>>Agencia</option>
          </select>
        </div>
        <div class="col-12">
          <label class="hlv-form-label">Notas (opcional)</label>
          <textarea name="notas" class="hlv-input" rows="2" placeholder="Preferencias, peticiones especiales..."><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

  </div>

  <!-- COLUMNA DERECHA: Habitación + Resumen -->
  <div class="col-12 col-lg-5">

    <!-- Selección de habitación -->
    <div class="hlv-card mb-3">
      <h6 class="fw-semibold mb-3" style="color:#374151">🛏 Habitación</h6>
      <select name="habitacion_id" id="habitacion_id" class="hlv-input" onchange="calcularTotal()" required>
        <option value="">— Selecciona una habitación —</option>
        <?php foreach ($habitaciones as $h): ?>
          <option value="<?= $h['id'] ?>"
                  data-precio="<?= $h['precio_base'] ?>"
                  data-capacidad="<?= $h['capacidad'] ?>"
                  <?= ($hab_id === $h['id'] || ($_POST['habitacion_id']??0) == $h['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($h['numero']) ?> — <?= htmlspecialchars($h['tipo']) ?>
            (Piso <?= $h['piso'] ?>) · <?= MONEDA ?> <?= number_format($h['precio_base'],2) ?>/noche
          </option>
        <?php endforeach; ?>
      </select>
      <p class="text-muted mt-2 mb-0" style="font-size:.78rem">Solo muestra habitaciones disponibles en este momento.</p>
    </div>

    <!-- Resumen y total -->
    <div class="hlv-card mb-3" id="resumen-card" style="display:none">
      <h6 class="fw-semibold mb-3" style="color:#374151">💰 Resumen</h6>
      <table style="width:100%;font-size:.875rem">
        <tr>
          <td class="text-muted">Noches</td>
          <td class="text-end fw-500" id="r-noches">—</td>
        </tr>
        <tr>
          <td class="text-muted">Precio por noche</td>
          <td class="text-end fw-500" id="r-precio-noche">—</td>
        </tr>
        <tr style="border-top:1px solid #f1f5f9">
          <td class="pt-2"><strong>Total</strong></td>
          <td class="text-end pt-2" id="r-total" style="font-size:1.1rem;font-weight:700;color:#6366f1">—</td>
        </tr>
      </table>
    </div>

    <!-- Botón guardar -->
    <button type="submit" class="btn-hlv w-100 justify-content-center py-2" style="font-size:1rem">
      Confirmar reserva
    </button>
    <a href="index.php" class="btn-hlv-outline w-100 justify-content-center mt-2">Cancelar</a>
  </div>

</div>
</form>

<?php include '../../includes/footer.php'; ?>

<script>
function calcularTotal() {
  const sel    = document.getElementById('habitacion_id');
  const opcion = sel.options[sel.selectedIndex];
  const fi     = document.getElementById('fecha_entrada').value;
  const ff     = document.getElementById('fecha_salida').value;

  if (!opcion.value || !fi || !ff) return;

  const precio = parseFloat(opcion.dataset.precio) || 0;
  const ms     = new Date(ff) - new Date(fi);
  const noches = Math.round(ms / 86400000);

  if (noches <= 0) return;

  const total = precio * noches;
  document.getElementById('r-noches').textContent      = noches;
  document.getElementById('r-precio-noche').textContent = '<?= MONEDA ?> ' + precio.toFixed(2);
  document.getElementById('r-total').textContent        = '<?= MONEDA ?> ' + total.toFixed(2);
  document.getElementById('resumen-card').style.display = '';
}

function buscarCliente() {
  const q = document.getElementById('buscar-cliente').value.trim();
  if (q.length < 2) return;

  fetch('<?= BASE_URL ?>api/clientes.php?q=' + encodeURIComponent(q))
    .then(r => r.json())
    .then(data => {
      const div = document.getElementById('resultados-cliente');
      if (!data.length) { div.innerHTML = '<p class="text-muted mb-0" style="font-size:.8rem">No encontrado.</p>'; return; }

      div.innerHTML = data.map(c => `
        <div class="p-2 rounded mb-1" style="background:#f8faff;border:1px solid #e2e8f0;cursor:pointer;font-size:.85rem"
             onclick="seleccionarCliente(${c.id}, '${c.nombre} ${c.apellido}', '${c.numero_documento}')">
          <strong>${c.nombre} ${c.apellido}</strong> · ${c.numero_documento}
        </div>
      `).join('');
    });
}

function seleccionarCliente(id, nombre, doc) {
  document.getElementById('cliente_id').value = id;
  document.getElementById('resultados-cliente').innerHTML = '';
  document.getElementById('buscar-cliente').value = nombre;
  const d = document.getElementById('cliente-seleccionado');
  d.style.display = '';
  d.innerHTML = `✅ <strong>${nombre}</strong> · ${doc}`;
}

// Calcular al cargar si hay valores previos
calcularTotal();

// Enter en buscador
document.getElementById('buscar-cliente').addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); buscarCliente(); }
});
</script>