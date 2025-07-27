<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require 'vendor/autoload.php'; // Adjust path!

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kiranpanta9846@gmail.com';
    $mail->Password = 'gqaoprdghaxuymat'; // Use app password, NOT your Gmail login!
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('kiranpanta9846@gmail.com', 'Your Site');
    $mail->addAddress('your-email@example.com', 'Your Name');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = '<p>This is a test email sent via PHPMailer SMTP.</p>';

    $mail->send();
    echo 'Mail sent successfully!';
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
