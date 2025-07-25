<?php
session_start();
include 'includes/db.php';

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// User info (adjust as needed)
$user_email = "kiranpanta9846@gmail.com"; // your test email
$user_name = "Customer"; // or from session/user profile

// Build order summary text
$order_summary = "";
$total_price = 0;

foreach ($_SESSION['cart'] as $product_id => $item) {
    $name = $item['name'];
    $price = number_format($item['price'], 2);
    $quantity = intval($item['quantity']);
    $subtotal = $item['price'] * $quantity;
    $total_price += $subtotal;

    $order_summary .= "$name x $quantity = $" . number_format($subtotal, 2) . "\n";
}

// Email subject and message
$subject = "Order Confirmation from Your Shop";
$message = "Thank you for your purchase, $user_name!\n\n";
$message .= "Order Summary:\n$order_summary\n";
$message .= "Total: $" . number_format($total_price, 2) . "\n\n";
$message .= "We will notify you when your order ships.\n";

// Email headers
$headers = "From: kiranpanta9846@gmail.com\r\n";
$headers .= "Reply-To: kiranpanta9846@gmail.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
if (mail($user_email, $subject, $message, $headers)) {
    // Clear the cart
    unset($_SESSION['cart']);
    // Redirect or show success message
    echo "Order placed successfully! A confirmation email has been sent.";
} else {
    echo "Failed to send confirmation email.";
}
