<?php
include_once __DIR__ . '/../database.php';
require_once 'add_public_holiday.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Fallback for form-encoded requests (normal form submission)
    if (!$data) {
        $data = $_POST;
    }

    if (!empty($data['holiday_date']) && !empty($data['holiday_name'])) {
        $database = new Database();
        $db = $database->conn;

        $holiday = new PublicHoliday($db);
        $holiday->holiday_date = $data['holiday_date'];
        $holiday->holiday_name = $data['holiday_name'];

        if ($holiday->create()) {
            echo json_encode([
                "status" => "success",
                "holiday_date" => $data['holiday_date'],
                "holiday_name" => $data['holiday_name']
            ]);
        } else {
            echo json_encode(["status" => "failure", "message" => "Unable to add holiday."]);
        }
    } else {
        echo json_encode(["status" => "failure", "message" => "Please fill all fields."]);
    }
    exit;
}
?>
