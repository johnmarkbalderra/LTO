<?php
require_once 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['editOptionTable'];
    $column = $_POST['editOptionColumn'];
    $id = $_POST['editOptionId'];
    $name = $_POST['editOptionName'];

    $sql = "UPDATE $table SET $column = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error); // Log the error
        echo "Error: " . $conn->error;
        exit();
    }
    $stmt->bind_param("si", $name, $id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error); // Log the error
        echo "Error executing query: " . $stmt->error;
    }
    $stmt->close();

    echo "Option edited successfully!";
}
?>
