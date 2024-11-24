<?php
// Include the database connection
include '../php/setting.php';  // Ensure this file contains the connection to the database.
require_once "dbconnect.php"; 

if (isset($_POST['schedule_id'])) {
    $schedule_id = $_POST['schedule_id'];

    // Prepare and execute the SQL statement to cancel the appointment
    $stmt = $conn->prepare("UPDATE schedule_list SET status = 'canceled' WHERE schedule_id = ?");
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        // Redirect back to the user dashboard with a success message (optional)
        header("Location: index.php?message=Appointment successfully canceled");
    } else {
        // Redirect back to the user dashboard with an error message (optional)
        header("Location: index.php?message=Error: Could not cancel appointment");
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If no schedule_id is provided, redirect back with an error message
    header("Location: index.php?message=Invalid appointment ID");
}

exit();
