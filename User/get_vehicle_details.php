<?php
require_once 'dbconnect.php';

header('Content-Type: application/json'); // Ensure we return JSON response

$response = ['success' => false];

if (isset($_GET['id'])) {
    $vehicle_id = $_GET['id'];
    
    // Fetch vehicle details by vehicle ID
    $sql = "SELECT * FROM vehicle WHERE vehicle_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $response['error'] = 'Database query failed: ' . $conn->error;
    } else {
        $stmt->bind_param("i", $vehicle_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehicle = $result->fetch_assoc();

        if ($vehicle) {
            $response['success'] = true;
            $response['vehicle'] = $vehicle;
        } else {
            $response['error'] = 'Vehicle not found';
        }
        $stmt->close();
    }
} else {
    $response['error'] = 'Invalid request: Vehicle ID not provided';
}

$conn->close();
echo json_encode($response);
?>
