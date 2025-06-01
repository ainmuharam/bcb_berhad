<?php
include_once 'admin_session.php';
include 'register_handling.php'; 
include 'depart_handling.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$admin_id = $_SESSION['admin_session']['emp_id'];
$currentUserId = $admin_id;

$db = new Database(); 
$user = new User($db); 
$department = new Department($db); 
$empId = $_GET['id'] ?? null;

$sql = "SELECT * FROM users WHERE status = 1";  // Only fetch active employees
$result = $db->conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
} else {
    $employees = [];  // No active employees found
}

$deptSql = "SELECT department_id, department_name FROM departments WHERE status = 1"; // Only active departments
$deptResult = $db->conn->query($deptSql); 

if ($deptResult === false) {
    die("Error executing query: " . $db->conn->error); // Use $db->conn to access the connection
}

$departments = [];
if ($deptResult->num_rows > 0) {
    while ($row = $deptResult->fetch_assoc()) {
        $departments[$row['department_id']] = $row['department_name'];
    }
} else {
    die("No departments found."); 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>    <!-- My CSS -->
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
                        <a href="#">Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">User List</a>
                    </li>
                </ul>
            </div>

            <a href="register_user.php">
                <button id="addEmployeeBtn" class="add-employee-btn">
                    <i class='bx bx-plus'></i>Register User
                </button>
            </a>

            <div class="table-data">
    <div class="order">
<div class="employee-row">
    <?php foreach ($employees as $employee): ?>
        <?php if ($employee['emp_id'] == $currentUserId) continue; ?>
        <a href="emp_profile.php?id=<?php echo htmlspecialchars($employee['emp_id']); ?>" class="employee-container">
            <div class="employee-card">
            <?php
                $profileImg = !empty($employee['image']) ? "display_image.php?id=" . $employee['emp_id'] : "images/profile.png";
            ?>
                <img src="<?php echo $profileImg; ?>"
                    alt="<?php echo htmlspecialchars($employee['name']); ?>'s Profile Picture"
                    class="profile-picture">
                <h4><?php echo htmlspecialchars($employee['emp_id']); ?></h4>
                <p><?php echo htmlspecialchars($employee['name']); ?></p>
                <p><?php echo htmlspecialchars($departments[$employee['department_id']] ?? 'N/A'); ?></p>
                <p><?php echo htmlspecialchars($employee['email']); ?></p>
                <div class="button-group">
                    <a href="edit_profile.php?id=<?php echo htmlspecialchars($employee['emp_id']); ?>" class="edit-button">Edit</a>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

        
        </div>
    </div>
</div>

        <script src="javascripts/form.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    </section>


</body>

</html>