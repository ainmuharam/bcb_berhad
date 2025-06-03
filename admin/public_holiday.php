<?php
include_once 'admin_session.php';
include_once __DIR__ . '/../database.php';

$database = new Database();
$db = $database->conn;

// Handle the selected month
$selectedMonth = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m');
$month = date('m', strtotime($selectedMonth));
$year = date('Y', strtotime($selectedMonth));

// Query to fetch the holidays for the selected month
$holidayQuery = "SELECT DISTINCT holiday_date, holiday_name FROM public_holiday 
                 WHERE DATE_FORMAT(holiday_date, '%Y-%m') = ?";

$stmt = $db->prepare($holidayQuery);
$stmt->bind_param("s", $selectedMonth);
$stmt->execute();
$result = $stmt->get_result();

$holidays = [];
while ($row = $result->fetch_assoc()) {
    $holidayDate = $row['holiday_date'];
    $holidayName = $row['holiday_name'];
    
    // Store holidays as an array for each date
    if (!isset($holidays[$holidayDate])) {
        $holidays[$holidayDate] = [];
    }
    $holidays[$holidayDate][] = $holidayName;
}

$displayMonth = date('F Y', strtotime($selectedMonth)); // Get the month name for display

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="side_navi.css">
    <title>Public Holiday Calendar</title>

    <style>
        .calendar {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar th, .calendar td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            vertical-align: top;
            height: 100px;
        }
        .calendar th {
            background-color: #f2f2f2;
        }
        .calendar td {
            background-color: #fff;
        }
        .calendar .today {
            background-color: #ffe0b3;
        }

        .popup-form {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            background: white;
            border: 1px solid #ccc;
            padding: 20px;
            z-index: 1000;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.3);
        }
        .popup-form.active {
            display: block;
        }
        .popup-overlay {
            display: none;
            position: fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .popup-overlay.active {
            display: block;
        }
        .day-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: inherit;
        }

        .holiday-text {
            color: green; /* Text color */
            padding: 5px; /* Padding around text */
            border-radius: 5px; /* Rounded corners */
            text-align: center; /* Center the text */
            font-weight: bold; /* Make the text bold */
        }

        .delete-holiday {
            background: red;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-left: 10px;
        }
        .delete-holiday:hover {
            color: darkred;
        }
    </style>
</head>

<body>
<?php include 'side_bar.php'; ?>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Public Holiday</h1>
            <ul class="breadcrumb">
                <li><a href="#">Home</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Public Holiday</a></li>
            </ul>
        </div>
    </div>

    <form method="GET" action="">
        <div class="form-container">
            <div class="dropdown-date">
                <div class="dropdown-item">
                    <label for="date">Select Date:</label>
                    <input type="month" id="from-date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m')) ?>">
                </div>
            </div>
            <div class="dropdown-item">
                <button class="filter-button" id="filter-button">Filter</button>
            </div>
        </div>
    </form>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Calendar (<span>
                    <?php 
                        date_default_timezone_set('Asia/Kuala_Lumpur');
                        $selectedMonth = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m');
                        $displayMonth = date('F Y', strtotime($selectedMonth));
                        echo $displayMonth;
                    ?>
                </span>)</h3>
            </div>

            <table class="calendar">
    <thead>
        <tr>
            <th>Sun</th>
            <th>Mon</th>
            <th>Tue</th>
            <th>Wed</th>
            <th>Thu</th>
            <th>Fri</th>
            <th>Sat</th>
        </tr>
    </thead>
    <tbody>
    <?php
        // Set timezone
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // First day of the month
        $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDayOfMonth);
        $dateToday = date('Y-m-d');

        // Find what day of week first day is (0=Sunday, 6=Saturday)
        $startDayOfWeek = date('w', $firstDayOfMonth);

        $currentDay = 1;
        $currentWeekDay = 0;

        echo "<tr>";

        for ($currentWeekDay = 0; $currentWeekDay < $startDayOfWeek; $currentWeekDay++) {
            echo "<td></td>";
        }

        while ($currentDay <= $daysInMonth) {
            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
            $isToday = ($currentDate == $dateToday) ? "today" : "";
            $holidayName = isset($holidays[$currentDate]) ? $holidays[$currentDate] : ''; // Get the holiday name for the current date
        
            echo "<td class='$isToday'>
                <button class='day-button' onclick='openForm(\"$currentDate\")'>$currentDay</button>";
        
                if (isset($holidays[$currentDate])) {
                    foreach ($holidays[$currentDate] as $holiday) {
                        echo "<div class='holiday-text'>
                                $holiday
                                <button class='delete-holiday' onclick='deleteHoliday(\"$holiday\", \"$currentDate\")'>X</button>
                              </div>"; // Add a delete button next to the holiday name
                    }
                }
            echo "</td>";


            $currentDay++;
            $currentWeekDay++;

            // If Saturday, end row
            if ($currentWeekDay == 7) {
                echo "</tr>";
                if ($currentDay <= $daysInMonth) {
                    echo "<tr>"; // Start new row if days still remain
                }
                $currentWeekDay = 0; // Reset week counter
            }
        }

        // Fill empty cells after last day
        if ($currentWeekDay != 0) {
            for (; $currentWeekDay < 7; $currentWeekDay++) {
                echo "<td></td>";
            }
            echo "</tr>";
        }
    ?>
    </tbody>
