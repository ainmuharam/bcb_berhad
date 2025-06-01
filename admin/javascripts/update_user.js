// Function to fetch total employees and update the UI
function fetchTotalEmployees() {
    $.ajax({
        url: 'register_handlingphp', // Path to your PHP file
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

$(document).ready(function() {
    fetchTotalEmployees();
});
