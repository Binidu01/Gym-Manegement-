<?php
session_start();

// Include database connection
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch attendance with status for the current month
function fetchAttendanceForMonth($conn, $user_id, $month, $year) {
    $sql = "SELECT DATE_FORMAT(date, '%Y-%m-%d') AS attended_date, status 
            FROM attendance 
            WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $user_id, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[$row['attended_date']] = $row['status'];
    }
    return $attendance;
}

// Fetch all users
function fetchUsers($conn) {
    $sql = "SELECT id, name FROM users";
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

// Current month and year
$current_month = date('m');
$current_year = date('Y');
$user_id = $_GET['user_id'] ?? null;

// Fetch attendance and users
$attendance = $user_id ? fetchAttendanceForMonth($conn, $user_id, $current_month, $current_year) : [];
$users = fetchUsers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        .day {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
            background-color: #f9f9f9;
        }
        .day.present {
            background-color: #4caf50;
            color: white;
        }
        .day.absent {
            background-color: #f44336;
            color: white;
        }
        .day.weekend {
            background-color: #f4f4f4;
        }
        .summary {
            margin-top: 20px;
            font-size: 18px;
        }
        .custom-select-container {
    position: relative;
}

#user_search {
    width: 100%;
    box-sizing: border-box;
    margin-bottom: 5px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#user_id {
    width: 100%;
    box-sizing: border-box;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
    </style>
</head>
<body>
    <div class="main-content">
        <header>
            <div class="header">
                <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="container">
            <h2>View Attendance - <?php echo date('F Y'); ?></h2>

<!-- User Selection -->
<form method="GET">
    <label for="user_id">Search User:</label>
    <input 
        list="user_list" 
        name="user_id" 
        id="user_id" 
        placeholder="Type to search..." 
        value="<?php echo isset($user_id) ? htmlspecialchars($user_id) : ''; ?>" 
        onchange="this.form.submit()"
    />
    <datalist id="user_list">
        <option value="">-- Select User --</option>
        <?php foreach ($users as $user): ?>
            <option value="<?php echo $user['id']; ?>">
                <?php echo htmlspecialchars($user['name']); ?>
            </option>
        <?php endforeach; ?>
    </datalist>
</form>

<script>
document.getElementById('user_id').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const dataList = document.getElementById('user_list');
    const options = dataList.options;

    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        const text = option.value.toLowerCase();
        
        // Show option if it matches the search value
        option.style.display = text.includes(searchValue) ? 'block' : 'none';
    }
});
</script>



            <!-- Calendar -->
            <div class="calendar">
                <?php
                $total_days = 0;
                $present_days = 0;
                $absent_days = 0;

                // Days in the current month
                $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

                if ($user_id):
                    // Generate calendar
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                        $status = $attendance[$date] ?? null;
                        $is_weekend = date('N', strtotime($date)) >= 6; // Saturday or Sunday
                        $class = 'day';

                        if ($status === 'Present') {
                            $class .= ' present';
                            $present_days++;
                        } elseif ($status === 'Absent') {
                            $class .= ' absent';
                            $absent_days++;
                        }

                        $total_days++;

                        echo "<div class='$class'>$day</div>";
                    }
                else:
                    echo "<p>Please select a user to view attendance.</p>";
                endif;
                ?>
            </div>

            <!-- Attendance Summary -->
            <?php if ($user_id): ?>
                <div class="summary">
                    <p><strong>Total Days in Month:</strong> <?php echo $total_days; ?></p>
                    <p><strong>Total Attended Days:</strong> <?php echo $present_days; ?></p>
                    <p><strong>Total Absent Days:</strong> <?php echo $absent_days; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
