<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, callable $callback): void {
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, callable $callback): void {
        $this->routes['POST'][$path] = $callback;
    }

    public function dispatch(string $uri, string $method): void {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $callback = $this->routes[$method][$path] ?? null;
        
        if (!$callback) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1>";
            return;
        }

        echo call_user_func($callback);
    }
    public function redirect(string $path)
    {
        header("Location: $path");
        exit;
    }
}
