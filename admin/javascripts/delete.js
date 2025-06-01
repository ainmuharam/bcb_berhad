function deleteDepartment(button, departmentId) {
    if (confirm("Are you sure you want to delete this department?")) {
        // Create an AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_handling.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Handle the response
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                // Update the total department count
                document.querySelector('.box-info h3').innerText = response.total;

                // Remove the row from the table
                var row = button.closest('tr'); // Get the closest row
                row.parentNode.removeChild(row); // Remove the row from the table
            } else {
                alert("Error deleting department.");
            }
        };

        // Send the request with the department ID
        xhr.send("department_id=" + departmentId);
    }
}



