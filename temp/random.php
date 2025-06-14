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

    if (file_put_contents($filepath, $decodedImage)) {
        $command = escapeshellcmd("/var/www/html/bcb_berhad/venv/bin/python /var/www/html/bcb_berhad/match_face.py " . escapeshellarg($filename));
        $output = shell_exec($command);

        if ($output === null) {
            echo "❌ Python script did not return any output";
            exit;
        }

        // First check if output is just an employee ID (matched case)
        if (is_numeric(trim($output))) {
            $matched_emp_id = trim($output);
            include_once __DIR__ . '/../database.php';
            include_once __DIR__ . '/../attendance.php';
            date_default_timezone_set("Asia/Kuala_Lumpur");

            $action = $input['action'] ?? 'clock_in';
            $db = new Database();
            $attendance = new Attendance($db, $matched_emp_id);

            if ($action === "clock_in") {
                echo $attendance->clockIn();
            } elseif ($action === "clock_out") {
                echo $attendance->clockOut();
            } else {
                echo "Invalid action.";
            }

            $db->close();
        } 
        // Otherwise treat as JSON response
        else {
            $result = json_decode($output, true);
            
            if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
                echo "❌ Invalid JSON output from Python script: " . json_last_error_msg();
                echo "\nRaw output: " . htmlspecialchars($output);
                exit;
            }

            if (isset($result['status']) && $result['status'] === 'matched') {
                // This case should theoretically never happen now
                echo "❌ System error: Unexpected JSON match format";
            } else {
                echo "❌ NO MATCH";
            }
        }

        // Clean up the temporary image
        @unlink($filepath);
    } else {
        http_response_code(500);
        echo "❌ Failed to save image.";
    }
} else {
    http_response_code(400);
    echo "No image data received.";
}
?>