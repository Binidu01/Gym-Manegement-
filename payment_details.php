<?php
session_start();
include('config.php');


// Fetch all users' payment details
$sql = "
SELECT 
    u.id, 
    u.name, 
    u.schedule_type, 
    u.payment_day, 
    p.payment_date, 
    p.months
FROM users u
LEFT JOIN payment p ON u.id = p.id  
ORDER BY u.id, p.payment_date DESC";

$result = $conn->query($sql);
$users = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

$year = date("Y"); // Current year
$currentDate = date("Y-m-d");
$paymentDetails = [];

// Valid months for checking paid months (all lowercase)
$validMonths = [
    'january', 'february', 'march', 'april', 'may', 'june', 
    'july', 'august', 'september', 'october', 'november', 'december'
];

foreach ($users as $user) {
    // Skip this record if the months field is empty or only whitespace
    if (empty(trim($user['months']))) {
        continue;
    }
    
    $userId = $user['id'];
    $paymentDay = (int)$user['payment_day'];
    
    // Convert the months string into an array and remove any empty values
    $monthsPaidArray = array_filter(array_map('trim', explode(',', $user['months'])));
    
    if (!isset($paymentDetails[$userId])) {
        $paymentDetails[$userId] = [
            'name' => $user['name'],
            'schedule_type' => $user['schedule_type'],
            'payment_date' => $user['payment_date'],
            'payment_day' => $paymentDay,
            'total_months_paid' => 0,
            'is_overdue' => false
        ];
    }
    
    // Count valid paid months for the current year (if payment_date is provided)
    if (!empty($user['payment_date'])) {
        $paymentYear = date("Y", strtotime($user['payment_date']));
        if ($paymentYear == $year) {
            foreach ($monthsPaidArray as $month) {
                $trimmedMonth = strtolower($month);
                if (in_array($trimmedMonth, $validMonths)) {
                    $paymentDetails[$userId]['total_months_paid']++;
                }
            }
        }
    }
    
    // Calculate the next due start date based on the number of months paid.
    // Here we assume that if a user has paid for N months, the next due period starts in month (N % 12) + 1.
    $monthsPaid = $paymentDetails[$userId]['total_months_paid'];
    $startMonth = $monthsPaid % 12; // 0 for January, 1 for February, etc.
    
    // Build the due date string in "day-month-year" format
    $startDateStr = sprintf("%02d-%02d-%d", $paymentDay, $startMonth + 1, $year);
    $startDateObj = DateTime::createFromFormat('d-m-Y', $startDateStr);
    
    if ($startDateObj) {
        $formattedStartDate = $startDateObj->format('Y-m-d');
        // Determine if the payment is overdue (current date is past the due date)
        $isOverdue = ($currentDate > $formattedStartDate);
        $paymentDetails[$userId]['is_overdue'] = $isOverdue;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Gym Management System</title>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Highlight overdue rows */
    .overdue {
      background-color: yellow !important;
    }
    .search-container {
      margin-bottom: 20px; /* Space below the search bar */
      display: flex;
      justify-content: flex-start;
    }
    #filterPayments {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
    }
    .search-container input[type="text"] {
      width: 100%;
      max-width: 400px;
      padding: 10px 15px;
      font-size: 16px;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-right: 1080px;
    }
    /* Input Focus Effect */
    .search-container input[type="text"]:focus {
      border-color: rgb(0, 0, 0);
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body>
  <div class="main-content">
      <div class="search-container">
        <input type="text" id="searchBar" placeholder="Search" onkeyup="filterTable()">
        <select id="filterPayments" onchange="filterPayments()">
          <option value="all">All Users</option>
          <option value="overdue">Overdue Payments</option>
          <option value="paid">Paid Users</option>
        </select>
      </div>
      <div class="payment-section">
        <h2>All Users' Payment Details</h2>
        <table>
          <thead>
            <tr>
              <th>User ID</th>
              <th>Name</th>
              <th>Payment Type</th>
              <th>Payment Day</th>
              <th>Payment Date</th>
              <th>Time Period</th>
            </tr>
          </thead>
          <tbody id="paymentTable">
            <?php foreach ($paymentDetails as $userId => $userDetails) { ?>
              <tr class="<?php echo $userDetails['is_overdue'] ? 'overdue' : ''; ?>">
                <td><?php echo htmlspecialchars($userId); ?></td>
                <td><?php echo htmlspecialchars($userDetails['name']); ?></td>
                <td><?php echo htmlspecialchars($userDetails['schedule_type']); ?></td>
                <td><?php echo htmlspecialchars($userDetails['payment_day']); ?></td>
                <td><?php echo htmlspecialchars($userDetails['payment_date']); ?></td>
                <td>
                  <?php
                  // Calculate the time period for display based on total months paid
                  $monthsPaid = $userDetails['total_months_paid'];
                  $startMonth = $monthsPaid % 12;
                  $endMonth = ($monthsPaid + 1) % 12;
                  
                  // Adjust the end year if the period wraps past December
                  $startYear = $year;
                  $endYear = $year;
                  if (($monthsPaid + 1) > 11) {
                    $endYear = $year + 1;
                  }
                  
                  // Build the start date string (for display or debugging)
                  $startDateStr = sprintf("%02d-%02d-%d", $userDetails['payment_day'], $startMonth + 1, $startYear);
                  $startDateObj = DateTime::createFromFormat('d-m-Y', $startDateStr);
                  $formattedStartDate = $startDateObj ? $startDateObj->format('Y-m-d') : '';
                  
                  // Construct a time period string
                  $timePeriod = sprintf(
                    "%02d/%02d/%d To %02d/%02d/%d", 
                    $userDetails['payment_day'], $startMonth + 1, $startYear,
                    $userDetails['payment_day'], $endMonth + 1, $endYear
                  );
                  
                  echo $timePeriod;
                  ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function filterTable() {
      var searchInput = document.getElementById('searchBar').value.toLowerCase();
      var rows = document.getElementById('paymentTable').getElementsByTagName('tr');
      for (var i = 0; i < rows.length; i++) {
        var name = rows[i].cells[1].textContent.toLowerCase();
        var userId = rows[i].cells[0].textContent.toLowerCase();
        if (name.includes(searchInput) || userId.includes(searchInput)) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    }

    function filterPayments() {
      var selectedFilter = document.getElementById('filterPayments').value;
      var rows = document.getElementById('paymentTable').getElementsByTagName('tr');
      for (var i = 0; i < rows.length; i++) {
        if (selectedFilter === 'overdue' && !rows[i].classList.contains('overdue')) {
          rows[i].style.display = 'none';
        } else if (selectedFilter === 'paid' && rows[i].classList.contains('overdue')) {
          rows[i].style.display = 'none';
        } else {
          rows[i].style.display = '';
        }
      }
    }

    // ----- Auto-Refresh When Year Changes -----
    // Store the server's year in a JavaScript variable.
    var serverYear = <?php echo $year; ?>;
    // Check every minute if the client's current year differs.
    setInterval(function() {
      var clientYear = new Date().getFullYear();
      if (clientYear !== serverYear) {
        location.reload();  // Reload the page if the year has changed.
      }
    }, 60000); // 60000 milliseconds = 1 minute
  </script>
</body>
</html>
