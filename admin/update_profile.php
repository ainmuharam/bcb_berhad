<?php
session_start();

if (!isset($_SESSION['emp_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');  // Adjust path to your login file
    exit();
}
include 'register_handling.php'; 
include 'depart_handling.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new Database(); 
$user = new User($db); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $emp_id = isset($_POST['emp_id']) ? intval($_POST['emp_id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $department = isset($_POST['department']) ? intval($_POST['department']) : 0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Validate input
    if (empty($name) || empty($email) || $department <= 0) {
        die("Invalid input.");
    }

    // Check if the email already exists in the database
    $emailCheckStmt = $db->conn->prepare("SELECT emp_id FROM users WHERE email = ? AND emp_id != ?");
    if ($emailCheckStmt === false) {
        die("Prepare failed: " . $db->conn->error);
    }

    $emailCheckStmt->bind_param("si", $email, $emp_id);
    $emailCheckStmt->execute();
    $emailCheckStmt->bind_result($existingEmpId);
    $emailCheckStmt->fetch();
    $emailCheckStmt->close();

    if ($existingEmpId) {
        header("Location: edit_profile.php?id=$emp_id&message=Error: The email address is already in use by another employee.");
        exit();
    }

    $stmt = $db->conn->prepare("UPDATE users SET name = ?, department_id = ?, email = ? WHERE emp_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $db->conn->error);
    }

    $stmt->bind_param("sisi", $name, $department, $email, $emp_id);
    $result = $stmt->execute();
    $stmt->close();

}
?>