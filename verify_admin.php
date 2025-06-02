<?php
session_start();
include_once __DIR__ . '/database.php';

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['pending_admin_session']['email'])) {
    $error = "Session expired or missing email. Please log in again.";
    header("Location: login.php");
    exit();
}

$adminEmail = $_SESSION['pending_admin_session']['email'];

if (isset($_POST['send_otp'])) {
    $otp = rand(100000, 999999);
    $_SESSION['admin_otp'] = $otp;

    $subject = "OTP Code for Verification";
    $message = "Dear Admin,\n\nYour OTP code is: $otp\n\nDo not share your OTP code.";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'muharamnurain@gmail.com';  // your Gmail address
        $mail->Password   = 'chzxxjooufibcbss';  // your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('muharamnurain@gmail.com', 'BCB Admin');
        $mail->addAddress($adminEmail);

        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        $success = "OTP has been sent to your email.";
    } catch (Exception $e) {
        $error = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
    }
}

if (isset($_POST['verify_otp'])) {
    $enteredOtp = $_POST['otp'] ?? '';

    if (isset($_SESSION['admin_otp']) && $enteredOtp == $_SESSION['admin_otp']) {
        $_SESSION['admin_session'] = $_SESSION['pending_admin_session'];
        unset($_SESSION['pending_admin_session'], $_SESSION['admin_otp']);

        header("Location: admin/admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Login</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
        .modal {
            display: none;
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body class="login">
    <div class="bg-wrapper">
        <img src="images/house.jpg" alt="Background logo" class="bg-image">
    </div>
<h2 class="font-heading">Login</h2>
<div class="login-container">
    <img src="images/bcblogo.png" alt="BCB logo" class="logo">

    <?php if (!empty($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div id="countdown-message" style="color: red; font-weight: bold;"></div>

<form method="POST" action="verify_admin.php" onsubmit="return validateOtp(event)">
    <div class="form-group">
        <label for="otp">Enter OTP Number:</label>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="number" id="otp" name="otp" placeholder="OTP Number" min="100000" max="999999" oninput="this.value = this.value.slice(0, 6)" disabled>
            <button type="submit" name="send_otp" onclick="startCountdown(event)">Send OTP</button>
        </div>
    </div>
        <div id="countdown" style="margin-top: 10px; font-weight: bold;"></div>
    <button type="submit" name="verify_otp" id="verify-btn" disabled>Verify OTP</button>
</form>

<script>
let countdown;
let timeLeft = 60; // 60 seconds

function startCountdown(event) {
    event.preventDefault(); // prevent form from submitting

    // Enable OTP input
    document.getElementById('otp').disabled = false;

    // Submit the form via JavaScript
    const form = event.target.closest("form");
    const formData = new FormData(form);
    formData.append('send_otp', '1');

    fetch("verify_admin.php", {
        method: "POST",
        body: formData
    }).then(response => response.text())
      .then(data => {
        console.log("OTP Sent.");
        document.getElementById('verify-btn').disabled = false;
        runCountdown();
    });

    return false;
}

function runCountdown() {
    timeLeft = 60;
    document.getElementById('countdown').textContent = `OTP valid in ${timeLeft} seconds`;

    countdown = setInterval(() => {
        timeLeft--;
        if (timeLeft > 0) {
            document.getElementById('countdown').textContent = `OTP valid in ${timeLeft} seconds`;
        } else {
            clearInterval(countdown);
            document.getElementById('countdown').textContent = "OTP expired. Please click Send OTP again.";
            document.getElementById('verify-btn').disabled = true;
            document.getElementById('otp').disabled = true;
            document.getElementById('otp').value = "";
        }
    }, 1000);
}

function validateOtp(event) {
    // If OTP expired
    if (timeLeft <= 0 && event.submitter.name === "verify_otp") {
        alert("OTP has expired. Please request a new one.");
        event.preventDefault();
        return false;
    }
    return true;
}
</script>


</div>

</body>
</html>
