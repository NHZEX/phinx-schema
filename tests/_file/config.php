<?php

return [
    'paths' => [
        'migrations' => [
            'Zxin\Tests\Migrations' => __DIR__ . '/schema_definition',
        ],
        'seeds' => __DIR__ . '/empty_seed',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter'      => 'mysql',
            'host'         => getenv('TESTS_DB_MYSQL_HOST'),
            'name'         => getenv('TESTS_DB_MYSQL_DATABASE'),
            'user'         => getenv('TESTS_DB_MYSQL_USERNAME'),
            'pass'         => getenv('TESTS_DB_MYSQL_PASSWORD'),
            'port'         => getenv('TESTS_DB_MYSQL_PORT'),
        ],
    ],
    'version_order' => 'creation',
];