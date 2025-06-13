<?php
include_once __DIR__ . '/../database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];

    $database = new Database();
    $conn = $database->conn;

    // Get emp_id and date from manual_login
    $stmt = $conn->prepare("SELECT emp_id, date FROM manual_login WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $loginData = $result->fetch_assoc();

    if (!$loginData) {
        echo json_encode(["hasClockIn" => false]);
        exit;
    }

    $empId = $loginData['emp_id'];
    $loginDate = $loginData['date'];

    $stmt2 = $conn->prepare("SELECT 1 FROM face_recognition WHERE emp_id = ? AND action = 'clock_in' AND attendance_date = ?");
    $stmt2->bind_param("ss", $empId, $loginDate);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($result2->num_rows > 0) {
        echo json_encode(["hasClockIn" => true]);
    } else {
        echo json_encode(["hasClockIn" => false]);
    }
}
?>
