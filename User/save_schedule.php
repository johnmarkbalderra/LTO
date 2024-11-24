<?php 
require_once('dbconnect.php');

// Start the session and ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script> alert('You must be logged in to save appointments.'); location.replace('./') </script>";
    exit;
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script> alert('Error: No data to save.'); location.replace('./') </script>";
    $conn->close();
    exit;
}

// Extract POST data safely
$full_name = $_POST['full_name'];
$phone_number = $_POST['phone_number'];
$email = $_POST['email'];
$vehicle_id = $_POST['type_of_vehicle']; // Now stores vehicle_id
$appointment_date = $_POST['appointment_date']; // Date from the form
$appointment_time = $_POST['appointment_time']; // Time from the form
$id = isset($_POST['id']) ? $_POST['id'] : null;

// Combine date and time into a valid MySQL DATETIME format
$start_datetime = $appointment_date . ' ' . $appointment_time;

// Convert start_datetime to a timestamp
$timestamp = strtotime($start_datetime);
$day_of_week = date('w', $timestamp); // 0 (for Sunday) through 6 (for Saturday)
$selected_date = date('Y-m-d', $timestamp);

// Check if the selected date is a weekend (Saturday or Sunday)
if ($day_of_week == 0 || $day_of_week == 6) {
    echo "<script> alert('Appointments cannot be scheduled on weekends (Saturday or Sunday). Please choose a weekday.'); location.replace('./') </script>";
    $conn->close();
    exit();
}

// Check if the selected date is a holiday or closed day
$holiday_check = $conn->prepare("SELECT COUNT(*) as total FROM holidays WHERE ? BETWEEN start_date AND IFNULL(end_date, start_date)");
$holiday_check->bind_param("s", $selected_date);
$holiday_check->execute();
$holiday_result = $holiday_check->get_result();
$holiday_row = $holiday_result->fetch_assoc();

if ($holiday_row['total'] > 0) {
    echo "<script> alert('Appointments cannot be scheduled on holidays or closed days. Please choose another date.'); location.replace('./') </script>";
    $holiday_check->close();
    $conn->close();
    exit();
}
$holiday_check->close();

// Check if the selected day has reached the maximum number of appointments
$max_slots_per_day = 3; // Maximum slots per day
$sql = "SELECT COUNT(*) as total FROM schedule_list WHERE DATE(start_datetime) = ? AND status != 'canceled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] >= $max_slots_per_day) {
    echo "<script> alert('The selected date has reached the maximum number of appointments. Please choose another date.'); location.replace('./') </script>";
    $stmt->close();
    $conn->close();
    exit();
}

$stmt->close();

// Fetch the body_type based on vehicle_id
$vehicle_query = $conn->prepare("SELECT body_type FROM vehicle WHERE vehicle_id = ?");
$vehicle_query->bind_param("i", $vehicle_id);
$vehicle_query->execute();
$vehicle_result = $vehicle_query->get_result();
$vehicle_data = $vehicle_result->fetch_assoc();

if (!$vehicle_data) {
    echo "<script> alert('Invalid vehicle selected.'); location.replace('./') </script>";
    $vehicle_query->close();
    $conn->close();
    exit();
}

$body_type = $vehicle_data['body_type'];
$vehicle_query->close();

// Insert or update the appointment and set waitlist to 0
if (empty($id)) {
    // Insert a new appointment with status 'pending' and waitlist = 0
    $sql = "INSERT INTO `schedule_list` (`full_name`, `phone_number`, `email`, `vehicle_type`, `start_datetime`, `status`, `user_id`, `waitlist`) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $full_name, $phone_number, $email, $body_type, $start_datetime, $user_id);
} else {
    // Update existing appointment details and set status to 'pending' and waitlist = 0
    $sql = "UPDATE `schedule_list` SET `full_name` = ?, `phone_number` = ?, `email` = ?, `vehicle_type` = ?, `start_datetime` = ?, `status` = 'pending', `waitlist` = 0 WHERE `schedule_id` = ? AND `user_id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $full_name, $phone_number, $email, $body_type, $start_datetime, $id, $user_id);
}

if ($stmt->execute()) {
    echo "<script> alert('Appointment successfully " . (empty($id) ? "scheduled" : "rescheduled") . ".'); location.replace('./') </script>";
} else {
    echo "<pre>";
    echo "An Error occurred.<br>";
    echo "Error: " . $stmt->error . "<br>";
    echo "</pre>";
}

$stmt->close();
$conn->close();
?>
