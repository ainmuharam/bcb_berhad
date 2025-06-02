<?php
if (!class_exists('Database')) {
    class Database {
        private $servername = "localhost";
        private $username = "user3";
        private $password = "";
        private $dbname = "bcb_berhad";
        public $conn;

        public function __construct() {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
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
}
?>
