<?php 
//session_start();
//if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == true) {
//$user_online = true;	
//$user_id = $_SESSION['user_id'];
//$username = $_SESSION['first_name'];
//$lastname = $_SESSION['last_name'];
//}else{
//$user_online = false;
//}
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ../index.html'); // Redirect to the login page if not logged in
    exit();
}

// The user is logged in, you can display the page content
echo "Welcome User, your user ID is: " . $_SESSION['user_id'];
?>
