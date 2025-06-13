<?php
include '../admin/request.php';
require_once '../admin/register_handling.php';
include 'notification.php';


$db = new Database();
$request = new Request($db);
$emp_id = $_SESSION['emp_id'] ?? null;

$userPendingRequest = $request->getUserPendingRequest($emp_id);
$userPendingCount = $userPendingRequest->num_rows;
$notification = new Notification($request, $emp_id);

if (!isset($_SESSION['viewed_notifications'])) {
    $_SESSION['viewed_notifications'] = false;
}

$showNotificationCount = ($notification->getUnreadCount() > 0 && !$_SESSION['viewed_notifications']);
?>

<nav id="sidebar">
		<a href="#" class="brand">
        <i class='bx bxs-home-smile'></i>
			<span class="text">Menu</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="manager_dashboard.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="manager_history.php">
                <i class='bx bx-git-pull-request'></i>
					<span class="text">History</span>
				</a>
			</li>
		</ul>

		<ul class="side-menu">
			<li>
				<a href="manager_profile.php">
				<i class='bx bxs-user'></i>				
				<span class="text">Profile</span>
				</a>
			</li>
			<li>
				<a href="../logout.php" class="logout">
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
				<a href="#" class="notification">
					<i class='bx bxs-bell'></i>
					<?php if ($showNotificationCount): ?>
        				<span class="num"><?= $notification->getUnreadCount() ?></span>
					<?php endif; ?>
				</a>
			</nav>
			<div class="notification-dropdown" id="notificationDropdown">
			<ul>
        		<?= $notification->renderNotifications(); ?>
			</ul>

			</div>

    <script src="javascripts/script.js"></script>
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
	<script>
		const bellIcon = document.querySelector('.notification');
		const dropdown = document.getElementById('notificationDropdown');
		const notificationCount = document.querySelector('.notification .num');

		bellIcon.addEventListener('click', function (e) {
			e.preventDefault();

			// Toggle dropdown visibility
			dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

			// Mark as viewed when dropdown is opened
			if (dropdown.style.display === 'block') {
				// Hide the count
				if (notificationCount) {
					notificationCount.style.display = 'none';
				}
				
				// Send AJAX request to mark as viewed
				fetch('read_notifications.php')
					.then(response => response.json())
					.then(data => {
						console.log('Notifications marked as viewed');
					})
					.catch(error => {
						console.error('Error:', error);
					});
			}
		});

		// Close dropdown if clicked outside
		document.addEventListener('click', function (e) {
			if (!bellIcon.contains(e.target) && !dropdown.contains(e.target)) {
				dropdown.style.display = 'none';
			}
		});
	</script>