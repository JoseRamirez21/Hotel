<?php

use App\Core\App;

$base = App::url();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= App::company(); ?></title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

<!-- CSS -->
<link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <?php require dirname(__DIR__) . '/components/navbar.php'; ?>

    <?php require dirname(__DIR__) . '/components/sidebar.php'; ?>

    <main id="app-content">

    <?= $content ?>

</main>

    <?php require dirname(__DIR__) . '/components/footer.php'; ?>

    <!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- JS -->
<script src="<?= $base ?>/assets/js/app.js"></script>
</body>

</html>