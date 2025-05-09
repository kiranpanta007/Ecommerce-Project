<?php
if (mail("kiranpanta9846@gmail.com", "Test Subject", "Test message")) {
    echo "Test email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>
