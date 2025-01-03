<?php
require_once 'dbconnect.php';

// Query the holidays table to get the start and end dates of all holidays
$query = "SELECT title, start_date, IFNULL(end_date, start_date) as end_date, color FROM holidays";
$result = $conn->query($query);

$holidays = [];
while ($row = $result->fetch_assoc()) {
    // Include holiday data as JSON
    $holidays[] = [
        'title' => $row['title'],
        'start_date' => $row['start_date'],
        'end_date' => $row['end_date'],
        'color' => $row['color']
    ];
}

// Return holiday data as JSON response
header('Content-Type: application/json');
echo json_encode($holidays);
?>
