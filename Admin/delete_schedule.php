<?php 
require_once('db-connect.php');

if (!isset($_GET['id'])) {
    echo "<script> alert('Undefined Schedule ID.'); location.replace('./') </script>";
    $mysqli->close();
    exit;
}

$id = $_GET['id'];

$delete = $conn->query("DELETE FROM `schedule_list` WHERE `schedule_id` = '{$id}'");

if ($delete) {
    echo "<script> alert('Event has been deleted successfully.'); location.replace('./') </script>";
} else {
    echo "<pre>";
    echo "An Error occurred.<br>";
    echo "Error: " . $mysqli->error . "<br>";
    echo "SQL: DELETE FROM `schedule_list` WHERE `id` = '{$id}'<br>";
    echo "</pre>";
}

$mysqli->close();
