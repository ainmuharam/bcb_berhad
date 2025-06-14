
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
            position: relative; /* Added */
            overflow: hidden;    /* Optional: hides image overflow */
        }
        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;   /* Added */
            z-index: 1;           /* Puts this above the background */
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
            display: none; /* Initially hidden */
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
    <script>
        function clockInOut(action) {
            let messageElement = document.getElementById("message");
            let errorMessageElement = document.getElementById("error-message");
            let manualLoginButton = document.getElementById("manual-login");

            messageElement.innerText = "Camera is processing...";
            messageElement.style.color = "blue";
            errorMessageElement.innerText = ""; 
            manualLoginButton.style.display = "none"; 

            fetch('run_camera.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=' + action
            })
            .then(response => response.text())
            .then(data => {
                if (data.startsWith("Error:")) {
                    errorMessageElement.innerText = data;
                    errorMessageElement.style.color = "red";
                    manualLoginButton.style.display = "inline-block"; // Show manual login
                } else {
                    messageElement.innerText = data;
                    messageElement.style.color = "green";
                }

                setTimeout(() => {
                    messageElement.innerText = "";
                    errorMessageElement.innerText = "";
                }, 4000);
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessageElement.innerText = "Error: Could not connect to server.";
                errorMessageElement.style.color = "red";
                manualLoginButton.style.display = "inline-block"; // Show manual login

                setTimeout(() => {
                    errorMessageElement.innerText = "";
                }, 4000);
            });
        }
    </script>

</head>
<body>
    </div>
        <div class="bg-wrapper">
            <img src="images/house.jpg" alt="Background logo" class="bg-image">
        </div>
    <div class="container">
        <img src="images/bcblogo.png" alt="BCB logo" class="logo">
        <h1>Smart Face Attendance System</h1>
        <button onclick="clockInOut('clock_in')" class="button">Clock In</button>
        <button onclick="clockInOut('clock_out')" class="button">Clock Out</button>
        <br>
        <button id="manual-login" class="button" onclick="window.location.href='manual_process.php'">Manual Login</button>
        <p id="message"></p>
        <p id="error-message"></p>


</body>
</html>
