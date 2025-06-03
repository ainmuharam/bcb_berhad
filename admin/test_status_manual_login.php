<?php
// For testing only: Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simulate database connection success (replace with actual connection in real use)
class FakeStmt {
    public $error = "";
    public function bind_param() {}
    public function execute() { return true; }
    public function get_result() { 
        return new class {
            public function fetch_assoc() {
                return ['emp_id' => 'EMP001', 'time' => '08:00:00', 'date' => '2025-06-03'];
            }
        };
    }
}
class FakeDB {
    public $conn;
    public function __construct() { $this->conn = $this; }
    public function prepare($query) {
        return new FakeStmt();
    }
}

// Your test logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;
    $clock = $_POST['clock'] ?? null;

    if (empty($id) || empty($status)) {
        echo json_encode(["success" => false, "message" => "Missing ID or status."]);
        exit;
    }

    $database = new FakeDB();
    $conn = $database->conn;

    // Simulate fetching manual_login record
    $stmt = $conn->prepare("SELECT emp_id, time, date FROM manual_login WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $loginData = $result->fetch_assoc();

    if (!$loginData) {
        echo json_encode(["success" => false, "message" => "Record not found."]);
        exit;
    }

    // Simulate updating status
    $stmt = $conn->prepare("UPDATE manual_login SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // Simulate updating clock
    $stmtClock = $conn->prepare("UPDATE manual_login SET clock = ? WHERE id = ?");
    $stmtClock->bind_param("si", $clock, $id);
    $stmtClock->execute();

    if ($status === "Approved") {
        if ($clock === "clockIn" || $clock === "clockOut") {
            $action = $clock === "clockIn" ? "clock_in" : "clock_out";

            // Simulate inserting to face_recognition
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
            if (!$stmtInsert->execute()) {
                echo json_encode(["success" => false, "message" => "Insert failed: " . $stmtInsert->error]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "message" => "Clock type not selected."]);
            exit;
        }
    }

    echo json_encode(["success" => true, "message" => "Status updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
