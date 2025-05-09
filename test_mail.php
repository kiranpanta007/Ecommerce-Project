<?php
$to = "kiranpanta9846@gmail.com";
$subject = "Test Email via XAMPP Sendmail";
$message = "This is a test email sent using PHP mail() with Gmail SMTP.";
$headers = "From: kiranpanta9846@gmail.com\r\n";
$headers .= "Reply-To: kiranpanta9846@gmail.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo "Test email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>
