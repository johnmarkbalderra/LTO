<?php
require_once 'dbconnect.php';

// Initialize the unavailable dates array
$unavailable_dates = [];

// Fetch holidays from the database
$holidays = [];
$holiday_query = $conn->query("SELECT `start_date` FROM `holidays`");
while ($row = $holiday_query->fetch_assoc()) {
    $holidays[] = $row['start_date'];
}

// Fetch dates that have reached 50 appointments
$full_dates = [];
$appointment_query = $conn->query("
    SELECT DATE(start_datetime) as date, COUNT(*) as total
    FROM schedule_list
    WHERE status != 'canceled'
    GROUP BY DATE(start_datetime)
    HAVING total >= 1
");
while ($row = $appointment_query->fetch_assoc()) {
    $full_dates[] = $row['date'];
}

// Add weekends to the unavailable dates
$start_date = new DateTime(); // Today
$end_date = (new DateTime())->modify('+1 year'); // Adjust the range as needed

$interval = new DateInterval('P1D'); // 1 day interval
$date_range = new DatePeriod($start_date, $interval, $end_date);

foreach ($date_range as $date) {
    $day_of_week = $date->format('w'); // 0 (for Sunday) through 6 (for Saturday)
    if ($day_of_week == 0 || $day_of_week == 6) {
        $unavailable_dates[] = $date->format('Y-m-d');
    }
}

// Merge all unavailable dates
$unavailable_dates = array_merge($unavailable_dates, $holidays, $full_dates);

// Remove duplicates
$unavailable_dates = array_unique($unavailable_dates);

// Return as JSON
echo json_encode($unavailable_dates);

$conn->close();
?>
