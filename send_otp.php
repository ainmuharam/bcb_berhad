<?php
ob_start(); // Start output buffering
session_start();
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once 'database.php';
include_once 'login_handling.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot-email'])) {
    $email = $_POST['forgot-email'];

    $db = new Database();
    $login = new Login($db);

    if (!$login->emailExists($email)) {
        ob_end_clean();
        echo json_encode(['status' => 'not_found']);
        exit;
    }

    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_time'] = time();

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'muharamnurain@gmail.com'; // Your Gmail address
        $mail->Password   = 'chzxxjooufibcbss';         // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('muharamnurain@gmail.com', 'BCB Berhad Password Recovery');
        $mail->addAddress($email, 'User');

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code for Password Reset';
        $mail->Body    = "<p>Your OTP code is: <strong>$otp</strong></p><p>This code will expire in 5 minutes.</p>";

        $mail->send();

        ob_end_clean(); // Clear any previous output
        echo json_encode(['status' => 'sent']);
    } catch (Exception $e) {
        ob_end_clean(); // Clear any output
        echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
    }

    exit;
}
?>
