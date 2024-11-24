<?php
require_once('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $vehicle_type = $_POST['vehicle_type'];
    $start_datetime = $_POST['start_datetime'];

    $sql = "UPDATE `schedule_list` SET `full_name` = ?, `phone_number` = ?, `email` = ?, `vehicle_type` = ?, `start_datetime` = ? WHERE `schedule_id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssi', $full_name, $phone_number, $email, $vehicle_type, $start_datetime, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
