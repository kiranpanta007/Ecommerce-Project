<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Validate order ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Database connection
require_once __DIR__ . '/../includes/db.php';

try {
    // Get order details - only existing columns
    $stmt = $conn->prepare("
        SELECT 
            o.*, 
            COALESCE(u.username, 'Guest') AS username,
            COALESCE(u.email, 'N/A') AS email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        throw new Exception("Order not found");
    }

    // Get order items
    $stmt = $conn->prepare("
        SELECT 
            oi.*, 
            p.name, 
            p.image
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Order Details Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading order details. Please try again.";
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Panel</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        /* [Previous CSS styles remain exactly the same] */
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="order-details-container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="order-header">
            <div class="order-info">
                <h2>Order #<?= htmlspecialchars($order['id'] ?? 'N/A') ?></h2>
                <p>Placed on <?= isset($order['order_date']) ? date('F j, Y \a\t h:i A', strtotime($order['order_date'])) : 'Date not available' ?></p>
                <span class="order-status status-<?= htmlspecialchars($order['status'] ?? 'pending') ?>">
                    <?= ucfirst(htmlspecialchars($order['status'] ?? 'Pending')) ?>
                </span>
            </div>
            
            <div class="customer-info">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($order['username'] ?? 'Guest') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'N/A') ?></p>
                <!-- Shipping address removed since it's not in your schema -->
            </div>
        </div>

        <h3>Order Items</h3>
        <?php if (empty($items)): ?>
            <p>No items found for this order.</p>
        <?php else: ?>
            <table class="items-table">
                <!-- [Table header remains the same] -->
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                         class="product-img">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($item['name'] ?? 'Unknown Product') ?></span>
                            </div>
                        </td>
                        <td><?= CURRENCY_SYMBOL ?> <?= number_format($item['price'] ?? 0, 2) ?></td>
                        <td><?= htmlspecialchars($item['quantity'] ?? 0) ?></td>
                        <td><?= CURRENCY_SYMBOL ?> <?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="order-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span><?= CURRENCY_SYMBOL ?> <?= number_format(($order['total_amount'] ?? 0) - ($order['shipping_cost'] ?? 0), 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span><?= CURRENCY_SYMBOL ?> <?= number_format($order['shipping_cost'] ?? 0, 2) ?></span>
            </div>
            <div class="summary-row summary-total">
                <span>Total:</span>
                <span><?= CURRENCY_SYMBOL ?> <?= number_format($order['total_amount'] ?? 0, 2) ?></span>
            </div>
        </div>

        <a href="orders.php" class="back-link">Back to Orders</a>
    </div>
</body>
</html>