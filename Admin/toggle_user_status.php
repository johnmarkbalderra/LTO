<?php
require_once "db-connect.php"; // Include database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE users SET IsActive = 1 WHERE user_id = ?");
    } elseif ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE users SET IsActive = 0 WHERE user_id = ?");
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid action.'));
        exit();
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to update user status.'));
    }

    $stmt->close();
}

$conn->close();
?>
