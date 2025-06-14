<?
include_once __DIR__ . '/database.php';
date_default_timezone_set("Asia/Kuala_Lumpur");

class Attendance {
    private $db;
    private $emp_id;
    private $current_time;
    private $today_date;

    public function __construct($db, $emp_id) {
        $this->db = $db;
        $this->emp_id = $emp_id;
        $this->current_time = date("H:i:s");
        $this->today_date = date("Y-m-d");
    }

    public function checkAttendance() {
        $sql = "SELECT clock_in, clock_out FROM face_recognition WHERE emp_id = ? AND attendance_date = ?";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param("ss", $this->emp_id, $this->today_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = $result->fetch_assoc();
        $stmt->close();
        return $attendance;
    }

    public function hasClockedInToday() {
        $sql = "SELECT 1 FROM face_recognition WHERE emp_id = ? AND attendance_date = ? AND action = 'clock_in'";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param("ss", $this->emp_id, $this->today_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasClockIn = $result->num_rows > 0;
        $stmt->close();
        return $hasClockIn;
    }

    public function clockIn() {
        $sql = "INSERT INTO face_recognition (emp_id, action, time, attendance_date) VALUES (?, 'clock_in', ?, ?)";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param("sss", $this->emp_id, $this->current_time, $this->today_date);
        $stmt->execute();
        $stmt->close();

        $this->updateDailySummary('clock_in');

        return "Clock In Success! Employee: " . $this->emp_id . " at " . $this->current_time;
    }

    public function clockOut() {
        if (!$this->hasClockedInToday()) {
            return "Error: You must clock in first before clocking out";
        }

        $sql = "INSERT INTO face_recognition (emp_id, action, time, attendance_date) VALUES (?, 'clock_out', ?, ?)";
        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param("sss", $this->emp_id, $this->current_time, $this->today_date);
        $stmt->execute();
        $stmt->close();

        $this->updateDailySummary('clock_out');

        return "Clock Out Success! Employee: " . $this->emp_id . " at " . $this->current_time;
    }

    public function updateDailySummary($action) {
        if ($action == 'clock_in') {
            $sql = "INSERT INTO daily_summary (emp_id, first_clock_in, attendance_date)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    first_clock_in = LEAST(first_clock_in, VALUES(first_clock_in))";
        } elseif ($action == 'clock_out') {
            $sql = "INSERT INTO daily_summary (emp_id, last_clock_out, attendance_date)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    last_clock_out = GREATEST(last_clock_out, VALUES(last_clock_out))";
        } else {
            return;
        }

        $stmt = $this->db->conn->prepare($sql);
        $stmt->bind_param("sss", $this->emp_id, $this->current_time, $this->today_date);
        $stmt->execute();
        $stmt->close();
    }
}
    if ($return_var === 0 && !empty($output)) {
        $matched_emp_id = trim(implode("", $output));

        $db = new Database();
        $attendance = new Attendance($db, $matched_emp_id);

        if ($data['action'] === "clock_in") {
            echo $attendance->clockIn();
        } elseif ($data['action'] === "clock_out") {
            echo $attendance->clockOut();
        } else {
            echo "Invalid action.";
        }

        $db->close();
    } else {
        echo "Error: No match found!";
    }

?>