<?php
include 'user_session.php';
include '../admin/register_handling.php';
include 'users_handling.php';

if (!isset($_GET['emp_id']) || $_GET['emp_id'] != $userId) {
    $queryString = $_SERVER['QUERY_STRING'];

    $params = [];
    if (!empty($queryString)) {
        parse_str($queryString, $params);
    }
    $params['emp_id'] = $userId;
    $redirectUrl = basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);

    header("Location: $redirectUrl");
    exit();
}

$employeeId = $_SESSION['user_session']['emp_id'];
$database = new Database();
$conn = $database->conn;
$attendance = new userAttendance($conn, $employeeId);

// Get month/year filters
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

$loginHistory = $attendance->getLoginHistoryByMonth($month, $year);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../admin/side_navi.css">
    <title>Manual Login History</title>
</head>
<body>
<?php include 'user_sidebar.php'; ?>
<main>
    <div class="head-title">
        <div class="left">
            <h1>Manul Login History</h1>
            <ul class="breadcrumb">
                <li><a href="#">Home</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="user_history.php">History</a></li>
            </ul>
        </div>
        <form method="GET" style="margin-bottom: 20px;">
            <div class= "form-container">
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

                    <button class="filter-button" id="filter-button" type="submit">Filter</button>  
            </div>
        </form>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Manual Login History</h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Clock In/Out</th>
                        <th>Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($loginHistory)) : ?>
                        <?php foreach ($loginHistory as $entry) : ?>
                            <tr>
                               <td><?= htmlspecialchars($entry['date']) ?></td>
                                <td><?= htmlspecialchars($entry['time']) ?></td>
                                <td>
                                    <?php 
                                        if ($entry['clock'] === 'clockIn') {
                                            echo '<span class="clock-in">Clock In</span>';
                                        } elseif ($entry['clock'] === 'clockOut') {
                                            echo '<span class="clock-out">Clock Out</span>';
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $status = strtolower($entry['status']);
                                        if ($status === 'none' || empty($status)) {
                                            echo '<span class="status-pending">Pending</span>';
                                        } elseif ($status === 'approved') {
                                            echo '<span class="status-approved">Approved</span>';
                                        } elseif ($status === 'rejected') {
                                            echo '<span class="status-rejected">Rejected</span>';
                                        } else {
                                            echo htmlspecialchars($entry['status']);
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="4">No login history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="users/javascripts/form.js"></script>
</body>
</html>
