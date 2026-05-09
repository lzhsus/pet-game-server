<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            Response::error('route not found', 404, 404);
        }

        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass)) {
            Response::error('controller not found', 500, 500);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            Response::error('action not found', 500, 500);
        }

        $controller->$action();
    }
}
