<?php
require_once "../dbconnect.php";
require_once "../phpqrcode/qrlib.php"; // Include the QR code library

if (isset($_POST['id'])) {
    $appointmentId = $_POST['id'];

    // Fetch appointment details
    $stmt = $conn->prepare("SELECT * FROM schedule_list WHERE schedule_id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($appointment) {
        // Generate QR code data
        $qrData = "Appointment Details:\n";
        $qrData .= "Name: " . $appointment['full_name'] . "\n";
        $qrData .= "Phone: " . $appointment['phone_number'] . "\n";
        $qrData .= "Email: " . $appointment['email'] . "\n";
        $qrData .= "Vehicle: " . $appointment['vehicle_type'] . "\n";
        $qrData .= "Date: " . date('F j, Y, g:i a', strtotime($appointment['start_datetime'])) . "\n";

        // QR code file path
        $qrFileName = "../qrcodes/qr_" . $appointmentId . ".png";

        // Generate QR code
        QRcode::png($qrData, $qrFileName, QR_ECLEVEL_L, 10);

        // Save the QR code path in the database
        $updateStmt = $conn->prepare("UPDATE schedule_list SET qr_code = ? WHERE schedule_id = ?");
        $updateStmt->bind_param("si", $qrFileName, $appointmentId);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Appointment not found.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
?>
