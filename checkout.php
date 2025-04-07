<?php
session_start();
include 'includes/db.php';

// Make sure no content is output before the header function
ob_start(); // This will buffer the output to prevent "headers already sent" error

// Include the header
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='index.php'>Go shopping</a></p>";
    include 'includes/footer.php';
    exit;
}

// Ensure user email is set in session
if (empty($_SESSION['user_email'])) {
    die("Error: User email is not set. Please log in again.");
}

// Calculate total amount and prepare checkout items
$total_amount = 0;
$checkout_items = [];

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    
    if (!$stmt->execute()) {
        die("Database query error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $price = (float) $product['price'];
        $subtotal = $price * (int) $quantity;
        $total_amount += $subtotal;

        $checkout_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'image' => $product['image']
        ];
    } else {
        echo "<p>Product with ID $product_id not found. <a href='index.php'>Go back to shopping</a></p>";
        include 'includes/footer.php';
        exit;
    }
}

$total_amount_formatted = number_format($total_amount, 2, '.', '');

// Check if a coupon is applied
if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    $coupon_discount = $_SESSION['coupon_discount'];
    $coupon_type = $_SESSION['coupon_type'];

    // Apply the coupon discount
    if ($coupon_type == 'percentage') {
        // Percentage discount
        $discount = ($total_amount * $coupon_discount) / 100;
    } else {
        // Fixed amount discount
        $discount = $coupon_discount;
    }

    // Adjust the total amount
    $total_amount -= $discount;
    $total_amount_formatted = number_format($total_amount, 2, '.', '');

    // Show coupon applied message
    echo "<p style='color: green;'>Coupon Applied: " . htmlspecialchars($coupon['code']) . " - Discount: -NPR " . number_format($discount, 2) . "</p>";
}

// Generate transaction UUID
$transaction_uuid = uniqid('txn_');

// eSewa credentials (Sandbox for testing)
$product_code = 'EPAYTEST';
$secret_key = '8gBm/:&EnhH.1/q'; // Sandbox secret key

// Create signature payload and generate signature
$payload = "total_amount=$total_amount_formatted,transaction_uuid=$transaction_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $payload, $secret_key, true));

// Insert transaction into the database
$stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_id, status, email) VALUES (?, ?, ?, 'pending', ?)");
$stmt->bind_param("idss", $_SESSION['user_id'], $total_amount_formatted, $transaction_uuid, $_SESSION['user_email']);

if (!$stmt->execute()) {
    die("Error inserting transaction: " . $stmt->error);
}
$stmt->close();

// Insert the order into the `orders` table
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method, status, order_date, transaction_uuid) VALUES (?, ?, ?, 'Pending', NOW(), ?)");
$order_stmt->bind_param("idss", $_SESSION['user_id'], $total_amount_formatted, $product_code, $transaction_uuid);

if (!$order_stmt->execute()) {
    die("Error inserting order: " . $order_stmt->error);
}

// Get the newly inserted order_id
$order_id = $order_stmt->insert_id;
$order_stmt->close();

// Insert order items into `order_items`
$orderItemStmt = $conn->prepare("INSERT INTO order_items (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
foreach ($checkout_items as $item) {
    $orderItemStmt->bind_param(
        "siiid",
        $transaction_uuid,
        $item['id'],
        $item['quantity'],
        $item['price'],
        $item['subtotal']
    );

    if (!$orderItemStmt->execute()) {
        die("Error inserting order items: " . $orderItemStmt->error);
    }
}
$orderItemStmt->close();

// Clear cart after successful checkout
unset($_SESSION['cart']);

// Store order_id in session to ensure it's passed properly
$_SESSION['order_id'] = $order_id;
?>

<div class="checkout-container" style="max-width: 1200px; margin: 20px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <h2 style="text-align: center; color: #333; font-size: 24px; margin-bottom: 20px;">Checkout Summary</h2>

    <!-- Coupon Code Form -->
    <form method="POST" action="apply_coupon.php" style="text-align: center; margin-bottom: 20px;">
        <label for="coupon_code" style="font-size: 16px; color: #555;">Coupon Code:</label>
        <input type="text" name="coupon_code" id="coupon_code" placeholder="Enter coupon code" style="padding: 10px; font-size: 16px; margin-right: 10px; border: 1px solid #ddd; border-radius: 4px;">
        <button type="submit" style="padding: 10px 20px; font-size: 16px; color: white; background-color: #28a745; border: none; border-radius: 4px; cursor: pointer;">Apply</button>
    </form>

    <!-- Display Cart Items -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #f9f9f9;">
        <thead>
            <tr style="background-color: #007bff; color: white; font-size: 16px;">
                <th style="padding: 10px; text-align: center;">Image</th>
                <th style="padding: 10px; text-align: left;">Name</th>
                <th style="padding: 10px; text-align: center;">Price (NPR)</th>
                <th style="padding: 10px; text-align: center;">Quantity</th>
                <th style="padding: 10px; text-align: center;">Subtotal (NPR)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($checkout_items as $item): ?>
            <tr style="text-align: center; background-color: #fff;">
                <td><img src="assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="50" style="border-radius: 4px;"></td>
                <td style="padding: 10px;"><?= htmlspecialchars($item['name']) ?></td>
                <td style="padding: 10px;"><?= number_format($item['price'], 2) ?></td>
                <td style="padding: 10px;"><?= (int) $item['quantity'] ?></td>
                <td style="padding: 10px;"><?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f1f1f1;">
                <td colspan="4" style="text-align: right; padding: 10px;">Total:</td>
                <td style="padding: 10px;"><?= number_format($total_amount_formatted, 2) ?></td>
            </tr>
        </tfoot>
    </table>
    
    <!-- Display Order ID -->
    <div style="text-align: center; margin-bottom: 20px;">
        <strong>Order ID: </strong> <?= $order_id ?>
    </div>

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
        <button type="submit" style="padding: 12px 24px; font-size: 18px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Pay with eSewa</button>
    </form>
</div>

<?php
// Include the footer
include 'includes/footer.php';

// Flush the output buffer to ensure no extra headers are sent
ob_end_flush();
?>
