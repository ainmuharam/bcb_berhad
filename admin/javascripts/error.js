document.addEventListener("DOMContentLoaded", function() {
    var modal = document.getElementById("departmentModal");
    var btn = document.getElementById("addDepartmentBtn");
    var span = document.getElementsByClassName("close")[0];

    // Open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Close the modal
    span.onclick = function() {
        modal.style.display = "none";
        document.getElementById('errorMessage').style.display = 'none'; // Clear error message
    }

    // Close the modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            document.getElementById('errorMessage').style.display = 'none'; // Clear error message
        }
    }

    // Handle form submission
    document.getElementById('addDepartmentForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        var formData = new FormData(this);

        fetch('', { // Use the current page for the AJAX request
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Check if the response contains an error message
            if (data.includes("Error:")) {
                // Display the error message in the modal
                document.getElementById('errorMessage').innerText = data;
                document.getElementById('errorMessage').style.display = 'block';
            } else {
                // Show success message
                alert(data);
                modal.style.display = 'none'; // Close modal
                this.reset(); // Reset the form
            }
        })
        .catch(error => console.error('Error:', error));
    });
});