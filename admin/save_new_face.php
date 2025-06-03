<?php
include_once __DIR__ . '/../database.php';

$data = json_decode(file_get_contents("php://input"), true);
$employee_id = $data['employee_id'];
$image_data = $data['image_data'];

if (!$employee_id || !$image_data) {
    http_response_code(400);
    echo "Missing employee ID or image data.";
    exit;
}

$image_parts = explode(";base64,", $image_data);
$image_base64 = base64_decode($image_parts[1]);

$folder = 'employee_picture/';
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
}

$filename = $folder . $employee_id . ".png";

try {
    $db = new Database();
    $conn = $db->conn;

    $escaped_filename = $conn->real_escape_string($filename);
    $escaped_employee_id = $conn->real_escape_string($employee_id);

    // Check if the user exists and is active
    $check_sql = "SELECT status FROM users WHERE emp_id = '$escaped_employee_id'";
    $result = $conn->query($check_sql);

    if ($result->num_rows === 0) {
        echo "Error: Employee does not exist.";
        $db->close();
        exit;
    }

    $row = $result->fetch_assoc();
    if ($row['status'] == 0) {
        echo "Error: Employee does not exist.";
        $db->close();
        exit;
    }

    // Save image to folder only after checks
    file_put_contents($filename, $image_base64);

    $sql = "UPDATE users SET profile_picture = '$escaped_filename' WHERE emp_id = '$escaped_employee_id'";

    if ($conn->query($sql) === TRUE) {
        echo "New Face updated successfully.";
    } else {
        echo "Error updating database: " . $conn->error;
    }

    $db->close();

} catch (Exception $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>
