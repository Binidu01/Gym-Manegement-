<?php
// Fetch user details
include('config.php');  // Include your DB connection

$payId = isset($_GET['pay_id']) ? intval($_GET['pay_id']) : 0; // Ensure it's an integer

if ($payId === 0) {
    die("Error: Invalid Payment ID.");
}

$sqlUser = "SELECT id, name, schedule_type, payment_day FROM users WHERE id = $payId";
$userResult = $conn->query($sqlUser);
if ($userResult === false || $userResult->num_rows == 0) {
    die("Error: User not found.");
}
$user = $userResult->fetch_assoc();

// Payment Date
$paymentDate = date('Y-m-d'); 
$paymentYear = date('Y', strtotime($paymentDate));
$paymentDay = $user['payment_day']; // Use stored payment day from database

// Retrieve months paid
$monthsToPay = isset($_GET['months']) ? explode(',', urldecode($_GET['months'])) : [];
if (empty($monthsToPay)) {
    die("Error: No months selected.");
}

// Get the latest paid month
$lastPaidMonth = end($monthsToPay); 

// Calculate "From" and "To" dates using actual payment day
$fromDate = date("Y-m-$paymentDay", strtotime("$lastPaidMonth $paymentYear +1 month"));
$toDate = date("Y-m-$paymentDay", strtotime("$fromDate +1 month"));

// Determine schedule type
$scheduleType = $user['schedule_type'];

// Calculate total amount
$totalAmount = count($monthsToPay) * ($scheduleType === 'Fitness' ? 1500 : 2500);
$monthsString = implode(', ', $monthsToPay);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
}

.container {
    width: 80%;
    margin: 50px auto;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2, h4 {
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #f1f1f1;
}

.total {
    font-weight: bold;
}

.btn-print,
.btn-back {
    margin-top: 20px;
    display: block;
    width: 100%;
    text-align: center;
    padding: 10px;
    border-radius: 5px;
    font-size: 16px;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-print {
    background-color: #3498db; /* Blue */
    color: white;
}

.btn-print:hover {
    background-color: #2980b9;
}

.btn-print:active {
    background-color: #1f6695;
    transform: translateY(1px);
}

.btn-back {
    background-color: #e74c3c; /* Red */
    color: white;
}

.btn-back:hover {
    background-color: #c0392b;
}

.btn-back:active {
    background-color: #9b2d2b;
    transform: translateY(1px);
}
    </style>
</head>
<body>

<div class="container">
    <h2>Payment Receipt</h2>
    <h4>Gym Management System</h4>
    <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
    <p><strong>Schedule Type:</strong> <?php echo htmlspecialchars($scheduleType); ?></p>
    <p><strong>Payment Date:</strong> <?php echo $paymentDate; ?></p>
    <p><strong>Payment Day:</strong> <?php echo $paymentDay; ?></p>
    <p><strong>From:</strong> <?php echo $fromDate; ?></p>
    <p><strong>To:</strong> <?php echo $toDate; ?></p>

    <table>
        <thead>
        <tr>
            <th>Paid Months</th>
            <th>Total Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo htmlspecialchars($monthsString); ?></td>
            <td>LKR <?php echo number_format($totalAmount, 2); ?></td>
        </tr>
        </tbody>
    </table>

    <a href="#" class="btn-print" onclick="window.print(); return false;">Print Receipt</a>
    <a href="payment.php" class="btn-back">Back</a>
</div>

</body>
</html>
