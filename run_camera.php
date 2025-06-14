<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['image']) || !isset($data['action'])) {
        http_response_code(400);
        echo "Invalid input";
        exit;
    }

    $imageData = $data['image'];
    $action = escapeshellarg($data['action']);

    // Extract base64 image data
    if (preg_match('/^data:image\/\w+;base64,/', $imageData)) {
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
    }
    $imageData = base64_decode($imageData);

    if (!$imageData) {
        echo "Error decoding image";
        exit;
    }

    // Save to a temporary file
    $tempImagePath = "/var/www/html/bcb_berhad/captured_image.jpg";
    file_put_contents($tempImagePath, $imageData);

    // Run the Python script with temp image
    $command = "/var/www/myenv/bin/python3 /var/www/html/bcb_berhad/match_face.py " . escapeshellarg($tempImagePath) . " " . $action;
    exec($command, $output, $return_var);

    // Remove temp image
    unlink($tempImagePath);


}
?>
