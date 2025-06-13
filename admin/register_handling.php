<?php
include_once __DIR__ . '/../database.php';

class User {
    private $db;
    private $table_name = "users";
    const TABLE_FACE_RECOGNITION = 'face_recognition';

    public function __construct(Database $database) {
        $this->db = $database->conn; 
    }

    public function create($employeeId, $name, $department, $email, $password, $profile_picture, $role_id) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE emp_id = ?");
        if ($checkStmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $checkStmt->bind_param("i", $employeeId);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            return false; 
        }

        $emailCheckStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        if ($emailCheckStmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $emailCheckStmt->bind_param("s", $email);
        $emailCheckStmt->execute();
        $emailCheckStmt->bind_result($emailCount);
        $emailCheckStmt->fetch();
        $emailCheckStmt->close();

        if ($emailCount > 0) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO users (emp_id, name, department_id, email, password, profile_picture, role_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
        $stmt->bind_param("isisssi", $employeeId, $name, $department, $email, $hashedPassword, $profile_picture, $role_id);

        $result = $stmt->execute();
        $stmt->close();

        return $result; 
    }

    public function updateAttendanceId($employeeId, $attendanceId) {
        $stmt = $this->db->prepare("UPDATE users SET attendance_id = ? WHERE emp_id = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("ii", $attendanceId, $employeeId);
        $result = $stmt->execute();
        $stmt->close();

        return $result; 
    }

    public function searchEmployees($query) {
        $stmt = $this->db->prepare("SELECT emp_id, name, email FROM users WHERE emp_id LIKE ? OR name LIKE ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $searchTerm = "%" . $query . "%"; // Prepare the search term for LIKE
        $stmt->bind_param("ss", $searchTerm, $searchTerm); // Bind parameters
        $stmt->execute();
        $result = $stmt->get_result();

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row; // Add each employee to the array
        }
        $stmt->close();

        return $employees; // Return the array of employees
    }

    public function getEmployees() {
        $query = "SELECT emp_id, name, department_id, email, profile_picture FROM users"; // Adjust the query as needed
        $result = $this->db->query($query);

        if ($result === false) {
            throw new Exception("Error executing query: " . $this->db->error);
        }

        $employees = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
 }
        }
        return $employees; // Return the array of employees
    }

    public function getUserById($employeeId) {
        $stmt = $this->db->prepare("SELECT emp_id, name, email, department_id, profile_picture FROM users WHERE emp_id = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }
    
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return user data
        }
        return null; 
    }

        public function emailExists($email, $excludeEmpId) {
        $stmt = $this->db->prepare("SELECT emp_id FROM users WHERE email = ? AND emp_id != ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }
        $stmt->bind_param("si", $email, $excludeEmpId);
        $stmt->execute();
        $stmt->bind_result($existingEmpId);
        $stmt->fetch();
        $stmt->close();
        return $existingEmpId ?? false;
    }
public function updateUser($id, $name, $departmentId, $email) {
    $sql = "UPDATE users SET name = ?, department_id = ?, email = ? WHERE emp_id = ?";
    $stmt = $this->db->prepare($sql);

    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $this->db->error);
    }

    $stmt->bind_param("sisi", $name, $departmentId, $email, $id); // ✅ Correct binding
    $result = $stmt->execute();

