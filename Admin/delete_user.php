<?php 
require_once('db-connect.php');

if (!isset($_GET['id'])) {
    echo "<script> alert('Undefined Schedule ID.'); location.replace('./') </script>";
    $conn->close();
    exit;
}

$id = $_GET['id'];

$delete = $conn->query("DELETE FROM `users` WHERE `user_id` = '{$id}'");

if ($delete) {
    echo "<script> alert('Event has been deleted successfully.'); location.replace('users.php') </script>";
} else {
    echo "<pre>";
    echo "An Error occurred.<br>";
    echo "Error: " . $mysqli->error . "<br>";
    echo "SQL: DELETE FROM `users` WHERE `user_id` = '{$id}'<br>";
    echo "</pre>";
}

$mysqli->close();