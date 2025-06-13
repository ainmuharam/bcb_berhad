<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'database.php';

include_once 'admin/depart_handling.php';
include_once 'admin/register_handling.php';
include_once 'login_handling.php';

$db = new Database(); 
$login = new Login($db);

$result = $login->handleLoginRequest();
$is_locked_out = $result['is_locked_out'] ?? false;
$remaining_time = $result['remaining_time'] ?? 0;
$error = $result['error'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
        /* Basic styles for modal */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 9999; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
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
    <?php if (!empty($_SESSION['default_password'])): ?>
    <div id="defaultPasswordModal" class="modal" style="display: block;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('defaultPasswordModal').style.display='none';">&times;</span>
            <p><strong>First Tme Login?</strong><br>
            Please click <strong><a href="#" id="forgotPasswordLink">Forgot Password</a></strong> to reset new password.</p>
        </div>
    </div>
<?php unset($_SESSION['default_password']); ?>
<?php endif; ?>

<h2 class="font-heading">LOGIN</h2>
<div class="login-container">
    <img src="images/bcblogo.png" alt="BCB logo" class="logo">
    <?php
    // Check if user is locked out
    $disabled = '';
    if ($is_locked_out) {
        $disabled = 'disabled';
    }
    ?>
    <?php if (!empty($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($is_locked_out): ?>
        <div id="countdown-message" 
            data-seconds="<?php echo (int)$remaining_time; ?>" 
            style="color: red; font-weight: bold;">
            Account locked. Please try again in <?php echo (int)$remaining_time; ?> seconds.
        </div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required <?php echo $disabled; ?>>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required <?php echo $disabled; ?>>
        </div>
        <div class="forgot-password-container">
            <a href="#" class="forgot_password" id="forgotPasswordLink">Forgot Password?</a>
        </div>
        <button type="submit" <?php echo $disabled; ?>>Login</button>
    </form>
</div>

<div id="forgotPasswordModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Forgot Password</h2>
        <div id="email-error" style="color: red; font-weight: bold; display: none;">
        </div>
        <div id="email-step" class="step">
            <form id="email-form" method="POST" action="send_otp.php">
                <div class="form-group">
                    <label for="forgot-email">Enter your email:</label>
                    <input type="email" id="forgot-email" name="forgot-email" required>
                </div>
                <button type="submit" name="verify_email">Sent OTP</button>
            </form>
        </div>

        <div id="otp-step" class="step" style="display: none;">
            <form id="otp-form" method="POST">
                <div id="otp-error" style="color: red; display: none; margin-bottom: 10px;"></div>
                <div class="form-group">
                    <label for="otp">Enter the OTP :</label>
                    <input type="number" id="otp" name="otp" min="0" step="1" required 
                        oninput="if(this.value.length > 6) this.value = this.value.slice(0,6);">
                </div>
                <p style="margin-top:10px;">
                    <a href="#" id="resendOtpLink">Resend OTP</a> 
                    <span id="otpTimer" style="margin-left: 10px;"></span>
                </p>
                <button type="submit">Verify OTP</button>
            </form>
        </div>

        <div id="new-password-step" class="step" style="display: none;">
            <form id="new-password-form" method="POST" action="update_password.php">
                <div id="password-error" style="color: red; display: none; margin-bottom: 10px;"></div>
                <div class="form-group">
                    <label for="new-password">Enter your new password:</label>
                    <input type="password" id="new-password" name="new-password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm your new password:</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
                <button type="submit">Change Password</button>
            </form>
        </div>
    </div>
</div>

<script src="forgot_password.js"></script>
<script src="default_password_modal.js"></script>
<script src="password_timer.js"></script>

</body>
</html>