    if ($result === false) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return true;
}
    public function updateProfilePicture($emp_id, $fileName) {
        $stmt = $this->db->prepare("UPDATE employees SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $fileName, $emp_id);
        $stmt->execute();
    }

    public function getTotalEmployees($date) {
        if (empty($date)) {
            $date = date('Y-m-d');
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT emp_id) as total 
            FROM users 
            WHERE created_at <= ? AND (deactivation_date IS NULL OR deactivation_date > ?)
        ");

        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("ss", $date, $date);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        return $total;
    }
    

    public function getAttendanceRecords() {

        $totalEmployeesStmt = $this->db->prepare("
            SELECT COUNT(DISTINCT emp_id) AS total_employees 
            FROM users 
            WHERE created_at <= CURDATE() AND (deactivation_date IS NULL OR deactivation_date > CURDATE())
        ");
        if ($totalEmployeesStmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }
        $totalEmployeesStmt->execute();
        $totalEmployeesStmt->bind_result($totalEmployees);
        $totalEmployeesStmt->fetch();
        $totalEmployeesStmt->close();
    
        $stmt = $this->db->prepare("
            SELECT ds.emp_id, u.name, ds.first_clock_in AS clock_in, ds.last_clock_out AS clock_out, d.department_name,
                   CASE 
                       WHEN ds.first_clock_in IS NOT NULL THEN 'Present'
                       ELSE 'Absent'
                   END AS status
            FROM users u
            LEFT JOIN daily_summary ds ON u.emp_id = ds.emp_id AND ds.attendance_date = CURDATE()
            LEFT JOIN departments d ON u.department_id = d.department_id
            WHERE u.created_at <= ? AND (u.deactivation_date IS NULL OR u.deactivation_date > ?)
        ");
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }
        $currentDate = date('Y-m-d');
        $stmt->bind_param("s", $currentDate);
        $stmt->execute();
    
        // Bind the result
        $stmt->bind_result($emp_id, $name, $clock_in, $clock_out, $department_name, $status);
    
        $attendanceRecords = [];
        $uniquePresentEmployees = []; // Array to track unique present employee IDs
    
        while ($stmt->fetch()) {
            $attendanceRecords[] = [
                'emp_id' => $emp_id,
                'name' => $name,
                'clock_in' => $clock_in,
                'clock_out' => $clock_out,
                'department' => $department_name,
                'status' => $status
            ];
    
            // Track present employees
            if ($status === 'Present') {
                $uniquePresentEmployees[$emp_id] = true;
            }
        }
    
        $stmt->close();
    
        $totalPresentCount = count($uniquePresentEmployees);
        $totalAbsentCount = $totalEmployees - $totalPresentCount;
    
        return [
            'records' => $attendanceRecords,
            'total_present' => $totalPresentCount,
            'total_absent' => $totalAbsentCount
        ];
    }
    
        public function getAbsentEmployees($selectedDate, $selectedDepartment = '') {
            $query = "
                SELECT DISTINCT u.emp_id, u.name, u.email, d.department_name
                FROM users u
                JOIN departments d ON u.department_id = d.department_id
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM daily_summary ds
                    WHERE ds.emp_id = u.emp_id
                    AND ds.attendance_date = ?
                )
                AND NOT EXISTS (
                    SELECT 1
                    FROM annual_leave al
                    WHERE al.emp_id = u.emp_id
                    AND al.leave_date = ?
                )
                AND (u.deactivation_date IS NULL OR u.deactivation_date > ?)
                AND u.created_at <= ?
            ";

            if (!empty($selectedDepartment)) {
                $query .= " AND u.department_id = ?";
            }

            $stmt = $this->db->prepare($query);

            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            if (!empty($selectedDepartment)) {
                $stmt->bind_param('sssss', $selectedDate, $selectedDate, $selectedDate, $selectedDate, $selectedDepartment);
            } else {
                $stmt->bind_param('ssss', $selectedDate, $selectedDate, $selectedDate, $selectedDate);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $absentEmployees = [];
            while ($row = $result->fetch_assoc()) {
                $absentEmployees[$row['emp_id']] = $row; // Use emp_id as key to prevent duplicates
            }

            return array_values($absentEmployees); // re-index
        }


    public function getAttendanceRecordsByFilter($date = '', $department = '') {
    $query = "
        SELECT ds.emp_id, u.name, ds.first_clock_in AS clock_in, ds.last_clock_out AS clock_out, d.department_name,
               CASE 
                   WHEN ds.first_clock_in IS NOT NULL THEN 'Present'
                   ELSE 'Absent'
               END AS status
        FROM users u
        LEFT JOIN daily_summary ds ON u.emp_id = ds.emp_id
        LEFT JOIN departments d ON u.department_id = d.department_id
        WHERE 1=1
    ";

    $params = [];
    $types = '';

    if (!empty($date)) {
        $query .= " AND ds.attendance_date = ?";
        $types .= 's';
        $params[] = $date;
    }

    if (!empty($department)) {
        $query .= " AND d.department_name = ?";
        $types .= 's';
        $params[] = $department;
    }

    $stmt = $this->db->prepare($query);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $this->db->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $stmt->bind_result($emp_id, $name, $clock_in, $clock_out, $department_name, $status);

    $attendanceRecords = [];
    $presentEmp = [];

    while ($stmt->fetch()) {
        $attendanceRecords[] = [
            'emp_id' => $emp_id,
            'name' => $name,
            'clock_in' => $clock_in,
            'clock_out' => $clock_out,
            'department' => $department_name,
            'status' => $status
        ];
        if ($status === 'Present') {
            $presentEmp[$emp_id] = true;
        }
    }

    $stmt->close();
    $totalPresent = count($presentEmp);
    $totalEmployees = $this->getTotalEmployees($date);
    $totalAbsent = $totalEmployees - $totalPresent;

    return [
        'records' => $attendanceRecords,
        'total_present' => $totalPresent,
        'total_absent' => $totalAbsent
    ];
}

public function getAttendanceRecordsByDate($selectedDate, $selectedDepartment = '') {
    $records = [];
    $total_present = 0;
    $total_absent = 0;

    $query = "
    SELECT u.emp_id, u.name, d.department_name AS department,
           ds.first_clock_in AS clock_in, ds.last_clock_out AS clock_out
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    LEFT JOIN daily_summary ds ON u.emp_id = ds.emp_id AND ds.attendance_date = ?
    WHERE u.emp_id NOT IN (
        SELECT emp_id FROM annual_leave WHERE leave_date = ?
    )
    AND (u.deactivation_date IS NULL OR u.deactivation_date >= ?)

    ";

    if (!empty($selectedDepartment)) {
        $query .= " AND u.department_id = ?";
    }

    $stmt = $this->db->prepare($query);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $this->db->error);
    }
if (!empty($selectedDepartment)) {
    $stmt->bind_param("ssss", $selectedDate, $selectedDate, $selectedDate, $selectedDepartment);
} else {
    $stmt->bind_param("sss", $selectedDate, $selectedDate, $selectedDate);
}

    

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['clock_in']) || !empty($row['clock_out'])) {
            $total_present++;
        } else {
            $total_absent++;
        }
        $records[] = $row;
    }

    return [
        'records' => $records,
        'total_present' => $total_present,
        'total_absent' => $total_absent
    ];
}


