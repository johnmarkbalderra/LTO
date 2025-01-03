<?php
require_once 'dbconnect.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script> alert('You must be logged in to reschedule appointments.'); location.replace('./') </script>";
    exit;
}
$user_id = $_SESSION['user_id'];

// Check if the form fields are set
if (isset($_POST['schedule_id'], $_POST['type_of_vehicle'], $_POST['reschedule_date'], $_POST['reschedule_time'])) {
    $schedule_id = $_POST['schedule_id'];
    $vehicle_id = $_POST['type_of_vehicle']; // Now includes vehicle_id
    $reschedule_date = $_POST['reschedule_date'];
    $reschedule_time = $_POST['reschedule_time'];

    // Combine the date and time into a single datetime string
    $start_datetime = $reschedule_date . ' ' . $reschedule_time;

    // Convert start_datetime to a timestamp
    $timestamp = strtotime($start_datetime);
    $day_of_week = date('w', $timestamp); // 0 (for Sunday) through 6 (for Saturday)
    $selected_date = date('Y-m-d', $timestamp); // Extract only the date

    // Check if the selected date is a weekend (Saturday or Sunday)
    if ($day_of_week == 0 || $day_of_week == 6) {
        echo "<script> alert('Appointments cannot be scheduled on weekends. Please choose a weekday.'); location.replace('./') </script>";
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

    // Check if the selected date has less than the maximum allowed appointments
    $sql = "SELECT COUNT(*) as total FROM schedule_list WHERE DATE(start_datetime) = ? AND status != 'canceled' AND schedule_id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $selected_date, $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['total'] >= 50) { // Adjust the limit as per your requirements
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

    // Prepare and execute the SQL statement to update the appointment
    $stmt = $conn->prepare("UPDATE schedule_list SET start_datetime = ?, vehicle_type = ?, status = 'pending' , `waitlist` = 0 WHERE schedule_id = ? AND user_id = ?");
    $stmt->bind_param("ssii", $start_datetime, $body_type, $schedule_id, $user_id);

    if ($stmt->execute()) {
        echo "<script> alert('Appointment successfully rescheduled.'); location.replace('./') </script>";
    } else {
        echo "<script> alert('Error: Could not reschedule appointment. Please try again later.'); location.replace('./') </script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect back if required fields are missing
    echo "<script> alert('Error: Missing required fields.'); location.replace('./') </script>";
}
?>
