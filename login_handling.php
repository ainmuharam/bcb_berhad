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
            // Generate new token
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

    // Optional: Validate session token
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





}
?>
