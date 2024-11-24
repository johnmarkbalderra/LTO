<?php
// update_schedule_status.php
include('db-connect.php');

$schedule_id = $_POST['schedule_id'];
$status = $_POST['status'];

if (!isset($schedule_id) || !isset($status)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$stmt = $conn->prepare("UPDATE schedule_list SET status = ? WHERE schedule_id = ?");
$stmt->bind_param('si', $status, $schedule_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Schedule status updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update schedule status']);
}

$stmt->close();
$conn->close();
?>
