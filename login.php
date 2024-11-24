<?php
session_start();
require_once "dbconnect.php";

// Handle AJAX Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['password'])) {
    // Sanitize Input
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Prepare and Execute Query for Admins
    $stmt_admin = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt_admin->bind_param("s", $email);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();
    $stmt_admin->close();

    // Check if user is an admin
    if ($result_admin->num_rows == 1) {
        $row = $result_admin->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Successful Admin Login
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['admin_id'];
            echo json_encode(array('success' => true, 'redirect' => 'Admin/index.php')); // Specific admin page
            exit(); // Stop further processing
        }
    }

    // Prepare and Execute Query for Users
    $stmt_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $stmt_user->close();

    if ($result_user->num_rows == 1) {
        $row = $result_user->fetch_assoc();
        if ($row['IsActive'] == 0) {
            // Account is deactivated
            echo json_encode(array('success' => false, 'message' => 'Your account on hold. please wait for the administrations Approval.'));
        } elseif (password_verify($password, $row['password'])) {
            // Successful User Login
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $row['user_id']; // Store user ID in session
            echo json_encode(array('success' => true, 'redirect' => 'User/index.php')); // Specific user page
            exit(); // Stop further processing
        } else {
            // Incorrect Password
            echo json_encode(array('success' => false, 'message' => 'Incorrect password'));
        }
    } else {
        // User Not Found
        echo json_encode(array('success' => false, 'message' => 'User not found'));
    }
}

$conn->close();
?>
