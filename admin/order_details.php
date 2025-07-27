<?php
// Error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
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

define('CURRENCY_SYMBOL', '₹'); // Change to your preferred symbol

$order_id = (int)$_GET['id'];

require_once __DIR__ . '/../includes/db.php';

try {
    // Get order and customer info
    $stmt = $conn->prepare("
        SELECT 
            o.*, 
            COALESCE(u.username, 'Guest') AS username,
            COALESCE(u.email, 'N/A') AS email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

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
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (Exception $e) {
    error_log("Order Details Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading order details.";
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details - Admin Panel</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        .order-details-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .order-header, .customer-info, .shipping-info {
            margin-bottom: 20px;
        }
        .order-status {
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-pending { background: orange; color: white; }
        .status-processing { background: blue; color: white; }
        .status-completed { background: green; color: white; }
        .status-cancelled { background: red; color: white; }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th, .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        .items-table img {
            width: 60px;
            height: auto;
            border-radius: 4px;
        }
        .order-summary {
            margin-top: 25px;
            font-size: 16px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
        }
        .summary-total {
            font-weight: bold;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="order-details-container">

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red;"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="order-header">
        <h2>Order #<?= htmlspecialchars($order['id']) ?></h2>
        <p>Placed on <?= date('F j, Y \a\t h:i A', strtotime($order['order_date'])) ?></p>
        <span class="order-status status-<?= htmlspecialchars($order['status']) ?>">
            <?= ucfirst(htmlspecialchars($order['status'])) ?>
        </span>
    </div>

    <div class="customer-info">
        <h3>Customer Info</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
        <p><strong>Shipping Status:</strong> <?= ucfirst(str_replace('_', ' ', htmlspecialchars($order['shipping_status']))) ?></p>
        <p><strong>Tracking Number:</strong> <?= htmlspecialchars($order['tracking_number'] ?? 'N/A') ?></p>
    </div>

    <div class="shipping-info">
        <h3>Shipping Address</h3>
        <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?><br>
           <?= htmlspecialchars($order['shipping_city']) ?>,
           <?= htmlspecialchars($order['shipping_state']) ?> -
           <?= htmlspecialchars($order['shipping_zip_code']) ?><br>
           <strong>Phone:</strong> <?= htmlspecialchars($order['shipping_phone']) ?>
        </p>
    </div>

    <h3>Order Items</h3>

    <?php if (empty($items)): ?>
        <p>No items found for this order.</p>
    <?php else: ?>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <?php if (!empty($item['image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php endif; ?>
                            <?= htmlspecialchars($item['name']) ?>
                        </div>
                    </td>
                    <td><?= CURRENCY_SYMBOL ?> <?= number_format($item['price'], 2) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td><?= CURRENCY_SYMBOL ?> <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="order-summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span><?= CURRENCY_SYMBOL ?> <?= number_format($order['total_price'], 2) ?></span>
        </div>
        <!-- Add shipping/tax here if you use those in future -->
        <div class="summary-row summary-total">
            <span>Total:</span>
            <span><?= CURRENCY_SYMBOL ?> <?= number_format($order['total_price'], 2) ?></span>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <a href="update_order.php?id=<?= $order['id'] ?>" class="btn">Edit Order</a>
        <a href="orders.php" class="btn back-link" style="background: #6c757d;">Back to Orders</a>
    </div>
</div>

</body>
</html>
