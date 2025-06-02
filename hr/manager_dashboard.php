<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'manager_session.php';
include '../admin/depart_handling.php';
include 'manager_handling.php';

$database = new Database();
$conn = $database->conn;

$user = new User($database);

$manager_id = $_SESSION['manager_session']['emp_id'];
$manager = $user->getUserById($manager_id);


$departmentId = $manager['department_id'];
$attendance = new managerAttendance($conn, $manager_id);

// Get selected date (default: today)
$selectedDate = $_GET['selectedDate'] ?? date('Y-m-d');
$dayOfWeek = date('l', strtotime($selectedDate));
$isPublicHoliday = $user->isPublicHoliday($selectedDate);

$allRecords = $attendance->getAllAttendanceRecords($departmentId);
$totalEmployees = $attendance->getTotalEmployeesInDepartment($departmentId, $selectedDate);

$attendanceRecords = [];
$totalPresent = 0;
$totalAbsent = 0;
$absentEmployees = [];
$leaveEmployees = [];

if ($dayOfWeek !== 'Saturday' && $dayOfWeek !== 'Sunday' && !$isPublicHoliday) {
    $result = $user->getAttendanceRecordsByDate($selectedDate, $departmentId);
    $attendanceRecords = $result['records'] ?? [];
    $totalPresent = $result['total_present'] ?? 0;

    $absentEmployees = $attendance->getAbsentEmployees($selectedDate, $departmentId);
    $leaveEmployees = $attendance->getAnnualLeaves($selectedDate, $departmentId);
    $totalAbsent = count($absentEmployees);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="../admin/side_navi.css">

    <title>DASHBOARDS</title>
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    <main>
        <div class="head-title">
            <div class="left">
                <h1>Dashboard</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">Dashboard</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">Home</a>
                    </li>
                </ul>
            </div>
            <div class="form-container">
                <div class="dropdown-date">
                    <div class="dropdown-item">
                        <label for="from-date">Date:</label>
                        <input type="date" id="from-date" name="from-date">
                    </div>
                </div>
                <div class="dropdown-item">
                <button class="filter-button" id="filter-button">Filter</button>  
                </div>
            </div>
        </div>

<ul class="box-info">
    <li>
        <i class='bx bxs-group'></i>
        <span class="text">
            <h3><?php echo $totalEmployees; ?></h3>
            <p>Total Employee</p>
        </span>
    </li>
    <li>
        <i class='bx bxs-calendar-check'></i>
        <span class="text">
            <h3><?php echo $totalPresent; ?></h3>
            <p>Present</p>
        </span>
    </li>
    <li>
        <i class='bx bxs-calendar-x'></i>
        <span class="text">
            <h3><?php echo $totalAbsent; ?></h3>
            <p>Absent</p>
        </span>
    </li>
</ul>
        <div class="table-data">
            <div class="order">
                <div class="head">
                    <h3>Attendance Records (<span>
                        <?php
                        date_default_timezone_set('Asia/Kuala_Lumpur');
                    ?>
                        <?php 
                            echo !empty($selectedDate) ? date('d-m-Y', strtotime($selectedDate)) : date('d-m-Y'); 
                        ?>
                    </span>) </h3>
                </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Clock-In</th>
                                    <th>Status</th>
                                    <th>Clock-Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                                <tbody>
                                    <?php foreach ($attendanceRecords as $record): ?>
                                        <?php if (!empty($record['clock_in']) || !empty($record['clock_out'])): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['emp_id']); ?></td>
                                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['clock_in']); ?></td>
                                                <td>
                                                    <?php
                                                        $statusData = $user->getClockInStatus($record['clock_in']);
                                                        echo "<span class='" . htmlspecialchars($statusData['class']) . "'>" . htmlspecialchars($statusData['label']) . "</span>";
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['clock_out']); ?></td>
                                                <td>
                                                    <?php
                                                        $statusData = $user->getClockOutStatus($record['clock_out']);
                                                        echo "<span class='" . htmlspecialchars($statusData['class']) . "'>" . htmlspecialchars($statusData['label']) . "</span>";
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
            </div>
                <div class="table-data">
                    <div class="order">
                        <div class="head">
                            <h3>Absent</h3>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($absentEmployees)): ?>
                                    <tr><td colspan="4">No absent employees today.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($absentEmployees as $employee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($employee['emp_id']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                            <div class="table-data">
                            <div class="order">
                                <div class="head">
                                    <h3>Annual Leave</h3>
                                </div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Leave Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($leaveEmployees)): ?>
                                            <tr><td colspan="4">No employees on annual leave today.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($leaveEmployees as $employee): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($employee['emp_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($employee['leave_date']))); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
            </div>
                <script>
                    document.getElementById('filter-button').addEventListener('click', function () {
                        const selectedDate = document.getElementById('from-date').value;

                        const url = new URL(window.location.href);
                        if (selectedDate) {
                            url.searchParams.set('selectedDate', selectedDate);  // matches PHP $_GET key
                        }

                        window.location.href = url.toString();
                    });
                </script>
        </div>
        
</main>
</body>
</html> 