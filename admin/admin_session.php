<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/time_session.php';

$session = new SessionManager(30);
$session->checkInactivity();

if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
$admin_id = $_SESSION['admin_session']['emp_id'];
