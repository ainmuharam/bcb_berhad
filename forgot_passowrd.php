<?php
session_start();
include_once 'database.php'; // Include your database connection
include_once 'user .php'; // Include the User class

$db = new Database(); // Create a new Database instance
$user = new User($db); // Create a new User instance

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['forgot-email'];

    // Check if the email is valid
    if ($user->isEmailValid($email)) {
        // Proceed to send OTP
        // Your logic to send OTP goes here
        echo "OTP has been sent to your email.";
    } else {
        echo "Invalid email address.";
    }
}
?>