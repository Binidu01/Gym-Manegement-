<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include the database configuration
include('config.php');

// Get the current date
$currentDate = new DateTime();

// Get the current year
$currentYear = date('Y');

// SQL Query to fetch users with payments in the current year
$sql = "
    SELECT 
        users.id, 
        users.name, 
        users.schedule_type, 
        users.payment_day,
        GROUP_CONCAT(payment.months ORDER BY payment.months) AS months,
        COUNT(payment.months) AS total_months_paid
    FROM 
        users
    LEFT JOIN 
        payment 
    ON 
        users.id = payment.id AND payment.year = '$currentYear'
    GROUP BY
        users.id, users.name, users.schedule_type, users.payment_day
";

$result = $conn->query($sql);

if (!$result) {
    die("Query Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Highlight overdue rows */
        .overdue {
            background-color: yellow !important;
        }
        .search-container {
            margin-bottom: 20px; /* Space below the search bar */
            display: flex; /* Use flexbox for alignment */
            justify-content: flex-start; /* Align items to the start */
        }
        #filterPayments {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .search-container input[type="text"] {
            width: 100%;
            max-width: 400px; /* Restrict the maximum width */
            padding: 10px 15px;
            font-size: 16px;
            border: 1px solid #ddd; /* Light border */
            border-radius: 4px;
            margin-right: 1080px;
        }
        /* Input Focus Effect */
        .search-container input[type="text"]:focus {
            border-color: rgb(0, 0, 0); /* Blue border on focus */
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.2); /* Light blue glow */
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Gym Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="attendance.php"><i class="fas fa-check-circle"></i> Attendance</a></li>
            <li><a href="report.php"><i class="fas fa-chart-line"></i> View Reports</a></li>
            <li><a href="payment.php" class="active"><i class="fas fa-credit-card"></i> View Payments</a></li>
            <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <header>
            <div class="header">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="container">
            <div class="admin-panel">
                <h2>Manage Payments</h2>
                <p>Here you can view, edit, or record Payments in the system.</p>

                <!-- Success Message -->
                <?php
                if (isset($_GET['success'])) {
                    echo "<div class='success'>" . htmlspecialchars($_GET['success']) . "</div>";
                }
                ?>

                <!-- Filter and User Table -->
                <div class="search-container">
                    <input type="text" id="searchBar" placeholder="Search" onkeyup="filterTable()">
                    <select id="filterPayments" onchange="filterPayments()">
                        <option value="all">All Users</option>
                        <option value="overdue">Overdue Payments</option>
                        <option value="paid">Paid Users</option>
                    </select>
                </div>

                <table id="paymentsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Schedule Type</th>
            <th>Payment Day</th>
            <th>Months Paid</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            // Extract payment details
            $paymentDay = (int)$row['payment_day'];
            $monthsPaid = (int)$row['total_months_paid'];
            $monthsString = $row['months'] ?? '';

            // Calculate start and end months and years
            $startMonth = $monthsPaid % 12;
            $endMonth = ($monthsPaid + 1) % 12;
            $startYear = $currentYear;
            $endYear = $currentYear;
            if (($monthsPaid + 1) > 11) {
                $endYear = $currentYear + 1;
            }

            // Build the start date
            $startDateStr = sprintf("%02d-%02d-%d", $paymentDay, $startMonth + 1, $startYear);
            $startDateObj = DateTime::createFromFormat('d-m-Y', $startDateStr);
            $formattedStartDate = $startDateObj ? $startDateObj->format('Y-m-d') : '';

            // Construct a time period string
            $timePeriod = sprintf(
                "%02d/%02d/%d To %02d/%02d/%d", 
                $paymentDay, $startMonth + 1, $startYear,
                $paymentDay, $endMonth + 1, $endYear
            );

            // Determine if the user is overdue
            $isOverdue = false;
            if ($startDateObj && $startDateObj < $currentDate) {
                $isOverdue = true;
            }
            ?>
            <tr class="<?php echo $isOverdue ? 'overdue' : ''; ?>">
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['schedule_type']); ?></td>
                <td><?php echo htmlspecialchars($row['payment_day']); ?></td>
                <td><?php echo htmlspecialchars($monthsString); ?></td>
                <td>
                    <a href="pay.php?pay_id=<?php echo $row['id']; ?>" class="pay-link">Pay</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
            </div>
        </div>
    </div>

    <script>
        function filterTable() {
            const searchInput = document.getElementById('searchBar').value.toLowerCase();
            const table = document.getElementById('paymentsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
                const cells = rows[i].getElementsByTagName('td');
                let match = false;

                // Assuming ID is in column 0 and Name is in column 1
                if (
                    cells[0].textContent.toLowerCase().includes(searchInput) || // ID column
                    cells[1].textContent.toLowerCase().includes(searchInput)    // Name column
                ) {
                    match = true;
                }

                rows[i].style.display = match ? '' : 'none'; // Show or hide the row
            }
        }

        function filterPayments() {
            const filterValue = document.getElementById('filterPayments').value;
            const table = document.getElementById('paymentsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const isOverdue = rows[i].classList.contains('overdue');
                if (filterValue === 'overdue' && !isOverdue) {
                    rows[i].style.display = 'none';
                } else if (filterValue === 'paid' && isOverdue) {
                    rows[i].style.display = 'none';
                } else {
                    rows[i].style.display = '';
                }
            }
        }
    </script>
</body>
</html>
