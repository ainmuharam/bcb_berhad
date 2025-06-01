<?php
require_once 'database.php';

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $password) {
        // Prepare the SQL statement to check the user credentials
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and verify the password
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Return user data if login is successful
        }
        return false; // Return false if login fails
    }

    public function isEmailValid($email) {
        // Prepare the SQL statement
        $query = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn(); // Fetch the count directly
    
        // Check if the email exists
        return $count > 0; // Return true if email exists, false otherwise
    }

   
}
?>