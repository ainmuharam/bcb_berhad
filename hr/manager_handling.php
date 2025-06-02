
<?php
include_once __DIR__ . '/../database.php';
class managerAttendance {
    private $conn;
    private $managerId;

    public function __construct($dbConnection, $userId) {
        $this->conn = $dbConnection;
        $this->managerId = $userId;
    }

public function getAllAttendanceRecords($departmentId = null) {
    $sql = "SELECT 
                ds.emp_id,
                ds.first_clock_in AS clock_in, 
                ds.last_clock_out AS clock_out, 
                ds.attendance_date,
                u.name, 
                u.department_id, 
                d.department_name AS department 
            FROM daily_summary ds
            JOIN users u ON ds.emp_id = u.emp_id
            JOIN departments d ON u.department_id = d.department_id";

    if ($departmentId !== null) {
        $sql .= " WHERE u.department_id = ?";
    }

    $sql .= " ORDER BY ds.first_clock_in DESC";

    $stmt = $this->conn->prepare($sql);

    if ($departmentId !== null) {
        $stmt->bind_param("i", $departmentId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}



public function getAbsentEmployees($selectedDate, $selectedDepartment = null) {
    $query = "
        SELECT DISTINCT u.emp_id, u.name, u.email, d.department_name
        FROM users u
        JOIN departments d ON u.department_id = d.department_id
        WHERE u.emp_id NOT IN (
            SELECT emp_id
            FROM daily_summary
            WHERE attendance_date = ?
            AND emp_id IN (
                SELECT emp_id FROM users WHERE department_id = ?
            )
        )
        AND u.emp_id NOT IN (
            SELECT emp_id
            FROM annual_leave
            WHERE leave_date = ?
        )
        AND (u.deactivation_date IS NULL OR u.deactivation_date > ?)
        AND u.created_at <= ?
        AND u.department_id = ?
    ";

    $stmt = $this->conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $this->conn->error);
    }

    $stmt->bind_param("ssssss", $selectedDate, $selectedDepartment, $selectedDate, $selectedDate, $selectedDate, $selectedDepartment);
    $stmt->execute();
    $result = $stmt->get_result();

    $absentEmployees = [];
    while ($row = $result->fetch_assoc()) {
        $absentEmployees[] = $row;
    }

    return $absentEmployees;
}

public function getAnnualLeaves($selectedDate, $selectedDepartment) {
    $query = "
        SELECT u.emp_id, u.name, u.email, al.leave_date
        FROM annual_leave al
        JOIN users u ON al.emp_id = u.emp_id
        JOIN departments d ON u.department_id = d.department_id
        WHERE al.leave_date = ?
        AND u.department_id = ?
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ss", $selectedDate, $selectedDepartment);
    $stmt->execute();
    $result = $stmt->get_result();

    $leaveEmployees = [];
    while ($row = $result->fetch_assoc()) {
        $leaveEmployees[] = $row;
    }

    return $leaveEmployees;
}

public function getTotalEmployeesInDepartment($departmentId, $date) {
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) AS total 
        FROM users 
        WHERE department_id = ? 
        AND created_at <= ? 
        AND (deactivation_date IS NULL OR deactivation_date > ?)
    ");
    $stmt->bind_param("iss", $departmentId, $date, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['total'] : 0;
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
        $stmt->bind_param("bs", $null, $this->managerId);
        $stmt->send_long_data(0, $imageData);

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function removeProfileImage() {
        $stmt = $this->conn->prepare("UPDATE users SET image = NULL WHERE emp_id = ?");
        $stmt->bind_param("i", $this->managerId);
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
}
?>

