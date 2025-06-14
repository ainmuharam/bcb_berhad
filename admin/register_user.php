<?php
include_once 'admin_session.php';
include_once 'depart_handling.php'; // Include the consolidated Department class
require_once 'register_handling.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
$admin_id = $_SESSION['admin_session']['emp_id'];

$database = new Database();
$user = new User($database);
$message = "";

$conn = $database->conn;

$sql = "SELECT COUNT(DISTINCT emp_id) AS total_employees FROM users WHERE deactivation_date IS NULL OR deactivation_date > CURDATE()";
$result = $conn->query($sql);

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

$row = $result->fetch_assoc();
$totalEmployees = $row['total_employees'];

$sql = "
    SELECT DISTINCT
        u.emp_id, 
        u.name, 
        u.email, 
        u.department_id, 
        u.created_at,
        u.deactivation_date,
        u.status
    FROM 
        users u
    ORDER BY 
        u.status DESC, u.name ASC
";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employeeId = preg_replace("/[^a-zA-Z0-9_-]/", '', trim($_POST['employeeId']));
    $name = htmlspecialchars(trim($_POST['name']));
    $department = htmlspecialchars(trim($_POST['department']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
    $role_id = intval($_POST['role_id']);
    $profile_picture = $_POST['profile_picture'] ?? null;


    if (preg_match('/^data:image\/(\w+);base64,/', $profile_picture, $type)) {
        $data = substr($profile_picture, strpos($profile_picture, ',') + 1);
        $data = base64_decode($data);
        if ($data === false) {
            die('Base64 decode failed');
        }

        $sanitizedEmployeeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $employeeId);
        $directory = '/var/www/html/bcb_berhad/employee_picture';
        $fileName = $sanitizedEmployeeId . '.png'; // only filename
        $fullPath = $directory . '/' . $fileName;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        if (file_put_contents($fullPath, $data) === false) {
            die('Failed to save image to server');
        }
    } else {
        die('Please capture user image for register!');
    }

    $user = new User($database); // Assuming $database is your database connection
    $userId = $user->create($employeeId, $name, $department, $email, $password, $fileName, $role_id);
    if ($userId) {
        $stmt = $database->conn->prepare("UPDATE users SET status = 1 WHERE emp_id = ?");
        $stmt->bind_param("s", $employeeId);
        $stmt->execute();
        $message = "New record created successfully";
    } else {
        $stmt = $database->conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $message = "Error: Email address already exists.";
        } else {
            $message = "Error: Employee ID already exists or could not create record.";
        }
    }
}

$conn = $database->conn; // Use the connection from the Database instance
$result = $conn->query($sql); // Execute the query to fetch employee data

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

$department = new Department(); // This will automatically connect to the database
$departmentData = $department->getDepartments(); // Ensure this method is defined correctly

if ($departmentData['total'] === 0) {
    die("No departments found."); // More user-friendly message
}
$departments = [];
$deptSql = "SELECT department_id, department_name FROM departments WHERE status = 1"; // Query only active departments
$deptResult = $conn->query($deptSql);

if ($deptResult === false) {
    die("Error executing query: " . $conn->error);
}

while ($row = $deptResult->fetch_assoc()) {
    $departments[$row['department_id']] = $row['department_name']; // Only active departments
}

// Fetch active roles
$roles = [];
$roleSql = "SELECT role_id, role_name FROM role";
$roleResult = $conn->query($roleSql);

if ($roleResult === false) {
    die("Error fetching roles: " . $conn->error);
}

while ($row = $roleResult->fetch_assoc()) {
    $roles[$row['role_id']] = $row['role_name'];
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="side_navi.css">
    <style>
        #cameraModal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            position: relative; /* Make sure the modal content is positioned relative */
        }

        #my_camera, #results {
            width: 640px;
            height: 480px;
            display: block; 
        }

        select {
            width: 100%; /* Full width */
            padding: 10px; /* Padding inside the dropdown */
            border: 1px solid #ccc; /* Border color */
            border-radius: 5px; /* Rounded corners */
            background-color: #fff; /* Background color */
            font-size: 16px; /* Font size */
            color: #333; /* Text color */
            appearance: none; /* Remove default arrow */
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><polygon points="0,0 10,0 5,5" fill="%23333"/></svg>'); /* Custom arrow */
            background-repeat: no-repeat; /* Prevent repeating */
            background-position: right 10px center; /* Position the arrow */
            background-size: 10px; /* Size of the arrow */
        }

        select:focus {
            border-color: #AAAAAA; /* Change border color on focus */
            outline: none; /* Remove outline */
        }

        /* Style for the options */
        option {
            padding: 10px; 
            background-color: #fff; /* Background color for options */
            color: #333; /* Text color for options */
        }

        #newFaceModal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5); /* Dark background */
    }

    #newFaceModal .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 1000px;
        position: relative;
    }

    </style>

    

    <title>Register User</title>
