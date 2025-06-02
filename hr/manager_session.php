<?php
include_once __DIR__ . '/../time_session.php';
include_once __DIR__ . '/../database.php';

$session = new SessionManager(30);
$session->checkInactivity();

if (!isset($_SESSION['manager_session']) || $_SESSION['manager_session']['role'] !== 'Manager') {
    header('Location: ../login.php');
    exit();
}
$manager_id = $_SESSION['manager_session']['emp_id'];
