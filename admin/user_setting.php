<?php
include_once 'admin_session.php';
include 'depart_handling.php';
require_once 'register_handling.php';

$message = "";
$departments = [];
$department = new Department(); // This will automatically connect to the database

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addEmployee'])) {
    $departmentId = $_POST['departmentId'];
    $departmentName = $_POST['departmentName'];

    $message = $department->addDepartment($departmentId, $departmentName);
}

$searchQuery = isset($_POST['search']) ? $_POST['search'] : '';

$departmentData = $department->getDepartments($searchQuery); // Use Department to fetch departments
$departments = $departmentData['departments'] ?? []; // Use null coalescing operator to avoid undefined variable
$totalDepartments = $departmentData['total'] ?? 0; // Default to 0 if not set
$totalActiveDepartments = $department->getActiveDepartments(); // This should return the active count
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

    <title>Register User</title>
</head>
<body>
<?php include 'side_bar.php'; ?>
    <main>
        <div class="head-title">
            <div class="left">
                <h1>User Setting</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">User Setting</a>
                    </li>
                </ul>
                <ul class="box-info">
                    <li>
                        <i class='bx bxs-buildings'></i>
                        <span class="text">
                         <h3><?php echo htmlspecialchars($totalActiveDepartments); ?></h3>
                        <p>Total Department</p>
                        </span>
                    </li>
                </ul>
            </div>
            <button id="addDepartmentBtn" class="add-department-btn"><i class='bx bx-plus'></i> New Department</button>
                <div class="table-data">
                    <div class="order">
                    <div class="head">
                        <h3>Department</h3>
                        <input type="text" id="searchInput" placeholder="Search by ID or Name" onkeyup="searchDepartments()">
                    </div>
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo strpos($message, 'Error:') === 0 ? 'error' : 'success'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Date Created</th>
                                <th>Setting</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dept['department_id']); ?></td>
                                    <td><?php echo htmlspecialchars($dept['department_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($dept['created_at']))); ?></td>
                                    <td>
                                        <button type="button" class="toggle-status-btn btn <?php echo $dept['status'] == 1 ? 'btn-success' : 'btn-secondary'; ?>"
                                            data-id="<?php echo $dept['department_id']; ?>">
                                            <?php echo $dept['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    </div>
                </div>
            </div>
            <div id="departmentModal" class="form">
            <div class="form-content">
                <span class="close">&times;</span>
                <h2>New Department</h2>
                <form method="POST" action="" name="addDepartment" enctype="multipart/form-data">
                    <label for="departmentId">Department ID:</label>
                    <input type="number" name="departmentId" required>
                    <label for="departmentName">Department Name:</label>
                    <input type="text" name="departmentName" required>  
                    <input type="submit" class="submit" value="Save" name="addEmployee">
                </form>
            </div>
        </div>

        <script src="javascripts/search.js"></script>
        <script src="javascripts/add_depart.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.toggle-status-btn').click(function() {
                    const button = $(this);
                    const departmentId = button.data('id');

                    $.ajax({
                        url: 'update_department_status.php',
                        method: 'POST',
                        data: { department_id: departmentId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                const newStatus = response.new_status;
                                const activeCount = response.active_count;

                                // Update button label and class
                                if (newStatus == 1) {
                                    button.text('Active');
                                    button.removeClass('btn-secondary').addClass('btn-success');
                                    alert('Successfully activated the department.');
                                } else {
                                    button.text('Inactive');
                                    button.removeClass('btn-success').addClass('btn-secondary');
                                    alert('Successfully deactivated the department.');
                                }

                                // 🔄 Update total department count in UI
                                $('.box-info h3').text(activeCount);
                            } else {
                                alert('Failed to update status: ' + (response.error || 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('AJAX error while updating department status.');
                        }
                    });
                });
            });

            </script>

        

    </section>


</body>

</html>