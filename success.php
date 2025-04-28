<?php
session_start();
include 'includes/db.php';

// eSewa secret key for sandbox
$secret_key = '8gBm/:&EnhH.1/q';

// Verify 'data' parameter
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

// Debug: Log the raw response from eSewa for better visibility
error_log("Full eSewa Response: " . print_r($response, true));  // Log the full response to debug

// Extract values
$transaction_code = $response['transaction_code'] ?? '';
$status = $response['status'] ?? '';
$total_amount = $response['total_amount'] ?? '';
$transaction_uuid = $response['transaction_uuid'] ?? '';
$product_code = $response['product_code'] ?? '';
$signed_field_names = $response['signed_field_names'] ?? '';
$received_signature = $response['signature'] ?? '';
$order_id = $response['order_id'] ?? null;

// Debugging: Log the extracted values
error_log("Extracted values from eSewa response: Transaction Code: $transaction_code, Status: $status, Order ID: $order_id");

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

// Update transaction status
$stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
$stmt->bind_param("ss", $status, $transaction_uuid);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // If order_id exists, update the order status
    if ($order_id) {
        $order_stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();

        if ($order_stmt->affected_rows > 0) {
            // Set success message for order update
            $order_update_msg = "<p class='success-msg'>Order #$order_id status updated to 'completed'.</p>";
        } else {
            // Set error message if order update failed
            $order_update_msg = "<p class='error-msg'>Failed to update order status. Please verify Order ID: $order_id.</p>";
        }
        $order_stmt->close();
    } else {
        // If order_id is missing, fetch it using transaction_uuid
        $stmt = $conn->prepare("SELECT id FROM orders WHERE transaction_uuid = ?");
        $stmt->bind_param("s", $transaction_uuid);
        $stmt->execute();
        $stmt->bind_result($order_id);
        $stmt->fetch();
        $stmt->close();

        if ($order_id) {
            // Once you have the order_id, update the order status
            $order_stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $order_stmt->bind_param("i", $order_id);
            $order_stmt->execute();

            if ($order_stmt->affected_rows > 0) {
                // Set success message for order update
                $order_update_msg = "<p class='success-msg'>Order #$order_id status updated to 'completed'.</p>";
            } else {
                // Set error message if order update failed
                $order_update_msg = "<p class='error-msg'>Failed to update order status. Please verify Order ID: $order_id.</p>";
            }
            $order_stmt->close();
        } else {
            // If no order_id is found, show an error message
            $order_update_msg = "<p class='error-msg'>No order found with the given transaction UUID. Please contact support.</p>";
        }
    }

    // **Send Email Confirmation**
    $to = filter_var($_SESSION['user_email'], FILTER_SANITIZE_EMAIL); // Sanitize email
    $subject = "Order Confirmation - Thank You for Your Purchase!";
    $message = "Hello " . htmlspecialchars($_SESSION['user_name']) . ",\n\n";
    $message .= "Thank you for your order. Here are your details:\n";
    $message .= "Transaction Code: " . htmlspecialchars($transaction_code) . "\n";
    $message .= "Total Amount: NPR " . number_format((float)$total_amount, 2) . "\n";
    $message .= "Transaction ID: " . htmlspecialchars($transaction_uuid) . "\n";
    $message .= "Order Status: Completed\n\n";
    $message .= "We will notify you once your order is shipped.\n";
    $message .= "Thank you for shopping with us!\n\nBest regards,\nYour Shop Name";

    $headers = "From: Your Shop Name <kiranpanta9846@gmail.com>\r\n";
    $headers .= "Reply-To: kiranpanta9846@gmail.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send email and handle potential errors
    if (mail($to, $subject, $message, $headers)) {
        $email_msg = "<p class='success-msg'>Confirmation email sent to $to.</p>";
    } else {
        error_log("Email sending failed to $to for transaction $transaction_uuid");
        $email_msg = "<p class='error-msg'>Failed to send confirmation email. Please contact support.</p>";
    }

    // Payment success message
    echo "
    <div class='payment-success-container'>
        <h2 class='success-title'>Payment Successful!</h2>
        <p class='success-msg'>Thank you for your payment.</p>
        <table class='transaction-details'>
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
    echo "<div class='error-msg'>Failed to update transaction. Contact support.</div>";
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
}

.success-title { color: #28a745; font-size: 28px; }
.success-msg, .error-msg { font-size: 18px; }
.transaction-details td { padding: 10px; border-bottom: 1px solid #ddd; }
.continue-btn { padding: 12px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; }
.continue-btn:hover { background-color: #0056b3; }
</style>
