<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
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
        #message, #error-message {
            margin-top: 20px;
            font-size: 18px;
        }
        #message { color: green; }
        #error-message { color: red; }
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
        video, canvas {
            margin-top: 15px;
            display: none;
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
        <button onclick="startCamera()" class="button">Clock In</button>
        <button onclick="clockOut()" class="button">Clock Out</button>
        <br>
        <video id="webcam" width="320" height="240" autoplay></video>
        <canvas id="snapshot" width="320" height="240"></canvas>
        <br>
        <button id="capture" class="button" style="display:none;" onclick="captureAndSend()">Capture & Submit</button>
        <button id="manual-login" class="button" onclick="window.location.href='manual_process.php'">Manual Login</button>
        <p id="message"></p>
        <p id="error-message"></p>
    </div>

    <script>
        let video = document.getElementById("webcam");
        let canvas = document.getElementById("snapshot");
        let captureBtn = document.getElementById("capture");

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.style.display = "block";
                    captureBtn.style.display = "inline-block";
                    video.srcObject = stream;
                })
                .catch(err => {
                    document.getElementById("error-message").innerText = "Unable to access webcam.";
                });
        }

        function captureAndSend() {
            const ctx = canvas.getContext("2d");
            canvas.style.display = "none"; // Hide if not needed visually
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = canvas.toDataURL("image/jpeg");

            document.getElementById("message").innerText = "Processing...";
            document.getElementById("message").style.color = "blue";

            fetch('run_camera.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'clock_in',
                    image: imageData
                })
            })
            .then(response => response.text())
            .then(data => {
                if (data.startsWith("Error:")) {
                    document.getElementById("error-message").innerText = data;
                    document.getElementById("manual-login").style.display = "inline-block";
                } else {
                    document.getElementById("message").innerText = data;
                }

                setTimeout(() => {
                    document.getElementById("message").innerText = "";
                    document.getElementById("error-message").innerText = "";
                }, 4000);
            })
            .catch(err => {
                document.getElementById("error-message").innerText = "Server error. Try again.";
                document.getElementById("manual-login").style.display = "inline-block";
            });
        }

        function clockOut() {
            fetch('run_camera.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=clock_out'
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById("message").innerText = data;
            })
            .catch(err => {
                document.getElementById("error-message").innerText = "Server error during clock out.";
            });
        }
    </script>
</body>
</html>
