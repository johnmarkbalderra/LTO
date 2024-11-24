<?php
// email_notifications.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer via Composer
require '../vendor/autoload.php'; // Adjust path if necessary

function sendApprovalEmail($to, $full_name, $appointment) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output (optional)
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.example.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'your_email@example.com';               // SMTP username
        $mail->Password   = 'your_email_password';                  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable SSL encryption
        $mail->Port       = 465;                                   

        // Recipients
        $mail->setFrom('no-reply@yourdomain.com', 'DUMA LTO');
        $mail->addAddress($to, $full_name);     

        // Content
        $mail->isHTML(true);                                        
        $mail->Subject = 'Your Appointment Has Been Approved';
        $mail->Body    = "
            <p>Dear {$full_name},</p>
            <p>Your appointment scheduled on <strong>" . date('F j, Y, g:i a', strtotime($appointment['start_datetime'])) . "</strong> has been approved.</p>
            <p>Please ensure to bring any necessary documents on the day of your appointment.</p>
            <p>Thank you,<br>DUMA LTO Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Approval Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
function sendDisapprovalEmail($to, $full_name, $appointment) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output (optional)
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.example.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'your_email@example.com';               // SMTP username
        $mail->Password   = 'your_email_password';                  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable SSL encryption
        $mail->Port       = 465;                                   

        // Recipients
        $mail->setFrom('no-reply@yourdomain.com', 'DUMA LTO');
        $mail->addAddress($to, $full_name);     

        // Content
        $mail->isHTML(true);                                        
        $mail->Subject = 'Your Appointment Has Been Rejected';
        $mail->Body    = "
            <p>Dear {$full_name},</p>
            <p>We regret to inform you that your appointment scheduled on <strong>" . date('F j, Y, g:i a', strtotime($appointment['start_datetime'])) . "</strong> has been rejected.</p>
            <p>Please contact us if you have any questions or need to reschedule.</p>
            <p>Thank you,<br>DUMA LTO Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Disapproval Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
