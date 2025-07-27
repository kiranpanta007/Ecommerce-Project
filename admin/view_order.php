<?php
// Show all errors (for debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../includes/db.php';

// Check admin login (adjust as per your auth system)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Validate order ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid order ID.');
}

$order_id = (int)$_GET['id'];

// Fetch order details and user info
$sql = "
  SELECT o.id, o.user_id,
         CASE 
             WHEN u.name IS NOT NULL AND u.name <> '' THEN u.name
             WHEN u.username IS NULL OR u.username = '' THEN 'Guest'
             ELSE u.username 
         END AS customer_name,
         COALESCE(u.email, 'N/A') AS email,
         COALESCE(u.phone, 'N/A') AS phone,
         COALESCE(u.address, 'N/A') AS address,
         COALESCE(u.city, '') AS city,
         COALESCE(u.state, '') AS state,
         COALESCE(u.zip_code, '') AS zip_code,
         o.order_date,
         COALESCE(o.total_price, 0) AS total_amount,
         COALESCE(o.status, 'pending') AS status,
         o.payment_method,
         o.shipping_status,
         o.transaction_uuid
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  WHERE o.id = ?
";




$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die('Order not found.');
}

// Format data for display
$order['total_amount'] = 'NRS ' . number_format($order['total_amount'], 2);
$order['order_date'] = date('M d, Y', strtotime($order['order_date']));

// Fetch ordered products for this order using transaction_uuid as filter
$sql_products = "
  SELECT p.name, p.image , oi.price, oi.quantity, oi.subtotal
  FROM order_items oi
  JOIN products p ON oi.product_id = p.id
  WHERE oi.transaction_id = ?
";

$stmt_products = $conn->prepare($sql_products);
$stmt_products->bind_param('s', $order['transaction_uuid']); // transaction_uuid is varchar
$stmt_products->execute();
$result_products = $stmt_products->get_result();

$products = [];
while ($row = $result_products->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Order #<?= htmlspecialchars($order['id']) ?></title>
<style>
  body {
    font-family: Arial, sans-serif;
    max-width: 700px;
    margin: 2rem auto;
    padding: 1rem;
    background: #f9f9f9;
  }
  h1 {
    text-align: center;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  th, td {
    padding: 0.5rem;
    border: 1px solid #ddd;
  }
  th {
    background-color: #eee;
    text-align: left;
  }
  .back-link {
    display: inline-block;
    margin-top: 1rem;
    text-decoration: none;
    color: #337ab7;
  }
  .back-link:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<h1>Order Details #<?= htmlspecialchars($order['id']) ?></h1>

<table>
  <tr><th>User</th><td><?= htmlspecialchars($order['customer_name']) ?></td></tr>
  <tr><th>Email</th><td><?= htmlspecialchars($order['email']) ?></td></tr>
  <tr><th>Phone</th><td><?= htmlspecialchars($order['phone']) ?></td></tr>
<tr><th>Address</th><td>
  <?= htmlspecialchars($order['address']) ?>
  <?= $order['city'] ? ', ' . htmlspecialchars($order['city']) : '' ?>
  <?= $order['state'] ? ', ' . htmlspecialchars($order['state']) : '' ?>
  <?= $order['zip_code'] ? ' - ' . htmlspecialchars($order['zip_code']) : '' ?>
</td></tr>

  <tr><th>Order Date</th><td><?= htmlspecialchars($order['order_date']) ?></td></tr>
  <tr><th>Total Amount</th><td><?= htmlspecialchars($order['total_amount']) ?></td></tr>
  <tr><th>Status</th><td><?= htmlspecialchars($order['status']) ?></td></tr>
  <tr><th>Payment Method</th><td><?= htmlspecialchars($order['payment_method']) ?></td></tr>
  <tr><th>Shipping Status</th><td><?= htmlspecialchars($order['shipping_status']) ?></td></tr>
  <tr><th>Transaction ID</th><td><?= htmlspecialchars($order['transaction_uuid']) ?></td></tr>
</table>

<h2>Ordered Products</h2>
<?php if (count($products) > 0): ?>
  <table>
    <thead>
      <tr>
        <th>Product Name</th>
        <th>Unit Price</th>
        <th>Quantity</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $prod): ?>
      <tr>
        <td><?= htmlspecialchars($prod['name']) ?></td>
        <td>NRS <?= number_format($prod['price'], 2) ?></td>
        <td><?= (int)$prod['quantity'] ?></td>
        <td>NRS <?= number_format($prod['subtotal'], 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No products found for this order.</p>
<?php endif; ?>

<a href="orders.php" class="back-link">&larr; Back to Orders List</a>

</body>
</html>
