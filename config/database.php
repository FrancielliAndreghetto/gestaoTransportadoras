<?php

$envFile = dirname(__DIR__) . '/.env';
$file    = is_file($envFile) ? (parse_ini_file($envFile) ?: []) : [];

$env = static function (string $key, string $default) use ($file): string {
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    return (string) ($file[$key] ?? $default);
};

return [
    'host' => $env('DB_HOST', 'localhost'),
    'port' => $env('DB_PORT', '3306'),
    'name' => $env('DB_NAME', 'brudam_test'),
    'user' => $env('DB_USER', 'root'),
    'pass' => $env('DB_PASS', ''),
];
