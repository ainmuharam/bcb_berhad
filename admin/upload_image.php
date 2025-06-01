<?php
// Receive JSON input
$data = json_decode(file_get_contents('php://input'), true);
$image_data = $data['image'];

// Remove the "data:image/jpeg;base64," part from the image data
$image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
$image_data = str_replace(' ', '+', $image_data);
$image_data = base64_decode($image_data);

// Save the image to a file
$filename = 'captured_image.jpg';
file_put_contents($filename, $image_data);

// Call the Python script for further processing
$output = shell_exec("python3 recognize_face.py known_face.jpg $filename");

echo "Recognition Result: " . $output;
?>