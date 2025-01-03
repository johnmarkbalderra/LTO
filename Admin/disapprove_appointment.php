<?php
// disapprove_appointment.php

require_once "../dbconnect.php"; // Ensure this path is correct

header('Content-Type: application/json');

$response = [];

// Enable error reporting for debugging (Disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session to verify admin authentication
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    $response = ['success' => false, 'error' => 'Unauthorized access.'];
    echo json_encode($response);
    exit();
}

if (isset($_POST['id'])) {
    $appointmentId = intval($_POST['id']);

    // Disapprove the appointment
    $stmt = $conn->prepare("UPDATE schedule_list SET status = 'rejected' WHERE schedule_id = ?");
    if (!$stmt) {
        $response = ['success' => false, 'error' => 'Database error: ' . $conn->error];
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("i", $appointmentId);

    if ($stmt->execute()) {
        $stmt->close();

        // Fetch appointment details from schedule_list
        $stmt = $conn->prepare("SELECT sl.*, u.email, u.user_id 
                                FROM schedule_list sl 
                                JOIN users u ON sl.user_id = u.user_id 
                                WHERE sl.schedule_id = ?");
        if (!$stmt) {
            $response = ['success' => false, 'error' => 'Database error: ' . $conn->error];
            echo json_encode($response);
            exit();
        }

        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointment = $result->fetch_assoc();
        $stmt->close();

        if (!$appointment) {
            $response = ['success' => false, 'error' => 'Appointment not found'];
            echo json_encode($response);
            exit();
        }

        // Create a detailed notification message with allowed HTML tags
        $notificationMessage = "Dear " . htmlspecialchars($appointment['full_name'], ENT_QUOTES, 'UTF-8') . ",<br><br>" .
            "We regret to inform you that your appointment scheduled on <strong>" . 
            date('F j, Y, g:i a', strtotime($appointment['start_datetime'])) . 
            "</strong> has been rejected.<br>" .
            "Please contact us if you have any questions or need to reschedule.<br><br>" .
            "Thank you,<br>DUMA LTO Team";

        // Insert notification into the notifications table
        $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, vehicle_id) VALUES (?, ?, ?)");
        if ($stmtNotif) {
            // Assuming 'vehicle_id' is relevant; adjust if necessary
            $vehicle_id = null; // Replace with actual vehicle_id if available
            $stmtNotif->bind_param("isi", $appointment['user_id'], $notificationMessage, $vehicle_id);
            if ($stmtNotif->execute()) {
                $response['notif_success'] = true; // Notification added successfully
            } else {
                $response['notif_success'] = false; // Notification insertion failed
                $response['notif_error'] = 'Failed to create notification';
            }
            $stmtNotif->close();
        } else {
            $response['notif_success'] = false;
            $response['notif_error'] = 'Failed to prepare notification statement: ' . $conn->error;
        }

        // Send disapproval email to the user
        require_once "email_notifications.php"; // Ensure this path is correct
        sendDisapprovalEmail($appointment['email'], $appointment['full_name'], $appointment); // Implement this function

        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['error'] = 'Failed to disapprove appointment';
    }

    $conn->close();
} else {
    $response['success'] = false;
    $response['error'] = 'Invalid request';
}

echo json_encode($response);  // Ensure JSON encoding of the response
exit();
?>
