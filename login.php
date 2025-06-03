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

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = null;
}

// Check if user is currently locked out
$is_locked_out = false;
$remaining_time = 0;
if ($_SESSION['login_attempts'] >= 3) {
    $elapsed = time() - $_SESSION['lockout_time'];
    if ($elapsed < 180) { // 3 minutes lockout
        $is_locked_out = true;
        $remaining_time = 180 - $elapsed;
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time'] = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_email'])) {
    $email = $_POST['forgot-email'];

    if (!$login->emailExists($email)) {
        header("Location: login.php?error=invalid_email");
        exit();
    }

    header("Location: login.php?success=email_found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_locked_out) {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $query = "
        SELECT u.emp_id, u.department_id, u.password, u.name, u.status, u.default_password, r.role_name 
        FROM users u
        LEFT JOIN role r ON u.role_id = r.role_id
        WHERE u.email = ?
    ";

    $stmt = $db->conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ((int)$user['status'] === 0) {
            $error = "Invalid email or password.";
            $_SESSION['login_attempts'] += 1;
        } elseif (password_verify($password, $user['password'])) {
            // Successful login - reset attempts
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lockout_time'] = null;

            $session_data = [
                'emp_id' => $user['emp_id'],
                'name' => $user['name'],
                'role' => $user['role_name'],
                'department_id' => $user['department_id'],
                'email' => $email,
                'logged_in' => true
            ];

            switch ($user['role_name']) {
                case 'Admin':
                    $_SESSION['admin_session'] = $session_data;
                    break;
                case 'Manager':
                    $_SESSION['manager_session'] = $session_data;
                    break;
                case 'User':
                    $_SESSION['user_session'] = $session_data;
                    break;
            }

            $token = bin2hex(random_bytes(32));
            $login->saveSessionToken($email, $token);

            switch ($user['role_name']) {
                case 'Admin':
                    $_SESSION['admin_session']['token'] = $token;
                    break;
                case 'Manager':
                    $_SESSION['manager_session']['token'] = $token;
                    break;
                case 'User':
                    $_SESSION['user_session']['token'] = $token;
                    break;
            }

            if ((int)$user['default_password'] === 1) {
                $_SESSION['default_password'] = true;
                header("Location: login.php");
                exit();
            }
            switch ($user['role_name']) {
                case 'Admin':
                    $_SESSION['pending_admin_session'] = $session_data;
                    header("Location: verify_admin.php");
                    exit();
                case 'Manager':
                    header('Location: hr/manager_dashboard.php');
                    exit();
                case 'User':
                    header('Location: users/user_dashboard.php');
                    exit();
                default:
                    $error = "Unknown role assigned. Contact administrator.";
                    break;
            }
        }  else {
            // Wrong password
            $_SESSION['login_attempts'] += 1;
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lockout_time'] = time();
                $is_locked_out = true;
                $remaining_time = 180;
            } else {
                $error = "Invalid email or password.";
            }
        }
    } else {
        // Email not found
        $_SESSION['login_attempts'] += 1;
        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lockout_time'] = time();
            $is_locked_out = true;
            $remaining_time = 180;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
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
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
            z-index: 9999;
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
    <div id="defaultPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('defaultPasswordModal').style.display='none';">&times;</span>
            <p><strong>First Tme Login?</strong><br>
            Please click <strong><a href="#" id="forgotPasswordLink">Forgot Password</a></strong> to reset new password.</p>
        </div>
    </div>
<script>
    window.onclick = function(event) {
        document.getElementById('defaultPasswordModal').style.display = 'block';
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
</script>
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
        <div id="countdown-message" style="color: red; font-weight: bold;">
            Account locked. Please try again in <?php echo $remaining_time; ?> seconds.
        </div>
        <script>
            // Update countdown timer every second
            let seconds = <?php echo $remaining_time; ?>;
            const countdownElement = document.getElementById('countdown-message');
            
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.textContent = `Account locked. Please try again in ${seconds} seconds.`;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    location.reload(); // Reload the page when countdown reaches 0
                }
            }, 1000);
        </script>
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
</body>
</html>