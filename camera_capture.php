<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Real-Time Face Recognition</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      text-align: center;
      margin: 0;
      padding: 40px;
    }
    video, canvas {
      border: 2px solid #333;
      border-radius: 8px;
      margin-top: 20px;
    }
    #response {
      font-size: 18px;
      margin-top: 20px;
    }
    .success {
      color: green;
    }
    .error {
      color: red;
    }
  </style>
</head>
<body>

  <h2>
    <?php 
      $action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : 'clock_in'; 
      echo ucfirst($action); 
    ?> - Real-Time Face Recognition
  </h2>

  <video id="video" width="320" height="240" autoplay muted></video>
  <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
  <p id="response"></p>

<script>
  const video = document.getElementById('video');
  const canvas = document.getElementById('canvas');
  const responseText = document.getElementById('response');
  const ctx = canvas.getContext('2d');
  let intervalId;
  let matchFound = false;
  let noMatchCount = 0; // Track failed attempts
  const maxAttempts = 5; // Stop after 5 tries if no match

  const action = new URLSearchParams(window.location.search).get('action') || 'clock_in';

  navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => {
      video.srcObject = stream;

      intervalId = setInterval(() => {
        if (!matchFound) {
          captureAndSend();
        }
      }, 1500);
    })
    .catch(err => {
      responseText.innerText = "Camera error: " + err.message;
      responseText.className = "error";
    });

  function captureAndSend() {
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = canvas.toDataURL('image/jpeg');

    fetch('temp/random.php', {
      method: 'POST',
      body: JSON.stringify({ image: imageData }),
      headers: {
        'Content-Type': 'application/json'
      }
    })
    .then(res => res.text())
    .then(data => {
      responseText.innerText = data;

      if (data.includes("MATCHED:")) {
        responseText.className = "success";
        matchFound = true;
        stopWebcam();
      } else if (data.includes("NO MATCH")) {
        noMatchCount++;
        responseText.className = "error";
        responseText.innerText = "❌ Face not recognized. Attempt " + noMatchCount + " of " + maxAttempts;

        if (noMatchCount >= maxAttempts) {
          stopWebcam();
          responseText.innerText += "\nStopped after too many failed attempts.";
        }
      } else {
        responseText.className = "error";
      }
    })
    .catch(err => {
      responseText.innerText = "Error uploading: " + err.message;
      responseText.className = "error";
    });
  }

  function stopWebcam() {
    const stream = video.srcObject;
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      video.srcObject = null;
    }
  }
</script>


</body>
</html>
