<?php
session_start();
include 'includes/db.php';
ob_start(); // Prevent "headers already sent"

// Include header
include 'includes/header.php';

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Empty cart check
if (empty($_SESSION['cart'])) {
    echo "<p style='text-align: center; padding: 50px;'>Your cart is empty. <a href='index.php'>Go shopping</a></p>";
    include 'includes/footer.php';
    exit;
}

// Check if email is set
if (empty($_SESSION['user_email'])) {
    die("Error: User email not set. Please login again.");
}

// Initialize totals and order items
$total_amount = 0;
$checkout_items = [];

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $price = (float) $product['price'];
        $subtotal = $price * (int)$quantity;
        $total_amount += $subtotal;

        $checkout_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'image' => $product['image']
        ];
    }
}

$total_amount_formatted = number_format($total_amount, 2, '.', '');

// Handle coupon
if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    $coupon_discount = $_SESSION['coupon_discount'];
    $coupon_type = $_SESSION['coupon_type'];

    $discount = ($coupon_type == 'percentage')
        ? ($total_amount * $coupon_discount) / 100
        : $coupon_discount;

    $total_amount -= $discount;
    $total_amount_formatted = number_format($total_amount, 2, '.', '');

    echo "<p style='color: green; text-align:center;'>Coupon Applied: " . htmlspecialchars($coupon['code']) . " - Discount: NPR " . number_format($discount, 2) . "</p>";
}

// Generate transaction UUID
$transaction_uuid = uniqid('txn_');

// eSewa credentials (Sandbox)
$product_code = 'EPAYTEST';
$secret_key = '8gBm/:&EnhH.1/q';

$payload = "total_amount=$total_amount_formatted,transaction_uuid=$transaction_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $payload, $secret_key, true));

// Save transaction
$stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_id, status, email) VALUES (?, ?, ?, 'pending', ?)");
$stmt->bind_param("idss", $_SESSION['user_id'], $total_amount_formatted, $transaction_uuid, $_SESSION['user_email']);
$stmt->execute();
$stmt->close();

// Save order
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method, status, order_date, transaction_uuid) VALUES (?, ?, ?, 'Pending', NOW(), ?)");
$order_stmt->bind_param("idss", $_SESSION['user_id'], $total_amount_formatted, $product_code, $transaction_uuid);
$order_stmt->execute();
$order_id = $order_stmt->insert_id;
$order_stmt->close();

// Save items
$item_stmt = $conn->prepare("INSERT INTO order_items (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
foreach ($checkout_items as $item) {
    $item_stmt->bind_param("siiid", $transaction_uuid, $item['id'], $item['quantity'], $item['price'], $item['subtotal']);
    $item_stmt->execute();
}
$item_stmt->close();

// Clear cart
unset($_SESSION['cart']);
$_SESSION['order_id'] = $order_id;
?>

<!-- Checkout Summary -->
<div style="max-width: 1200px; margin: 30px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); font-family: Arial;">
    <h2 style="text-align: center; margin-bottom: 30px;">Checkout Summary</h2>

    <!-- Coupon Form -->
    <form method="POST" action="apply_coupon.php" style="text-align: center; margin-bottom: 20px;">
        <input type="text" name="coupon_code" placeholder="Enter coupon code" style="padding: 10px; border-radius: 4px; border: 1px solid #ccc; width: 250px;">
        <button type="submit" style="padding: 10px 20px; background: green; color: white; border: none; border-radius: 4px;">Apply</button>
    </form>

    <!-- Cart Items Table -->
    <table style="width: 100%; border-collapse: collapse; background: #f9f9f9;">
        <thead style="background: #007bff; color: white;">
            <tr>
                <th style="padding: 10px;">Image</th>
                <th style="padding: 10px;">Name</th>
                <th style="padding: 10px;">Price (NPR)</th>
                <th style="padding: 10px;">Qty</th>
                <th style="padding: 10px;">Subtotal (NPR)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($checkout_items as $item): ?>
            <tr style="text-align: center; background: white;">
                <td><img src="assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="50"></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="font-weight: bold; background: #f1f1f1;">
            <tr>
                <td colspan="4" style="text-align: right; padding: 10px;">Total:</td>
                <td style="padding: 10px;"><?= $total_amount_formatted ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Order ID -->
    <div style="text-align: center; margin: 20px 0;"><strong>Order ID:</strong> <?= $order_id ?></div>

    <!-- eSewa Payment Form -->
    <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST" style="text-align: center;">
        <input type="hidden" name="amount" value="<?= $total_amount_formatted ?>">
        <input type="hidden" name="tax_amount" value="0">
        <input type="hidden" name="total_amount" value="<?= $total_amount_formatted ?>">
        <input type="hidden" name="transaction_uuid" value="<?= htmlspecialchars($transaction_uuid) ?>">
        <input type="hidden" name="product_code" value="<?= htmlspecialchars($product_code) ?>">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
        <input type="hidden" name="product_service_charge" value="0">
        <input type="hidden" name="product_delivery_charge" value="0">
        <input type="hidden" name="success_url" value="http://localhost/ecommerce-project/success.php">
        <input type="hidden" name="failure_url" value="http://localhost/ecommerce-project/failure.php">
        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
        <input type="hidden" name="signature" value="<?= htmlspecialchars($signature) ?>">
        <button type="submit" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px;">Pay with eSewa</button>
    </form>
</div>

<?php include 'includes/footer.php'; ob_end_flush(); ?>
