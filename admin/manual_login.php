<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/bcb_berhad/database.php';
include_once 'admin_session.php';

class ManualInsertLogin {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function getAllLogins($filterDate) {
        $filterDate = $this->conn->real_escape_string($filterDate);

        $sql = "
            SELECT id, date, image, emp_id, time, status, clock 
            FROM manual_login 
            WHERE date = '$filterDate'
            ORDER BY date DESC
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            die("Query Error: " . $this->conn->error);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['image'])) {
                $row['image'] = 'data:image/png;base64,' . base64_encode($row['image']);
            } else {
                $row['image'] = 'placeholder.png'; // Use actual image path if not base64
            }
            $data[] = $row;
        }

        return $data;
    }
}

// Determine the selected date
date_default_timezone_set('Asia/Kuala_Lumpur');
$selectedDate = isset($_GET['date']) && !empty($_GET['date']) 
    ? $_GET['date'] 
    : date('Y-m-d'); // Correct format for SQL filtering

$manualLogin = new ManualInsertLogin();
$logins = $manualLogin->getAllLogins($selectedDate);
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

    <title>Manual Login</title>
</head>
<body>
<?php include 'side_bar.php'; ?>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Manual Login Request</h1>
            <ul class="breadcrumb">
                <li><a href="#">Home</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Manual Login Request</a></li>
            </ul>
        </div>
    </div>

    <form method="GET" action="">
    <div class="form-container">
        <div class="dropdown-date">
            <div class="dropdown-item">
                <label for="from-date">Date:</label>
                <input type="date" id="from-date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
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
            <h3>Manual Login Request (<span>
        <?php 
            date_default_timezone_set('Asia/Kuala_Lumpur');  // Set timezone
            $selectedDate = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            $displayDate = date('d-m-Y', strtotime($selectedDate));
            echo $displayDate;
        ?>
    </span>)</h3>
            </div>

            <table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Image</th>
            <th>Employee ID</th>
            <th>Time</th>
            <th>Clock In/Out</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logins as $login) : ?>
            <tr data-id="<?= $login['id'] ?>"> <!-- Correct placement of data-id -->
                <td><?= htmlspecialchars($login['date']); ?></td>
                <td>
                    <img src="<?= htmlspecialchars($login['image']); ?>" 
                         alt="Employee Image" 
                         onclick="openModal(this.src)" 
                         style="width: 50px; height: 50px; cursor: pointer;">
                </td>

                <td><?= htmlspecialchars($login['emp_id']); ?></td>
                <td><?= htmlspecialchars($login['time']); ?></td>
                <td>
                    <select name="clock" class="clock-select" <?= ($login['status'] === 'Approved' || $login['status'] === 'Rejected') ? 'disabled' : '' ?>>
                        <option value="none" <?= ($login['clock'] === 'none') ? 'selected' : '' ?>>Select</option>
                        <option value="clockIn" <?= ($login['clock'] === 'clockIn') ? 'selected' : '' ?>>Clock In</option>
                        <option value="clockOut" <?= ($login['clock'] === 'clockOut') ? 'selected' : '' ?>>Clock Out</option>
                    </select>
                </td>
                <td>
                    <?php if ($login['status'] === 'Approved' || $login['status'] === 'Rejected') : ?>
                        <span class="<?= $login['status'] === 'Approved' ? 'status-approved' : 'status-rejected' ?>">
                            <?= htmlspecialchars($login['status']) ?>
                        </span>
                    <?php else : ?>
                        <select name="status" class="status-select">
                            <option value="none">Select</option>
                            <option value="Approved">Approve</option>
                            <option value="Rejected">Reject</option>
                        </select>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        </div>
    </div>
</main>

<div id="myModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="img01">
</div>

<script>
    function openModal(src) {
        document.getElementById("myModal").style.display = "block";
        document.getElementById("img01").src = src;
    }

    function closeModal() {
        document.getElementById("myModal").style.display = "none";
    }

    window.onclick = function(event) {
        const modal = document.getElementById("myModal");
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

<script src="javascripts/form.js"></script>
<script>
document.querySelectorAll("tr").forEach(row => {
    const clockSelect = row.querySelector("select[name='clock']");
    const statusSelect = row.querySelector("select[name='status']");

    if (clockSelect && statusSelect) {
        if (clockSelect.value !== "clockIn" && clockSelect.value !== "clockOut") {
            statusSelect.disabled = true;
        }

        clockSelect.addEventListener("change", () => {
            if (clockSelect.value === "clockIn" || clockSelect.value === "clockOut") {
                statusSelect.disabled = false;
            } else {
                statusSelect.disabled = true;
                statusSelect.value = "none";
            }
        });
    }
});

document.querySelectorAll("select[name='status']").forEach(select => {
    select.addEventListener("change", function (e) {
        const selectedValue = e.target.value;
        if (selectedValue === "Approved" || selectedValue === "Rejected") {
            const confirmMsg = `Are you sure you want to ${selectedValue.toLowerCase()} the request?`;
            const confirmed = confirm(confirmMsg);

            if (confirmed) {
                const row = e.target.closest('tr');
                const id = row.dataset.id;
                const clockSelect = row.querySelector("select[name='clock']");
                const clockValue = clockSelect ? clockSelect.value : "";


                if (selectedValue === "Approved" && clockValue === "clockOut") {
                    fetch("check_clock_in.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `id=${id}`
                    })
                    .then(res => res.json())
                    .then(clockData => {
                        if (!clockData.hasClockIn) {
                            alert("Clock In first before Clock Out.");
                            e.target.value = "none";
                            return;
                        } else {
                            proceedApproval(id, selectedValue, clockValue, e.target, clockSelect);
                        }
                    });
                } else {
                    proceedApproval(id, selectedValue, clockValue, e.target, clockSelect);
                }

            } else {
                e.target.value = "none"; // Reset if cancelled
            }
        }
    });
});

function proceedApproval(id, selectedValue, clockValue, selectElement, clockSelect) {
    fetch("status_manual_login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${id}&status=${selectedValue}&clock=${clockValue}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const statusCell = selectElement.parentElement;
            const newSpan = document.createElement("span");
            newSpan.textContent = selectedValue;
            newSpan.className = selectedValue === "Approved" ? "status-approved" : "status-rejected";
            statusCell.innerHTML = '';
            statusCell.appendChild(newSpan);

            if (clockSelect) clockSelect.disabled = true;
        } else {
            alert(data.message);
            selectElement.value = "none";
        }
    });
}
</script>

<script>
document.querySelectorAll("tr").forEach(row => {
    const clockSelect = row.querySelector(".clock-select");
    const statusSelect = row.querySelector(".status-select");

    if (clockSelect && statusSelect) {
        clockSelect.addEventListener("change", () => {
            if (clockSelect.value === "clockIn" || clockSelect.value === "clockOut") {
                statusSelect.disabled = false;
            } else {
                statusSelect.disabled = true;
                statusSelect.value = "none";
            }
        });
    }
});
</script>
</body>
</html>
