<?php
include('db-connect.php');

header('Content-Type: application/json');

// Fetch waitlisted schedules
$waitlist_query = $conn->query("
    SELECT sl.*, uv.user_id, uv.vehicle_id 
    FROM schedule_list sl 
    LEFT JOIN user_vehicles uv ON sl.user_id = uv.user_id 
    WHERE sl.waitlist = 1
");

$waitlist = [];

while ($row = $waitlist_query->fetch_assoc()) {
    $row['start_datetime_formatted'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
    $waitlist[] = $row;
}

echo json_encode(['success' => true, 'waitlist' => $waitlist]);
?>
