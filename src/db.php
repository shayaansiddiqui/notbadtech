<?php

function getDbConnection(): mysqli {
    // Local database for testing
    $host = getenv('MYSQL_HOST') ?: 'localhost';
    $port = getenv('MYSQL_PORT') ?: 3306;
    $database = getenv('MYSQL_DATABASE') ?: 'notbadtech_local';
    $user = getenv('MYSQL_USER') ?: 'root';
    $password = getenv('MYSQL_PASSWORD') ?: ''; // XAMPP default: empty

    // IONOS database (uncomment for production)
    
    $host = getenv('MYSQL_HOST') ?: 'db5017780327.hosting-data.io';
    $port = getenv('MYSQL_PORT') ?: 3306;
    $database = getenv('MYSQL_DATABASE') ?: 'dbs14193046';
    $user = getenv('MYSQL_USER') ?: 'dbu3008839';
    $password = getenv('MYSQL_PASSWORD') ?: 'unj3fvh4tew@wup4MBM';
    
    $link = new mysqli($host, $user, $password, $database, $port);

    if ($link->connect_error) {
        error_log("MySQL connection failed: " . $link->connect_error);
        die("Failed to connect to MySQL: " . $link->connect_error);
    }

    return $link;
}

?>