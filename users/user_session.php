<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/time_session.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/bcb_berhad/login_handling.php';

$session = new SessionManager(30);
$session->checkInactivity();

if (!isset($_SESSION['user_session']) || $_SESSION['user_session']['role'] !== 'User') {
    header('Location: ../login.php');
    exit();
}

$login = new Login(new Database());

$email = $_SESSION['user_session']['email'];
$token = $_SESSION['user_session']['token'] ?? null;

if (!$token || !$login->validateSessionToken($email, $token)) {
    session_destroy();
    header("Location: ../login.php?token_expired=1");
    exit();
}

$userId = $_SESSION['user_session']['emp_id'];
