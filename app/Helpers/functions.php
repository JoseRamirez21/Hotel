<?php

use App\Core\Config;

if (!function_exists('config')) {

    function config(string $key, $default = null)
    {
        return Config::get($key, $default);
    }

}