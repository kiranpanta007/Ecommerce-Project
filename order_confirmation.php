<?php
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Start the session
session_start();

// Include the database connection
include 'includes/db.php';

// Check if the order ID is provided in the URL
if (!isset($_GET['order_id'])) {
    header("Location: cart.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order details from the database
$query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: cart.php");
    exit();
}

$order = $result->fetch_assoc();

// Fetch order items from the database
$query = "SELECT products.name, products.price, order_items.quantity 
          FROM order_items 
          JOIN products ON order_items.product_id = products.id 
          WHERE order_items.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();

// Send confirmation email using PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com'; // Replace with your email
    $mail->Password = 'your-email-password'; // Replace with your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('no-reply@yourwebsite.com', 'Your Website');
    $mail->addAddress($order['customer_email'], $order['customer_name']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Order Confirmation - Order #' . $order['id'];
    $mail->Body = "Dear " . $order['customer_name'] . ",<br><br>";
    $mail->Body .= "Thank you for your order! Your order details are as follows:<br><br>";
    $mail->Body .= "Order ID: " . $order['id'] . "<br>";
    $mail->Body .= "Total Price: $" . number_format($order['total_price'], 2) . "<br>";
    $mail->Body .= "Shipping Address:<br>" . nl2br($order['customer_address']) . "<br><br>";
    $mail->Body .= "Order Items:<br>";

    while ($item = $order_items->fetch_assoc()) {
        $mail->Body .= "- " . $item['name'] . " ($" . number_format($item['price'], 2) . ") x " . $item['quantity'] . "<br>";
    }

    $mail->Body .= "<br>We will notify you once your order has been shipped.<br><br>";
    $mail->Body .= "Thank you for shopping with us!<br>";

    $mail->send();
    echo 'Email has been sent';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

// Include the header
include 'includes/header.php';
?>

<!-- Order Confirmation Content -->
<div class="order-confirmation">
    <h2>Order Confirmation</h2>
    <p>Thank you for your order, <?php echo htmlspecialchars($order['customer_name']); ?>!</p>
    <p>Your order ID is: <strong><?php echo $order['id']; ?></strong></p>
    <p>We have sent a confirmation email to: <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong></p>

    <h3>Order Details</h3>
    <div class="order-summary">
        <?php while ($item = $order_items->fetch_assoc()): ?>
            <div class="order-item">
                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                <p>$<?php echo number_format($item['price'], 2); ?></p>
                <p>Quantity: <?php echo $item['quantity']; ?></p>
            </div>
        <?php endwhile; ?>
        <p class="total-price">Total Price: $<?php echo number_format($order['total_price'], 2); ?></p>
    </div>

    <p>We will ship your order to:</p>
    <p><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>

    <p>Order Status: <strong><?php echo htmlspecialchars($order['status']); ?></strong></p>

    <a href="index.php" class="btn-continue">Continue Shopping</a>
    <p><a href="orders.php" class="btn-view-orders">View All Orders</a></p>
    <button onclick="window.print()" class="btn-print">Print Confirmation</button>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>