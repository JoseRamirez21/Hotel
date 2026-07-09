<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold">Dashboard</h2>
            <p class="text-muted mb-0">
                Bienvenido al Sistema Hotel Las Vegas.
            </p>
        </div>

        <div class="text-end">
            <small class="text-muted">
                <?= date('d/m/Y') ?>
            </small>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-lg-3 col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">Habitaciones</h6>

                    <h2 class="fw-bold">24</h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">Ocupadas</h6>

                    <h2 class="fw-bold text-danger">18</h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">Disponibles</h6>

                    <h2 class="fw-bold text-success">6</h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h6 class="text-muted">Caja del Día</h6>

                    <h2 class="fw-bold text-primary">
                        S/. 2,850
                    </h2>

                </div>

            </div>

        </div>

    </div>

    <div class="row mt-4">

        <div class="col-lg-8">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white fw-bold">

                    Ocupación del Hotel

                </div>

                <div class="card-body">

                    <canvas id="graficoOcupacion"></canvas>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white fw-bold">

                    Estado

                </div>

                <div class="card-body">

                    <p>🟢 Disponibles : 6</p>

                    <p>🔴 Ocupadas : 18</p>

                    <p>🟡 Limpieza : 0</p>

                    <p>⚫ Mantenimiento : 0</p>

                </div>

            </div>

        </div>

    </div>

</div>