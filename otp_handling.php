<?php
session_start(); // Start the session to store OTP
include 'index.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OTP {
    private $otp; // Store the generated OTP
    private $otpExpiry; // Store the expiry time for the OTP

    public function generateOtp() {
        $this->otp = rand(100000, 999999); // Generate a 6-digit OTP
        $this->otpExpiry = time() + 300; // Set expiry time for 5 minutes
        return $this->otp; // Return the generated OTP
    }

    public function sendOtp($email) {
        // Generate OTP before sending
        $otp = $this->generateOtp();

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;
            $mail->Username = 'muharamnurain@gmail.com';   // SMTP username
            $mail->Password = 'chzxxjooufibcbss';     // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('muharamnurain@gmail.com', 'Password Recovery'); // Use a fixed sender email
            $mail->addAddress($email, 'User  '); // Send OTP to the user's email

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body    = 'Your OTP for password reset is <strong>' . htmlspecialchars($otp) . '</strong>';

            $mail->send();
            echo "OTP has been sent to your email.";

            // Store OTP and expiry in session
            $_SESSION['otp'] = $this->otp;
            $_SESSION['otp_expiry'] = $this->otpExpiry;
        } catch (Exception $e) {
            // Log the error or handle it appropriately
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            echo "An error occurred while sending the OTP. Please try again.";
        }
    }

    public function verifyOtp($enteredOtp) {
        // Check if the OTP is valid and not expired
        if ($this->otp === $enteredOtp && time() < $this->otpExpiry) {
            return true; // OTP is valid
        }
        return false; // OTP is invalid or expired
    }
}
?>
