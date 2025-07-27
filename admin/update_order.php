<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle POST request to update order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int) $_POST['order_id'];
    $status = trim($_POST['status']);
    $tracking_number = trim($_POST['tracking_number'] ?? '');
    $shipping_status = trim($_POST['shipping_status'] ?? 'pending');

    try {
        // Update order in DB
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = ?, tracking_number = ?, shipping_status = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $status, $tracking_number, $shipping_status, $order_id);
        $stmt->execute();
        $stmt->close();

        // Get user info
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
        $payment_method = $order_info['payment_method'] ?? 'N/A';

        // Send email notification
        if (!empty($customer_email)) {
            $subject = "Update on Your Order #$order_id";
            $message = "Hi {$customer_name},\n\n";
            $message .= "Your order (#{$order_id}) has been updated.\n\n";
            $message .= "📝 Order Status: {$status}\n";
            $message .= "📦 Shipping Status: {$shipping_status}\n";
            $message .= "🔢 Tracking Number: {$tracking_number}\n";
            $message .= "💳 Payment Method: {$payment_method}\n\n";
            $message .= "Thank you for shopping with us!\nMeroShopping";

            $headers = "From: MeroShopping <kiranpanta9846@gmail.com>\r\n";
            $headers .= "Reply-To: kiranpanta9846@gmail.com\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if (!mail($customer_email, $subject, $message, $headers)) {
                error_log("Failed to send email to $customer_email");
            }
        }

        $_SESSION['success'] = "Order updated successfully.";
        header("Location: order_details.php?id=$order_id");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Something went wrong: " . $e->getMessage();
        error_log("Order update error: " . $e->getMessage());
        header("Location: order_details.php?id=$order_id");
        exit();
    }
}

// Handle GET request to load order data
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int) $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception("Order not found.");
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
    <h1>Update Order #<?= htmlspecialchars($order['id']) ?></h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red; font-weight: 600;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php elseif (isset($_SESSION['success'])): ?>
        <div style="color: green; font-weight: 600;"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">

        <div class="form-group">
            <label for="status">Order Status</label>
            <select id="status" name="status" required>
                <?php
                $statuses = ['pending', 'processing', 'completed', 'cancelled'];
                foreach ($statuses as $s) {
                    $selected = ($order['status'] === $s) ? 'selected' : '';
                    echo "<option value=\"$s\" $selected>" . ucfirst($s) . "</option>";
                }
                ?>
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
                <?php
                $shipping_statuses = ['pending', 'shipped', 'in_transit', 'delivered'];
                foreach ($shipping_statuses as $status_option) {
                    $selected = ($order['shipping_status'] === $status_option) ? 'selected' : '';
                    echo "<option value=\"$status_option\" $selected>" . ucfirst(str_replace('_', ' ', $status_option)) . "</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn">Update Order</button>
        <a href="order_details.php?id=<?= htmlspecialchars($order['id']) ?>" style="margin-left: 10px;">Cancel</a>
    </form>
</div>
</div>
</body>
</html>
