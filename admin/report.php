<?php
include_once 'admin_session.php';
require_once 'register_handling.php';
require_once 'depart_handling.php';
include 'attendance_summary.php'; 

$nameFilter = $_GET['name'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$db = new Database(); 
$user = new User($db); 
$department = new Department($db); 

$summary = new AttendanceSummary($db);
$departmentName = $summary->getDepartmentNameByName($nameFilter);

$totalHours = 0;
$totalOvertime = 0;
$totalDays = 0;
$presentDays = 0;
$totalOnLeave = 0;

$data = $summary->generateDateRangeWithStatus($nameFilter, $fromDate, $toDate);
foreach ($data as $record) {
    if ($record['status'] === 'On Leave') {
        $totalOnLeave++;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="side_navi.css">

    <title>Report</title>

</head>
<body>
<?php include 'side_bar.php'; ?>
    <main>
        <div class="head-title">
            <div class="left">
                <h1>Report</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="admin_dashboard.php">Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">Report</a>
                    </li>
                </ul>
            </div>
            <div class="btn-download" id="download-pdf" style="cursor: pointer;">
                    <i class='bx bxs-cloud-download'></i>
                    <span class="text">Download PDF</span>
                </div>
                        </div>

                <div class="form-container">
                    <div class="dropdown-date">
                     <div class="dropdown-item">
                        <label for="from-date">From:</label>
                        <input type="date" id="from-date" name="from-date" 
                            value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>" 
                            max="<?= date('Y-m-d') ?>"
                            onchange="updateToDate()">
                    </div>                                   

                    <div class="dropdown-item">
                        <label for="to-date">To:</label>
                        <input type="date" id="to-date" name="to-date" 
                            value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>" 
                            max="<?= date('Y-m-d') ?>">

                    </div>
                </div>

                    <div class="dropdowns">
                        <div class="dropdown-item">
                            <label for="status">Employee:</label>
                            <select id="status" name="name">                                <option value="">Select Employee</option>
                            </select>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <button class="filter-button" id="filter-button">Filter</button>  
                    </div>
                </div>
            
            <div id="report-section">
                <div class="table-data">
                    <div class="order">
                        <div class="head">
                            <?php if (!empty($nameFilter) && !empty($data)): ?>
                                <?php if (!empty($nameFilter)): ?>
                                    <p><strong>Name:</strong> <?= htmlspecialchars($nameFilter) ?></p> 
                                <?php endif; ?>
                                <?php if (!empty($departmentName)): ?>
                                    <p><strong>Department:</strong> <?= htmlspecialchars($departmentName) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock-In</th>
                                    <th>Clock-Out</th>
                                    <th>Hour</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($nameFilter)): ?>
                            <?php if (count($data) > 0): ?>
                                <?php foreach ($data as $row): ?>
                                    <tr>
                                    <td><?= date('d-m-Y', strtotime($row['attendance_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['first_clock_in'] ?? 'null') ?></td>
                                    <td><?= htmlspecialchars($row['last_clock_out'] ?? 'null') ?></td>
                                    <td>
                                    <?php
                                        if (!empty($row['first_clock_in']) && !empty($row['last_clock_out'])) {
                                            $start = new DateTime($row['first_clock_in']);
                                            $end = new DateTime($row['last_clock_out']);
                                            $interval = $start->diff($end);
                                            $minutes = ($interval->h * 60) + $interval->i;
                                            $hours = $minutes / 60;
                                            $totalHours += $hours;

                                            if ($hours > 9) {
                                                $totalOvertime += ($hours - 9);
                                            }

                                            echo number_format($hours, 1);
                                        } else {
                                            echo '0';
                                        }
                                        ?>
                                    </td>
                                    <?php
                                        $attendanceDate = $row['attendance_date'];
                                        $clockIn = $row['first_clock_in'] ?? '';
                                        $clockOut = $row['last_clock_out'] ?? '';

                                        // Get status using the method
                                        $status = $user->getDailyAttendanceStatus($attendanceDate, $clockIn, $clockOut);

                                        // Get corresponding CSS class
                                        $statusClass = $user->getStatusClass($status);
                                    ?>

                                    <td class="<?= htmlspecialchars($statusClass) ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </td>
                                  </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">No data found for <?= htmlspecialchars($nameFilter) ?>.</td></tr>
                                <?php endif; ?>
                            <?php endif; ?>

                        </tbody>
                        </table>
                    </div>

                    </div>
                 </div>
            </div>
         </div>

            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script src="javascripts/filter_date.js"></script>

        <script>
            $(document).ready(function() {
                $('#status').select2({
                placeholder: "Select Employee",
                allowClear: true
                });
            });
            </script>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                fetch('name_employee.php')
                    .then(response => response.json())
                    .then(data => {
                        const select = document.getElementById('status');

                        for (let i = select.options.length - 1; i > 0; i--) {
                            select.remove(i);
                        }

                        data.forEach(name => {
                            const option = document.createElement('option');
                            option.value = name;
                            option.textContent = name;
                            select.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Failed to fetch employee names:', error));
            });
            </script>
            <script>
                document.getElementById('filter-button').addEventListener('click', function () {
                    const name = document.getElementById('status').value;
                    const fromDate = document.getElementById('from-date').value;
                    const toDate = document.getElementById('to-date').value;

                    if ((fromDate || toDate) && !name) {
                        alert('Please select employee !');
                        return; 
                    }

                    if (name && (!fromDate || !toDate)) {
                        alert('Please select date range!');
                        return;
                    }
                    const params = new URLSearchParams();

                    if (name) params.append('name', name);
                    if (fromDate) params.append('from_date', fromDate);
                    if (toDate) params.append('to_date', toDate);

                    window.location.href = `report.php?${params.toString()}`;
                });
            </script>
            <script>
                document.getElementById('download-pdf').addEventListener('click', function () {
                    const element = document.getElementById('pdf-template');

                    // Temporarily make it visible to capture PDF
                    element.style.display = 'block';

                    html2pdf().from(element).set({
                        margin: 0.5,
                        filename: 'attendance-report-<?= strtolower(str_replace(' ', '-', $nameFilter)) ?>.pdf',
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
                    }).save().then(() => {
                        element.style.display = 'none';
                    });
                });
            </script>
            <script>
                function updateToDate() {
                    var fromDate = document.getElementById("from-date").value;
                    var toDateInput = document.getElementById("to-date");
                    
                    if (fromDate) {
                        toDateInput.setAttribute("min", fromDate);
                    }
                }

                window.onload = updateToDate;
            </script>


    </section>
</body>

<div id="pdf-template" style="display:none;">
    <img src="images/bcblogo.png" alt="BCB Logo" style="width: 100px; height: auto; margin-bottom: 10px;"> 
    <h1 style="text-align: center;">Attendance Report</h1>
    <p style= "font-size: 18px;"><strong>Name:</strong> <?= htmlspecialchars($nameFilter) ?></p>
    <p style="font-size: 18px;"><strong>Department:</strong> <?= htmlspecialchars($departmentName) ?></p>
    <p style="font-size: 18px;">
        <strong>Date:</strong>
        <?= date('d-m-Y', strtotime($fromDate)) ?> to <?= date('d-m-Y', strtotime($toDate)) ?>
    </p>

    <table border="1" cellspacing="0" cellpadding="5" style="width:80%; margin: 20px auto; font-size: 18px; text-align: center;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Clock-In</th>
                <th>Clock-Out</th>
                <th>Hours</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?= date('d-m-Y', strtotime($row['attendance_date'])) ?></td>
                <td><?= $row['first_clock_in'] ?? '-' ?></td>
                <td><?= $row['last_clock_out'] ?? '-' ?></td>
                <td>
                    <?php
                        if (!empty($row['first_clock_in']) && !empty($row['last_clock_out'])) {
                            $start = new DateTime($row['first_clock_in']);
                            $end = new DateTime($row['last_clock_out']);
                            $interval = $start->diff($end);
                            $minutes = ($interval->h * 60) + $interval->i;
                            echo number_format($minutes / 60, 1);
                        } else {
                            echo '-';
                        }
                    ?>
                </td>
                <td class="<?=
                    ($row['status'] === 'Present') ? 'status-present' : 
                    (($row['status'] === 'On Leave') ? 'status-al' : 'status-absent')
                ?>">
                    <?= htmlspecialchars($row['status']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


</html>