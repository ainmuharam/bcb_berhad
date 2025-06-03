<?php
ob_start();  // Prevent output before headers
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/../database.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];

    if (empty($id) || empty($status)) {
        echo json_encode(["success" => false, "message" => "Missing ID or status."]);
        exit;
    }

    $database = new Database();
    $conn = $database->conn;

    // Get the original data first
    $stmt = $conn->prepare("SELECT emp_id, time, date FROM manual_login WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $loginData = $result->fetch_assoc();

    if (!$loginData) {
        echo json_encode(["success" => false, "message" => "Record not found."]);
        exit;
    }

    // Update status in manual_login table
    $stmt = $conn->prepare("UPDATE manual_login SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    $clock = $_POST['clock'] ?? '';

    if ($clock !== '' && ($clock === "clockIn" || $clock === "clockOut")) {
        $stmtClock = $conn->prepare("UPDATE manual_login SET clock = ? WHERE id = ?");
        $stmtClock->bind_param("si", $clock, $id);
        $stmtClock->execute();
    }


    if ($status === "Approved") {
        if ($clock === "clockIn" || $clock === "clockOut") {
            $action = $clock === "clockIn" ? "clock_in" : "clock_out";

            $stmtInsert = $conn->prepare("
                INSERT INTO face_recognition (emp_id, action, time, attendance_date)
                VALUES (?, ?, ?, ?)
            ");
            $stmtInsert->bind_param(
                "ssss",
                $loginData['emp_id'],
                $action,
                $loginData['time'],
                $loginData['date']
            );
            $stmtInsert->execute();
        } else {
            echo json_encode(["success" => false, "message" => "Clock type not selected."]);
            exit;
        }
    }

echo json_encode(["success" => true, "message" => "Status updated successfully."]);

}
?>
