<?php
header("Content-Type: application/json");
require_once "../dbconnect.php";

// Check if QR data is received via POST
if (!isset($_POST['qr_data']) || empty($_POST['qr_data'])) {
    echo json_encode(['success' => false, 'error' => 'No QR data received.']);
    exit();
}

$qr_data = trim($_POST['qr_data']);

// Depending on what the QR code contains, process accordingly
// For example, if QR code contains an appointment ID
// $appointment_id = intval($qr_data);

// Fetch appointment details based on QR data
// Example: Assuming QR data contains the license plate number
$stmt = $conn->prepare("SELECT u.full_name, v.type_of_vehicle, v.plate_number, v.additional_info
                        FROM user_vehicles uv
                        JOIN users u ON uv.user_id = u.user_id
                        JOIN vehicle v ON uv.vehicle_id = v.vehicle_id
                        WHERE v.plate_number = ?");
$stmt->bind_param("s", $qr_data);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $details = $result->fetch_assoc();
    echo json_encode(['success' => true, 'details' => $details]);
} else {
    echo json_encode(['success' => false, 'error' => 'No details found for the scanned QR code.']);
}

$stmt->close();
$conn->close();
?>
