<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Fetch orders
$stmt = $conn->query("SELECT id, customer_name, total_price, status, tracking_number FROM orders");
$orders = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
    <header>Manage Orders</header>
    <nav>
        <a href="index.php">🏠 Dashboard</a>
    </nav>
    <main>
        <h2>Orders List</h2>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Tracking Number</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td>
                        <form action="update_order.php" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                            <input type="text" name="tracking_number" value="<?php echo htmlspecialchars($order['tracking_number']); ?>" placeholder="Tracking Number">
                    </td>
                    <td>
                            <select name="status">
                                <option value="Pending" <?php echo ($order['status'] == "Pending") ? "selected" : ""; ?>>Pending</option>
                                <option value="Shipped" <?php echo ($order['status'] == "Shipped") ? "selected" : ""; ?>>Shipped</option>
                                <option value="In Transit" <?php echo ($order['status'] == "In Transit") ? "selected" : ""; ?>>In Transit</option>
                                <option value="Delivered" <?php echo ($order['status'] == "Delivered") ? "selected" : ""; ?>>Delivered</option>
                            </select>
                            <button type="submit" class="btn btn-edit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
