<?php
// Include the config.php file for database connection
include('config.php');

// Check if the user is logged in and is an admin
session_start();

// Get the user ID from the URL query parameter
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete user data from the database
    $sql = "DELETE FROM users WHERE id = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        // Redirect to manage users page after successful deletion
        header("Location: users.php");
        exit();
    } else {
        // Display error if the deletion fails
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    // Redirect to manage users page if no ID is provided in the URL
    header("Location: users.php");
    exit();
}

// Close the database connection
$conn->close();
?>
