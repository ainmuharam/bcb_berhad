<?php
class PublicHoliday {
    private $conn;
    private $table = "public_holiday";

    public $holiday_date;
    public $holiday_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (holiday_date, holiday_name) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $this->holiday_date, $this->holiday_name);
        
        if (!$stmt->execute()) {
            error_log("Error creating holiday: " . $stmt->error);
            return false;
        }
        return true;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE holiday_date = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->holiday_date);
        return $stmt->execute();
    }
}
?>