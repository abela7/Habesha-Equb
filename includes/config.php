<?php
/**
 * HabeshaEqub Secure Configuration File
 * Contains sensitive configuration data
 * This file should NOT be accessible via web browser
 */

// Prevent direct access
if (!defined('CONFIG_LOADED')) {
    die('Direct access not allowed');
}

/**
 * Database Configuration
 * Move these to environment variables in production
 */
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'habeshjv_habeshaequb',
        'username' => 'habeshjv_abel',
        'password' => '2121@Habesha',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    ],
    
    'security' => [
        'session_timeout' => 3600, // 1 hour for users
        'admin_session_timeout' => 28800, // 8 hours for admins
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'csrf_token_lifetime' => 3600,
        'password_min_length' => 12,
        'require_strong_passwords' => true
    ],
    
    'app' => [
        'debug_mode' => false, // NEVER set to true in production
        'log_security_events' => true,
        'environment' => 'production' // production, staging, development
    ]
];
?>