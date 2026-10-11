<?php

declare(strict_types=1);

// Let the PHP development server serve existing static assets directly, then
// route application URLs through CodeIgniter's front controller.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$staticPath = realpath(__DIR__ . (is_string($requestPath) ? $requestPath : '/'));
$publicRoot = realpath(__DIR__);

if ($staticPath !== false && $publicRoot !== false
    && str_starts_with($staticPath, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($staticPath)) {
    return false;
}

require __DIR__ . '/index.php';
