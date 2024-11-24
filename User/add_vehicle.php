<?php
session_start();
require_once "dbconnect.php";

// Ensure session user_id is set
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'User is not logged in.';
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve POST data
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

    // Insert new vehicle
    $sql = "INSERT INTO vehicle (body_type, motor_vehicle_file_number, engine_number, chassis_number, plate_number, vehicle_brand, year_release, vehicle_color, vehicle_fuel) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $_SESSION['error'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
        header("Location: vehicle.php");
        exit();
    }
    $stmt->bind_param("ssssssiss", $body_type, $motor_vehicle_file_number, $engine_number, $chassis_number, $plate_number, $vehicle_brand, $year_release, $vehicle_color, $vehicle_fuel);

    if ($stmt->execute()) {
        // Get the new vehicle ID
        $vehicle_id = $conn->insert_id;

        // Associate the new vehicle with the user
        $sql = "INSERT INTO user_vehicles (user_id, vehicle_id) VALUES (?, ?)";
        $stmt_link = $conn->prepare($sql);
        if ($stmt_link === false) {
            $_SESSION['error'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
            header("Location: vehicle.php");
            exit();
        }
        $stmt_link->bind_param("ii", $user_id, $vehicle_id);

        if ($stmt_link->execute()) {
            $_SESSION['success'] = "Vehicle added successfully.";
        } else {
            $_SESSION['error'] = "Failed to associate vehicle with user: " . htmlspecialchars($stmt_link->error);
        }
        $stmt_link->close();
    } else {
        $_SESSION['error'] = "Failed to insert vehicle: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
    $conn->close();

    header("Location: vehicle.php");
    exit();
} else {
    // Invalid request method
    header("Location: vehicle.php");
    exit();
}
?>
