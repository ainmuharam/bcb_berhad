<!DOCTYPE html>
<html>
<head>
    <title>Webcam Capture and Upload</title>
</head>
<body>

<h2>Webcam Capture</h2>

<video id="video" width="640" height="480" autoplay></video>
<br>
<button id="snap">Capture</button>
<button id="upload" disabled>Upload</button>
<br>
<canvas id="canvas" width="640" height="480" style="display:none;"></canvas>

<p id="status"></p>

<script>
// Access webcam
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const snapBtn = document.getElementById('snap');
const uploadBtn = document.getElementById('upload');
const status = document.getElementById('status');

navigator.mediaDevices.getUserMedia({ video: true })
.then(stream => {
    video.srcObject = stream;
})
.catch(err => {
    alert("Error accessing webcam: " + err);
});

// Capture snapshot
snapBtn.addEventListener('click', () => {
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    uploadBtn.disabled = false;
    status.textContent = "Snapshot taken! Ready to upload.";
});

// Upload snapshot
uploadBtn.addEventListener('click', () => {
    canvas.toBlob(blob => {
        const formData = new FormData();
        formData.append('image', blob, 'snapshot.png');

        fetch('testupload.php', { // PHP script to send to Python backend
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            status.textContent = "Server response: " + data;
        })
        .catch(err => {
            status.textContent = "Upload failed: " + err;
        });
    }, 'image/png');
});
</script>

</body>
</html>
