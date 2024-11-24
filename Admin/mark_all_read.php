<?php
require_once "../dbconnect.php";

$stmt = $conn->prepare("UPDATE `schedule_list` SET `read` = 1 WHERE `status` = 'active' AND `read` = 0");

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['message'] = "Failed to mark all as read.";
}

$stmt->close();
$conn->close();

header('Location: ' . $_SERVER['HTTP_REFERER']);
?>
