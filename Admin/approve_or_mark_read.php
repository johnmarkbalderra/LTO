<?php
// approve_or_mark_read.php

require_once "../dbconnect.php"; // Ensure this path is correct

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

// Include Composer's autoloader if not already included
require_once "../vendor/autoload.php"; // Adjust the path if necessary

header('Content-Type: application/json');

$response = [];

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

    // Approve the appointment
    $stmt = $conn->prepare("UPDATE schedule_list SET status = 'approved' WHERE schedule_id = ?");
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

        // Generate QR code with appointment details
        $qrContent = "Appointment Details:\n" .
            "Name: " . $appointment['full_name'] . "\n" .
            "Phone: " . $appointment['phone_number'] . "\n" .
            "Email: " . $appointment['email'] . "\n" .
            "Vehicle: " . $appointment['vehicle_type'] . "\n" .
            "Date: " . date('F j, Y, g:i a', strtotime($appointment['start_datetime'])) . "\n";

        try {
            $qrCode = Builder::create()
                ->writer(new PngWriter())
                ->data($qrContent)
                ->build();

            $qrDirectory = "../qr_codes/"; // Ensure this path is correct
            if (!is_dir($qrDirectory)) {
                mkdir($qrDirectory, 0755, true); // Create directory if it doesn't exist
            }

            $qrPath = $qrDirectory . 'appointment_' . $appointmentId . '.png';
            file_put_contents($qrPath, $qrCode->getString());

            if (file_exists($qrPath)) {
                $response['success'] = true;
                $response['qr_path'] = $qrPath;

                // Insert notification into the notifications table
                $notificationMessage = "Your appointment for " . $appointment['vehicle_type'] . " has been approved.";
                $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, vehicle_id) VALUES (?, ?, ?)");
                if ($stmtNotif) {
                    // Assuming 'vehicle_id' corresponds to 'vehicle_type', which may not be accurate. Adjust as needed.
                    $vehicle_id = null; // Set to null or fetch the actual vehicle_id if available
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

                // Send approval email to the user
                require_once "email_notifications.php"; // Ensure this path is correct
                sendApprovalEmail($appointment['email'], $appointment['full_name'], $appointment); // Implement this function

            } else {
                $response['success'] = false;
                $response['error'] = 'Failed to save QR code';
            }
        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = 'Failed to generate QR code: ' . $e->getMessage();
        }
    } else {
        $response['success'] = false;
        $response['error'] = 'Failed to approve appointment';
    }

    $conn->close();
} else {
    $response['success'] = false;
    $response['error'] = 'Invalid request';
}

echo json_encode($response);  // Ensure JSON encoding of the response
exit();
?>
