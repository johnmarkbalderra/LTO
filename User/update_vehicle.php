<?php
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = $_POST['vehicle_id'];
    $body_type = $_POST['body_type'];
    $motor_vehicle_file_number = $_POST['motor_vehicle_file_number'];
    $engine_number = $_POST['engine_number'];
    $chassis_number = $_POST['chassis_number'];
    $plate_number = $_POST['plate_number'];
    $vehicle_brand = $_POST['vehicle_brand'];
    $year_release = $_POST['year_release'];
    $vehicle_color = $_POST['vehicle_color'];
    $vehicle_fuel = $_POST['vehicle_fuel'];

    $sql = "UPDATE vehicle SET body_type = ?, motor_vehicle_file_number = ?, engine_number = ?, chassis_number = ?, plate_number = ?, vehicle_brand = ?, year_release = ?, vehicle_color = ?, vehicle_fuel = ? WHERE vehicle_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssissi", $body_type, $motor_vehicle_file_number, $engine_number, $chassis_number, $plate_number, $vehicle_brand, $year_release, $vehicle_color, $vehicle_fuel, $vehicle_id);
    $stmt->execute();
    
    header("Location: vehicle.php");
    exit();
}
