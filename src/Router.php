<?php

declare(strict_types=1);

namespace App;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            Response::error("Route not found: {$method} {$path}", 404, 404);
        }

        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            $controller = new $class();
            $controller->$methodName();
            return;
        }

        $handler();
    }
}
