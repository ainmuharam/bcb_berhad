                document.getElementById('filter-button').addEventListener('click', function () {
                const selectedDate = document.getElementById('from-date').value;
                const department = document.getElementById('department').value;

                const url = new URL(window.location.href);
                if (selectedDate) {
                    url.searchParams.set('date', selectedDate);  // Pass date
                }
                if (department) {
                    url.searchParams.set('department', department);  // Pass department
                } else {
                    url.searchParams.delete('department'); // If "Select All" is selected
                }

                window.location.href = url.toString();
            });