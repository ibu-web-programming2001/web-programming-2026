<?php
/*
 * Phinx configuration – database MIGRATIONS and SEEDERS.
 *
 *   vendor/bin/phinx migrate                  create/update all tables (runs new migrations only)
 *   vendor/bin/phinx seed:run                 insert sample data
 *   vendor/bin/phinx rollback                 undo the last migration
 *   vendor/bin/phinx create AddPhoneToUsers   new migration file in db/migrations
 *
 * The connection comes from config/config.php – the same settings the DAOs use.
 */
require_once __DIR__ . '/config/config.php';

return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds' => __DIR__ . '/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',   // Phinx remembers which migrations already ran
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => Config::DB_HOST(),
            'port' => Config::DB_PORT(),
            'name' => Config::DB_NAME(),
            'user' => Config::DB_USER(),
            'pass' => Config::DB_PASSWORD(),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]
    ],
    'version_order' => 'creation'
];
