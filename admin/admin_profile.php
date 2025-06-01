<?php
include_once 'admin_session.php';
include 'register_handling.php';
include 'depart_handling.php';

$database = new Database();
$conn = $database->conn;
$user = new User($database);
$department = new Department($database);
$employeeId = $_SESSION['admin_session']['emp_id'] ?? null;
$admin = $user->getUserById($employeeId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowedTypes)) {
        echo "<script>alert('Only JPG and PNG files are allowed.');</script>";
    } elseif ($file['size'] > $maxSize) {
        echo "<script>alert('File size must be less than 2MB.');</script>";
    } else {
        $imageData = file_get_contents($file['tmp_name']); // Get binary data
        $conn = $database->conn;
        $stmt = $conn->prepare("UPDATE users SET image = ? WHERE emp_id = ?");
        $stmt->bind_param("bs", $null, $employeeId); // 'b' for blob, 's' for string
        $stmt->send_long_data(0, $imageData); // Send blob data
        if ($stmt->execute()) {
            echo "<script>alert('Image uploaded successfully!'); window.location.href='admin_profile.php';</script>";
        } else {
            echo "<script>alert('Database error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

if (isset($_POST['remove_image'])) {
    $stmt = $conn->prepare("UPDATE users SET image = NULL WHERE emp_id = ?");
    $stmt->bind_param("s", $employeeId);
    $stmt->execute();
    $stmt->close();

    echo "<script>window.location.href = 'admin_profile.php';</script>";
    exit();
}

if (!$admin) {
    echo "User not found.";
    exit();
}


// Fetch department name
$deptSql = "SELECT department_name FROM departments WHERE department_id = ?";
$deptStmt = $database->conn->prepare($deptSql);
$deptStmt->bind_param("i", $admin['department_id']);
$deptStmt->execute();
$deptResult = $deptStmt->get_result();

if ($deptResult->num_rows > 0) {
    $department = $deptResult->fetch_assoc();
    $departmentName = $department['department_name'];
} else {
    $departmentName = "Department not found";
}

$deptStmt->close();
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
                    $profileImg = !empty($admin['image']) ? "display_image.php?id=" . $admin['emp_id'] : "images/profile.png";
                    ?>
                    <img src="display_image.php?id=<?php echo $admin['emp_id']; ?>"
                    alt="<?php echo htmlspecialchars($admin['name']); ?>'s Profile Picture"
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

                       <h4>Employee ID: <?php echo htmlspecialchars($admin['emp_id']); ?></h4>

                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" value="<?php echo htmlspecialchars($admin['name']); ?>" readonly>
                        </div>

                        <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" value="<?php echo htmlspecialchars($departmentName); ?>" readonly>
                    </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($admin['email']); ?>" readonly>
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