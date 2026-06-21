<?php
// config/config.php

define('HOTEL_NOMBRE',    'Hotel Las Vegas');
define('HOTEL_ESTRELLAS', 3);
define('HOTEL_RUC',       '20123456789');
define('HOTEL_CIUDAD',    'Huanta, Ayacucho');
define('HOTEL_TELEFONO',  '066-312000');
define('HOTEL_EMAIL',     'info@hotellasvegaslas.com');
define('HOTEL_DIRECCION', 'Jr. Lima 123, Huanta');

define('IGV_PORCENTAJE',  0.18);   // 18% IGV Perú
define('MONEDA',          'S/');   // Soles

define('SESSION_NOMBRE',  'hlv_session');
define('SESSION_TIEMPO',  3600 * 8); // 8 horas

define('BASE_URL',  'http://localhost/hotel_las_vegas/');
define('UPLOADS',   __DIR__ . '/../uploads/');

// Colores de estado para habitaciones (UI)
define('ESTADOS_HABITACION', [
    'disponible'   => ['label' => 'Disponible',    'color' => 'success'],
    'ocupada'      => ['label' => 'Ocupada',        'color' => 'danger'],
    'mantenimiento'=> ['label' => 'Mantenimiento',  'color' => 'warning'],
    'limpieza'     => ['label' => 'Limpieza',       'color' => 'info'],
    'bloqueada'    => ['label' => 'Bloqueada',      'color' => 'secondary'],
]);

// Colores de prioridad mantenimiento
define('PRIORIDAD_COLORES', [
    'baja'    => 'secondary',
    'media'   => 'info',
    'alta'    => 'warning',
    'urgente' => 'danger',
]);