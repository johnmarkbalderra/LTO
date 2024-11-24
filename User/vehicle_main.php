<?php
// Include the database connection file
require_once "dbconnect.php";

// Start session to retrieve user_id
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user_id is not set
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the user_id from session

// Check if the user has any vehicles linked in the user_vehicles table
$sql = "SELECT vehicle_id FROM user_vehicles WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// If user has one or more vehicles, redirect to vehicle.php, else redirect to vehicle_reg.php
if ($result->num_rows > 0) {
    // User has at least one vehicle, redirect to vehicle.php
    header("Location: vehicle.php");
} else {
    // User has no vehicles, redirect to vehicle_reg.php
    header("Location: vehicle_reg.php");
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
exit(); // Ensure no further code is executed after the redirection
