<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/database.php';

class userAttendance {
    private $conn;
    private $userId;

    public function __construct($dbConnection, $userId) {
        $this->conn = $dbConnection;
        $this->userId = $userId;
    }

public function getUserAttendanceRecords($fromDate = null, $toDate = null) {
    $records = [];
    $sql = "SELECT * FROM daily_summary WHERE emp_id = ?";
    $types = "i";
    $params = [$this->userId];

    if ($fromDate && $toDate) {
        $sql .= " AND (DATE(first_clock_in) BETWEEN ? AND ? OR DATE(last_clock_out) BETWEEN ? AND ?)";
        $types .= "ssss";
        array_push($params, $fromDate, $toDate, $fromDate, $toDate);
    } elseif ($fromDate) {
        $sql .= " AND (DATE(first_clock_in) >= ? OR DATE(last_clock_out) >= ?)";
        $types .= "ss";
        array_push($params, $fromDate, $fromDate);
    } elseif ($toDate) {
        $sql .= " AND (DATE(first_clock_in) <= ? OR DATE(last_clock_out) <= ?)";
        $types .= "ss";
        array_push($params, $toDate, $toDate);
    }

    $sql .= " ORDER BY first_clock_in DESC";

    $stmt = $this->conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    return $records;
}


    public function getUserLeaveDates($fromDate = null, $toDate = null) {
        $leaveDates = [];

        if (!$fromDate || !$toDate) {
            return $leaveDates;
        }

        $query = "SELECT leave_date FROM annual_leave 
                  WHERE emp_id = ? AND leave_date BETWEEN ? AND ?";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) return $leaveDates;

        $empId = (int)$this->userId;
        $stmt->bind_param("iss", $empId, $fromDate, $toDate);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $leaveDates[] = $row['leave_date'];
        }

        $stmt->close();
        return $leaveDates;
    }

    public function getLoginHistoryByMonth($month, $year) {
        $sql = "SELECT date, time, status, clock 
                FROM manual_login 
                WHERE emp_id = ? 
                AND MONTH(date) = ? 
                AND YEAR(date) = ?
                ORDER BY date DESC, time DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("iii", $this->userId, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }

    public function uploadProfileImage($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Only JPG and PNG files are allowed.');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('File size must be less than 2MB.');
        }

        $imageData = file_get_contents($file['tmp_name']);
        $null = null;

        $stmt = $this->conn->prepare("UPDATE users SET image = ? WHERE emp_id = ?");
        $stmt->bind_param("bs", $null, $this->userId);
        $stmt->send_long_data(0, $imageData);

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function removeProfileImage() {
        $stmt = $this->conn->prepare("UPDATE users SET image = NULL WHERE emp_id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $stmt->close();
    }

    public function getDepartmentName($departmentId) {
        $stmt = $this->conn->prepare("SELECT department_name FROM departments WHERE department_id = ?");
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $dept = $result->fetch_assoc();
            return $dept['department_name'];
        } else {
            return "Department not found";
        }
    }

    public function getClockInStatus($firstClockIn)
    {
        if (empty($firstClockIn)) {
            return ['label' => '-', 'class' => ''];
        }

        $clockInTime = new DateTime($firstClockIn);
        $earlyTime = new DateTime($clockInTime->format('Y-m-d') . ' 07:30:00');
        $onTime = new DateTime($clockInTime->format('Y-m-d') . ' 08:00:00');

        if ($clockInTime < $earlyTime) {
            return ['label' => 'Early Arrive', 'class' => 'status-on-time'];
        } elseif ($clockInTime <= $onTime) {
            return ['label' => 'On Time', 'class' => 'status-on-time'];
        } else {
            return ['label' => 'Late Arrive', 'class' => 'status-late'];
        }
    }

    public function getClockOutStatus($lastClockOut)
    {
        if (empty($lastClockOut)) {
            return ['label' => '-', 'class' => ''];
        }

        $clockOutTime = new DateTime($lastClockOut);
        $exactFivePM = new DateTime($clockOutTime->format('Y-m-d') . ' 17:00:00');
        $bufferTime = new DateTime($clockOutTime->format('Y-m-d') . ' 17:10:00');

        if ($clockOutTime < $exactFivePM) {
            return ['label' => 'Early Leave', 'class' => 'status-on-time'];
        } elseif ($clockOutTime <= $bufferTime) {
            return ['label' => 'On Time Leave', 'class' => 'status-on-time'];
        } else {
            return ['label' => 'Late Leave', 'class' => 'status-late'];
        }
    }

        public function getDailySummary($fromDate, $toDate) {
        $sql = "SELECT attendance_date, first_clock_in, last_clock_out 
                FROM daily_summary 
                WHERE emp_id = ? AND attendance_date BETWEEN ? AND ?
                ORDER BY attendance_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $this->userId, $fromDate, $toDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $summary = [];
        while ($row = $result->fetch_assoc()) {
            $summary[] = $row;
        }
        return $summary;
    }

    


}
?>
