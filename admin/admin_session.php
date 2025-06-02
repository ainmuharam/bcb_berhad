<?php
include_once __DIR__ . '/../database.php';
include_once __DIR__ . '/time_session.php';

$session = new SessionManager(30);
$session->checkInactivity();

if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
$admin_id = $_SESSION['admin_session']['emp_id'];