</table>


        </div>
    </div>

    <div class="popup-overlay" id="popup-overlay" onclick="closeForm()"></div>

    <div class="popup-form" id="popup-form">
        <h2>Add Public Holiday</h2>
        <form id="holiday-form" method="POST" action="public_holiday.php">
            <input type="hidden" id="holiday-date" name="holiday_date">
            <div>
                <label for="holiday-name">Holiday Name:</label>
                <input type="text" id="holiday-name" name="holiday_name" required>
            </div>
            <div style="margin-top: 10px;">
                <button type="submit" class="save-button">Save</button>
                <button type="button" class="cancel-button" onclick="closeForm()">Cancel</button>
            </div>
        </form>

    </div>
</main>

<script>
function openForm(date) {
    document.getElementById('holiday-date').value = date;
    document.getElementById('popup-form').classList.add('active');
    document.getElementById('popup-overlay').classList.add('active');
}

function closeForm() {
    document.getElementById('popup-form').classList.remove('active');
    document.getElementById('popup-overlay').classList.remove('active');
}
</script>
<script>
document.getElementById('holiday-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent normal form submission

    const formData = {
        holiday_name: document.getElementById('holiday-name').value,
        holiday_date: document.getElementById('holiday-date').value
    };

    fetch('save_public_holiday.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.text()) // Get raw text
    .then(data => {
        console.log("Server response:", data);
        const json = JSON.parse(data); // Parse manually
        if (json.status === "success") {
            alert("Holiday added successfully!");
            closeForm();
            addHolidayToCalendar(json.holiday_date, json.holiday_name);
        } else {
            alert("Failed to add holiday: " + json.message);
        }
    })
    .catch(error => {
        console.error("Fetch error:", error);
        alert("An error occurred: " + error);
    });


function addHolidayToCalendar(holidayDate, holidayName) {
    const cells = document.querySelectorAll('.calendar td');
    cells.forEach(cell => {
        const button = cell.querySelector('.day-button');
        if (button && button.innerText.trim() === holidayDate.split('-')[2]) {
            const existingHoliday = cell.querySelector('.holiday-item');

            // If holiday already exists, just add the new holiday
            if (existingHoliday) {
                const newHolidayLabel = document.createElement('div');
                newHolidayLabel.textContent = holidayName;
                newHolidayLabel.classList.add('holiday-text');
                
                // Create a delete button for the new holiday
                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'X';
                deleteButton.classList.add('delete-holiday');
                deleteButton.onclick = function() {
                    deleteHoliday(holidayName, holidayDate); // Delete this holiday
                };

                // Append the holiday label and delete button to the holiday item
                const holidayDiv = document.createElement('div');
                holidayDiv.classList.add('holiday-item');
                holidayDiv.appendChild(newHolidayLabel);
                holidayDiv.appendChild(deleteButton);

                // Append the holiday div to the cell
                cell.appendChild(holidayDiv);
            } else {
                // If no existing holiday, create a new one
                const holidayLabel = document.createElement('div');
                holidayLabel.textContent = holidayName;
                holidayLabel.classList.add('holiday-text'); // Add the class for styling

                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'X';
                deleteButton.classList.add('delete-holiday');
                deleteButton.onclick = function() {
                    deleteHoliday(holidayName, holidayDate); // Delete this holiday
                };

                // Create a div for the holiday and delete button
                const holidayDiv = document.createElement('div');
                holidayDiv.classList.add('holiday-item');
                holidayDiv.appendChild(holidayLabel);
                holidayDiv.appendChild(deleteButton);

                // Append the holiday div to the cell
                cell.appendChild(holidayDiv);
            }
        }
    });
}

function deleteHoliday(holidayName, holidayDate) {
    if (confirm('Are you sure you want to delete this holiday?')) {
        // Send AJAX request to delete the holiday
        fetch('delete_public_holiday.php', {
            method: 'POST',
            body: JSON.stringify({ holiday_name: holidayName, holiday_date: holidayDate }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("Holiday deleted successfully!");
                location.reload(); // Reload the page to reflect changes
            } else {
                alert("Error deleting holiday: " + data.message);
            }
        })
        .catch(error => {
            alert("An error occurred: " + error);
        });
    }
}





</script>



</body>
</html>
