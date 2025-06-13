<?php
include_once 'manager_session.php';
include '../admin/register_handling.php';

$db = new Database(); 
$user = new User($db);

$employeeId = $_SESSION['manager_session']['emp_id'];

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$employeeId) {
        $_SESSION['error_message'] = "Unauthorized access.";
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All fields are required.";
    } else {
        $message = $user->changePassword($employeeId, $current_password, $new_password, $confirm_password);

        if (stripos($message, 'successfully') !== false) {
            $_SESSION['success_message'] = $message;
        } else {
            $_SESSION['error_message'] = $message;
        }
    }

    header("Location: change_password.php");
    exit;
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
    <link rel="stylesheet" href="style.css">

    <title>User List</title>
</head>
<body>
<?php include 'user_sidebar.php'; ?>
    <main>
        <div class="head-title">
            <div class="left">
                <h1>Edit Profile</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="manager_profile.php">Profile</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="change_password.php">Change Password</a>
                    </li>
                </ul>
            </div>


            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Change Password</h3>
                        <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="success-message" style="color: green;">
                            <?php
                            echo htmlspecialchars($_SESSION['success_message']);
                            unset($_SESSION['success_message']); // Clear the message after displaying
                            ?>
                        </div>
                    <?php elseif (isset($_SESSION['error_message'])): ?>
                        <div class="error-message" style="color: red;">
                            <?php
                            echo htmlspecialchars($_SESSION['error_message']);
                            unset($_SESSION['error_message']); // Clear the message after displaying
                            ?>
                        </div>
                    <?php endif; ?>
                    </div>
                    
                    <div class="employee-profile">
                        <form action="change_password.php" method="POST">      
                        <div class="form-group">
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" class="form-control" name="current_password" required autocomplete="current-password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <input type="password" id="new_password" class="form-control" name="new_password" required autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password:</label>
                            <input type="password" id="confirm_password" class="form-control" name="confirm_password" required autocomplete="new-password">
                        </div>
                        <div class="button-group">
                            <button type="submit" class="save-button">Save</button>
                            <a href="manager_profile.php" class="cancel-button">Cancel</a>
                        </div>
                        </form>
                    </div>  
            </div>
        </div>
    <style>

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