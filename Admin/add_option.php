<?php
require_once 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['optionTable'];
    $column = $_POST['optionColumn'];
    $name = $_POST['optionName'];

    $sql = "INSERT INTO $table ($column) VALUES (?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error); // Log the error
        echo "Error: " . $conn->error;
        exit();
    }
    $stmt->bind_param("s", $name);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error); // Log the error
        echo "Error executing query: " . $stmt->error;
    }
    $stmt->close();

    echo "Option added successfully!";
}
?>
