<?php
class SessionManager {
    private $timeoutDuration;

    public function __construct($timeoutMinutes = 30) {
        $this->timeoutDuration = $timeoutMinutes * 60; // Convert to seconds
        if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
    }

    public function checkInactivity() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $this->timeoutDuration) {
            $this->logout('timeout');
        }
        $this->updateLastActivity();
    }

    private function updateLastActivity() {
        $_SESSION['last_activity'] = time();
    }

    public function logout($reason = 'manual') {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header("Location: /bcb_berhad/login.php");
        exit();
    }
}
?>
