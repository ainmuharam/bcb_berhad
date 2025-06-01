
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles.css">
    <title>Manual Login</title>
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
            display: flex;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 1200px;
            position: relative;   /* Added */
            z-index: 1;    
        }

        .camera-container {
            flex: 1;
            text-align: center;
            padding-right: 20px;
            border-right: 2px solid #ddd;
        }

        video, canvas {
            width: 100%; /* Ensures both video and canvas have the same size */
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .input-container {
            flex: 1;
            text-align: center;
            padding-left: 20px;
        }

        input {
            width: 90%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #45a049;
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .capture-container {
            margin-top: 10px;
        }

        #action-buttons {
            display: none;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        #retry-button {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        #retry-button i {
            font-size: 24px;
            color: #17a2b8;
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
</div>
    <div class="bg-wrapper">
        <img src="images/house.jpg" alt="Background logo" class="bg-image">
    </div>
    <div class="container">
        <div class="camera-container">
            <h3>Face Recognition</h3>
            <video id="video" autoplay></video>
            <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
            <div class="capture-container">
                <button id="capture" class="button">Capture Image</button>
            </div>
            <div id="action-buttons">
                <div id="retry-button" onclick="retryImage()">
                    <i class='bx bx-reset'></i> <!-- Retry icon -->
                </div>
            </div>
        </div>

        <div class="input-container">
            <h3>Employee Login</h3>
            <input type="text" id="empID" name="empID" placeholder="Enter Employee ID" required>

            <form id="uploadForm" action="process_ml.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="imageData" id="imageData">
                <input type="hidden" name="employeeID" id="hiddenEmpID">
                <button type="submit" id="submitBtn" class="button">Submit</button>
            </form>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture');
        const submitBtn = document.getElementById('submitBtn');
        const imageDataInput = document.getElementById('imageData');
        const empIDInput = document.getElementById('empID');
        const hiddenEmpID = document.getElementById('hiddenEmpID');
        const actionButtons = document.getElementById('action-buttons'); 
        const retryButton = document.getElementById('retry-button');

        let stream; // Store the camera stream
        let imageCaptured = false; // Track if image is captured

        // Access the camera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(mediaStream => {
                stream = mediaStream;
                video.srcObject = mediaStream;
            })
            .catch(err => {
                console.error("Error accessing the camera: ", err);
            });

        // Adjust canvas size to match video
        video.addEventListener('loadedmetadata', function() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
        });

        // Capture Image
        captureBtn.addEventListener('click', function() {
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = canvas.toDataURL('image/png');

            imageDataInput.value = imageData;
            imageCaptured = true; // Mark image as captured

            // Display the captured image
            canvas.style.display = "block";
            video.style.display = "none"; // Hide video

            // Stop the camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Show retry button
            actionButtons.style.display = "flex";
            captureBtn.style.display = "none"; // Hide Capture button
        });

        // Retry capturing the image
        function retryImage() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(mediaStream => {
                    stream = mediaStream;
                    video.srcObject = mediaStream;

                    // Reset UI
                    canvas.style.display = "none";
                    video.style.display = "block";
                    actionButtons.style.display = "none"; // Hide Retry button
                    captureBtn.style.display = "block"; // Show Capture button
                    imageCaptured = false;
                })
                .catch(err => {
                    console.error("Error accessing the camera: ", err);
                });
        }

        submitBtn.addEventListener("click", function(event) {
            if (empIDInput.value.trim() === "" || !imageCaptured) {
                alert("Please enter Employee ID and capture an image before submitting.");
                event.preventDefault();
            } else {
                hiddenEmpID.value = empIDInput.value;
            }
        });

    </script>

</body>
</html>
