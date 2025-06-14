<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles.css">
  <title>Clock In/Out</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100vh;
      margin: 0;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .container {
      text-align: center;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 1;
    }
    .button {
      background-color: #4CAF50;
      border: none;
      color: white;
      padding: 15px 32px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 16px;
      margin: 4px 2px;
      cursor: pointer;
      border-radius: 5px;
      transition: background-color 0.3s;
    }
    .button:hover {
      background-color: #45a049;
    }
    #manual-login {
      background-color: #f44336;
      display: none;
    }
    #manual-login:hover {
      background-color: #d32f2f;
    }
    #message {
      margin-top: 20px;
      font-size: 18px;
      color: green;
    }
    #error-message {
      margin-top: 20px;
      font-size: 18px;
      color: red;
    }
    .bg-wrapper {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      opacity: 0.2;
    }
    .bg-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      pointer-events: none;
    }
  </style>
</head>

<body>
  <div class="bg-wrapper">
    <img src="images/house.jpg" alt="Background logo" class="bg-image">
  </div>

  <div class="container">
    <img src="images/bcblogo.png" alt="BCB logo" class="logo">
    <h1>Smart Face Attendance System</h1>
    <button onclick="location.href='camera_capture.php?action=clock_in'" class="button">Clock In</button>
    <button onclick="location.href='camera_capture.php?action=clock_out'" class="button">Clock Out</button>

    <br>
    <button id="manual-login" class="button" onclick="window.location.href='manual_process.php'">Manual Login</button>
    <p id="message"></p>
    <p id="error-message"></p>
  </div>
</body>
</html>
