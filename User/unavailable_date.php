<?php
require_once 'dbconnect.php';

// Fetch unavailable dates from schedule_list (for fully booked or closed days)
$unavailable_query = "SELECT DISTINCT DATE(start_datetime) AS unavailable_date 
                      FROM schedule_list 
                      WHERE status = 'approved'
                      AND DATE(start_datetime) >= CURDATE()";
$unavailable_result = $conn->query($unavailable_query);

$unavailable_dates = [];
while ($row = $unavailable_result->fetch_assoc()) {
    $unavailable_dates[] = $row['unavailable_date'];
}

// Fetch holidays from the holidays table
$holiday_query = "SELECT title, start_date, IFNULL(end_date, start_date) as end_date, color FROM holidays";
$holiday_result = $conn->query($holiday_query);

$holidays = [];
while ($row = $holiday_result->fetch_assoc()) {
    $holidays[] = [
        'title' => $row['title'],
        'start_date' => $row['start_date'],
        'end_date' => $row['end_date'],
        'color' => $row['color']
    ];
}

// Fetch remaining slots per day (assuming slots per day is handled in schedule_list)
$slots_query = "SELECT DATE(start_datetime) AS schedule_date, 
                COUNT(*) as booked_slots 
                FROM schedule_list 
                WHERE status = 'approved'
                GROUP BY schedule_date";
$slots_result = $conn->query($slots_query);

$slots_per_day = [];
$max_slots_per_day = 50;  // Define the max slots per day, can be dynamically set
while ($row = $slots_result->fetch_assoc()) {
    $remaining_slots = $max_slots_per_day - $row['booked_slots'];
    if ($remaining_slots < 0) {
        $remaining_slots = 0; // Ensure remaining slots are not negative
    }
    $slots_per_day[$row['schedule_date']] = $remaining_slots;
}

// Remove fully booked dates from unavailable_dates
$unavailable_dates = array_filter($unavailable_dates, function($date) use ($slots_per_day) {
    return isset($slots_per_day[$date]) && $slots_per_day[$date] <= 0;
});

// Return data as JSON response
$response = [
    'unavailable_dates' => array_values($unavailable_dates),
    'holiday_dates' => $holidays,
    'slots_per_day' => $slots_per_day
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
