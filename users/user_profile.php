<?php
include_once 'user_session.php';

$employeeId = $_SESSION['user_session']['emp_id'];

if (!isset($_GET['emp_id']) || $_GET['emp_id'] != $employeeId) {
    header("Location: user_profile.php?emp_id=$employeeId");
    exit();
}
include_once '../database.php';
include_once '../admin/register_handling.php'; // Assumes this contains the User class
include_once '../admin/depart_handling.php';   // Assumes this contains the Department class
include_once 'users_handling.php';            // Assumes this contains the userAttendance class

$database = new Database();
$conn = $database->conn;

$user = new User($database);
$attendance = new userAttendance($conn, $employeeId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    try {
        $attendance->uploadProfileImage($_FILES['profile_image']);
        echo "<script>alert('Image uploaded successfully!'); window.location.href='user_profile.php?emp_id={$employeeId}';</script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('".$e->getMessage()."');</script>";
    }
}

if (isset($_POST['remove_image'])) {
    $attendance->removeProfileImage();
    echo "<script>window.location.href = 'user_profile.php?emp_id={$employeeId}';</script>";
    exit();
}

// Get user data
$employee = $user->getUserById($employeeId);
if (!$employee) {
    echo "User not found.";
    exit();
}

// Get department name
$departmentName = $attendance->getDepartmentName($employee['department_id']);
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
                    <?php
                    $profileImg = !empty($employee['image']) ? "../admin/display_image.php?id=" . $employee['emp_id'] : "images/profile.png";
                    ?>
                    <img src="../admin/display_image.php?id=<?php echo $employee['emp_id']; ?>"
                    alt="<?php echo htmlspecialchars($employee['name']); ?>'s Profile Picture"
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
                        <h4>Employee ID: <?php echo htmlspecialchars($employee['emp_id']); ?></h4>

                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" value="<?php echo htmlspecialchars($employee['name']); ?>" readonly>
                        </div>

                        <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" value="<?php echo htmlspecialchars($departmentName); ?>" readonly>
                    </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($employee['email']); ?>" readonly>
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

        <script src="javascripts/form.js"></script>
    </section>


</body>

</html>