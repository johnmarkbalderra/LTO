<?php
// fetch_holidays.php
include('db-connect.php');

// Enable error reporting for debugging (remove or comment out in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$holidays_query = $conn->query("SELECT * FROM `holidays` ORDER BY `start_date` ASC");

if (!$holidays_query) {
    // Output SQL error for debugging
    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
    exit;
}

$holidays = [];
while ($row = $holidays_query->fetch_assoc()) {
    $holidays[] = $row;
}

echo json_encode(['success' => true, 'holidays' => $holidays]);
?>
