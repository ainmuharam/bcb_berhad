<?php
include_once __DIR__ . '/../database.php';

$database = new Database();
$db = $database->conn;

$input = json_decode(file_get_contents('php://input'), true);

// Check if the necessary data is provided
if (isset($input['holiday_name']) && isset($input['holiday_date'])) {
    $holidayName = $input['holiday_name'];
    $holidayDate = $input['holiday_date'];

    // Prepare SQL query to delete the holiday
    $query = "DELETE FROM public_holiday WHERE holiday_name = ? AND holiday_date = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ss", $holidayName, $holidayDate);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete holiday']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}

$db->close();
?>
