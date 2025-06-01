<?php
class Database {
    private $servername = "localhost"; // Your server name
    private $username = "root"; // Your database username
    private $password = ""; // Your database password
    private $dbname = "bcb_berhad"; // Your database name
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Check connection
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>