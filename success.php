<?php
session_start();
include 'includes/db.php';

require __DIR__ . '/vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// eSewa secret key for sandbox
$secret_key = '8gBm/:&EnhH.1/q';

// Check if 'data' parameter exists
if (!isset($_GET['data'])) {
    die("<div class='error-msg'>Invalid response from eSewa: Missing 'data' parameter.</div>");
}

// Decode Base64 data
$decoded_data = base64_decode($_GET['data']);
if ($decoded_data === false) {
    die("<div class='error-msg'>Failed to decode eSewa data.</div>");
}

// Parse JSON response
$response = json_decode($decoded_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("<div class='error-msg'>Invalid JSON in eSewa response.</div>");
}

// Extract values from response
$transaction_code = $response['transaction_code'] ?? '';
$status = $response['status'] ?? '';
$total_amount = $response['total_amount'] ?? '';
$transaction_uuid = $response['transaction_uuid'] ?? '';
$product_code = $response['product_code'] ?? '';
$signed_field_names = $response['signed_field_names'] ?? '';
$received_signature = $response['signature'] ?? '';
$order_id = $response['order_id'] ?? null;

// Verify signature
$signed_fields = explode(',', $signed_field_names);
$payload_data = [];

foreach ($signed_fields as $field) {
    if (!isset($response[$field])) {
        die("<div class='error-msg'>Missing parameter '$field' in eSewa response.</div>");
    }
    $payload_data[] = "$field={$response[$field]}";
}

$payload = implode(',', $payload_data);
$expected_signature = base64_encode(hash_hmac('sha256', $payload, $secret_key, true));

if ($expected_signature !== $received_signature) {
    die("<div class='error-msg'>Invalid signature. Payment verification failed.</div>");
}

// Update transaction status in DB
$stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
$stmt->bind_param("ss", $status, $transaction_uuid);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // If order_id not passed, get it using transaction_uuid
    if (!$order_id) {
        $stmt = $conn->prepare("SELECT id FROM orders WHERE transaction_uuid = ?");
        $stmt->bind_param("s", $transaction_uuid);
        $stmt->execute();
        $stmt->bind_result($order_id);
        $stmt->fetch();
        $stmt->close();
    }

    // Update order status to 'completed'
    if ($order_id) {
        $order_stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();

        if ($order_stmt->affected_rows > 0) {
            $order_update_msg = "<p class='success-msg'>Order #$order_id status updated to 'completed'.</p>";
        }
        $order_stmt->close();
    } else {
        $order_update_msg = "<p class='error-msg'>No order found for this transaction. Please contact support.</p>";
    }

    // Fetch user email and name by order_id
    $user_email = '';
    $user_name = '';
    if ($order_id) {
        $stmt = $conn->prepare("SELECT u.email, u.name FROM users u JOIN orders o ON u.id = o.user_id WHERE o.id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($user_email, $user_name);
        $stmt->fetch();
        $stmt->close();
    }

    // Send confirmation email with PHPMailer
    if ($user_email) {
        try {
            $mail = new PHPMailer(true);

            // SMTP config - replace with your real SMTP credentials
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kiranpanta9846@gmail.com';        // Your SMTP email
            $mail->Password   = 'gqaoprdghaxuymat';           // Your SMTP password or app password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('kiranpanta9846@gmail.com', 'Your Store Name');
            $mail->addAddress($user_email, $user_name ?: '');

            $mail->isHTML(true);
            $mail->Subject = "Order Confirmation - Order #$order_id";

            $body = "<h3>Thank you for your order!</h3>";
            $body .= "<p>Hi " . htmlspecialchars($user_name) . ",</p>";
            $body .= "<p>Your order #<strong>$order_id</strong> has been successfully paid.</p>";
            $body .= "<p>Transaction Code: <strong>" . htmlspecialchars($transaction_code) . "</strong><br>";
            $body .= "Total Amount: <strong>NPR " . number_format((float)$total_amount, 2) . "</strong></p>";
            $body .= "<p>We will process and deliver your order soon.</p>";
            $body .= "<p>Thank you for shopping with us!</p>";
            $body .= "<br><p>Best regards,<br>Your Store Name</p>";

            $mail->Body = $body;

            $mail->send();
            $email_msg = "<p class='success-msg'>Confirmation email sent to $user_email.</p>";
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            $email_msg = "<p class='error-msg'>Failed to send confirmation email. Please contact support.</p>";
        }
    } else {
        $email_msg = "<p class='error-msg'>User email not found for order #$order_id.</p>";
    }

    // Show confirmation page with messages
    echo "
    <div class='payment-success-container'>
        <h2 class='success-title'>Payment Successful!</h2>
        <p class='success-msg'>Thank you for your payment.</p>
        <table class='transaction-details' style='margin: 0 auto;'>
            <tr><td><strong>Transaction Code:</strong></td><td>" . htmlspecialchars($transaction_code) . "</td></tr>
            <tr><td><strong>Status:</strong></td><td>" . htmlspecialchars($status) . "</td></tr>
            <tr><td><strong>Total Amount:</strong></td><td>NPR " . number_format((float)$total_amount, 2) . "</td></tr>
            <tr><td><strong>Transaction ID:</strong></td><td>" . htmlspecialchars($transaction_uuid) . "</td></tr>
        </table>
        $order_update_msg
        $email_msg
        <p>Your order has been placed. A confirmation email will be sent shortly.</p>
        <a href='index.php' class='continue-btn'>Continue Shopping</a>
    </div>";

} else {
    echo "<div class='error-msg'>Failed to update transaction status. Please contact support.</div>";
}

$conn->close();
?>

<style>
.payment-success-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
    text-align: center;
    font-family: Arial, sans-serif;
}

.success-title { color: #28a745; font-size: 28px; margin-bottom: 15px; }
.success-msg { color: #28a745; font-size: 18px; }
.error-msg { color: #dc3545; font-size: 18px; }
.transaction-details td { padding: 10px 15px; border-bottom: 1px solid #ddd; text-align: left; }
.continue-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}
.continue-btn:hover {
    background-color: #0056b3;
}
</style>
