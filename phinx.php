<?php

$config = require __DIR__ . '/config/database.php';

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_log',
        'default_environment'     => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host'    => $config['host'],
            'name'    => $config['name'],
            'user'    => $config['user'],
            'pass'    => $config['pass'],
            'port'    => (int) $config['port'],
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
