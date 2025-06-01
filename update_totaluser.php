<?php
require_once '../database.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employeeId = $_POST['employeeId'];
    $attendanceId = $_POST['attendanceId'];

    try {
        $db = new Database(); // Replace with your database connection logic
        $conn = $db->getConnection();

        // Update attendance ID
        $stmt = $conn->prepare("UPDATE users SET attendance_id = ? WHERE emp_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $attendanceId, $employeeId);
        $stmt->execute();
        $stmt->close();

        // Get updated total employee count
        $result = $conn->query("SELECT COUNT(*) AS total_employees FROM users");
        $row = $result->fetch_assoc();
        $totalEmployees = $row['total_employees'];

        echo json_encode([
            "success" => true,
            "totalEmployees" => $totalEmployees,
            "message" => "Attendance ID updated successfully!"
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }
}
