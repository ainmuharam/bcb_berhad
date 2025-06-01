<?php
// Assuming you have a PDO instance created
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
$user = new User($pdo);

// Example of checking if an email is valid
$email = 'test@example.com'; // Replace with the email you want to check
$emailCheck = $user->isEmailValid($email);

if ($emailCheck['valid']) {
    echo $emailCheck['message']; // Email is valid
    // Proceed with sending OTP or other actions
} else {
    echo $emailCheck['message']; // Email does not exist
}
?>