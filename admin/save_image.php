<?php
class Database {
    private $conn;

    public function __construct($servername, $username, $password, $dbname) {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function saveImage($image) {
        // Prepare the SQL statement
        $stmt = $this->conn->prepare("INSERT INTO images (image_data) VALUES (?)");

        // Check if the statement was prepared successfully
        if ($stmt === false) {
            return "Error preparing statement: " . $this->conn->error;
        }

        // Bind the image data as a blob
        $stmt->bind_param("b", $image);

        // Set the image data as a blob
        $stmt->send_long_data(0, $image);

        // Execute the statement
        if ($stmt->execute()) {
            $stmt->close();
            return "Image saved successfully!";
        } else {
            return "Error saving image: " . $stmt->error;
        }
    }

    public function close() {
        $this->conn->close();
    }
}
?>