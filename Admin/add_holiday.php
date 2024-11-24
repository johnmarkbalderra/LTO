<?php
session_start();
require_once "db-connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input data
    $holiday_date = $_POST['holiday_date'] ?? '';
    $holiday_description = trim($_POST['holiday_description'] ?? '');

    // Validate inputs
    if (empty($holiday_date) || empty($holiday_description)) {
        $_SESSION['error'] = "Both date and description are required.";
        header("Location: index.php");
        exit();
    }

    // Check if the holiday already exists on the given date
    $check_sql = "SELECT * FROM holidays WHERE date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $holiday_date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "A holiday already exists on this date.";
        $check_stmt->close();
        $conn->close();
        header("Location: index.php");
        exit();
    }

    $check_stmt->close();

    // Insert the new holiday
    $insert_sql = "INSERT INTO holidays (date, description) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    if ($insert_stmt === false) {
        $_SESSION['error'] = "Failed to prepare statement: " . htmlspecialchars($conn->error);
        header("Location: index.php");
        exit();
    }
    $insert_stmt->bind_param("ss", $holiday_date, $holiday_description);

    if ($insert_stmt->execute()) {
        $_SESSION['success'] = "Holiday added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add holiday: " . htmlspecialchars($insert_stmt->error);
    }

    $insert_stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
} else {
    // Invalid request method
    $_SESSION['error'] = "Invalid request.";
    header("Location: index.php");
    exit();
}
?>
