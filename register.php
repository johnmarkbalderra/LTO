<?php
require_once "dbconnect.php"; // Your database connection file
require_once "send_email.php"; // Your email sending function file

// Form Data Handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize Input to Prevent SQL Injection and XSS
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $middleName = $conn->real_escape_string($_POST['middle_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $_POST['password'];
    $passwordRepeat = $_POST['password_repeat'];

    // Basic Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($telephone) || empty($address) || empty($password) || empty($passwordRepeat)) {
        echo "<script>alert('All fields are required.'); window.location.href = 'register.html';</script>";
    } elseif ($password != $passwordRepeat) {
        echo "<script>alert('Passwords do not match.'); window.location.href = 'register.html';</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.location.href = 'register.html';</script>";
    } else {
        // Password Hashing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Generate OTP
        $otp = rand(1000, 9999);

        // Store OTP in the database
        $otpQuery = "INSERT INTO otp_verification (email, otp) VALUES (?, ?)";
        $otpStmt = $conn->prepare($otpQuery);
        $otpStmt->bind_param("ss", $email, $otp);

        try {
            if ($otpStmt->execute()) {
                // Send OTP to user's email
                sendOtpEmail($email, $otp);

                // Store user data temporarily in session
                session_start();
                $_SESSION['user_data'] = [
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'telephone' => $telephone,
                    'address' => $address,
                    'password' => $hashedPassword
                ];

                // Redirect to OTP input form
                header("Location: otp_input.php");
                exit; // Stop further execution
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                echo "<script>alert('Duplicate entry \'$email\' for key \'email\'.'); window.location.href = 'register.html';</script>";
            } else {
                echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href = 'register.html';</script>";
            }
        }

        $otpStmt->close();
    }
}

$conn->close();
?>
