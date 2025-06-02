<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'muharamnurain@gmail.com';  // Your Gmail
    $mail->Password   = 'chzxxjooufibcbss';         // Your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('muharamnurain@gmail.com', 'Mailer Test');
    $mail->addAddress('ain.na308@gmail.com', 'Ain'); // Change to your test email

    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test Email';
    $mail->Body    = '<b>This is a test email sent using PHPMailer via Gmail SMTP.</b>';

    $mail->send();
    echo '✅ SMTP email sent successfully.';
} catch (Exception $e) {
    echo "Message could not be sent. Error: {$mail->ErrorInfo}";
}
?>
