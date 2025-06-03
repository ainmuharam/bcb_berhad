<?php
include_once __DIR__ . '/../database.php';

class PublicHoliday {
    private $conn;
    private $table_name = "public_holiday";

    public $holiday_date;
    public $holiday_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO public_holiday (holiday_date, holiday_name) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $this->holiday_date, $this->holiday_name);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM public_holiday WHERE holiday_date = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->holiday_date);
        return $stmt->execute();
    }
}
?>
