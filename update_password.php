<?php
session_start();
include_once 'database.php';
include_once 'login_handling.php';

$db = new Database();
$login = new Login($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['forgot_email'])) {
        $_SESSION['reset_error'] = 'Session expired. Please start over.';
        header('Location: forgot_password_form.php'); // Change to your actual forgot password form
        exit;
    }

    $email = $_SESSION['forgot_email']; 
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['reset_error'] = 'Passwords do not match.';
        header('Location: login.php'); // Change this if needed
        exit;
    }

    $result = $login->updatePasswordByEmail($email, $new_password);

    if ($result['status']) {
        // Success: password updated
        unset($_SESSION['forgot_email']);
        $_SESSION['reset_success'] = 'Password updated. Please login.';
        header('Location: login.php');
        exit;
    } else {
        // Failure: something went wrong
        $_SESSION['reset_error'] = $result['message'];
        header('Location: login.php');  // redirect to login.php instead
        exit;
    }
}
?>