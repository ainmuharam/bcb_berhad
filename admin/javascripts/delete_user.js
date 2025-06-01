function deleteUser(employeeId) {
    if (confirm("Are you sure you want to delete this user?")) {
        $.ajax({
            url: "deleteuser_handling.php", // The PHP script to handle deletion
            type: "POST",
            data: { employeeId: employeeId },
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert(result.message);
                    location.reload(); 
                } else {
                    alert(result.message);
                }
            },
            error: function () {
                alert("An error occurred while deleting the user.");
            }
        });
    }
}
