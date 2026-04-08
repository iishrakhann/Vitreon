<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $data['currentUser'] = $_SESSION['user'] ?? null;
        $data['appBasePath'] = $this->path();
        $data['appBaseUrl'] = Config::get('services.app.base_url', '');
        $data['currentPath'] = $this->currentPath();
        $data['url'] = fn (string $path = ''): string => $this->path($path);
        extract($data, EXTR_SKIP);
        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';
        require dirname(__DIR__) . '/Views/layout.php';
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function redirect(string $path): void
    {
        $basePath = rtrim((string) Config::get('services.app.base_path', ''), '/');
        $target = $path;

        if (preg_match('#^https?://#i', $path) !== 1) {
            $isAppAbsolutePath = $basePath !== '' && str_starts_with($path, $basePath . '/');
            $target = $isAppAbsolutePath ? $path : $this->path($path);
        }

        header('Location: ' . $target);
        exit;
    }

    protected function path(string $path = ''): string
    {
        $basePath = rtrim((string) Config::get('services.app.base_path', ''), '/');

        if ($path === '' || $path === '/') {
            return $basePath !== '' ? $basePath . '/' : '/';
        }

        return ($basePath !== '' ? $basePath : '') . '/' . ltrim($path, '/');
    }

    protected function currentUser(): ?array
    {
        $user = $_SESSION['user'] ?? null;
        return is_array($user) ? $user : null;
    }

    protected function currentPath(): string
    {
        $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        $basePath = rtrim((string) Config::get('services.app.base_path', ''), '/');

        if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
            $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
        }

        $normalized = '/' . ltrim($requestPath, '/');
        return $normalized !== '/' ? rtrim($normalized, '/') : '/';
    }

    protected function requireAuth(): array
    {
        $user = $this->currentUser();
        if ($user === null || empty($user['id'])) {
            $_SESSION['post_login_redirect'] = $_SERVER['REQUEST_URI'] ?? $this->path('/');
            $this->redirect('/login');
        }

        return $user;
    }
}
