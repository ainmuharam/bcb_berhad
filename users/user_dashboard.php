<?php
include 'user_session.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/database.php';
require_once 'users_handling.php';
require_once '../admin/register_handling.php';

$database = new Database();
$conn = $database->conn;

$user = new User($database);

$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$fromDate = date('Y-m-d', strtotime("$selectedYear-$selectedMonth-01"));
$lastDayOfMonth = date('Y-m-t', strtotime($fromDate));
$today = date('Y-m-d');
$toDate = ($lastDayOfMonth > $today) ? $today : $lastDayOfMonth;

$attendance = new userAttendance($conn, $userId);

$dailySummaries = $attendance->getDailySummary($fromDate, $toDate);

$dateRange = new DatePeriod(
    new DateTime($fromDate),
    new DateInterval('P1D'),
    (new DateTime($toDate))->modify('+1 day') // Include last day
);

$allDates = [];
foreach ($dateRange as $date) {
    $allDates[] = $date->format('Y-m-d');
}

$existingDates = array_column($dailySummaries, 'attendance_date');
$dailySummariesWithAllDates = [];

$existingDates = is_array($dailySummaries) ? array_column($dailySummaries, 'attendance_date') : [];
$dailySummariesWithAllDates = [];

foreach ($allDates as $date) {
    $index = array_search($date, $existingDates);

    if ($index !== false) {
        $dailySummariesWithAllDates[] = $dailySummaries[$index];
    } else {
        // Insert a blank record for the missing date
        $dailySummariesWithAllDates[] = [
            'attendance_date' => $date,
            'first_clock_in' => null,
            'last_clock_out' => null
        ];
    }
}

$dailySummaries = $dailySummariesWithAllDates;

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
                <form method="GET" action="">
                    <div class="form-container">
                        <input type="hidden" name="emp_id" value="<?= htmlspecialchars($_SESSION['user_session']['emp_id']) ?>">
                        <label for="month">Month:</label>
                        <select name="month" id="month">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($m == ($_GET['month'] ?? date('n'))) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <label for="year">Year:</label>
                        <select name="year" id="year">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= ($y == ($_GET['year'] ?? date('Y'))) ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <div class="dropdown-item">
                            <button class="filter-button" id="filter-button" type="submit">Filter</button>  
                        </div>
                    </div>
                </form>
        </div>
        <form method="GET" action="">
            <input type="hidden" name="emp_id" value="<?= htmlspecialchars($_SESSION['user_session']['emp_id']) ?>">
        </form>

        <div class="table-data">
            <div class="order">
        <div class="head">
            <?php
                date_default_timezone_set('Asia/Kuala_Lumpur');
            ?>
        <h3>Attendance Records </h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Clock-In</th>
                    <th>Status</th>
                    <th>Clock-Out</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($dailySummaries)): ?>
                <?php foreach ($dailySummaries as $record): ?>
                        <?php
                            $date = $record['attendance_date'];
                            $isHoliday = $user->isPublicHoliday($date); // assign here inside loop
                        ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($record['attendance_date']))) ?></td>
                        <td><?= htmlspecialchars($record['first_clock_in'] ?? '-') ?></td>
                        <td>
                            <?php
                                $statusData = $user->getClockInStatus($record['first_clock_in']);
                                echo "<span class='" . htmlspecialchars($statusData['class']) . "'>" . htmlspecialchars($statusData['label']) . "</span>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($record['last_clock_out'] ?? '-') ?></td>
                        <td>
                            <?php
                                $clockOutStatus = $user->getClockOutStatus($record['last_clock_out'] ?? '');

                                if ($clockOutStatus !== null && is_array($clockOutStatus)) {
                                    echo "<span class='" . htmlspecialchars($clockOutStatus['class']) . "'>" . htmlspecialchars($clockOutStatus['label']) . "</span>";
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                $status = $user->getDailyAttendanceStatus($record['attendance_date'], $record['first_clock_in'], $record['last_clock_out']);
                                $statusClass = $user->getStatusClass($status);
                                echo "<span class='" . htmlspecialchars($statusClass) . "'>" . htmlspecialchars($status) . "</span>";
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html> 