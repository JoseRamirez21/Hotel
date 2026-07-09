<?php

namespace App\Core;

class App
{
    public static function run(): void
    {
        $router = new Router();

        require dirname(__DIR__, 2) . '/routes/web.php';

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $base = parse_url(config('app.url'), PHP_URL_PATH);

        $uri = str_replace($base, '', $uri);

        if ($uri === '') {
            $uri = '/';
        }

        $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
    }

    public static function name(): string
    {
        return config('app.name');
    }

    public static function company(): string
    {
        return config('app.company');
    }

    public static function version(): string
    {
        return config('app.version');
    }

    public static function url(): string
    {
        return rtrim(config('app.url'), '/');
    }
}