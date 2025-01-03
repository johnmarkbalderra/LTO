<?php
// Include the database connection
include '../php/setting.php';  // Ensure this file contains the connection to the database.
require_once "dbconnect.php"; 

if (isset($_POST['schedule_id'])) {
    $schedule_id = $_POST['schedule_id'];

    // Prepare and execute the SQL statement to cancel the appointment
    $stmt = $conn->prepare("UPDATE schedule_list SET status = 'Canceled' WHERE schedule_id = ?");
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            // Redirect back to the user dashboard with a success message
            header("Location: index.php?message=Appointment successfully canceled");
        } else {
            // Redirect back to the user dashboard with an error message if no rows were affected
            header("Location: index.php?message=Error: Appointment ID not found or already canceled");
        }
    } else {
        // Redirect back to the user dashboard with an error message
        header("Location: index.php?message=Error: Could not cancel appointment. " . $stmt->error);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If no schedule_id is provided, redirect back with an error message
    header("Location: index.php?message=Invalid appointment ID");
}

exit();
