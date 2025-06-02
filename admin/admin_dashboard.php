<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'admin_session.php';
require_once 'register_handling.php';
require_once 'depart_handling.php';
require_once 'daily_report.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

$database = new Database();
$user = new User($database);

$selectedDate = AttendanceReport::getDate();
$selectedDepartment = AttendanceReport::getDepartment();

$department = new Department($database);
$departments = $department->getActiveDepartmentsName();


$reportGenerator = new AttendanceReport($user);
$report = $reportGenerator->generateDailyReport($selectedDate, $selectedDepartment);

$attendanceRecords = $report['attendanceRecords'];
$absentEmployees = $report['absentEmployees'];
$leaveEmployees = $report['leaveEmployees'];
$totalPresent = $report['totalPresent'];
$totalAbsent = $report['totalAbsent'];
$totalEmployees = $report['totalEmployees'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet">
    <!-- My CSS -->
    <link rel="stylesheet" href="side_navi.css">

    <title>DASHBOARDS</title>
</head>
<body>
    <?php include 'side_bar.php'; ?>
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
        </div>

        <div class="form-container">
            <div class="dropdown-date">
                <div class="dropdown-item">
                    <label for="from-date">Date:</label>
                    <input type="date" id="from-date" name="from-date">
                </div>
            </div>

            <div class="dropdowns">
                <div class="dropdown-item">
                    <label for="department">Department:</label>
                    <select id="department">
                        <option value="">Select All</option> <!-- Only once -->
                        <?php foreach ($departments as $id => $name): ?>
                            <option value="<?php echo htmlspecialchars($id); ?>">
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="dropdown-item">
            <button class="filter-button" id="filter-button">Filter</button>  
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
                    <h3><?php echo $totalPresent; ?></h3> <!-- Display total present employees -->
                    <p>Present</p>
                </span>
            </li>
            <li>
                <i class='bx bxs-calendar-x'></i>
                <span class="text">
                <h3><?php echo $totalAbsent; ?></h3> <!-- Display total present employees -->
                <p>Absent</p>
                </span>
            </li>
        </ul>

<div class="table-data">
    <div class="order">
        <div class="head">
            <?php
                date_default_timezone_set('Asia/Kuala_Lumpur');
            ?>
            <h3>Attendance Records (<span>
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
                    <th>Department</th>
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
                            <td><?php echo htmlspecialchars($record['department']); ?></td>
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
                        <th>Department</th>
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
                                <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
                                <th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($leaveEmployees)): ?>
                                <tr><td colspan="4">No employees on leave today.</td></tr>
                            <?php else: ?>
                                <?php foreach ($leaveEmployees as $employee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($employee['emp_id']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <script src="javascripts/filter_dashboard.js"></script>
        </div>
    </main>
</body>
</html>