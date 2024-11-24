<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Error handling setup
ini_set('log_errors', 1);
ini_set('error_log', 'C:\xampp\htdocs\LTOO1\Admin\log.log'); // Adjust the path for error logging

require_once 'db-connect.php'; // Ensure this file establishes the $conn variable

// Capture POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate data received
if (!isset($data['user_id'], $data['vehicle_id'], $data['message'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required input data.']);
    exit();
}

$user_id = $data['user_id'];
$vehicle_id = $data['vehicle_id'];
$message = $data['message'];

// Log the received data for debugging
error_log("Received User ID: " . $user_id);
error_log("Received Vehicle ID: " . $vehicle_id);
error_log("Received Message: " . $message);

// Check if the user exists in the users table
$stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();

    // Insert notification into the notifications table
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, vehicle_id, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("isi", $user_id, $message, $vehicle_id);

    if ($stmt->execute()) {
        error_log("Notification inserted successfully for User ID: " . $user_id);
        echo json_encode(['success' => true, 'message' => 'User notified successfully!']);
    } else {
        error_log("Failed to insert notification for User ID: " . $user_id . ". Error: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Failed to insert notification.']);
    }

    $stmt->close();
} else {
    $stmt->close();
    error_log("User ID: " . $user_id . " not found in the users table.");
    echo json_encode(['success' => false, 'error' => 'User not found in the database.']);
}

$conn->close();
?>
