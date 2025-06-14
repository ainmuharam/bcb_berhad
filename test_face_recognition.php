<?php
// test_face_recognition.php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Face Recognition Test</title>
    <style>
        #webcam, #canvas { border: 2px solid #333; margin: 10px; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; }
    </style>
</head>
<body>
    <h1>Face Recognition Test</h1>
    
    <div>
        <video id="webcam" width="640" height="480" autoplay></video>
        <canvas id="canvas" width="640" height="480"></canvas>
    </div>
    
    <button id="captureBtn">Test Recognition</button>
    <div id="result" style="margin: 20px; font-size: 18px;"></div>

    <script>
        const webcam = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const resultDiv = document.getElementById('result');

        // Start webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                webcam.srcObject = stream;
            })
            .catch(err => {
                resultDiv.innerHTML = `Error: ${err.message}`;
            });

        // Capture and process image
        document.getElementById('captureBtn').addEventListener('click', () => {
            // Draw webcam frame to canvas
            ctx.drawImage(webcam, 0, 0, canvas.width, canvas.height);
            
            // Get image data
            const imageData = canvas.toDataURL('image/jpeg').split(',')[1];
            
            // Send to server
            fetch('process_face.py', {
                method: 'POST',
                body: JSON.stringify({ image: imageData }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.emp_id) {
                    resultDiv.innerHTML = `✅ Recognized Employee ID: ${data.emp_id}`;
                } else {
                    resultDiv.innerHTML = '❌ No match found';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `Error: ${error.message}`;
            });
        });
    </script>
</body>
</html>