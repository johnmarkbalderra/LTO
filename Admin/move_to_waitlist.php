<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'db-connect.php'; // Assuming this is your DB connection file

    $schedule_id = $_POST['schedule_id'];

    // Update the schedule to move it to the waitlist
    $query = "UPDATE schedule_list SET waitlist = TRUE WHERE schedule_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to move to waitlist.']);
    }

    $stmt->close();
    $conn->close();
}
?>
