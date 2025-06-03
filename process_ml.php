<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/database.php';
date_default_timezone_set("Asia/Kuala_Lumpur");

class ManualLogin {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->conn; 
    }

    // Insert login record with BLOB image storage
    public function insertLogin($empID, $imageData) {
        $date = date("Y-m-d");
        $time = date("H:i:s");

        $sql = "INSERT INTO manual_login (emp_id, image, date, time) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $null = null;
        $stmt->bind_param("sbss", $empID, $null, $date, $time);

        // Send the binary image data
        $stmt->send_long_data(1, $imageData);


    if ($stmt->execute()) {
        $adminQuery = "SELECT email FROM users WHERE role_id = 1 AND status = 1"; 
        $adminResult = $this->conn->query($adminQuery);

        if ($adminResult && $adminResult->num_rows > 0) {
            $subject = "Manual Login Request";
            $message = "Employee ID $empID has submitted a manual login request on $date at $time. <br><br>Please review the request.";
            $headers = "From: muharamnurain@gmail.com";

            while ($row = $adminResult->fetch_assoc()) {
                $adminEmail = $row['email'];
                mail($adminEmail, $subject, $message, $headers);
            }
        }

        echo "<script>alert('Submission successful! If you do not receive a notification, kindly contact HR.'); window.location.href='scan_face.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }


        $stmt->close();
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

$manualLogin = new ManualLogin();

// Get employee ID and image data from form submission
$empID = $_POST['employeeID'] ?? null;
$imageData = $_POST['imageData'] ?? null;

if (!$empID || !$imageData) {
    echo "<script>alert('Employee ID and Image are required!'); window.history.back();</script>";
    exit;
}

// ✅ Check if employee exists
$db = new Database();
$conn = $db->conn;
$checkStmt = $conn->prepare("SELECT emp_id FROM users WHERE emp_id = ? AND status = 1");
$checkStmt->bind_param("s", $empID);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    echo "<script>alert('Employee ID does not exist. Please enter a valid ID.'); window.history.back();</script>";
    $checkStmt->close();
    $manualLogin->closeConnection();
    exit;
}
$checkStmt->close();

// Decode Base64 image data
$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = base64_decode($imageData);

// Store the actual BLOB data in the database
$manualLogin->insertLogin($empID, $imageData);

// Close the database connection
$manualLogin->closeConnection();
?>
