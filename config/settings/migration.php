<?php
return [
    'table_storage' => [
        'table_name' => env('DOCTRINE_MIGRATION_TABLE_NAME', 'sys_migrations'),
        'version_column_name' => 'version',
        'version_column_length' => 1024,
        'executed_at_column_name' => 'timestamp',
        'execution_time_column_name' => 'executionDuration',
    ],

    'migrations_paths' => [
        'Tranquillity\Migrations' => APP_BASE_PATH . '/resources/Migrations'
    ],

    'all_or_nothing' => true,
    'check_database_platform' => true,
];