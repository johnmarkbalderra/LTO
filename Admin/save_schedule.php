<?php 
require_once('db-connect.php');


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script> alert('Error: No data to save.'); location.replace('./') </script>";
    $conn->close();
    exit;
}

// Check if the user has any vehicles linked (existing code)

// Extract POST data safely
$full_name = $_POST['full_name'];
$phone_number = $_POST['phone_number'];
$email = $_POST['email'];
$type_of_vehicle = $_POST['type_of_vehicle'];
$start_datetime = $_POST['start_datetime'];
$id = isset($_POST['id']) ? $_POST['id'] : null;

// Convert start_datetime to a timestamp
$timestamp = strtotime($start_datetime);
$day_of_week = date('w', $timestamp); // 0 (for Sunday) through 6 (for Saturday)
$selected_date = date('Y-m-d', $timestamp);

// Check if the selected date is a weekend
if ($day_of_week == 0 || $day_of_week == 6) {
    echo "<script> alert('Appointments cannot be scheduled on weekends. Please choose a weekday.'); location.replace('./') </script>";
    $conn->close();
    exit();
}

// Check if the selected date is a holiday or closed day
$holiday_check = $conn->prepare("SELECT COUNT(*) as total FROM holidays WHERE `start_date` = ?");
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

// Existing code to check maximum slots per day
$sql = "SELECT COUNT(*) as total FROM schedule_list WHERE DATE(start_datetime) = ? AND status != 'canceled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] >= 50) {
    echo "<script> alert('The selected date has reached the maximum number of appointments. Please choose another date.'); location.replace('./') </script>";
    $stmt->close();
    $conn->close();
    exit();
}



if ($stmt->execute()) {
    echo "<script> alert('Schedule Successfully Saved.'); location.replace('./') </script>";
} else {
    echo "<pre>";
    echo "An Error occurred.<br>";
    echo "Error: " . $stmt->error . "<br>";
    echo "</pre>";
}

$stmt->close();
$conn->close();
?>
