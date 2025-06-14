<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['image'])) {
    $imageData = $input['image'];

    $parts = explode(',', $imageData);
    if (count($parts) !== 2) {
        http_response_code(400);
        echo "Invalid image format.";
        exit;
    }

    $decodedImage = base64_decode($parts[1]);

    if ($decodedImage === false) {
        http_response_code(400);
        echo "Base64 decode failed.";
        exit;
    }

    $filename = 'capture_' . uniqid() . '.jpg';
    $filepath = __DIR__ . '/' . $filename;

// ... [previous code remains the same until line 45]

if (file_put_contents($filepath, $decodedImage)) {
    $command = escapeshellcmd("/var/www/html/bcb_berhad/venv/bin/python /var/www/html/bcb_berhad/match_face.py " . escapeshellarg($filename));
    $output = shell_exec($command);

    if ($output === null) {
        echo "❌ Python script did not return any output";
        exit;
    }

    // Handle numeric output (matched case)
    if (is_numeric(trim($output))) {
        $matched_emp_id = trim($output);
        include_once __DIR__ . '/../database.php';
        include_once __DIR__ . '/../attendance.php';
        date_default_timezone_set("Asia/Kuala_Lumpur");

        $action = $input['action'] ?? 'clock_in'; // Define action once here
        
        $db = new Database();
        $attendance = new Attendance($db, $matched_emp_id);

        // Clear and consistent action handling
        $response = "";
        if ($action === "clock_in") {
            $response = $attendance->clockIn();
        } elseif ($action === "clock_out") {
            $response = $attendance->clockOut();
        } else {
            $response = "Invalid action specified.";
        }
        
        echo $response;
        $db->close();

    } 
    else {
        $result = json_decode($output, true);
        if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
            echo "❌ Invalid response from face matching system";
        } elseif (isset($result['status']) && $result['status'] === 'no_match') {
            echo "❌ NO MATCH";
        } else {
            echo "❌ Error: " . ($result['message'] ?? 'Unknown error');
        }
    }

    @unlink($filepath); // Clean up temp file
} else {
    http_response_code(500);
    echo "❌ Failed to save image.";
}
} else {
    http_response_code(400);
    echo "No image data received.";
}
?>