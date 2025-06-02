<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'database.php';

try {
    $db = new Database();
    echo "Database connected successfully.";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
