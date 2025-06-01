<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/time_session.php';

$session = new SessionManager(30);
$session->checkInactivity();

if (!isset($_SESSION['manager_session']) || $_SESSION['manager_session']['role'] !== 'Manager') {
    header('Location: ../login.php');
    exit();
}
$manager_id = $_SESSION['manager_session']['emp_id'];
