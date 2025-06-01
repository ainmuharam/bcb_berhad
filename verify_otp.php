<?php
session_start();
include_once 'database.php';
include_once 'login_handling.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'], $_POST['email'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    $_SESSION['forgot_email'] = $email;

    $db = new Database();
    $login = new Login($db);

    $result = $login->verifyOtp($email, $otp);
    echo json_encode($result);
}
?>
