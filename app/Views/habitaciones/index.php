<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between mb-3">

        <h2>Habitaciones</h2>

        <button class="btn btn-primary">

            Nueva Habitación

        </button>

    </div>

    <table class="table table-bordered table-hover">

        <thead class="table-dark">

            <tr>

                <th>Número</th>

                <th>Piso</th>

                <th>Tipo</th>

                <th>Capacidad</th>

                <th>Precio</th>

                <th>Estado</th>

                <th>Acciones</th>

            </tr>

        </thead>

        <tbody>

        <?php foreach($habitaciones as $h): ?>

            <tr>

                <td><?= $h['numero'] ?></td>

                <td><?= $h['piso'] ?></td>

                <td><?= $h['tipo'] ?></td>

                <td><?= $h['capacidad'] ?></td>

                <td>S/. <?= number_format($h['precio'],2) ?></td>

                <td><?= $h['estado'] ?></td>

                <td>

                    <button class="btn btn-warning btn-sm">

                        Editar

                    </button>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>