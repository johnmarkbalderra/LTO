<?php
session_start();
require_once "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    $email = $_SESSION['user_data']['email'];

    // Verify OTP
    $otpQuery = "SELECT * FROM otp_verification WHERE email = ? AND otp = ?";
    $otpStmt = $conn->prepare($otpQuery);
    $otpStmt->bind_param("ss", $email, $otp);
    $otpStmt->execute();
    $otpResult = $otpStmt->get_result();

    if ($otpResult->num_rows > 0) {
        // OTP is correct, proceed with registration
        $userData = $_SESSION['user_data'];
        $insertUserQuery = "INSERT INTO users (first_name, middle_name, last_name, email, telephone, password) VALUES (?, ?, ?, ?, ?, ?)";
        $insertUserStmt = $conn->prepare($insertUserQuery);
        $insertUserStmt->bind_param("ssssss", $userData['first_name'], $userData['middle_name'], $userData['last_name'], $userData['email'], $userData['telephone'], $userData['password']);

        if ($insertUserStmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href = 'index.html';</script>";
            exit;
        } else {
            echo "<script>alert('Error: " . $conn->error . "'); window.location.href = 'register.html';</script>";
        }
        $insertUserStmt->close();

        // Delete OTP after successful registration
        $deleteOtpQuery = "DELETE FROM otp_verification WHERE email = ?";
        $deleteOtpStmt = $conn->prepare($deleteOtpQuery);
        $deleteOtpStmt->bind_param("s", $email);
        $deleteOtpStmt->execute();
        $deleteOtpStmt->close();
    } else {
        echo "<script>alert('Invalid OTP.'); window.location.href = 'otp_input.php';</script>";
    }

    $otpStmt->close();
    unset($_SESSION['user_data']);
}

$conn->close();
?>
