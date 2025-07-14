<?php
/**
 * Database Configuration
 *
 * Configure your MySQL database connection settings here.
 * Update these values to match your existing MySQL database.
 */

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_NAME') ?: 'timeline_db',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: 'password',
    'charset' => 'utf8mb4',
    'port' => getenv('DB_PORT') ?: 3306
];