<?php
session_start();
require_once "dbconnect.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'User is not logged in.';
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $vehicle_id = intval($_POST['vehicle_id']);
    $body_type = trim($_POST['body_type']);
    $motor_vehicle_file_number = trim($_POST['motor_vehicle_file_number']);
    $engine_number = trim($_POST['engine_number']);
    $chassis_number = trim($_POST['chassis_number']);
    $plate_number = trim($_POST['plate_number']);
    $vehicle_brand = trim($_POST['vehicle_brand']);
    $year_release = intval($_POST['year_release']);
    $vehicle_color = trim($_POST['vehicle_color']);
    $vehicle_fuel = trim($_POST['vehicle_fuel']);

    // Validate required fields
    if (empty($body_type) || empty($motor_vehicle_file_number) || empty($engine_number) ||
        empty($chassis_number) || empty($plate_number) || empty($vehicle_brand) ||
        empty($year_release) || empty($vehicle_color) || empty($vehicle_fuel)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: vehicle.php");
        exit();
    }

    // Check if the vehicle belongs to the user
    $check_sql = "SELECT * FROM user_vehicles WHERE user_id = ? AND vehicle_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        $_SESSION['error'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
        header("Location: vehicle.php");
        exit();
    }
    $check_stmt->bind_param("ii", $user_id, $vehicle_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Proceed with updating the vehicle
        $sql = "UPDATE vehicle SET body_type = ?, motor_vehicle_file_number = ?, engine_number = ?, chassis_number = ?, plate_number = ?, vehicle_brand = ?, year_release = ?, vehicle_color = ?, vehicle_fuel = ? WHERE vehicle_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $_SESSION['error'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
            header("Location: vehicle.php");
            exit();
        }
        $stmt->bind_param("ssssssissi", $body_type, $motor_vehicle_file_number, $engine_number, $chassis_number, $plate_number, $vehicle_brand, $year_release, $vehicle_color, $vehicle_fuel, $vehicle_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Vehicle updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update vehicle: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        // Vehicle does not belong to the user
        $_SESSION['error'] = "Unauthorized access or vehicle does not exist.";
    }

    $check_stmt->close();
    $conn->close();
    header("Location: vehicle.php");
    exit();
} else {
    // Invalid request method
    header("Location: vehicle.php");
    exit();
}
?>
