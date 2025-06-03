<?php
include 'request.php'; 
require_once 'register_handling.php';

$db = new Database();
$user = new User($db);
$requestObj = new Request($db);

$employeeId = $_SESSION['emp_id'] ?? null; 
if ($employeeId) {
    $employee = $user->getUserById($employeeId); 
}

$requests = $requestObj->getPendingManualLoginRequests();
?>


<nav id="sidebar">
		<a href="#" class="brand">
        	<i class='bx bxs-home-smile'></i>
			<span class="text">Menu</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="admin_dashboard.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="register_user.php">
                <i class='bx bxs-registered'></i>
					<span class="text">Register User</span>
				</a>
			</li>
			<li>
				<a href="report.php">
                <i class='bx bxs-report'></i>
					<span class="text">Report</span>
				</a>
			</li>
			<li>
				<a href="user_list.php">
                <i class='bx bxs-user-detail'></i>
					<span class="text">User List</span>
				</a>
			</li>
			<li>
				<a href="manual_login.php">
                <i class='bx bx-git-pull-request'></i>
					<span class="text">Manual Login</span>
				</a>
			</li>
			<li>
				<a href="public_holiday.php">
				<i class='bx bxs-calendar-star'></i>
					<span class="text">Public Holiday</span>
				</a>
			</li>
		</ul>

		<ul class="side-menu">
			<li>
				<a href="user_setting.php">
					<i class='bx bxs-cog' ></i>
					<span class="text">User Setting</span>
				</a>
			</li>
			<li>
				<a href="admin_profile.php">
				<i class='bx bxs-user'></i>				
				<span class="text">Profile</span>
				</a>
			</li>
			<li>
				<a href="/logout.php" class="logout">
					<i class='bx bxs-log-out-circle' ></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
</nav>

    <section id="content">
		<nav>
			<i class='bx bx-menu' ></i>
			<form action="#">
				<div class="form-input">
					<img src="../images/bcblogo.png" alt="BCB Logo" style="height: 45px;">
				</div>
			</form>
				<a href="#" class="notification" id="notificationBell">
					<i class='bx bxs-bell'></i>
					<?php if ($requests->num_rows > 0): ?>
						<span class="num" id="notificationCount"><?php echo $requests->num_rows; ?></span>
					<?php endif; ?>
				</a>
		</nav>
			<div class="notification-dropdown" id="notificationDropdown">
				<ul>
				<?php
				if ($requests->num_rows > 0) {
					while ($row = $requests->fetch_assoc()) {
						$formattedDate = date("j F Y", strtotime($row['date'])); 
						echo "<li><a href='manual_login.php?emp_id=" . htmlspecialchars($row['emp_id']) . "'>" . "(" . $formattedDate . ") Manual login request from Employee ID: " . htmlspecialchars($row['emp_id']) . "</a></li>";
					}
				} else {
					echo "<li><a href='#'>No new requests</a></li>";
				}
				?>
				</ul>
			</div>
    <script src="javascripts/sidebar.js"></script>
	<script src="javascripts/noti_button.js"></script>

	<script>
    const currentPath = window.location.pathname.split('/').pop();

    const links = document.querySelectorAll('#sidebar a');

    links.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.parentElement.classList.add('active'); // Add active class to the parent <li>
        } else {
            link.parentElement.classList.remove('active'); // Remove active class from other <li>
        }
    });
</script>

