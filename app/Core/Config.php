<?php

namespace App\Core;

class Config
{
    private static array $config = [];

    /**
     * Cargar todos los archivos de configuración.
     */
    public static function load(): void
    {
        $path = dirname(__DIR__, 2) . '/config';

        foreach (glob($path . '/*.php') as $file) {

            $name = basename($file, '.php');

            self::$config[$name] = require $file;
        }
    }

    /**
     * Obtener un valor de configuración.
     * Ejemplo:
     * config('app.name')
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);

        $config = self::$config;

        foreach ($keys as $value) {

            if (!isset($config[$value])) {
                return $default;
            }

            $config = $config[$value];
        }

        return $config;
    }
}