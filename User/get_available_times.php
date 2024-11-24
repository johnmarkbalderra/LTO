<?php
require_once "dbconnect.php";

if (isset($_GET['selected_date'])) {
    $selected_date = $_GET['selected_date'];
    
    // Define the start and end times for the working day (08:00 to 16:00)
    $start_time = '08:00:00';
    $end_time = '16:00:00';

    // Define the interval between slots (e.g., 10 minutes)
    $interval_minutes = 10;
    
    // Maximum slots per day
    $max_slots_per_day = 50;
    
    // Create an array to store all potential time slots for the day
    $available_slots = [];
    $current_time = strtotime($selected_date . ' ' . $start_time);
    $end_time_of_day = strtotime($selected_date . ' ' . $end_time);

    // Generate all possible 10-minute time slots within the working day
    while ($current_time < $end_time_of_day) {
        $slot_start = date('H:i:s', $current_time);
        $slot_end = date('H:i:s', strtotime("+$interval_minutes minutes", $current_time));
        $available_slots[] = [$slot_start, $slot_end];
        $current_time = strtotime("+$interval_minutes minutes", $current_time);
    }

    // Fetch the number of appointments already booked on this day
    $stmt = $conn->prepare("SELECT start_datetime, end_datetime FROM schedule_list WHERE start_datetime BETWEEN ? AND ? AND status IN ('pending', 'approved')");
    $start_of_day = $selected_date . ' 00:00:00';
    $end_of_day = $selected_date . ' 23:59:59';
    $stmt->bind_param("ss", $start_of_day, $end_of_day);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count how many slots are already booked
    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_start_time = date('H:i:s', strtotime($row['start_datetime']));
        $booked_end_time = date('H:i:s', strtotime($row['end_datetime']));
        $booked_slots[] = [$booked_start_time, $booked_end_time];
    }

    $stmt->close();
    $conn->close();

    // Function to check if a slot is still available
    function is_slot_available($slot_start, $slot_end, $booked_slots) {
        foreach ($booked_slots as $booked) {
            if (
                ($slot_start >= $booked[0] && $slot_start < $booked[1]) || // Start overlaps
                ($slot_end > $booked[0] && $slot_end <= $booked[1]) || // End overlaps
                ($slot_start <= $booked[0] && $slot_end >= $booked[1]) // Enveloping a booked slot
            ) {
                return false; // Slot is already booked
            }
        }
        return true; // Slot is available
    }

    // Filter out the slots that are already booked
    $final_available_slots = [];
    $slots_booked_today = count($booked_slots);
    foreach ($available_slots as $slot) {
        if ($slots_booked_today < $max_slots_per_day && is_slot_available($slot[0], $slot[1], $booked_slots)) {
            $final_available_slots[] = $slot[0] . ' - ' . $slot[1];
        }
    }

    // Return the final available slots in JSON format
    echo json_encode($final_available_slots);
}
