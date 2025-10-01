<?php
/**
 * config.php
 * Application configuration file containing database credentials and session settings.
 * This file stores all the configuration variables used throughout the application.
 */

declare(strict_types=1);

// Database and session configuration
$CONFIG = [
    'db_host' => 'localhost',        // Database server hostname
    'db_name' => 'library_db',       // Database name
    'db_user' => 'root',             // Database username
    'db_pass' => '',                 // Database password
    'session_name' => 'library_sess', // Custom session name for security
];
