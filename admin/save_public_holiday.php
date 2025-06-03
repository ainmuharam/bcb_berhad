<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/../database.php';
require_once 'add_public_holiday.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

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
}
?>
