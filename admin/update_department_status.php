<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/bcb_berhad/database.php';

// ✅ Create database instance and get connection
$db = new Database();
$conn = $db->conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department_id'])) {
    $departmentId = $_POST['department_id'];

    // Toggle status using NOT operator
    $stmt = $conn->prepare("UPDATE departments SET status = NOT status WHERE department_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $stmt->close();

    // Fetch new status after update
    $stmt = $conn->prepare("SELECT status FROM departments WHERE department_id = ?");
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'new_status' => $row['status']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
