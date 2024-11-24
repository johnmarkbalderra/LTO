<?php
// Clear the output buffer to ensure only JSON is sent
ob_clean();
header('Content-Type: application/json');  // Set the content type to JSON

// Disable HTML output for errors, log them instead
ini_set('display_errors', 0);        // Disable error display
ini_set('log_errors', 1);            // Enable error logging
ini_set('error_log', 'log.log'); // Set error log file

include 'db-connect.php'; // Ensure this points to your DB connection

// Get the input from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);

// Log the received data for debugging
error_log('Received data: ' . print_r($data, true));

if (isset($data['schedule_id'])) {
    $schedule_id = $data['schedule_id'];

    // Log the schedule ID
    error_log('Received schedule ID: ' . $schedule_id);

    // Update the schedule to set waitlist = 1
    $update_query = "UPDATE schedule_list SET waitlist = 1 WHERE schedule_id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        error_log('Prepare failed: ' . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
        exit;
    }
    
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        error_log('Successfully updated waitlist for schedule ID: ' . $schedule_id);

        // Fetch user and vehicle details by joining with user_vehicles table
        $user_vehicle_query = "
            SELECT sl.user_id, uv.vehicle_id 
            FROM schedule_list sl 
            LEFT JOIN user_vehicles uv ON sl.user_id = uv.user_id
            WHERE sl.schedule_id = ?
            LIMIT 1
        ";
        $user_vehicle_stmt = $conn->prepare($user_vehicle_query);
        $user_vehicle_stmt->bind_param("i", $schedule_id);
        $user_vehicle_stmt->execute();
        $user_vehicle_result = $user_vehicle_stmt->get_result();
        $user_vehicle_row = $user_vehicle_result->fetch_assoc();
        $user_id = $user_vehicle_row['user_id'];
        $vehicle_id = $user_vehicle_row['vehicle_id'];

        // Insert a notification for the user
        $notification_message = "Your appointment has been moved to the waitlist by Causing of Late, please Re-Schedule your Appointment";
        $notification_query = "INSERT INTO notifications (user_id, message, is_read, created_at, vehicle_id) 
                               VALUES (?, ?, 0, NOW(), ?)";
        $notification_stmt = $conn->prepare($notification_query);
        $notification_stmt->bind_param("isi", $user_id, $notification_message, $vehicle_id);

        if ($notification_stmt->execute()) {
            error_log('Notification sent for user ID: ' . $user_id);
            echo json_encode(['success' => true]); // Successfully moved to waitlist and notified
        } else {
            error_log('Failed to insert notification: ' . $conn->error);
            echo json_encode(['success' => false, 'error' => 'Notification insert failed']);
        }

        $notification_stmt->close();
    } else {
        error_log('Failed to update waitlist: ' . $stmt->error);
        echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    error_log('Invalid request, missing schedule_id');
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
