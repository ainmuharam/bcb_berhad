let capturedNewFaceImage = ''; // For the new face modal's captured image

function openNewFaceCamera() {
    document.getElementById('newFaceModal').style.display = 'flex'; // Show the modal
    Webcam.set({
        width: 640,
        height: 480,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    Webcam.attach('#new_camera'); // Attach the webcam to the new face modal
}

function closePopup() {
    document.getElementById('newFaceModal').style.display = 'none'; // Hide the new face modal
    Webcam.reset(); // Stop the camera
}

function takeNewSnapshot() {
    Webcam.snap(function(data_uri) {
        // Set the captured image to the new position as the camera
        document.getElementById('new_camera').style.display = 'none'; // Hide the camera feed
        document.getElementById('new_results').innerHTML = '<img src="' + data_uri + '" id="new-captured-image" style="width: 640px; height: 480px;"/>'; // Set the image size
        document.getElementById('new_results').style.display = 'block'; // Show the captured image
        document.getElementById('new-action-buttons').style.display = 'flex'; // Show action buttons
        
        document.getElementById('new-snapshot-button').style.display = 'none'; // Hide the snapshot button
        capturedNewFaceImage = data_uri;

        Webcam.reset(); // Stop the camera
    });
}

function retryNewImage() {
    document.getElementById('new_results').style.display = 'none'; // Hide the captured image
    document.getElementById('new-action-buttons').style.display = 'none'; // Hide action buttons
    document.getElementById('new_camera').style.display = 'block'; // Show the camera feed again
    Webcam.attach('#new_camera'); 
    document.getElementById('new-snapshot-button').style.display = 'inline'; // Show the snapshot button
}

function discardNewFace() {
    closeNewFaceModal(); // Close the new face modal
}

function registerNewFace() {
    const employeeId = document.getElementById('employee-id').value;

    if (!employeeId || !capturedNewFaceImage) {
        alert("Please enter Employee ID!");
        return;
    }

    // Send data using JSON format
    fetch('save_new_face.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            employee_id: employeeId,
            image_data: capturedNewFaceImage
        })
    })
    .then(res => res.text())
    .then(response => {
        alert(response);
        Webcam.reset();           // Stop the camera
        closePopup();             // Close the modal
        window.location.href = 'register_user.php';  // ✅ Redirect after success
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Failed to register new face.");
        Webcam.reset();           // Reset even on failure
    });
}




