<?php
include_once __DIR__ . '/../database.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emp_id'])) {
    $emp_id = $_POST['emp_id'];

    $database = new Database();
    $conn = $database->conn;

    // Get current status
    $stmt = $conn->prepare("SELECT status FROM users WHERE emp_id = ?");
    $stmt->bind_param('i', $emp_id);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    $new_status = ($current_status == 1) ? 0 : 1;
    $deactivationDate = ($new_status == 0) ? date('Y-m-d') : null;

    $stmt = $conn->prepare("UPDATE users SET status = ?, deactivation_date = ? WHERE emp_id = ?");
    $stmt->bind_param("isi", $new_status, $deactivationDate, $emp_id);
    $success = $stmt->execute();
    $stmt->close();

    echo json_encode([
        'success' => $success,
        'new_status' => $new_status,
        'deactivation_date' => $deactivationDate
    ]);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}
?>
