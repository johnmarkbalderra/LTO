<?php
require_once 'dbconnect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM `schedule_list` WHERE `user_id` = ? AND `status` = 'approved'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'notifications' => $notifications
]);

$stmt->close();
$conn->close();
?>
