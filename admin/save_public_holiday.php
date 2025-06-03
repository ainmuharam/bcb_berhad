<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Always set JSON header first
header('Content-Type: application/json');

include_once __DIR__ . '/../database.php';
require_once 'add_public_holiday.php';

try {
    // Only handle POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(["status" => "error", "message" => "Only POST method is accepted"]);
        exit;
    }

    // Validate required fields
    if (empty($_POST['holiday_date']) || empty($_POST['holiday_name'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Both date and name are required"]);
        exit;
    }

    $database = new Database();
    $db = $database->conn;

    $holiday = new PublicHoliday($db);
    $holiday->holiday_date = $_POST['holiday_date'];
    $holiday->holiday_name = $_POST['holiday_name'];

    if ($holiday->create()) {
        echo json_encode([
            "status" => "success", 
            "holiday_date" => $_POST['holiday_date'],
            "holiday_name" => $_POST['holiday_name']
        ]);
    } else {
        http_response_code(500); // Server Error
        echo json_encode(["status" => "error", "message" => "Failed to save holiday"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
