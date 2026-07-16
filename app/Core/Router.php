<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, $action): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function post(string $uri, $action): void
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    public function dispatch(string $uri, string $method): void
    {
        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            exit;
        }

        $action = $this->routes[$method][$uri];

        // Formato moderno:
        // [Controlador::class, 'metodo']
        if (is_array($action)) {

            [$controller, $function] = $action;

            if (!class_exists($controller)) {
                die("Controlador no encontrado: {$controller}");
            }

            $controller = new $controller();

            if (!method_exists($controller, $function)) {
                die("Método no encontrado: {$function}");
            }

            call_user_func([$controller, $function]);
            return;
        }

        // Formato antiguo:
        // 'DashboardController@index'
        if (is_string($action)) {

            [$controller, $function] = explode('@', $action);

            $controller = "App\\Controllers\\{$controller}";

            if (!class_exists($controller)) {
                die("Controlador no encontrado: {$controller}");
            }

            $controller = new $controller();

            if (!method_exists($controller, $function)) {
                die("Método no encontrado: {$function}");
            }

            call_user_func([$controller, $function]);
            return;
        }

        die('Ruta inválida.');
    }

    private function normalize(string $uri): string
    {
        $uri = '/' . trim($uri, '/');

        return $uri === '//' ? '/' : $uri;
    }
}