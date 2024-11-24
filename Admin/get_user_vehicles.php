<?php
require_once 'db-connect.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($user_id) {
    $stmt = $conn->prepare("
    SELECT v.vehicle_id, v.plate_number, v.vehicle_brand, v.body_type, v.engine_number, v.chassis_number, 
           v.year_release, v.vehicle_color, v.vehicle_fuel, v.pass
    FROM user_vehicles uv
    JOIN vehicle v ON uv.vehicle_id = v.vehicle_id
    WHERE uv.user_id = ?");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($vehicle = $result->fetch_assoc()) {
            echo '<div class="vehicle-item">';
            echo "<p><strong>Brand:</strong> " . htmlspecialchars($vehicle['vehicle_brand']) . "</p>";
            echo "<p><strong>Body Type:</strong> " . htmlspecialchars($vehicle['body_type']) . "</p>";
            echo "<p><strong>Plate Number:</strong> " . htmlspecialchars($vehicle['plate_number']) . "</p>";
            echo "<p><strong>Engine Number:</strong> " . htmlspecialchars($vehicle['engine_number']) . "</p>";
            echo "<p><strong>Chassis Number:</strong> " . htmlspecialchars($vehicle['chassis_number']) . "</p>";
            echo "<p><strong>Year Released:</strong> " . htmlspecialchars($vehicle['year_release']) . "</p>";
            echo "<p><strong>Color:</strong> " . htmlspecialchars($vehicle['vehicle_color']) . "</p>";
            echo "<p><strong>Fuel Type:</strong> " . htmlspecialchars($vehicle['vehicle_fuel']) . "</p>";

            // Check if the vehicle has passed (pass == 1)
            if ($vehicle['pass'] != 1) {
                // Add the "Passed" button with data attributes
                echo "<button type='button' class='btn btn-success passed-vehicle-btn mt-2' 
                        data-user-id='" . htmlspecialchars($user_id) . "' 
                        data-vehicle-id='" . htmlspecialchars($vehicle['vehicle_id']) . "'>Passed</button>";
                // Add the "Failed" button with data attributes
                echo "<button type='button' class='btn btn-danger failed-vehicle-btn mt-2' 
                        data-user-id='" . htmlspecialchars($user_id) . "' 
                        data-vehicle-id='" . htmlspecialchars($vehicle['vehicle_id']) . "'>Failed</button>";
            } else {
                echo "<h3><strong>Status:</strong>Done</h3>";
            }

            echo "</div><hr>";
        }
    } else {
        echo "<p>No vehicles found for this user.</p>";
    }

    $stmt->close();
} else {
    echo "<p>No user ID provided.</p>";
}
?>
