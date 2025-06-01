<?php
// Include your database connection file
include '../config/db_connection.php'; // Update path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $emp_id = $_POST['emp_id'];
    $file = $_FILES['profile_image'];

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        die("Only JPG and PNG files are allowed.");
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        die("File size must be less than 2MB.");
    }

    // Ensure upload directory exists
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate a unique file name
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('profile_', true) . '.' . $fileExt;
    $uploadPath = $uploadDir . $fileName;

    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Save to database
        $stmt = $conn->prepare("UPDATE users SET image = ? WHERE emp_id = ?");
        $stmt->bind_param("ss", $fileName, $emp_id);

        if ($stmt->execute()) {
            echo "<script>alert('Image uploaded successfully!'); window.location.href='admin_dashboard.php';</script>";
        } else {
            echo "Database error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to upload image.";
    }
} else {
    echo "Invalid request.";
}
?>
