<?php
include_once 'database.php';
include_once 'login_handling.php';

$db = new Database();
$login = new Login($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['forgot-email'];

    if ($login->emailExists($email)) {
        echo 'exists';
    } else {
        echo 'not_found';
    }
}
?>
