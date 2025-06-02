<?php
include_once __DIR__ . '/../database.php';
require_once 'add_public_holiday.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['holiday_date']) && !empty($_POST['holiday_name'])) {
        $database = new Database();
        $db = $database->conn;

        $holiday = new PublicHoliday($db);
        $holiday->holiday_date = $_POST['holiday_date'];
        $holiday->holiday_name = $_POST['holiday_name'];

        if ($holiday->create()) {
            echo json_encode(["status" => "success", "holiday_date" => $_POST['holiday_date'], "holiday_name" => $_POST['holiday_name']]);
        } else {
            echo json_encode(["status" => "failure", "message" => "Unable to add holiday."]);
        }
    } else {
        echo json_encode(["status" => "failure", "message" => "Please fill all fields."]);
    }
}
?>