</head>
<body>
<?php include 'side_bar.php'; ?>
    <main>
        <div class="head-title">
            <div class="left">
                <h1>Register User</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">Register User</a>
                    </li>
                </ul>
                <ul class="box-info">
                    <li>
                        <i class='bx bxs-group'></i>
                        <span class="text">
                            <h3><?php echo htmlspecialchars($totalEmployees); ?></h3>
                            <p>Total Employee</p>
                        </span>
                    </li>
                </ul>
            </div>
            
            <button id="addEmployeeBtn" class="add-employee-btn"><i class='bx bx-plus'></i>New Employee</button>

            <div class="table-data">
                <div class="order">
                <div class="faceRecognition">
                    <button id="changeFaceBtn" class="change-face-recognition" onclick="openNewFaceCamera()">
                        <i class='bx bx-plus'></i> New Face
                    </button>
                </div>
                <div id="newFaceModal">
                    <div class="modal-content">
                        <span id="closePopup" class="close-btn" onclick="closePopup()">&times;</span>
                        <h2>New Face Enrollment</h2>
                        <div id="new_camera"></div>
                        <div id="new_results" style="display: none;"></div>

                        <button type="button" id="new-snapshot-button" class="snapshot-button" onclick="takeNewSnapshot()">Take Snapshot</button>

                        <div id="new-action-buttons" style="display: none; flex-direction: column; align-items: center; margin-top: 20px;">
                            <div style="text-align: center; margin-bottom: 15px;">
                                <label for="employee-id" style="display: block; margin-bottom: 5px;">Employee ID</label>
                                <input type="number" id="employee-id" name="employee-id"
                                    required min="1" max="9999999999"
                                    oninput="if(this.value.length > 10) this.value = this.value.slice(0, 10);"
                                    placeholder="Enter Employee ID"
                                    style="padding: 8px; width: 250px; border: 1px solid #ccc; border-radius: 5px;"
                                    onwheel="this.blur();">
                            </div>
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <div id="new-retry-button" onclick="retryNewImage()"
                                    style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px;">
                                    <i class='bx bx-reset' style="font-size: 24px; color: #17a2b8;"></i>
                                </div>
                                <button id="new-cancel-button" class="cancel-button" onclick="discardNewFace()">Cancel</button>
                                <button id="new-save-button" class="save-button" onclick="registerNewFace()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="head">
                        <h3>Employee</h3>
                        <input type="text" id="searchInput" placeholder="Search by ID or Name" />
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo strpos($message, 'Error:') === 0 ? 'error' : 'success'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Department</th>
                                <th>Date Registered</th>
                                <th>Deactivate Date</th>
                                <th>Setting</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['emp_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($departments[$row['department_id']] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['created_at'] ?? ''))); ?></td>
                            <td>
                                <?php 
                                    echo !empty($row['deactivation_date']) 
                                        ? date('d-m-Y', strtotime($row['deactivation_date'])) 
                                        : 'NULL'; 
                                ?>
                            </td>
                            <td>
                                <button type="button" class="toggle-status-btn btn <?php echo $row['status'] == 1 ? 'btn-success' : 'btn-secondary'; ?>"
                                    data-id="<?php echo $row['emp_id']; ?>">
                                    <?php echo $row['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                </button>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No employees found.</td>
                    </tr>
                <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div id="employeeModal" class="form">
            <div class="form-content">
                <span class="close">&times;</span>
                <h2>New Employee</h2>
                <form method="POST" action="register_user.php" name="addEmployee" enctype="multipart/form-data" onsubmit="return validateForm();">

                    <div class="circular-button" onclick="openCamera()">
                        <i class='bx bxs-camera'></i>
                        <p>Register Face</p>
                    </div>

                    <div id="cameraModal">
                        <div class="modal-content">
                            <i class='bx bx-x close' onclick="closeCamera()" style="cursor: pointer; position: absolute; top: 10px; right: 10px; font-size: 24px; color: #333;"></i> <!-- Close icon -->
                            <h2>Enrollment Employee</h2>
                            <div id="my_camera"></div> <!-- Webcam feed will be attached here -->
                            <div id="results" style="display: none;"></div> <!-- This will show the captured image -->
                            
                            <button type="button" id="snapshot-button" class="snapshot-button" onclick="take_snapshot()">Take Snapshot</button> <!-- Snapshot button -->
                            
                            <div id="action-buttons" style="display: none; justify-content: center; align-items: center; margin-top: 20px;">
                                <div id="retry-button" onclick="retryImage()" style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px;">
                                    <i class='bx bx-reset' style="font-size: 24px; color: #17a2b8;"></i> <!-- Icon only -->
                                </div>
                                <button id="cancel-buttton" class="cancel-button" onclick="cancelImage()">Cancel</button>
                                <button id="submit" class="save-button" onclick="saveImage()">Save</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="profile_picture" id="profile_picture">

                    <div id="circular-image" style="display: none; width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 2px solid #ccc;">
                        <img id="saved-image" src="" alt="Captured Image" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                                    
                                                
                    <label for="employeeId">Employee Id:</label>
                    <input type="number" name="employeeId" id="employeeId" required min="1" max="9999999999" oninput="this.value = this.value.slice(0, 10)">


                    <label for="name">Full Name:</label>
                    <input type="text" name="name" required>

                    <label for="department">Department:</label>
                        <select name="department" required>
                            <option value="">Select Department</option>
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $departmentId => $departmentName): ?>
                                    <option value="<?php echo htmlspecialchars($departmentId); ?>">
                                        <?php echo htmlspecialchars($departmentName); ?>
                                    </option>                       
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No departments available</option>
                            <?php endif; ?>
                        </select>

                    <label for="email">Email:</label>
                    <input type="email" name="email" required>

                    <label for="role_id">Role:</label>
                    <select name="role_id" required>
                            <option value="" disabled selected>Select Role</option>
                        <?php foreach ($roles as $id => $name): ?>
                            <option value="<?= $id ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="password">Password:</label>
                        <div class="input-group">
                            <input type="text" id="password" class="form-control" name="password" required>
                            <div class="input-group-append">
                            </div>
                        </div>
                    <input type="submit" class="submit" value="Save" name="addEmployee">
                </form>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="javascripts/form.js"></script>
        <script src="javascripts/capture.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
        <script src="javascripts/new_capture.js"></script>
        
        <script>
            function validateForm() {
                const employeeId = document.forms["addEmployee"]["employeeId"].value;
                const name = document.forms["addEmployee"]["name"].value;
                const email = document.forms["addEmployee"]["email"].value;
                const password = document.forms["addEmployee"]["password"].value;
                const errorMessage = document.getElementById("error-message");

                // Clear previous error messages
                errorMessage.style.display = "none";
                errorMessage.innerHTML = "";

                // Simple validation checks
                if (!employeeId || !name || !email || !password) {
                    errorMessage.innerHTML = "All fields are required.";
                    errorMessage.style.display = "block";
                    return false; 
                }

                return true; // Allow form submission
            }
        </script>
<script>
$(document).ready(function() {
    $('.toggle-status-btn').click(function() {
        const button = $(this);
        const empId = button.data('id');

        $.ajax({
            url: 'update_status.php',
            method: 'POST',
            data: {
                emp_id: empId
            },
            dataType: 'json', // Important: Expect JSON response
            success: function(response) {
                if (response.success) {
                    const newStatus = response.new_status;
                    button.text(newStatus ? 'Active' : 'Inactive');
                    button.toggleClass('btn-success btn-secondary');

                    // Show success message based on the status
                    if (newStatus === 1) {
                        alert('Successfully activated the user.');
                    } else {
                        alert('Successfully deactivated the user.');
                    }
                } else {
                    alert('Failed to update status: ' + (response.error || 'Unknown error'));
                }
            },
            error: function() {
                alert('AJAX error while updating status.');
            }
        });
    });
});
</script>
<script>
    document.getElementById("changeFaceBtn").addEventListener("click", function () {
        document.getElementById("newFaceModal").style.display = "block";
    });

    // Close the modal
    function closeNewFaceModal() {
        document.getElementById("newFaceModal").style.display = "none";
    }

    // Also close when clicking Cancel
    function discardNewFace() {
        document.getElementById("newFaceModal").style.display = "none";
    }
</script>
<script>
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const id = row.cells[0]?.textContent.toLowerCase();
            const name = row.cells[1]?.textContent.toLowerCase();

            if (id.includes(searchValue) || name.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>



    </main>
</body>
</html>