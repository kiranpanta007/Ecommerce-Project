<?php
session_start();
include '../includes/db.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input data
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $tracking_number = isset($_POST['tracking_number']) ? trim($_POST['tracking_number']) : '';

    // Valid order statuses
    $valid_statuses = ['Pending', 'Shipped', 'In Transit', 'Delivered'];

    // Input validation
    if ($order_id <= 0 || !in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid input data.";
        header("Location: admin_orders.php");
        exit();
    }

    // Update order in the database
    $stmt = $conn->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $tracking_number, $order_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Order #$order_id updated successfully!";

        // Send email notification to the customer
        $user_stmt = $conn->prepare("SELECT email FROM users WHERE id = (SELECT user_id FROM orders WHERE id = ?)");
        $user_stmt->bind_param("i", $order_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_data = $user_result->fetch_assoc()) {
            $to = $user_data['email'];
            $subject = "Order #$order_id Status Updated";
            
            $message = "Hello,\n\n";
            $message .= "Your order #$order_id has been updated.\n";
            $message .= "Status: $status\n";
            
            if (!empty($tracking_number)) {
                $message .= "Tracking Number: $tracking_number\n";
            }
            
            $message .= "\nThank you for shopping with us!\nMero Shopping Team";
            
            $headers = "From: no-reply@meroshopping.com\r\n";
            mail($to, $subject, $message, $headers);
        }
    } else {
        $_SESSION['error'] = "Database error: Failed to update order.";
    }

    $stmt->close();
    header("Location: admin_orders.php");
    exit();
}
?>
