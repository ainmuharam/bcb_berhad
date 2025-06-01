<?php
include_once 'manager_session.php';
include '../admin/register_handling.php';
include '../admin/depart_handling.php';
include 'manager_handling.php';

$managerId = $_SESSION['manager_session']['emp_id'];

$database = new Database();
$conn = $database->conn;

$user = new User($database);
$department = new Department($database); 
$attendance = new managerAttendance($conn, $managerId);
$manager = $user->getUserById($managerId);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    try {
        $attendance->uploadProfileImage($_FILES['profile_image']);
        echo "<script>alert('Image uploaded successfully!'); window.location.href='manager_profile.php?emp_id={$employeeId}';</script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('".$e->getMessage()."');</script>";
    }
}

if (isset($_POST['remove_image'])) {
    $attendance->removeProfileImage();
    echo "<script>window.location.href = 'manager_profile.php?emp_id={$employeeId}';</script>";
    exit();
}

$deptSql = "SELECT department_name FROM departments WHERE department_id = ?";
$deptStmt = $database->conn->prepare($deptSql);
$deptStmt->bind_param("i", $manager['department_id']); // Bind the department_id from the employee data
$deptStmt->execute();
$deptResult = $deptStmt->get_result();

if ($deptResult->num_rows > 0) {
    $department = $deptResult->fetch_assoc(); // Fetch the department data
    $departmentName = $department['department_name']; // Get the department name
} else {
    $departmentName = "Department not found"; // Fallback if department is not found
}

$deptStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../admin/side_navi.css">
    <title>User List</title>
</head>
<body>
<?php include 'user_sidebar.php'; ?>
    <main>
        <div class="head-title">
            <div class="left">
                <h1>User Profile</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">Profile</a>
                    </li>
                </ul>
            </div>


            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Profile</h3>
                        
                    </div>
                    
                    <div class="employee-profile">
                    <?
                    $profileImg = !empty($manager['image']) ? "../admin/display_image.php?id=" . $manager['emp_id'] : "images/profile.png";
                    ?>
                    <img src="../admin/display_image.php?id=<?php echo $manager['emp_id']; ?>"
                    alt="<?php echo htmlspecialchars($manager['name']); ?>'s Profile Picture"
                    class="profile-picture">
                        <form method="post" class="remove-form">
                            <button type="submit" name="remove_image" class="remove-button">Remove Image</button>
                        </form>
                        <form method="post" enctype="multipart/form-data" class="upload-form">
                            <div class="upload-row">
                                <input type="file" name="profile_image" accept=".png,.jpg,.jpeg" required class="upload-input">
                                <button type="submit" class="upload-button">Upload</button>
                            </div>
                        </form>  
                        <h4>Employee ID: <?php echo htmlspecialchars($manager['emp_id']); ?></h4>

                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" value="<?php echo htmlspecialchars($manager['name']); ?>" readonly>
                        </div>

                        <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" value="<?php echo htmlspecialchars($departmentName); ?>" readonly>
                    </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($manager['email']); ?>" readonly>
                        </div>

                        <div class="button-group">
                            <a href="change_password.php" class="change-password-button">Change Password</a>
                        </div>
                    </div>
            </div>
        </div>
    <style>
        .profile-picture {
            width: 250px;
            height: 250px;
            object-fit: cover;      /* Crop image nicely */
            border-radius: 50%;     /* Make it circular */
            overflow: hidden;       /* Hide overflow if any */
            display: block;
            margin: 0 auto 20px;
            border: 3px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group {
                display: flex; 
                align-items: center; 
                margin-bottom: 15px; /* Space between form fields */
            }

            label {
                flex: 1; /* Allow label to take up space */
                margin-right: 10px; /* Space between label and input */
                font-size: 14px; 
                font-weight: bold;
                min-width: 100px;  /* Bold labels for emphasis */
            }

            input[type="text"], input[type="email"] {
                flex: 2; 
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box; 
                font-size: 14px; 
            }
    </style>
    </style>

        <script src="javascripts/form.js"></script>
    </section>


</body>

</html>