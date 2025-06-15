<?php
session_start();

// Include database connection
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}



// Function to fetch users (with optional search)
function fetchUsers($conn, $search_user_id = '')
{
    if (!empty($search_user_id)) {
        $sql = "SELECT id, name FROM users WHERE id LIKE ? OR name LIKE ?";
        $stmt = $conn->prepare($sql);
        $search_param = '%' . $search_user_id . '%';
        $stmt->bind_param('ss', $search_param, $search_param);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        $sql = "SELECT id, name FROM users";
        return $conn->query($sql);
    }
}

// Function to fetch attendance records for a specific date
function fetchAttendance($conn, $date)
{
    $sql = "SELECT u.id, u.name, a.status 
            FROM users u
            LEFT JOIN attendance a 
            ON u.id = a.user_id AND a.date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $date);
    $stmt->execute();
    return $stmt->get_result();
}

// Handle AJAX request to fetch attendance
if (isset($_POST['fetchAttendance']) && $_POST['fetchAttendance'] === 'true') {
    $date = $_POST['attendance_date'] ?? date('Y-m-d');
    $result = fetchAttendance($conn, $date);

    $response = [];
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    echo json_encode($response);
    exit();
}

// Handle AJAX request to fetch users based on search
if (isset($_POST['searchUsers'])) {
    $search_user_id = $_POST['search_user_id'] ?? '';
    $attendance_date = $_POST['attendance_date'] ?? date('Y-m-d'); // Get the selected date
    $result = fetchUsers($conn, $search_user_id);

    $response = [];
    while ($row = $result->fetch_assoc()) {
        // Fetch attendance status for the searched user
        $attendance_result = fetchAttendance($conn, $attendance_date);
        $attendance_status = 'Absent'; // Default status
        while ($attendance_row = $attendance_result->fetch_assoc()) {
            if ($attendance_row['id'] == $row['id']) {
                $attendance_status = $attendance_row['status'];
                break;
            }
        }
        $row['status'] = $attendance_status; // Add status to user data
        $response[] = $row;
    }
    echo json_encode($response);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['fetchAttendance']) && !isset($_POST['searchUsers'])) {
    $attendance_date = $_POST['attendance_date'];

    foreach ($_POST['attendance_status'] as $user_id => $status) {
        $sql = "INSERT INTO attendance (user_id, date, status) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE status = VALUES(status)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $user_id, $attendance_date, $status);
        $stmt->execute();
    }

    echo json_encode(['success' => 'Attendance updated successfully!']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet | Gym Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .success-message, .error-message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724 }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table th {
            background-color: #f4f4f4;
        }
        .search-container input[type="text"] {
    width: 100%;
    max-width: 400px; /* Restrict the maximum width */
    padding: 10px 15px;
    font-size: 16px;
    border: 1px solid #ddd; /* Light border */
    border-radius: 4px;
    margin-bottom: 10px;
   
}

/* Input Focus Effect */
.search-container input[type="text"]:focus {
    border-color:rgb(0, 0, 0); /* Blue border on focus */
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.2); /* Light blue glow */
}
</style>
</head>
<body>
    <!-- Main Content Area -->
    <div class="main-content">
        <header>
            <div class="header">
                <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Gym Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="attendance.php" class="active"><i class="fas fa-check-circle"></i> Attendance</a></li>
                <li><a href="report.php"><i class="fas fa-chart-line"></i> View Reports</a></li>
                <li><a href="payment.php"><i class="fas fa-credit-card"></i> View Payments</a></li>
                <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="container">
            <div class="admin-panel">
                <h2>Attendance Sheet</h2>
                <p>Mark attendance for each user below:</p>

                <div class="search-container">
        <input type="text" id="searchUser " placeholder="Search" />
    </div>
                <form method="POST" id="attendanceForm">
                    <button type="button" onclick="window.location.href='view_attendance.php'">View Attendance</button>    
                    <div id="message"></div>
                    <button type="submit">Submit Attendance</button>
                    <label for="attendance_date">Select Date:</label>
                    <input type="date" name="attendance_date" id="attendance_date" required>
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTable">
                            <!-- Attendance data will be loaded here -->
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <script>
        const attendanceForm = document.getElementById('attendanceForm');
        const attendanceDate = document.getElementById('attendance_date');
        const attendanceTable = document.getElementById('attendanceTable');
        const messageDiv = document.getElementById('message');
        const searchUser  = document.getElementById('searchUser ');

        function fetchAttendance(date) {
            const formData = new FormData();
            formData.append('fetchAttendance', 'true');
            formData.append('attendance_date', date);

            fetch('attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                attendanceTable.innerHTML = '';
                data.forEach(user => {
                    const row = `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td>
                                <input type="radio" name="attendance_status[${user.id}]" value="Present" ${user.status === 'Present' ? 'checked' : ''}> Present 
                                <input type="radio" name="attendance_status[${user.id}]" value="Absent" ${user.status === 'Absent' ? 'checked' : ''}> Absent
                            </td>
                        </tr>
                    `;
                    attendanceTable.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(error => console.error('Error:', error));
        }

        searchUser .addEventListener('input', () => {
            const searchTerm = searchUser .value;
            const formData = new FormData();
            formData.append('searchUsers', 'true');
            formData.append('search_user_id', searchTerm );
            formData.append('attendance_date', attendanceDate.value); // Include the selected date

            fetch('attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                attendanceTable.innerHTML = '';
                data.forEach(user => {
                    const row = `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td>
                                <input type="radio" name="attendance_status[${user.id}]" value="Present" ${user.status === 'Present' ? 'checked' : ''}> Present 
                                <input type="radio" name="attendance_status[${user.id}]" value="Absent" ${user.status === 'Absent' ? 'checked' : ''}> Absent
                            </td>
                        </tr>
                    `;
                    attendanceTable.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(error => console.error('Error:', error));
        });

        attendanceDate.addEventListener('change', () => {
            fetchAttendance(attendanceDate.value);
        });

        attendanceForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(attendanceForm);

            fetch('attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.textContent = data.success || data.error;
                messageDiv.className = data.success ? 'success-message' : 'error-message';
            })
            .catch(error => console.error('Error:', error));
        });

        // Load attendance for today's date on page load
        document.addEventListener('DOMContentLoaded', () => {
            attendanceDate.value = new Date().toISOString().split('T')[0];
            fetchAttendance(attendanceDate.value);
        });
    </script>
</body>
</html>