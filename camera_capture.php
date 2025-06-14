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
    video {
      border: 2px solid #333;
      border-radius: 8px;
    }
    #response {
      font-size: 18px;
      color: green;
      margin-top: 20px;
    }
  </style>
</head>
<body>

  <h2><?php echo ucfirst($_GET['action']); ?> - Real-Time Face Recognition</h2>
  <video id="video" width="320" height="240" autoplay></video>
  <p id="response"></p>

  <script>
    const video = document.getElementById('video');
    const responseText = document.getElementById('response');
    let intervalId;
    let matchFound = false;

    // Start webcam
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => {
        video.srcObject = stream;

        // Start interval capturing every 1 second
        intervalId = setInterval(() => {
          if (!matchFound) captureAndSend();
        }, 1000);
      })
      .catch(err => alert("Camera error: " + err));

    function captureAndSend() {
      const canvas = document.createElement('canvas');
      canvas.width = 320;
      canvas.height = 240;
      canvas.getContext('2d').drawImage(video, 0, 0);

      const imageData = canvas.toDataURL('image/jpeg');
      const action = new URLSearchParams(window.location.search).get('action');

      fetch('run_camera.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image: imageData, action: action })
      })
      .then(response => response.text())
      .then(data => {
        if (data.toLowerCase().includes("success") || data.toLowerCase().includes("matched")) {
          responseText.innerText = data;
          responseText.style.color = "green";
          matchFound = true;

          clearInterval(intervalId);
          stopWebcam();
        } else {
          responseText.innerText = data;
          responseText.style.color = "red";
        }
      })
      .catch(err => {
        responseText.innerText = "Error: Could not connect to server.";
        responseText.style.color = "red";
      });
    }

    function stopWebcam() {
      const stream = video.srcObject;
      const tracks = stream.getTracks();
      tracks.forEach(track => track.stop());
      video.srcObject = null;
    }
  </script>
</body>
</html>
