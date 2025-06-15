<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
// Include the database connection file
include('config.php');

// Get the last user ID from the database and increment it for the next ID
$sql = "SELECT id FROM users ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
$next_id = 1; // Default value for the first user

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $next_id = $row['id'] + 1; // Increment last ID
}

// Format the ID to 4 digits (e.g., 0001, 0002)
$formatted_id = str_pad($next_id, 4, '0', STR_PAD_LEFT);

$sql = "SELECT id, name, gender, contact, schedule_type, weigth, payment_day, schedule_day, 
               DATE_FORMAT(registered_date, '%d-%m-%Y') as registered_date, 
               DATE_FORMAT(birthday, '%d-%m-%Y') as birthday 
        FROM users";

$result = $conn->query($sql);
$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .search-container {
            margin-bottom: 20px; /* Space below the search bar */
            display: flex; /* Use flexbox for alignment */
            justify-content: flex-start; /* Align items to the start */
        }
        /* Input Styling */
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
        #ageFilter{
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
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
                <h2>Manage Users</h2>
                <!-- Add User Form -->
                <h3>Add New User</h3>
                <form action="add_user.php" method="post">
                    <label for="id">ID</label>
                    <input type="text" id="id" name="id" value="<?php echo $formatted_id; ?>" readonly>

                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>

                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>

                    <label for="contact">Contact</label>
                    <input type="tel" id="contact" name="contact" required pattern="[0-9]{10}" title="Please enter a valid 10-digit telephone number.">

                    <label for="schedule_type">Schedule Type</label>
                    <select id="schedule_type" name="schedule_type" required>
                        <option value="Fitness">Fitness</option>
                        <option value="Cardio">Cardio</option>
                    </select>

                    <label for="weigth">Weigth</label>
                    <input type="number" id="weigth" name="weigth" required>

                    <label for="payment_day">Payment Day</label>
                    <input type="text" id="payment_day" name="payment_day" required>

                    <label for="schedule_day">Schedule Day</label>
                    <input type="text" id="schedule_day" name="schedule_day" required>

                    <label for="birthday">Birthday</label>
                    <input type="date" id="birthday" name="birthday" required>

                    <button type="submit">Add User</button>
                </form>
                <p>Here you can view, edit, or delete users in the system.</p>
                <div class="search-container">
                    <input type="text" id="searchUser" placeholder="Search" onkeyup="searchUsers()" />
                    <!-- Age Filter -->
                    <select id="ageFilter" onchange="filterUsersByAge()">
                        <option value="">All</option>
                        <option value="above18">Above 18</option>
                        <option value="below18">Below 18</option>
                    </select>
                </div>

                <!-- User Table -->
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Schedule Type</th>
                            <th>Weigth</th>
                            <th>Payment Day</th>
                            <th>Schedule Day</th>
                            <th>Birthday</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['gender']; ?></td>
                                <td><?php echo $user['contact']; ?></td>
                                <td><?php echo $user['schedule_type']; ?></td>
                                <td><?php echo $user['weigth']; ?></td>
                                <td><?php echo $user['payment_day']; ?></td>
                                <td><?php echo $user['schedule_day']; ?></td>
                                <td><?php echo $user['birthday']; ?></td>
                                <td><?php echo $user['registered_date']; ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" style="color: red; margin-left: 80px;" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');"><i class="fas fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <script>
                    function searchUsers() {
                        const input = document.getElementById('searchUser');
                        const filter = input.value.toLowerCase();
                        const table = document.querySelector('table');
                        const rows = table.getElementsByTagName('tr');

                        for (let i = 1; i < rows.length; i++) {
                            const cells = rows[i].getElementsByTagName('td');
                            let found = false;

                            // Check both ID (first cell) and Name (second cell)
                            if (cells[0] && cells[1]) { // Ensure both cells exist
                                const idText = cells[0].innerText.toLowerCase();
                                const nameText = cells[1].innerText.toLowerCase();

                                // Check if the input matches either the ID or the Name
                                if (idText.indexOf(filter) > -1 || nameText.indexOf(filter) > -1) {
                                    found = true; // Match found
                                }
                            }

                            // Show or hide the row based on the search result
                            rows[i].style.display = found ? "" : "none";
                        }
                    }

                    document.addEventListener("DOMContentLoaded", function () {
                        calculateAges(); // Calculate and display ages when page loads
                    });

                    function filterUsersByAge() {
    let filter = document.getElementById("ageFilter").value;
    let today = new Date();
    
    document.querySelectorAll("#userTable tbody tr").forEach((row) => {
        let birthdayText = row.cells[8].textContent; // Get birthday from the 9th column (index 8)
        let birthday = new Date(birthdayText.split('-').reverse().join('-')); // Convert birthday to Date object
        let age = today.getFullYear() - birthday.getFullYear();
        let monthDiff = today.getMonth() - birthday.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
            age--;
        }
        
        if (filter === "above18" && age < 18) {
            row.style.display = "none";
        } else if (filter === "below18" && age >= 18) {
            row.style.display = "none";
        } else {
            row.style.display = "";
        }
    });
}
                </script>
            </div>
        </div>
    </div>
</body>
</html>
