<?php
include_once __DIR__ . '/../database.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Missing ID');
}

$employeeId = $_GET['id'];
$database = new Database();
$conn = $database->conn;

$stmt = $conn->prepare("SELECT image FROM users WHERE emp_id = ?");
$stmt->bind_param("s", $employeeId);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($imageData);

if ($stmt->num_rows > 0) {
    $stmt->fetch();

    if ($imageData !== null) {
        header("Content-Type: image/jpeg");
        echo $imageData;
    } else {
        // Redirect to default profile image if image is null
        header("Location: images/profile.png");
        exit();
    }
} else {
    http_response_code(404);
    exit("Image not found");
}

$stmt->close();
?>
