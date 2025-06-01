<?php
include_once 'admin_session.php';
include 'register_handling.php';
include 'depart_handling.php'; // assumes Department and User class are defined here

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new Database();
    $user = new User($db);
    $department = new Department($db);

    $emp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $employee = $user->getUserById($emp_id);
    if (!$employee) {
        throw new Exception("Employee not found.");
    }

    $departments = $department->getActiveDepartmentsName();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $deptId = intval($_POST['department']);

        if (empty($name) || empty($email) || $deptId <= 0) {
            throw new Exception("Invalid input.");
        }

        if ($user->emailExists($email, $emp_id)) {
            header("Location: edit_profile.php?id=$emp_id&message=Error: The email address is already in use.");
            exit();
        }

        try {
            $user->updateUser($emp_id, $name, $deptId, $email);
            header("Location: user_list.php?message=Profile updated successfully.");
            exit();
        } catch (Exception $e) {
            die("Update error: " . $e->getMessage());
        }

    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@latest/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="side_navi.css">
    <title>Edit Employee Profile</title>
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
            margin-bottom: 15px;
        }
        label {
            flex: 1;
            margin-right: 10px;
            font-size: 14px;
            font-weight: bold;
            min-width: 100px;
        }
        input[type="text"], input[type="email"], select {
            flex: 2;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include 'side_bar.php'; ?>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Edit Employee Profile</h1>
            <ul class="breadcrumb">
                <li><a href="#">Home</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="user_list.php">User List</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="#">Employee Profile</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Edit Profile</a></li>
            </ul>
        </div>
    </div>

    <div class="table-data">
    <div class="order">
        <div class="head">
            <h3>Edit Employee Profile</h3>
        </div>
        <div id="successModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <p><?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : ''; ?></p>
            </div>
        </div>
        <form action="edit_profile.php?id=<?php echo htmlspecialchars($employee['emp_id']); ?>" method="POST">
            <div class="employee-profile">
            <?php
                    $profileImg = !empty($employee['image']) ? "display_image.php?id=" . $employee['emp_id'] : "images/profile.png";
                    ?>
                    <img src="display_image.php?id=<?php echo $employee['emp_id']; ?>"
                    alt="<?php echo htmlspecialchars($employee['name']); ?>'s Profile Picture"
                    class="profile-picture">
                
                <h4>Employee ID: <?php echo htmlspecialchars($employee['emp_id']); ?></h4>
                <input type="hidden" name="emp_id" value="<?php echo htmlspecialchars($employee['emp_id']); ?>">

                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="department">Department:</label>
                    <select id="department" name="department" required>
                        <?php foreach ($departments as $id => $name): ?>
                            <option value="<?php echo htmlspecialchars($id); ?>" 
                                <?php echo (!empty($employee['department_id']) && $id == $employee['department_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="save-button">Save</button>
                    <a href="user_list.php" class="cancel-button">Cancel</a>
                </div>
            </div>
        </form>
        <script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("successModal");
    const close = document.getElementById("closeModal");

    if (modal && <?php echo isset($_GET['message']) ? 'true' : 'false'; ?>) {
        modal.style.display = "block";
    }

    if (close && modal) {
        close.onclick = function () {
            modal.style.display = "none";
        };

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    }
});
</script>
    </div>



</div>

</main>
</body>
</html>
