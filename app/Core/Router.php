<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get($uri, $action)
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch($uri, $method)
    {
        if (!isset($this->routes[$method][$uri])) {
            require_once dirname(__DIR__) . '/Views/errors/404.php';
            exit;
        }

        [$controller, $function] = explode('@', $this->routes[$method][$uri]);

        $controller = "App\\Controllers\\{$controller}";

        $controller = new $controller();

        call_user_func([$controller, $function]);
    }
}