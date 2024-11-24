<?php
ob_start(); // Start output buffering to capture any unexpected output

require_once 'session.php';
require_once 'dbconnect.php';

header('Content-Type: application/json'); // Ensure JSON headers

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = $input['notification_id'] ?? null;

    // Initialize an output array
    $output = [];

    if ($notificationId) {
        // Fetch notification details
        $stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE id = ?");
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();
        $stmt->close();

        if ($notification) {
            // Mark as read
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->bind_param("i", $notificationId);
            $stmt->execute();
            $stmt->close();

            // Store data in the output array
            $output = [
                'success' => true,
                'message' => $notification['message'],
                'created_at' => $notification['created_at']
            ];
        } else {
            $output = ['success' => false, 'error' => 'Notification not found'];
        }
    } else {
        $output = ['success' => false, 'error' => 'Invalid request'];
    }

    // Clean any unexpected output and return JSON only
    ob_clean(); // Clear any buffer before outputting JSON
    echo json_encode($output);
    exit;
} else {
    // Handle invalid request method
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
