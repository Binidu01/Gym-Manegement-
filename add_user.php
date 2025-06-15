<?php
// Include the config.php file for database connection
include('config.php');

// Check if the user is logged in and is an admin
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to login page or home page if not logged in as admin
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $id = $_POST['id'];
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $contact = $_POST['contact'];
    $schedule_type = $_POST['schedule_type'];
    $weight = $_POST['weigth'];
    $payment_day = $_POST['payment_day'];
    $schedule_day = $_POST['schedule_day']; // New field
    $birthday = $_POST['birthday'];

    // Prepare SQL to insert the new user
    $sql = "INSERT INTO users (id, name, gender, contact, schedule_type, weigth, payment_day, schedule_day, birthday) 
    VALUES ('$id', '$name', '$gender', '$contact', '$schedule_type', '$weight', '$payment_day', '$schedule_day', '$birthday')";


    if ($conn->query($sql) === TRUE) {
        // Redirect to manage users page after successful insertion
        header("Location: users.php");
        exit();
    } else {
        // If there's an error, display it
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
