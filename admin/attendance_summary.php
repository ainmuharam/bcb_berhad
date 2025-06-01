<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
include_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/database.php';

class AttendanceSummary {
    private $conn;
    private $empId;
    private $fromDate;
    private $toDate;

    private $totalHours = 0;
    private $totalOvertime = 0;
    private $leaveDates = [];
    private $attendanceDates = [];

public function __construct($db) {
    $this->conn = $db->conn;
}

    public function getSummaryByName($name, $fromDate = '', $toDate = '') {
        $query = "SELECT ds.attendance_date, ds.first_clock_in, ds.last_clock_out, u.name
                  FROM daily_summary ds
                  JOIN users u ON ds.emp_id = u.emp_id
                  WHERE u.name = ?";
        
        $params = [$name];
        $types = "s";
    
        if (!empty($fromDate)) {
            $query .= " AND ds.attendance_date >= ?";
            $params[] = $fromDate;
            $types .= "s";
        }
    
        if (!empty($toDate)) {
            $query .= " AND ds.attendance_date <= ?";
            $params[] = $toDate;
            $types .= "s";
        }
    
        $query .= " ORDER BY ds.attendance_date ASC";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function generateDateRangeWithStatus($name, $fromDate, $toDate) {
        $summaryData = $this->getSummaryByName($name, $fromDate, $toDate);
        $indexedData = [];

        foreach ($summaryData as $record) {
            $indexedData[$record['attendance_date']] = $record;
        }

        $dateList = [];
        $totalOnLeave = 0;
        $start = new DateTime($fromDate);
        $end = new DateTime($toDate);
        $end->modify('+1 day');

        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');

            if (isset($indexedData[$dateStr])) {
                $record = $indexedData[$dateStr];
                $record['status'] = 'Present';
                $dateList[] = $record;
            } else {
                $leaveQuery = "SELECT 1 FROM annual_leave al
                               JOIN users u ON al.emp_id = u.emp_id
                               WHERE u.name = ? AND al.leave_date = ?";
                $leaveStmt = $this->conn->prepare($leaveQuery);
                $leaveStmt->bind_param("ss", $name, $dateStr);
                $leaveStmt->execute();
                $leaveResult = $leaveStmt->get_result();

                if ($leaveResult->num_rows > 0) {
                    $dateList[] = [
                        'attendance_date' => $dateStr,
                        'first_clock_in' => null,
                        'last_clock_out' => null,
                        'name' => $name,
                        'status' => 'On Leave'
                    ];
                } else {
                    $dateList[] = [
                        'attendance_date' => $dateStr,
                        'first_clock_in' => null,
                        'last_clock_out' => null,
                        'name' => $name,
                        'status' => 'Absent'
                    ];
                }
            }
        }

        return $dateList;
    }

    public function getDepartmentNameByName($name) {
        $stmt = $this->conn->prepare("SELECT d.department_name 
                                      FROM users u
                                      JOIN departments d ON u.department_id = d.department_id
                                      WHERE u.name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['department_name'] ?? null;
    }

    public function getByEmpId($emp_id) {
        $query = "SELECT attendance_date, first_clock_in, last_clock_out 
                  FROM daily_summary 
                  WHERE emp_id = ? 
                  ORDER BY attendance_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function initializeSummary($empId, $fromDate, $toDate) {
        $this->empId = $empId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->totalHours = 0;
        $this->totalOvertime = 0;
        $this->leaveDates = [];
        $this->attendanceDates = [];
    }

    public function calculateSummary() {
        $this->fetchLeaveDates();
        $this->fetchAttendance();
    }

    private function fetchLeaveDates() {
        $sql = "SELECT leave_date FROM annual_leave WHERE emp_id = ? AND leave_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $this->empId, $this->fromDate, $this->toDate);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $this->leaveDates[] = $row['leave_date'];
        }
    }

    private function fetchAttendance() {
        $sql = "SELECT attendance_date, first_clock_in, last_clock_out 
                FROM daily_summary 
                WHERE emp_id = ? AND attendance_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $this->empId, $this->fromDate, $this->toDate);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $this->attendanceDates[] = $row['attendance_date'];

            $in = $row['first_clock_in'];
            $out = $row['last_clock_out'];
            if ($in && $out) {
                $start = new DateTime($in);
                $end = new DateTime($out);
                $diff = $start->diff($end);
                $hours = (int)$diff->format('%h');
                $minutes = (int)$diff->format('%i');
                $total = $hours + $minutes / 60;

                $this->totalHours += $total;

                if ($total > 9) {
                    $this->totalOvertime += ($total - 9);
                }
            }
        }
    }

    public function getTotalHours() {
        return round($this->totalHours, 2);
    }

    
    public function calculateTotalHours($employeeId, $fromDate, $toDate) {
        $query = "SELECT first_clock_in, last_clock_out 
                FROM daily_summary 
                WHERE emp_id = ? 
                AND attendance_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iss", $employeeId, $fromDate, $toDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $totalMinutes = 0;

        while ($row = $result->fetch_assoc()) {
            if (!empty($row['first_clock_in']) && !empty($row['last_clock_out'])) {
                $start = new DateTime($row['first_clock_in']);
                $end = new DateTime($row['last_clock_out']);
                $interval = $start->diff($end);
                $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                $totalMinutes += $minutes;
            }
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf("%02d.%02d", $hours, $minutes);
    }
}
?>
