<?php

include_once '../database.php';

class Attendance {
    private $database;
    
    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function create($employeeId, $clockIn, $clockOut, $breakHour) {
        $stmt = $this->database->conn->prepare("INSERT INTO attendance (emp_id, clock_in, clock_out, break_hour) VALUES (?, ?, ?, ?)");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->database->conn->error);
        }

        $stmt->bind_param("issd", $employeeId, $clockIn, $clockOut, $breakHour);
        
        if ($stmt->execute()) {
            $attendanceId = $this->database->conn->insert_id; // Return the last inserted attendance ID
            $stmt->close(); // Close the statement
            return $attendanceId; // Return the attendance ID
        } else {
            $stmt->close(); // Close the statement even on failure
            throw new Exception("Execute failed: " . $stmt->error); // Throw an exception with the error
        }
    }

    
}
?>