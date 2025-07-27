<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isNew = ($orderId === 0);
$error = '';
$success = '';
$order = [
    'id' => 0,
    'user_id' => null,
    'order_date' => date('Y-m-d'),
    'total_price' => 0.00,
    'status' => 'pending',
    'payment_method' => 'credit_card',
    'shipping_status' => 'pending',
    'transaction_uuid' => '',
    'username' => 'Guest',
    'email' => 'N/A'
];

if (!$isNew) {
    // Fetch order data for editing
    $stmt = $conn->prepare("
        SELECT o.id, o.user_id, o.order_date, o.total_price, o.status, o.payment_method, o.shipping_status, o.transaction_uuid,
               COALESCE(u.username, 'Guest') AS username, COALESCE(u.email, 'N/A') AS email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $fetchedOrder = $result->fetch_assoc();
    $stmt->close();

    if (!$fetchedOrder) {
        die('Order not found');
    }

    $order = $fetchedOrder;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? $order['status'];
    $newPaymentMethod = $_POST['payment_method'] ?? $order['payment_method'];
    $newShippingStatus = $_POST['shipping_status'] ?? $order['shipping_status'];

    $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
    $validPaymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery'];
    $validShippingStatuses = ['pending', 'shipped', 'delivered', 'returned'];

    if (!in_array($newStatus, $validStatuses)) {
        $error = "Invalid order status selected.";
    } elseif (!in_array($newPaymentMethod, $validPaymentMethods)) {
        $error = "Invalid payment method.";
    } elseif (!in_array($newShippingStatus, $validShippingStatuses)) {
        $error = "Invalid shipping status.";
    }

    if (!$error) {
        if ($isNew) {
            // Create new order
            $insertStmt = $conn->prepare("
                INSERT INTO orders (user_id, order_date, total_price, status, payment_method, shipping_status, transaction_uuid)
                VALUES (NULL, NOW(), 0.00, ?, ?, ?, UUID())
            ");
            $insertStmt->bind_param('sss', $newStatus, $newPaymentMethod, $newShippingStatus);
            if ($insertStmt->execute()) {
                $newOrderId = $insertStmt->insert_id;
                header("Location: edit_order.php?id=$newOrderId&created=1");
                exit();
            } else {
                $error = "Failed to create order.";
            }
            $insertStmt->close();
        } else {
            // Update existing order
            $updateStmt = $conn->prepare("
                UPDATE orders
                SET status = ?, payment_method = ?, shipping_status = ?
                WHERE id = ?
            ");
            $updateStmt->bind_param('sssi', $newStatus, $newPaymentMethod, $newShippingStatus, $orderId);
            if ($updateStmt->execute()) {
                $success = "Order updated successfully.";
                $order['status'] = $newStatus;
                $order['payment_method'] = $newPaymentMethod;
                $order['shipping_status'] = $newShippingStatus;
            } else {
                $error = "Failed to update order.";
            }
            $updateStmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Order #<?= htmlspecialchars($order['id']) ?></title>
<style>
  body {
    font-family: Arial, sans-serif;
    max-width: 700px;
    margin: 2rem auto;
    padding: 1rem;
    background: #f4f6fc;
    position: relative; /* Needed for absolute positioning */
  }
  h1 {
    color: #333;
  }
  form {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
  }
  label {
    display: block;
    margin: 1rem 0 0.25rem;
    font-weight: 600;
  }
  select, input {
    width: 100%;
    padding: 0.6rem;
    font-size: 1rem;
    border-radius: 5px;
    border: 1.5px solid #ddd;
  }
  button {
    margin-top: 1.5rem;
    padding: 0.75rem 1.5rem;
    background: #5c6ac4;
    color: white;
    font-weight: 700;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  button:hover {
    background: #4251a2;
  }
  .msg-success {
    background: #d4edda;
    color: #155724;
    padding: 0.8rem 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
  }
  .msg-error {
    background: #f8d7da;
    color: #721c24;
    padding: 0.8rem 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
  }
  .order-info {
    background: #eef1f8;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
  }
  .order-info p {
    margin: 0.3rem 0;
  }
  /* Back button styles */
  .btn-back {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: #6c757d;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.9rem;
  }
  .btn-back:hover {
    background-color: #5a6268;
  }
</style>
</head>
<body>

<h1>Edit Order #<?= htmlspecialchars($order['id']) ?></h1>

<a href="orders.php" class="btn-back" aria-label="Back to Orders List">← Back to Orders</a>

<div class="order-info" aria-label="Order details">
  <p><strong>User:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
  <p><strong>Order Date:</strong> <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
  <p><strong>Total Amount:</strong> <?= CURRENCY_SYMBOL . ' ' . number_format($order['total_price'], 2) ?></p>
  <p><strong>Transaction ID:</strong> <?= htmlspecialchars($order['transaction_uuid']) ?></p>
</div>

<?php if ($success): ?>
  <div class="msg-success" role="alert"><?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
  <div class="msg-error" role="alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="">
  <label for="status">Order Status</label>
  <select name="status" id="status" required>
    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
    <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
  </select>

  <label for="payment_method">Payment Method</label>
  <select name="payment_method" id="payment_method" required>
    <option value="credit_card" <?= $order['payment_method'] === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
    <option value="paypal" <?= $order['payment_method'] === 'paypal' ? 'selected' : '' ?>>PayPal</option>
    <option value="bank_transfer" <?= $order['payment_method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
    <option value="cash_on_delivery" <?= $order['payment_method'] === 'cash_on_delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
  </select>

  <label for="shipping_status">Shipping Status</label>
  <select name="shipping_status" id="shipping_status" required>
    <option value="pending" <?= $order['shipping_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="shipped" <?= $order['shipping_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
    <option value="delivered" <?= $order['shipping_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
    <option value="returned" <?= $order['shipping_status'] === 'returned' ? 'selected' : '' ?>>Returned</option>
  </select>

  <button type="submit">Update Order</button>
</form>

</body>
</html>
