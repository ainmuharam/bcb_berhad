<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Camera Capture</title>
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
    .button {
      margin-top: 20px;
      padding: 10px 20px;
      font-size: 16px;
      background: #4CAF50;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .button:hover {
      background: #45a049;
    }
  </style>
</head>
<body>

  <h2><?php echo ucfirst($_GET['action']); ?> - Face Recognition</h2>
  <video id="video" width="320" height="240" autoplay></video>
  <br>
  <button onclick="captureImage()" class="button">Capture & Submit</button>
  <p id="response"></p>

  <script>
    const video = document.getElementById('video');

    // Start webcam stream
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => video.srcObject = stream)
      .catch(err => alert("Camera error: " + err));

    function captureImage() {
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
        document.getElementById('response').innerText = data;
      })
      .catch(err => {
        document.getElementById('response').innerText = "Error: Could not connect to server.";
      });
    }
  </script>
</body>
</html>
