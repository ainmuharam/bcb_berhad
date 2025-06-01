<?php
session_start();
$_SESSION['viewed_notifications'] = true;
echo json_encode(['status' => 'success']);
?>