<?php
class AttendanceReport {
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function generateDailyReport($date, $departmentId) {
        $dayOfWeek = date('l', strtotime($date));
        $isHoliday = $this->user->isPublicHoliday($date);

        if (in_array($dayOfWeek, ['Saturday', 'Sunday']) || $isHoliday) {
            return [
                'attendanceRecords' => [],
                'absentEmployees' => [],
                'leaveEmployees' => [],
                'totalPresent' => 0,
                'totalAbsent' => 0,
                'totalEmployees' => $this->user->getTotalEmployees($date),
            ];
        }

        $result = $this->user->getAttendanceRecordsByDate($date, $departmentId);

        return [
            'attendanceRecords' => $result['records'] ?? [],
            'absentEmployees' => $this->user->getAbsentEmployees($date, $departmentId),
            'leaveEmployees' => $this->user->getEmployeesOnLeave($date, $departmentId),
            'totalPresent' => $result['total_present'] ?? 0,
            'totalAbsent' => count($this->user->getAbsentEmployees($date, $departmentId)),
            'totalEmployees' => $this->user->getTotalEmployees($date),
        ];
    }

    public static function getDate(): string {
        return $_GET['date'] ?? date('Y-m-d');
    }

    public static function getDepartment(): string {
        return $_GET['department'] ?? '';
    }
}
?>
