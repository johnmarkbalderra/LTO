<?php
session_start();
require_once "db-connect.php"; // Include database connection file


if (isset($_GET['id'])) {
    $userId = (int)$_GET['id'];

    // Prepare the SQL statement to update the user's IsActive status
    $query = "UPDATE users SET IsActive = TRUE WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: users.php?msg=User activated successfully");
    } else {
        header("Location: users.php?msg=Failed to activate user");
    }
}
?>
