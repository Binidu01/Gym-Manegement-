<?php
// Include the config.php file for database connection
include('config.php');

// Check if the user is logged in and is an admin
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the URL query parameter
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Basic sanitization to ensure it's an integer

    // Fetch user data from the database
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        header("Location: manage_users.php");
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}

// Handle form submission (Update user details)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = $conn->real_escape_string($_POST['name']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $schedule_type = $conn->real_escape_string($_POST['schedule_type']);
    $weigth = $conn->real_escape_string($_POST['weigth']);
    $payment_day = $conn->real_escape_string($_POST['payment_day']);
    $schedule_day = $conn->real_escape_string($_POST['schedule_day']);
    $birthday = $conn->real_escape_string($_POST['birthday']); // Added Birthday

    // Update user data in the database
    $sql = "UPDATE users 
            SET name = '$name', 
                gender = '$gender', 
                contact = '$contact', 
                schedule_type = '$schedule_type', 
                weigth = '$weigth', 
                payment_day = '$payment_day', 
                schedule_day = '$schedule_day',
                birthday = '$birthday'  -- Added Birthday field
            WHERE id = $user_id";

    if ($conn->query($sql) === TRUE) {
        // Redirect to manage users page after successful update
        header("Location: users.php");
        exit();
    } else {
        // Debugging: Display error if the update fails
        echo "Error updating record: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Gym Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_users.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="attendance.php"><i class="fas fa-check-circle"></i> Attendance</a></li>
            <li><a href="report.php"><i class="fas fa-chart-line"></i> View Reports</a></li>
            <li><a href="payment.php"><i class="fas fa-credit-card"></i> View Payments</a></li>
            <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <header>
            <div class="header">
                <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="container">
            <div class="admin-panel">
                <h2>Edit User</h2>
                <p>Edit the details of the user below.</p>

                <!-- Edit User Form -->
                <form action="edit_user.php?id=<?php echo $user['id']; ?>" method="post">
                    <label for="id">ID</label>
                    <input type="text" id="id" name="id" value="<?php echo $user['id']; ?>" readonly>

                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required>

                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>

                    <label for="contact">Contact</label>
                    <input type="tel" id="contact" name="contact" value="<?php echo $user['contact']; ?>" required pattern="[0-9]{10}" title="Please enter a valid 10-digit telephone number.">

                    <label for="schedule_type">Schedule Type</label>
                    <select id="schedule_type" name="schedule_type" required>
                        <option value="Fitness" <?php echo ($user['schedule_type'] == 'Fitness') ? 'selected' : ''; ?>>Fitness</option>
                        <option value="Cardio" <?php echo ($user['schedule_type'] == 'Cardio') ? 'selected' : ''; ?>>Cardio</option>
                    </select>

                    <label for="weigth">Weigth</label>
                    <input type="number" id="weigth" name="weigth" value="<?php echo $user['weigth']; ?>" required>

                    <label for="payment_day">Payment Day</label>
                    <input type="text" id="payment_day" name="payment_day" value="<?php echo $user['payment_day']; ?>" required>

                    <label for="schedule_day">Schedule Day</label>
                    <input type="text" id="schedule_day" name="schedule_day" value="<?php echo $user['schedule_day']; ?>" required>

                    <label for="birthday">Birthday</label>
                    <input type="date" id="birthday" name="birthday" value="<?php echo $user['birthday']; ?>" required>

                    <button type="submit">Update User</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
