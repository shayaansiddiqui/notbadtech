<?php
ini_set('display_errors', 0); // Disable for production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

function getDbConnection(): mysqli {
    $host = getenv('MYSQL_HOST') ?: 'db5017780327.hosting-data.io';
    $port = getenv('MYSQL_PORT') ?: 3306;
    $database = getenv('MYSQL_DATABASE') ?: 'dbs14193046';
    $user = getenv('MYSQL_USER') ?: 'dbu3008839';
    $password = getenv('MYSQL_PASSWORD') ?: 'unj3fvh4tew@wup4MBM';

    $link = new mysqli($host, $user, $password, $database, $port);

    if ($link->connect_error) {
        error_log("MySQL connection failed: " . $link->connect_error . " (Host: $host, Database: $database, User: $user)");
        die("Failed to connect to MySQL. Please check server logs.");
    }

    return $link;
}
?>