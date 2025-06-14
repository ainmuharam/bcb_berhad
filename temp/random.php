<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['image'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No image data received."]);
    exit;
}

$imageData = $input['image'];
$parts = explode(',', $imageData);

if (count($parts) !== 2) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid image format."]);
    exit;
}

$decodedImage = base64_decode($parts[1]);
if ($decodedImage === false) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Base64 decode failed."]);
    exit;
}

$filename = 'capture_' . uniqid() . '.jpg';
$filepath = __DIR__ . '/' . $filename;

if (!file_put_contents($filepath, $decodedImage)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "❌ Failed to save image."]);
    exit;
}

// Run Python face matching
$command = escapeshellcmd("/var/www/html/bcb_berhad/venv/bin/python /var/www/html/bcb_berhad/match_face.py " . escapeshellarg($filename));
$output = shell_exec($command);

if ($output === null) {
    echo json_encode(["status" => "error", "message" => "❌ Python script did not return any output"]);
    exit;
}

// If Python returns plain emp_id string, wrap it into a matched result
if (preg_match('/^\d+$/', trim($output))) {
    $result = [
        "status" => "matched",
        "employee_id" => trim($output),
        "filename" => $filename
    ];
} else {
    $result = json_decode($output, true);
}

if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "status" => "error",
        "message" => "❌ Invalid JSON output from Python script: " . json_last_error_msg(),
        "raw_output" => htmlspecialchars($output)
    ]);
    exit;
}

// === Handle matched case ===
if ($result['status'] === 'matched') {
    include_once __DIR__ . '/../database.php';
    include_once __DIR__ . '/../attendance.php';
    date_default_timezone_set("Asia/Kuala_Lumpur");

    $matched_emp_id = $result['employee_id'];
    $db = new Database();

    $action = $input['action'] ?? null;
    if (!in_array($action, ['clock_in', 'clock_out'])) {
        echo json_encode([
            "status" => "error",
            "employee_id" => $matched_emp_id,
            "message" => "Invalid or missing action.",
            "timestamp" => date("H:i:s")
        ]);
        $db->close();
        exit;
    }

    $attendance = new Attendance($db, $matched_emp_id);
    $message = ($action === "clock_in") ? $attendance->clockIn() : $attendance->clockOut();

    echo json_encode([
        "status" => "matched",
        "employee_id" => $matched_emp_id,
        "filename" => $result['filename'] ?? null,
        "message" => $message,
        "timestamp" => date("H:i:s")
    ]);

    $db->close();
} else {
    echo json_encode([
        "status" => "no_match",
        "message" => "❌ NO MATCH"
    ]);
}
?>
