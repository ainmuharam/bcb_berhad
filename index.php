<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_session'])) {
    header("Location: admin/admin_dashboard.php");
    exit();
} elseif (isset($_SESSION['manager_session'])) {
    header("Location: hr/manager_dashboard.php");
    exit();
} elseif (isset($_SESSION['user_session'])) {
    header("Location: users/user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Smart Face Attendance System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            position: relative;   /* Added */
            z-index: 1; 
        }

        .container h1 {
            margin-bottom: 30px;
            font-size: 26px;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            font-size: 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-login {
            background-color: #0056b3;
        }

        .btn-login:hover {
            background-color:  #007bff;
        }

        .btn-scan {
            background-color: #1e7e34;
        }

        .btn-scan:hover {
            background-color: #28a745 ;
        }

        .logo {
            max-width: 120px;
            margin-bottom: 20px;
        }

        @media screen and (max-width: 480px) {
            .btn {
                width: 100%;
                margin: 10px 0;
            }
        }
        .bg-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0; /* behind .container */
            opacity: 0.2;
        }
        .bg-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* fill screen nicely */
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="bg-wrapper">
        <img src="images/house.jpg" alt="Background logo" class="bg-image">
        </div>
    <div class="container">
        <img src="images/bcblogo.png" alt="BCB Logo" class="logo">
        <h1>Welcome to Smart Face Attendance System</h1>
        <a href="login.php" class="btn btn-login">Login</a>
        <a href="scan_face.php" class="btn btn-scan">Scan Face</a>
    </div>
</body>
</html>
