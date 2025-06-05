<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $targetPath = __DIR__ . '/uploads/snapshot.png';
    if (!file_exists(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0777, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        // Call Python script and pass the saved image path
        $command = escapeshellcmd("python3 /var/www/ain/testCamera.py " . escapeshellarg($targetPath));
        $output = shell_exec($command);

        echo trim($output); // Output Python script response
    } else {
        echo "Failed to save uploaded image.";
    }
} else {
    echo "No image received.";
}
?>
