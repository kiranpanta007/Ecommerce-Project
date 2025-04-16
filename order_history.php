<?php
session_start();
include 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all transactions for the user
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Database query error: " . $stmt->error);
}

$result = $stmt->get_result();
include 'includes/header.php';
?>

<!-- Page Container with full screen height minus header/footer -->
<div style="min-height: calc(100vh - 160px); padding: 30px 20px; box-sizing: border-box; max-width: 800px; margin: auto; font-family: Arial, sans-serif;">
    <h2 style="text-align: center;">Your Order History</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($transaction = $result->fetch_assoc()): ?>
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transaction['transaction_id']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($transaction['created_at']) ?></p>
                <p><strong>Total Amount:</strong> NPR <?= number_format($transaction['amount'], 2) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($transaction['status'])) ?></p>

                <?php
                // Fetch order items for this transaction
                $itemsStmt = $conn->prepare("SELECT order_items.*, products.name FROM order_items 
                                           JOIN products ON order_items.product_id = products.id 
                                           WHERE transaction_id = ?");
                $itemsStmt->bind_param("s", $transaction['transaction_id']);

                if (!$itemsStmt->execute()) {
                    die("Item query error: " . $itemsStmt->error);
                }

                $itemsResult = $itemsStmt->get_result();
                ?>

                <?php if ($itemsResult->num_rows > 0): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                        <thead>
                            <tr style="background-color: #f4f4f4;">
                                <th style="border: 1px solid #ddd; padding: 8px;">Product Name</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Quantity</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Price (NPR)</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Subtotal (NPR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $itemsResult->fetch_assoc()): ?>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;"><?= htmlspecialchars($item['name']) ?></td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?= (int)$item['quantity'] ?></td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?= number_format($item['price'], 2) ?></td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?= number_format($item['subtotal'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No items found for this transaction.</p>
                <?php endif; ?>
                <?php $itemsStmt->close(); ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; font-size: 18px; color: #666; margin-top: 50px;">
            You haven't placed any orders yet.
        </p>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>
