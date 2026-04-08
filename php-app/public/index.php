<?php

declare(strict_types=1);

session_start();

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

App\Core\Env::load(dirname(__DIR__) . '/.env');

App\Core\Config::set('services', require dirname(__DIR__) . '/config/app.php');

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = preg_replace('#/index\.php$#', '', $scriptName) ?: '';
$basePath = $basePath === '' ? '' : rtrim($basePath, '/');
$services = App\Core\Config::get('services', []);
$services['app']['base_path'] = $basePath;

if (empty($services['app']['base_url'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $services['app']['base_url'] = $scheme . '://' . $host . ($basePath !== '' ? $basePath : '');
}

App\Core\Config::set('services', $services);

$router = new App\Core\Router();

foreach (require dirname(__DIR__) . '/config/routes.php' as [$method, $pattern, $handler]) {
    $router->add($method, $pattern, $handler);
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
