<?php
class PublicHoliday {
    private $conn;
    private $table_name = "public_holiday";

    public $holiday_date;
    public $holiday_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (holiday_date, holiday_name) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            // Log or handle error explicitly
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("ss", $this->holiday_date, $this->holiday_name);

        if ($stmt->execute()) {
            return true;
        } else {
            // Log or handle error explicitly
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
    }


    public function delete() {
        $query = "DELETE FROM public_holiday WHERE holiday_date = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->holiday_date);

        return $stmt->execute();
    }
}


?>
