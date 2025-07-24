<?php
require_once '../includes/db.php';

// 1. Total Revenue
$result = $conn->query("SELECT IFNULL(SUM(total_price),0) AS total_revenue FROM orders");
$total_revenue = $result->fetch_assoc()['total_revenue'];

// 2. Total Orders
$result = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
$total_orders = $result->fetch_assoc()['total_orders'];

// 3. New Customers (registered in last 30 days)
$result = $conn->query("SELECT COUNT(*) AS new_customers FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$new_customers = $result->fetch_assoc()['new_customers'];

// 4. Inventory Alert (products with stock <= min_stock_level)
$result = $conn->query("SELECT COUNT(*) AS inventory_alert FROM products WHERE stock <= IFNULL(min_stock_level, 5)");
$inventory_alert = $result->fetch_assoc()['inventory_alert'];

// 5. Sales Analytics - orders per day for last 7 days
$sales_analytics = [];
$sql = "
    SELECT DATE(order_date) AS date, COUNT(*) AS orders_count, IFNULL(SUM(total_price),0) AS revenue 
    FROM orders 
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date)";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $sales_analytics[] = $row;
}

// 6. Revenue Source (payment method summary)
$revenue_source = [];
$sql = "
    SELECT payment_method, COUNT(*) AS orders_count, IFNULL(SUM(total_price),0) AS revenue 
    FROM orders 
    GROUP BY payment_method";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $revenue_source[] = $row;
}

// 7. Order Status count
$order_status = [];
$sql = "
    SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $order_status[$row['status']] = (int)$row['count'];
}

// Prepare response
$response = [
    'total_revenue' => $total_revenue,
    'total_orders' => $total_orders,
    'new_customers' => $new_customers,
    'inventory_alert' => $inventory_alert,
    'sales_analytics' => $sales_analytics,
    'revenue_source' => $revenue_source,
    'order_status' => $order_status,
];

header('Content-Type: application/json');
echo json_encode($response);
