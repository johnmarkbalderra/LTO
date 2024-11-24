<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendOtpEmail($toEmail, $otp) {
    $mail = new PHPMailer(exceptions:true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;                       // Enable detailed debug output
        $mail->isSMTP();                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                   // Enable SMTP authentication
        $mail->Username   = 'your_email@gmail.com'; // SMTP username
        $mail->Password   = 'your_password'; // SMTP password
        $mail->SMTPSecure = 'PHPMailer::ENCRYPTION_SMTPS'; // Enable TLS encryption, `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 465;                    // TCP port to connect to

        //Recipients
        $mail->setFrom('email@gmail.com', 'LTO.com');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);                        // Set email format to HTML
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = 'Your OTP code is ' . $otp;

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>
