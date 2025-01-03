<?php
// get_appointment_details.php

require_once "../dbconnect.php"; // Adjust the path if necessary

header('Content-Type: application/json');

$response = [];

if (isset($_GET['id'])) {
    $schedule_id = intval($_GET['id']);
    
    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("
        SELECT 
            schedule_id, 
            full_name, 
            phone_number, 
            email, 
            vehicle_type, 
            start_datetime 
        FROM 
            schedule_list 
        WHERE 
            schedule_id = ?
    ");
    
    if (!$stmt) {
        $response = ['success' => false, 'error' => 'Database error: ' . $conn->error];
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        $response['success'] = true;
        $response['schedule_id'] = $appointment['schedule_id'];
        $response['full_name'] = htmlspecialchars($appointment['full_name']);
        $response['phone_number'] = htmlspecialchars($appointment['phone_number']);
        $response['email'] = htmlspecialchars($appointment['email']);
        $response['vehicle_type'] = htmlspecialchars($appointment['vehicle_type']);
        $response['start_datetime'] = $appointment['start_datetime'];
    } else {
        $response['success'] = false;
        $response['error'] = 'Appointment not found.';
    }
    
    $stmt->close();
} else {
    $response['success'] = false;
    $response['error'] = 'Invalid request. Appointment ID not provided.';
}

$conn->close();

echo json_encode($response);
?>
