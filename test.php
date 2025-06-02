<?php
$to = "ain.na308@gmail.com"; // ✅ Replace with your actual email
$subject = "Test Email from PHP Server";
$message = "This is a test email to verify if mail() is working on your server.";
$headers = "From: muharamnurain@gmail.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully to $to";
} else {
    echo " Failed to send email.";
}
?>
