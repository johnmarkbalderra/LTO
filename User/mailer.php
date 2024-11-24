<?php
// mailer.php

function send_email($to, $subject, $message) {
    // This is a basic email setup, adjust as necessary (e.g., using PHPMailer for more control)
    $headers = "From: no-reply@yourdomain.com\r\n" .
               "Reply-To: no-reply@yourdomain.com\r\n" .
               "X-Mailer: PHP/" . phpversion();
    
    mail($to, $subject, $message, $headers);
}
?>
