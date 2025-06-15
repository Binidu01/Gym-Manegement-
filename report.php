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

// If the Export button is clicked, generate the PDF
if (isset($_POST['export_pdf'])) {
    // Include TCPDF library
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Create new TCPDF instance
    $pdf = new TCPDF();
    $pdf->SetCreator('Gym Management System');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('User Payment Report');
    $pdf->SetSubject('Report');

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(15, 15, 15);

    // Add a page
    $pdf->AddPage();

    // Set title and header info
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'User Payment Report', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Ln(10); // Add a line break

    // Add table header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(15, 10, 'ID', 1, 0, 'C');       // Smaller width for ID
    $pdf->Cell(70, 10, 'Name', 1, 0, 'C');      // Wider for names
    $pdf->Cell(30, 10, 'Schedule', 1, 0, 'C');
    $pdf->Cell(15, 10, 'P_Day', 1, 0, 'C');     // Smaller width for Payment Day
    $pdf->Cell(60, 10, 'Paid Months', 1, 1, 'C'); // Wider for paid months

    // Reset font for table content
    $pdf->SetFont('helvetica', '', 10);

    // Output the data rows for PDF
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Safely retrieve the months string; if null, default to an empty string
            $monthsVal = $row['months'] ?? '';
            // If not empty, explode into an array; otherwise, use an empty array
            $months = ($monthsVal !== '') ? explode(',', $monthsVal) : array();

            // Convert month names to month numbers
            $monthNumbers = [];
            foreach ($months as $month) {
                // Convert the month name to a numeric value (1-12)
                $monthNumber = date('n', strtotime($month));
                $monthNumbers[] = $monthNumber;
            }

            // Check if the current month (numeric) is in the list of paid months
            $isPaid = in_array(date('n'), $monthNumbers);
            $isPaymentDayPast = intval($row['payment_day']) < date('j');
            
            // Determine if overdue: not paid and payment day has passed
            $isOverdue = (!$isPaid && $isPaymentDayPast);

            // Set fill color for overdue rows
            if ($isOverdue) {
                $pdf->SetFillColor(255, 255, 0);  // Yellow for overdue
            } else {
                $pdf->SetFillColor(255, 255, 255);  // White for non-overdue
            }

            // Output table cells
            $pdf->Cell(15, 10, htmlspecialchars($row['id']), 1, 0, 'C', 1);
            $pdf->Cell(70, 10, htmlspecialchars($row['name']), 1, 0, 'C', 1);
            $pdf->Cell(30, 10, htmlspecialchars($row['schedule_type']), 1, 0, 'C', 1);
            $pdf->Cell(15, 10, htmlspecialchars($row['payment_day']), 1, 0, 'C', 1);
            
            // Join the month numbers as a string for display
            $paidMonths = implode(", ", $monthNumbers);
            $pdf->Cell(60, 10, $paidMonths, 1, 1, 'C', 1);
        }
    } else {
        $pdf->Cell(0, 10, 'No records found', 1, 1, 'C');
    }

    // Output PDF to browser
    $pdf->Output('user_payment_report.pdf', 'I'); // 'I' for inline display in browser
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Report | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Highlight overdue rows */
        .overdue {
            background-color: yellow !important;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
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
            <li><a href="report.php" class="active"><i class="fas fa-chart-line"></i> View Reports</a></li>
            <li><a href="payment.php"><i class="fas fa-credit-card"></i> View Payments</a></li>
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
                <h2>Payment Report</h2>

                <!-- Export Button -->
                <form method="POST" action="report.php">
                    <button type="submit" name="export_pdf" class="btn-export">Export to PDF</button>
                </form>

                <!-- User Table -->
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Schedule Type</th>
                            <th>Payment Day</th>
                            <th>Paid Months</th>
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
            </tr>
        <?php endwhile; ?>
    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
