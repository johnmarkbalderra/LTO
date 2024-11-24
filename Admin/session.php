<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.html'); // Redirect to the login page if not logged in
    exit();
}

// The admin is logged in, you can display the page content
echo "Welcome Admin, your admin ID is: " . $_SESSION['admin_id'];