<?php
include_once __DIR__ . '/database.php';

class Login {
    private $db;

   public function __construct(Database $database) {
        $this->db = $database->conn; 
    }
 
public function emailExists($email) {
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->num_rows > 0);
}

public function verifyOtp($email, $inputOtp) {
    if (!isset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_time'])) {
        return ['status' => 'error', 'message' => 'OTP session not set.'];
    }

    if ($_SESSION['otp_email'] !== $email) {
        return ['status' => 'error', 'message' => 'Email mismatch.'];
    }

    if (time() - $_SESSION['otp_time'] > 300) {
        return ['status' => 'expired', 'message' => 'OTP expired. Please resend.'];
    }

    if ($_SESSION['otp'] != $inputOtp) {
        return ['status' => 'invalid', 'message' => 'Wrong OTP number.'];
    }

    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_time']);
    return ['status' => 'valid'];
}

public function updatePasswordByEmail($email, $new_password) {
    $stmt = $this->db->prepare("SELECT emp_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['status' => false, 'message' => 'Email not found.'];
    }

    // Validate password strength
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%&*_]).{8,}$/', $new_password)) {
        return ['status' => false, 'message' => 'Password must include at least 8 characters, uppercase letter, number, and special character.'];
    }

    $hash = password_hash($new_password, PASSWORD_ARGON2ID);
    $updateStmt = $this->db->prepare("UPDATE users SET password = ?, default_password = 0 WHERE email = ?");
    $updateStmt->bind_param("ss", $hash, $email);

    if ($updateStmt->execute()) {
        return ['status' => true, 'message' => 'Password updated successfully.'];
    } else {
        return ['status' => false, 'message' => 'Failed to update password.'];
    }
}

    public function loginUserWithToken($email) {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $token = bin2hex(random_bytes(32));

            $updateStmt = $this->db->prepare("UPDATE users SET session_token = ? WHERE email = ?");
            $updateStmt->bind_param("ss", $token, $email);
            $updateStmt->execute();

            $_SESSION['user_session'] = [
                'emp_id' => $user['emp_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'token' => $token
            ];

            return ['status' => true, 'message' => 'Login successful'];
        }

        return ['status' => false, 'message' => 'Login failed'];
    }

    public function validateSessionToken($email, $token) {
        $stmt = $this->db->prepare("SELECT session_token FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($storedToken);
        $stmt->fetch();

        return $token === $storedToken;
    }

    public function saveSessionToken($email, $token) {
    $stmt = $this->db->prepare("UPDATE users SET session_token = ? WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);
    return $stmt->execute();
}

public function handleLoginRequest() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    if (!isset($_SESSION['lockout_time'])) {
        $_SESSION['lockout_time'] = null;
    }

    $is_locked_out = false;
    $remaining_time = 0;

    if ($_SESSION['login_attempts'] >= 3) {
        $elapsed = time() - $_SESSION['lockout_time'];
        if ($elapsed < 180) {
            $is_locked_out = true;
            $remaining_time = 180 - $elapsed;

            return [
                'is_locked_out' => $is_locked_out,
                'remaining_time' => $remaining_time,
                'error' => 'Too many login attempts. Try again later.'
            ];
        } else {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lockout_time'] = null;
        }
    }
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_email'])) {
        $email = $_POST['forgot-email'];

        if (!$this->emailExists($email)) {
            header("Location: login.php?error=invalid_email");
            exit();
        }

        header("Location: login.php?success=email_found");
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_locked_out) {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        $query = "
            SELECT u.emp_id, u.department_id, u.password, u.name, u.status, u.default_password, r.role_name 
            FROM users u
            LEFT JOIN role r ON u.role_id = r.role_id
            WHERE u.email = ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ((int)$user['status'] === 0) {
                $_SESSION['login_attempts'] += 1;
            } elseif (password_verify($password, $user['password'])) {
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

                $token = bin2hex(random_bytes(32));
                $this->saveSessionToken($email, $token);

                switch ($user['role_name']) {
                    case 'Admin':
                        $_SESSION['admin_session'] = $session_data;
                        $_SESSION['admin_session']['token'] = $token;
                        break;
                    case 'Manager':
                        $_SESSION['manager_session'] = $session_data;
                        $_SESSION['manager_session']['token'] = $token;
                        break;
                    case 'User':
                        $_SESSION['user_session'] = $session_data;
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
                        header("Location: manager/manager_dashboard.php");
                        exit();
                    case 'User':
                        header("Location: users/user_dashboard.php");
                        exit();
                    default:
                        echo "Unknown role assigned.";
                        break;
                }
            } else {
                $_SESSION['login_attempts'] += 1;
                if ($_SESSION['login_attempts'] >= 3) {
                    $_SESSION['lockout_time'] = time();
                } else {
                    echo "Invalid email or password.";
                }
            }
        } else {
            $_SESSION['login_attempts'] += 1;
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lockout_time'] = time();
            } else {
                echo "Invalid email or password.";
            }
        }
    }

    if ($is_locked_out) {
        echo "Account locked. Try again in {$remaining_time} seconds.";
    }
}

}

?>
