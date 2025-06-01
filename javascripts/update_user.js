function fetchTotalEmployees() {
    $.ajax({
        url: 'register_handling.php', // Path to your PHP file
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#total-employees').text(response.totalEmployees);
            } else {
                console.error('Error fetching total employees:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
}

// Call the function on page load
$(document).ready(function() {
    fetchTotalEmployees();
});
