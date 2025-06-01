let capturedImage = '';

function openCamera() {
    document.getElementById('cameraModal').style.display = 'flex'; // Show the modal
    Webcam.set({
        width: 640,
        height: 480,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    Webcam.attach('#my_camera'); // Attach the webcam to the specified element
}

function closeCamera() {
    document.getElementById('cameraModal').style.display = 'none'; // Hide the modal
    Webcam.reset(); // Stop the camera
}

function take_snapshot() {
    Webcam.snap(function(data_uri) {
        // Set the captured image to the same position as the camera
        document.getElementById('my_camera').style.display = 'none'; // Hide the camera feed
        document.getElementById('results').innerHTML = '<img src="' + data_uri + '" id="captured-image" style="width: 640px; height: 480px;"/>'; // Set the image size
        document.getElementById('results').style.display = 'block'; // Show the captured image
        document.getElementById('action-buttons').style.display = 'flex'; // Show action buttons
        
        document.getElementById('snapshot-button').style.display = 'none'; // Hide the snapshot button
        capturedImage = data_uri;

        Webcam.reset(); // Stop the camera
    });
}

function saveImage() {

    document.getElementById('profile_picture').value = capturedImage;
    // Set the src of the circular image to the captured image
    document.getElementById('saved-image').src = capturedImage;

    // Show the circular image
    document.getElementById('circular-image').style.display = 'block';

    // Optionally, you can close the camera modal
    closeCamera();
}

function retryImage() {
    document.getElementById('results').style.display = 'none'; // Hide the captured image
    document.getElementById('action-buttons').style.display = 'none'; // Hide action buttons
    document.getElementById('my_camera').style.display = 'block'; // Show the camera feed again
    Webcam.attach('#my_camera'); // Reattach the webcam

    // Show the Take Snapshot button again
    document.getElementById('snapshot-button').style.display = 'inline'; // Show the snapshot button
}

function cancelImage() {
    closeCamera(); // Close the camera modal
    // Additional logic to handle cancellation can be added here
}

function registerNewFace() {
    const employeeId = document.getElementById('employee-id').value;

    if (!employeeId || !capturedImage) {
        alert("Please enter an Employee ID and capture an image.");
        return;
    }

    // Create FormData object to send data to the server
    const formData = new FormData();
    formData.append("employeeId", employeeId);
    formData.append("profile_picture", capturedImage); // Send the captured image

    fetch('register_user.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(response => {
        alert(response); // Show server response
        closeCamera(); // Optionally, close the camera modal after registration
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Failed to register new face.");
    });
}