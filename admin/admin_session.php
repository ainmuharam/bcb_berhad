<?php
include_once __DIR__ . '/../database.php';
include_once __DIR__ . '/../time_session.php';

$session = new SessionManager(30); // 30 minutes
$session->checkInactivity();

if (
    !isset($_SESSION['admin_session']) || 
    $_SESSION['admin_session']['role'] !== 'Admin' || 
    !isset($_SESSION['admin_authenticated']) || 
    $_SESSION['admin_authenticated'] !== true // ✅ Enforce OTP verification
) {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['admin_session']['emp_id'];
