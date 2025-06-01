
<?php
include_once 'admin_session.php';
include 'register_handling.php'; 
include 'depart_handling.php';
include 'attendance_summary.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$allowedReferer = 'user_list.php';

$db = new Database(); 
$user = new User($db); 
$department = new Department($db);
$attendance = new AttendanceSummary($db);

$emp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$employee = $user->getUserById($emp_id); 

$deptSql = "SELECT department_name FROM departments WHERE department_id = ?";
$stmt = $db->conn->prepare($deptSql);
$stmt->bind_param("i", $employee['department_id']);
$stmt->execute();
$result = $stmt->get_result();
$department = $result->fetch_assoc();
$departmentName = $department ? $department['department_name'] : 'N/A';

$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$totalHours = 0;
if (!empty($fromDate) && !empty($toDate) && $emp_id > 0) {
    $totalHours = $attendance->calculateTotalHours($emp_id, $fromDate, $toDate);
}


$leaveDates = [];
$leaveQuery = "SELECT leave_date FROM annual_leave WHERE emp_id = ?";
$stmtLeave = $db->conn->prepare($leaveQuery);
$stmtLeave->bind_param("i", $emp_id);
$stmtLeave->execute();
$leaveResult = $stmtLeave->get_result();

while ($leaveRow = $leaveResult->fetch_assoc()) {
    $leaveDates[] = date('Y-m-d', strtotime($leaveRow['leave_date']));
}



$attendanceQuery = "SELECT attendance_date, first_clock_in, last_clock_out 
                    FROM daily_summary 
                    WHERE emp_id = ?";

$params = [$emp_id];
$types = "i";

if (!empty($fromDate)) {
    $attendanceQuery .= " AND attendance_date >= ?";
    $params[] = $fromDate;
    $types .= "s";
}

if (!empty($toDate)) {
    $attendanceQuery .= " AND attendance_date <= ?";
    $params[] = $toDate;
    $types .= "s";
}


$attendanceQuery .= " ORDER BY attendance_date ASC";

$stmt = $db->conn->prepare($attendanceQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$attendanceResult = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="side_navi.css">

    <title>User List</title>
</head>
<body>
<?php include 'side_bar.php'; ?>
    <main>
        <div class="head-title">
            <div class="left">
                <h1>User List</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">User</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="user_list.php">User List</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">Employee Profile</a>
                    </li>
                </ul>
            </div>
            <form method="GET" action="emp_profile.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($emp_id) ?>">
                <div class="dropdown-date">
                    <div class="dropdown-item">
                        <label for="from-date">From:</label>
                        <input type="date" id="from-date" name="from_date" 
                            value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>" 
                            max="<?= date('Y-m-d') ?>" onchange="updateToDate()">
                    </div>
                    <div class="dropdown-item">
                        <label for="to-date">To:</label>
                        <input type="date" id="to-date" name="to_date" 
                            value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>" 
                            max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="dropdown-item">
                        <button class="filter-button" type="submit">Filter</button>  
                    </div>
                </div>
            </form>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Employee Profile</h3>
                    </div>
                    <div class="employee-row">
                        <div class="employee-profile">
                        <?php
                        $profileImg = !empty($employee['image']) ? "display_image.php?id=" . $employee['emp_id'] : "images/profile.png";
                        ?>
                        <img src="display_image.php?id=<?php echo $employee['emp_id']; ?>"
                        alt="<?php echo htmlspecialchars($employee['name']); ?>'s Profile Picture"
                        class="profile-picture"> 
                        <p><strong>ID: </strong> <?php echo htmlspecialchars($employee['emp_id']); ?></p>                           
                        <p><strong>Name: </strong> <?php echo htmlspecialchars($employee['name']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($departmentName); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                        <div class="button-group">
                        <a href="edit_profile.php?id=<?php echo htmlspecialchars($employee['emp_id']); ?>" class="edit-button">Edit</a>          
                                </div>
                        </div>
                     <div class="attendance-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock-in</th>
                                    <th>Clock-out</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                if (!empty($fromDate) && !empty($toDate)) {
                                    $startDate = new DateTime($fromDate);
                                    $endDate = new DateTime($toDate);
                                    $endDate->modify('+1 day'); // include end date

                                    $attendanceData = [];
                                    while ($row = $attendanceResult->fetch_assoc()) {
                                        $attendanceData[$row['attendance_date']] = $row;
                                    }

                                    foreach (new DatePeriod($startDate, new DateInterval('P1D'), $endDate) as $date) {
                                        $currentDate = $date->format('Y-m-d');
                                        $row = $attendanceData[$currentDate] ?? null;
                                        ?>
                                        <tr>
                                            <td><?= date('d-m-Y', strtotime($currentDate)) ?></td>
                                            <td><?= $row['first_clock_in'] ?? '-' ?></td>
                                            <td><?= $row['last_clock_out'] ?? '-' ?></td>
                                            <td>
                                                <?php
                                                if (!empty($row['first_clock_in']) && !empty($row['last_clock_out'])) {
                                                    $start = new DateTime($row['first_clock_in']);
                                                    $end = new DateTime($row['last_clock_out']);
                                                    $interval = $start->diff($end);
                                                    echo $interval->format('%h:%I');
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            
                                            <td>
                                                <?php 
                                                    $dayOfWeek = (new DateTime($currentDate))->format('N'); // 6=Sat, 7=Sun
                                                    $isWeekend = ($dayOfWeek >= 6);
                                                    $isHoliday = $user->isPublicHoliday($currentDate);

                                                    if (!empty($row['first_clock_in'])) {
                                                        echo '<span class="status-present">Present</span>';
                                                    } elseif (in_array($currentDate, $leaveDates)) {
                                                        echo '<span class="status-al">On Leave</span>';
                                                    } elseif ($isHoliday) {
                                                        echo '<span class="status-holiday">Public Holiday</span>';
                                                    } elseif ($isWeekend) {
                                                        echo '<span class="status-weekend">Weekend</span>';
                                                    } else {
                                                        echo '<span class="status-absent">Absent</span>';
                                                    }

                                                ?>
                                            </td>

                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center;">Please filter by date to view attendance data.</td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                </div> 
            </div>
        </div>
<style>
    .attendance-table {
        margin-left:20px;
        flex-grow: 1; 
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }
    
    </style>


        <script src="javascripts/form.js"></script>
        <script src="javascripts/capture.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
                function updateToDate() {
                    var fromDate = document.getElementById("from-date").value;
                    var toDateInput = document.getElementById("to-date");
                    
                    if (fromDate) {
                        toDateInput.setAttribute("min", fromDate);
                    }
                }

                window.onload = updateToDate;
            </script>
        

    </section>


</body>

</html>