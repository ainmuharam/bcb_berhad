Webcam.set({
    width: 640, 
    height: 480,
    image_format: 'jpeg',
    jpeg_quality: 90
});
Webcam.attach('#my_camera');

function take_snapshot() {
    Webcam.snap(function(data_uri) {
        document.getElementById('results').innerHTML = '<img src="'+data_uri+'" id="captured-image"/>';
        document.getElementById('results').style.display = 'block'; // Show the captured image
        document.getElementById('action-buttons').style.display = 'block'; // Show action buttons
        document.getElementById('snapshot-button').style.display = 'none'; // Hide the snapshot button
        Webcam.reset(); // Stop the camera
        document.getElementById('retry-button').style.display = 'block'; // Show retry button
    });
}

function goBack() {
    window.history.back(); // Go back to the previous page
}

function saveImage() {
    const image = document.getElementById('captured-image').src;

    // Send the image data to the PHP script using AJAX
    $.ajax({
        type: "POST",
        url: "save_image.php", // The PHP script to handle the image saving
        data: { image: image },
        success: function(response) {
            alert(response); // Show success message or handle response
        },
        error: function() {
            alert("Error saving image.");
        }
    });
}

function cancelImage() {
    document.getElementById('results').innerHTML = ''; // Clear the results
    document.getElementById('results').style.display = 'none'; // Hide the captured image
    document.getElementById('action-buttons').style.display = 'none'; // Hide action buttons
    document.getElementById('retry-button').style.display = 'none'; // Hide retry button
    document.getElementById('snapshot-button').style.display = 'block'; // Show the snapshot button again
    Webcam.attach('#my_camera'); // Restart the camera
}

function retryImage() {
    document.getElementById('results').innerHTML = ''; // Clear the previous image
    document.getElementById('results').style.display = 'none'; // Hide the captured image
    document.getElementById('action-buttons').style.display = 'none'; // Hide action buttons
    document.getElementById('retry-button').style.display = 'none'; // Hide retry button
    document.getElementById('snapshot-button').style.display = 'block'; // Show the snapshot button again
    Webcam.attach('#my_camera'); // Restart the camera for a new snapshot
}