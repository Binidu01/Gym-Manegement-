<?php
session_start();

// Check if the pay_id is passed in the URL
if (!isset($_GET['pay_id'])) {
    die("Error: pay_id not found.");
}

$payId = $_GET['pay_id'];

// Fetch user data including schedule_type
include('config.php');

$sql = "SELECT id, name, schedule_type FROM users WHERE id = $payId";
$result = $conn->query($sql);

// Check if the user exists
if ($result === false || $result->num_rows == 0) {
    die("Error: User not found.");
}

$user = $result->fetch_assoc();

// Fetch already paid months for this user for the current year
$currentYear = date('Y');
$sqlPaidMonths = "SELECT months FROM payment WHERE id = $payId AND year = $currentYear";
$paidMonthsResult = $conn->query($sqlPaidMonths);
$paidMonths = [];
if ($paidMonthsResult->num_rows > 0) {
    while ($row = $paidMonthsResult->fetch_assoc()) {
        $paidMonthsArray = explode(',', $row['months']);
        $paidMonths = array_merge($paidMonths, $paidMonthsArray);
    }
}

// Handle payment submission
if (isset($_POST['pay'])) {
    $monthsToPay = $_POST['months']; // Get selected months from the form
    $year = date('Y'); // Current year
    $paymentDate = date('Y-m-d'); // Current date for payment
    $totalAmount = count($monthsToPay) * ($user['schedule_type'] === 'Fitness' ? 1500 : 2500);
    $monthsString = implode(',', $monthsToPay);

    // Insert payment details into the payment table
    $sql = "INSERT INTO payment (id, months, year, payment_date, amount) 
            VALUES ($payId, '$monthsString', '$year', '$paymentDate', $totalAmount)";
    if (!$conn->query($sql)) {
        die("Error: " . $conn->error);
    }

    // Redirect to receipt page with the necessary information
    header("Location: receipt.php?pay_id=$payId&amount=$totalAmount&months=" . urlencode($monthsString));
    exit();
}


// Fetch payment history for the user
$sqlHistory = "SELECT months, year, payment_date, amount FROM payment WHERE id = $payId ORDER BY payment_date DESC";
$paymentHistory = $conn->query($sqlHistory);

// Check if the query was successful
if ($paymentHistory === false) {
    die("Error: Could not fetch payment history. " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay for Membership | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-form {
            margin-bottom: 30px;
        }

        .payment-history {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            padding: 10px;
        }

        .payment-history table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-history th,
        .payment-history td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .payment-history th {
            background-color: #f1f1f1;
        }

        .payment-form select,
        .payment-form button {
            padding: 10px;
            font-size: 16px;
        }

        .payment-form button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        .payment-form button:hover {
            background-color: #2980b9;
        }

        .success {
            color: green;
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
        }

        select[name="months[]"] {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
        }

        select[name="months[]"]:multiple {
            height: 150px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Pay for Your Membership</h2>
        <p>Hello, <?php echo htmlspecialchars($user['name']); ?>! Here you can view your payment history and pay for your membership.</p>
    </div>

    <!-- Success Message -->
    <?php if (isset($_GET['success'])): ?>
        <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>

    <!-- Payment Form -->
    <div class="payment-form">
        <h3>Select Months to Pay</h3>
        <form method="POST" action="pay.php?pay_id=<?php echo $payId; ?>">
            <label>Select months you want to pay for:</label>
            <select name="months[]" multiple size="6">
                <?php
                $allMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                foreach ($allMonths as $month) {
                    if (!in_array($month, $paidMonths)) {
                        echo "<option value=\"$month\">$month</option>";
                    }
                }
                ?>
            </select>
            <br><br>
            <button type="submit" id="payButton" name="pay" onclick="setButtonName()">Pay Now</button>

        </form>
    </div>

    <!-- Payment History -->
    <div class="payment-history">
        <h3>Your Payment History</h3>
        <table>
            <thead>
                <tr>
                    <th>Months</th>
                    <th>Year</th>
                    <th>Payment Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($paymentHistory->num_rows > 0): ?>
                    <?php while ($row = $paymentHistory->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['months']); ?></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                            <td>LKR <?php echo number_format($row['amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No payment history found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
