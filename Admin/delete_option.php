<?php
require_once 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['deleteOptionTable'];
    $id = $_POST['deleteOptionId'];

    $sql = "DELETE FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error); // Log the error
        echo "Error: " . $conn->error;
        exit();
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error); // Log the error
        echo "Error executing query: " . $stmt->error;
    }
    $stmt->close();

    echo "Option deleted successfully!";
}
?>
