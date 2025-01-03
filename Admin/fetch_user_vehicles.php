<?php
include('db-connect.php');

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Fetch user vehicles from the database
    $vehicle_query = $conn->query("SELECT v.* FROM `user_vehicles` uv JOIN `vehicle` v ON uv.vehicle_id = v.vehicle_id WHERE uv.user_id = $user_id");

    if ($vehicle_query->num_rows > 0) {
        echo "<ul>";
        while ($vehicle = $vehicle_query->fetch_assoc()) {
            echo "<li><strong>Brand:</strong> " . htmlspecialchars($vehicle['vehicle_brand']) . "<br>";
            echo "<strong>Body Type:</strong> " . htmlspecialchars($vehicle['body_type']) . "<br>";
            echo "<strong>Plate Number:</strong> " . htmlspecialchars($vehicle['plate_number']) . "<br>";
            echo "<strong>Engine Number:</strong> " . htmlspecialchars($vehicle['engine_number']) . "<br>";
            echo "<strong>Chassis Number:</strong> " . htmlspecialchars($vehicle['chassis_number']) . "<br>";
            echo "<strong>Year Released:</strong> " . htmlspecialchars($vehicle['year_release']) . "<br>";
            echo "<strong>Color:</strong> " . htmlspecialchars($vehicle['vehicle_color']) . "<br>";
            echo "<strong>Fuel Type:</strong> " . htmlspecialchars($vehicle['vehicle_fuel']) . "</li><br>";
            
        }
        echo "</ul>";
    } else {
        echo "No vehicles found for this user.";
    }
} else {
    echo "Invalid user ID.";
}
?>
