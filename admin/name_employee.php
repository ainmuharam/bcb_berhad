<?php
include_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

$database = new Database();

$sql = "SELECT name FROM users WHERE status = 1"; 
$result = $database->conn->query($sql);

$names = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $names[] = $row['name'];
    }
}

echo json_encode($names);
?>
