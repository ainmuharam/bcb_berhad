<?php
require_once 'users_handling.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/database.php';

class UserAttendanceDashboard {
    private $userId;
    private $fromDate;
    private $toDate;
    private $attendance;
    private $allDates = [];
    private $leaveDates = [];
    private $recordsByDate = [];

    public function __construct($userId, $fromDate = null, $toDate = null) {
        $db = new Database();
        $this->userId = $userId;
        $this->fromDate = $fromDate ?? date('Y-m-01');
        $this->toDate = $toDate ?? date('Y-m-d');
        $this->attendance = new UserAttendance($db->conn, $userId);
    }

    public function handleDashboardData() {
        $this->generateDateRange();
        $this->fetchLeaveDates();
        $this->fetchAttendanceRecords();
    }

    private function generateDateRange() {
        $start = new DateTime($this->fromDate);
        $end = (new DateTime($this->toDate))->modify('+1 day');
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end);

        foreach ($dateRange as $date) {
            $this->allDates[] = $date->format('Y-m-d');
        }
    }

    private function fetchLeaveDates() {
        $this->leaveDates = $this->attendance->getUserLeaveDates($this->fromDate, $this->toDate) ?? [];
    }

    private function fetchAttendanceRecords() {
        $records = $this->attendance->getUserAttendanceRecords($this->fromDate, $this->toDate);

        foreach ($records as $record) {
            if (!empty($record['first_clock_in'])) {
                $dt = new DateTime($record['first_clock_in'], new DateTimeZone('UTC'));
                $dt->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
                $key = $dt->format('Y-m-d');
            } elseif (!empty($record['last_clock_out'])) {
                $dt = new DateTime($record['last_clock_out'], new DateTimeZone('UTC'));
                $dt->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
                $key = $dt->format('Y-m-d');
            } else {
                $key = null;
            }

            if ($key) {
                $this->recordsByDate[$key] = $record;
            }
        }
    }

    // Getters
    public function getAllDates() { return $this->allDates; }
    public function getLeaveDates() { return $this->leaveDates; }
    public function getRecordsByDate() { return $this->recordsByDate; }
    public function getFromDate() { return $this->fromDate; }
    public function getToDate() { return $this->toDate; }
}


?>