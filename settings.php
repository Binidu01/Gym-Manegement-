<?php
session_start();
// Include the database connection file
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch current admin credentials
$sql = "SELECT username, password FROM admin WHERE id = 1"; // Assuming admin ID is 1
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$adminUsername = $row['username'];
$adminPassword = $row['password'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get new username and password from the form
    $newUsername = trim($_POST['username']);
    $newPassword = trim($_POST['new_password']);

    // Basic validation
    if (!empty($newUsername) && !empty($newPassword)) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the database
        $updateSql = "UPDATE admin SET username = ?, password = ? WHERE id = 1"; // Assuming admin ID is 1
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ss", $newUsername, $hashedPassword);
        $stmt->execute();

        // Update session variables
        $_SESSION['username'] = $newUsername;
        $adminUsername = $newUsername; // Update the variable for display
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styling for settings form */
        .settings-panel {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .settings-form label {
            font-weight: bold;
            color: #333;
        }
        .settings-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            font-size: 1rem;
            color: #555;
        }
        h3 {
            color: #444;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .header span {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .note {
            color: #888;
            font-size: 0.9rem;
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
            <li><a href="payment.php"><i class="fas fa-credit-card"></i> View Payments</a></li>
            <li><a href="settings.php" class="active"><i class ="fas fa-cogs"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <div class="header">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="settings-panel">
            <h3>Change Username and Password</h3>
            <form class="settings-form" method="POST" action="">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($adminUsername); ?>" required>

                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>

                <p class="note">* Current password cannot be displayed for security reasons.</p>

                <button type="submit">Update</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>