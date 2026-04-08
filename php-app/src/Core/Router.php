<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, array $handler): void
    {
        $parameterNames = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $matches) use (&$parameterNames) {
            $parameterNames[] = $matches[1];
            return '([a-zA-Z0-9-]+)';
        }, $pattern);

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => '#^' . rtrim((string) $regex, '/') . '/?$#',
            'parameterNames' => $parameterNames,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $basePath = (string) Config::get('services.app.base_path', '');

        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        $normalizedPath = $path === '/' ? '/' : rtrim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            if (!preg_match($route['regex'], $normalizedPath, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = array_combine($route['parameterNames'], $matches) ?: [];

            [$controllerClass, $action] = $route['handler'];
            $controller = new $controllerClass();
            $controller->{$action}($params);
            return;
        }

        http_response_code(404);
        echo 'Page not found.';
    }
}
