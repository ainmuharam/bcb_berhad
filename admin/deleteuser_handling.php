<?php
include_once 'depart_handling.php'; 
require_once 'register_handling.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employeeId'];

    if (!empty($employeeId)) {
        $database = new Database();
        $conn = $database->conn;

        $stmt = $conn->prepare("DELETE FROM users WHERE emp_id = ?");
        $stmt->bind_param("i", $employeeId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