public function getEmployeesOnLeave($selectedDate, $selectedDepartment = '') {
    $query = "
        SELECT u.emp_id, u.name, u.email, d.department_name
        FROM annual_leave al
        JOIN users u ON al.emp_id = u.emp_id
        JOIN departments d ON u.department_id = d.department_id
        WHERE al.leave_date = ? 
        AND (u.deactivation_date IS NULL OR u.deactivation_date > ?)
    ";

    // If a department is selected, add filtering
    if (!empty($selectedDepartment)) {
        $query .= " AND u.department_id = ?";
    }

    $stmt = $this->db->prepare($query);

    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $this->db->error);
    }

    if (!empty($selectedDepartment)) {
        $stmt->bind_param('sss', $selectedDate, $selectedDate, $selectedDepartment);
    } else {
        $stmt->bind_param('ss', $selectedDate, $selectedDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $leaveEmployees = [];
    while ($row = $result->fetch_assoc()) {
        $leaveEmployees[] = $row;
    }

    return $leaveEmployees;
}



public function isPublicHoliday($selectedDate)
{
    $query = "SELECT * FROM public_holiday WHERE holiday_date = ?";
    $stmt = $this->db->prepare($query);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $this->db->error);
    }

    $stmt->bind_param('s', $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0; // If found, it is a public holiday
}

public function toggleStatus($empId) {
    $stmt = $this->db->prepare("UPDATE users SET status = NOT status WHERE emp_id = ?");
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $this->db->error);
    }

    $stmt->bind_param("s", $empId);
    return $stmt->execute();
}
public function getPasswordByEmpId($emp_id) {
    $stmt = $this->db->prepare("SELECT password FROM users WHERE emp_id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result(); // Get MySQLi result
    return $result->fetch_assoc(); // No argument needed
}


    public function updatePasswordByEmpId($emp_id, $new_password) {
        $hash = password_hash($new_password, PASSWORD_ARGON2ID);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE emp_id = ?");
        return $stmt->execute([$hash, $emp_id]);
    }

        public function changePassword($emp_id, $current_password, $new_password, $confirm_password) {
        $userData = $this->getPasswordByEmpId($emp_id);

        if (!$userData || !password_verify($current_password, $userData['password'])) {
            return 'Password is incorrect.';
        }
        if ($new_password !== $confirm_password) {
            return 'New password does not match.';
        }

         if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%&*_]).{8,}$/', $new_password)) {
        return 'Password must at least 8 characters include uppercase letter, number, and special character.';
    }
        $updated = $this->updatePasswordByEmpId($emp_id, $new_password);
        return $updated ? 'Password successfully updated.' : 'Failed to update password.';
    }
    
    public function getClockInStatus($clockIn) {
    if (empty($clockIn)) {
        return ['label' => '-', 'class' => ''];
    }

    $clockInTime = new DateTime($clockIn);
    $onTime = new DateTime($clockInTime->format('Y-m-d') . ' 09:00:00');

    if ($clockInTime <= $onTime) {
        return ['label' => 'On Time', 'class' => 'status-on-time'];
    } else {
        return ['label' => 'Late', 'class' => 'status-late'];
    }
}

public function getClockOutStatus($clockOut) {
    if (empty($clockOut)) {
        return ['label' => '-', 'class' => ''];
    }

    $clockOutTime = new DateTime($clockOut);
    $exactFivePM = new DateTime($clockOutTime->format('Y-m-d') . ' 17:15:00');
    $bufferTime = new DateTime($clockOutTime->format('Y-m-d') . ' 17:30:00');

    if ($clockOutTime < $exactFivePM) {
        return ['label' => 'Early Leave', 'class' => 'status-late'];
    } elseif ($clockOutTime <= $bufferTime) {
        return ['label' => 'On Time', 'class' => 'status-on-time'];
    } else {
        return ['label' => 'On Time', 'class' => 'status-on-time'];
    }
}

    public function getDailyAttendanceStatus($date, $clockIn, $clockOut) {
        $dayOfWeek = (new DateTime($date))->format('l');
        
        if ($this->isPublicHoliday($date)) {
            return 'Public Holiday';
        } elseif ($dayOfWeek === 'Sunday') {
            return 'Weekend';
        } elseif (empty($clockIn) && empty($clockOut)) {
            return 'Absent';
        } else {
            return 'Present';
        }
    }

    public function getStatusClass($status) {
        return match ($status) {
            'Present' => 'status-present',
            'Absent' => 'status-absent',
            'Public Holiday' => 'status-holiday',
            'Weekend' => 'status-weekend',
            default => 'status-default',
        };
    }



}
?>