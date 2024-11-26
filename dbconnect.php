<?php
$servername = "sql213.ezyro.com";
$username = "ezyro_37787060";
$password = "02pgzx9v";
$dbname = "ezyro_37787060_land";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
