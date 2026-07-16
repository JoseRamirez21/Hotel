<?php

$router->get('/', 'DashboardController@index');

$router->get('/prueba', function () {
    echo "FUNCIONA";
});