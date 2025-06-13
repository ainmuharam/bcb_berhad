<?php
$conn = new mysqli("localhost", "root", "Nurainmuharam02@", "bcb_berhad");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to MySQL database.";
?>
