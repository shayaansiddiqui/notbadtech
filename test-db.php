<?php
require_once 'src/db.php';

try {
    $conn = getDbConnection();
    echo "Connection to MySQL server successfully established.";
    $conn->close();
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>