<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $tracking_number = $_POST['tracking_number'] ?? null;
    $shipping_status = $_POST['shipping_status'] ?? 'pending';

    try {
        // Update order
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = ?, tracking_number = ?, shipping_status = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $status, $tracking_number, $shipping_status, $order_id);
        $stmt->execute();
        $stmt->close();

        // Fetch customer info
        $stmt = $conn->prepare("
            SELECT o.transaction_uuid, o.payment_method, u.username, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order_info = $result->fetch_assoc();
        $stmt->close();

        $customer_name = $order_info['username'] ?? 'Customer';
        $customer_email = $order_info['email'] ?? '';
        $payment_method = $order_info['payment_method'] ?? '';
        $new_status = $status;

        if (!empty($customer_email)) {
            // Prepare email
            $to = $customer_email;
            $subject = "Your Order #$order_id Status Update";

            // Plain text message
            $message = "Hi {$customer_name},\n\n";
            $message .= "Your order #{$order_id} has been updated.\n\n";
            $message .= "Order Status: {$new_status}\n";
            $message .= "Shipping Status: {$shipping_status}\n";
            $message .= "Payment Method: {$payment_method}\n\n";
            $message .= "Thank you for shopping with us.";

            // Headers
            $headers = "From: MeroShopping <kiranpanta9846@gmail.com>\r\n";
$headers .= "Reply-To: kiranpanta9846@gmail.com\r\n";

            $headers .= "X-Mailer: PHP/" . phpversion();

            // Send email
            if (!mail($to, $subject, $message, $headers)) {
                error_log("Email sending failed to $to");
            }
        }

        $_SESSION['success'] = "Order updated and email sent successfully.";
        header("Location: order_details.php?id=$order_id");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        error_log("Order update/email error: " . $e->getMessage());
        header("Location: order_details.php?id=$order_id");
        exit();
    }
}

// GET request to load order details below...

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception("Order not found");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Order - Admin Panel</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        .update-order-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="update-order-container">
    <h1>Update Order #<?= $order['id'] ?></h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red; margin-bottom: 15px;"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

        <div class="form-group">
            <label for="status">Order Status</label>
            <select id="status" name="status" required>
                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <div class="form-group">
            <label for="tracking_number">Tracking Number</label>
            <input type="text" id="tracking_number" name="tracking_number"
                   value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="shipping_status">Shipping Status</label>
            <select id="shipping_status" name="shipping_status" required>
                <option value="pending" <?= $order['shipping_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="shipped" <?= $order['shipping_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="in_transit" <?= $order['shipping_status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                <option value="delivered" <?= $order['shipping_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
            </select>
        </div>

        <button type="submit" class="btn">Update Order</button>
        <a href="order_details.php?id=<?= $order['id'] ?>" style="margin-left: 10px;">Cancel</a>
    </form>
</div>
</body>
</html>
