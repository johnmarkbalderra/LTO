<?php
require_once 'db-connect.php'; // Include your database connection

// Get the data from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['vehicle_id'])) {
    $vehicle_id = intval($data['vehicle_id']);

    // Update the is_read status to 1 in the schedule_list table
    $stmt = $conn->prepare("UPDATE vehicle SET `pass` = 1 WHERE vehicle_id = ?");
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?>
