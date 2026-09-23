<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;charset=utf8mb4',
    $config['host'],
    $config['port']
);

echo "Aguardando MySQL em {$config['host']}:{$config['port']}...\n";

for ($i = 0; $i < 30; $i++) {
    try {
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->query('SELECT 1');
        echo "MySQL pronto.\n";
        break;
    } catch (Throwable $e) {
        if ($i === 29) {
            fwrite(STDERR, "MySQL não ficou pronto: {$e->getMessage()}\n");
            exit(1);
        }
        sleep(2);
    }
}

passthru('composer install --no-interaction --no-progress', $code);
if ($code !== 0) {
    exit($code);
}

passthru('php vendor/bin/phinx migrate', $code);
if ($code !== 0) {
    exit($code);
}

$dsnDb = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $config['host'],
    $config['port'],
    $config['name']
);

$pdo = new PDO($dsnDb, $config['user'], $config['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$count = (int) $pdo->query('SELECT COUNT(*) FROM transportadoras')->fetchColumn();
if ($count === 0) {
    echo "Rodando seeds...\n";
    passthru('php vendor/bin/phinx seed:run', $code);
    if ($code !== 0) {
        exit($code);
    }
} else {
    echo "Banco já populado, seeds ignorados.\n";
}

echo "API em http://0.0.0.0:8000\n";
passthru('php -S 0.0.0.0:8000 public/index.php');
