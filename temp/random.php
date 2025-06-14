<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['image'])) {
    $imageData = $input['image'];

    // Extract base64 data from Data URL
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
        // Run Python and capture result
        $command = escapeshellcmd("/var/www/html/bcb_berhad/venv/bin/python /var/www/html/bcb_berhad/match_face.py " . escapeshellarg($filename));
        $output = shell_exec($command);

        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "❌ Python script error or invalid JSON output: $output";
            exit;
        }

        if ($result['status'] === 'matched') {
            echo "✅ MATCHED: " . $result['employee_id'];
        } else {
            echo "❌ NO MATCH";
        }
    } else {
        http_response_code(500);
        echo "❌ Failed to save image.";
    }
} else {
    http_response_code(400);
    echo "No image data received.";
}
?>
