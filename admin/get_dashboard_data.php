<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$response = ['success' => false, 'data' => []];

try {
    // 1. Get Core Metrics
    $coreMetrics = $conn->query("
        SELECT
            (SELECT SUM(total_price) FROM orders WHERE status = 'Delivered') AS total_sales,
            (SELECT COUNT(*) FROM orders WHERE status = 'Delivered') AS completed_orders,
            (SELECT COUNT(*) FROM orders WHERE order_date >= CURDATE()) AS today_orders,
            (SELECT SUM(total_price) FROM orders WHERE order_date >= CURDATE()) AS today_revenue,
            (SELECT COUNT(*) FROM products) AS total_products,
            (SELECT COUNT(*) FROM products WHERE stock_status = 'low_stock' OR stock < min_stock_level) AS low_stock_items,
            (SELECT COUNT(DISTINCT user_id) FROM orders) AS total_customers,
            (SELECT COUNT(DISTINCT user_id) FROM orders WHERE order_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)) AS active_customers
    ")->fetch_assoc();

    // 2. Monthly Sales Data
    $monthlySales = $conn->query("
        SELECT 
            DATE_FORMAT(order_date, '%b') AS month,
            SUM(total_price) AS total
        FROM orders
        WHERE order_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        AND status = 'Delivered'
        GROUP BY DATE_FORMAT(order_date, '%Y-%m'), DATE_FORMAT(order_date, '%b')
        ORDER BY MIN(order_date)
    ")->fetch_all(MYSQLI_ASSOC);

    // 3. Weekly Visitors (if you have visitors table)
    $visitorsData = [];
    $visitorsTableExists = $conn->query("SHOW TABLES LIKE 'visitors'")->num_rows > 0;
    
    if ($visitorsTableExists) {
        $visitorsData = $conn->query("
            SELECT 
                DAYNAME(visit_date) AS day, 
                COUNT(*) AS count
            FROM visitors
            WHERE visit_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 WEEK)
            GROUP BY DAYOFWEEK(visit_date), DAYNAME(visit_date)
            ORDER BY DAYOFWEEK(visit_date)
        ")->fetch_all(MYSQLI_ASSOC);
    }

    // 4. Top Selling Products
    $topProducts = $conn->query("
        SELECT 
            p.id,
            p.name,
            p.image,
            SUM(oi.quantity) AS total_sold,
            SUM(oi.subtotal) AS total_revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.transaction_id = o.transaction_uuid
        WHERE o.status = 'Delivered'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    // 5. Recent Orders
    $recentOrders = $conn->query("
        SELECT 
            o.id,
            o.customer_name,
            o.total_price,
            o.order_date,
            o.status,
            COUNT(oi.id) AS item_count
        FROM orders o
        JOIN order_items oi ON o.transaction_uuid = oi.transaction_id
        GROUP BY o.id
        ORDER BY o.order_date DESC
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'metrics' => $coreMetrics,
            'monthly_sales' => $monthlySales,
            'visitors' => $visitorsData,
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders
        ]
    ];

} catch (Exception $e) {
    $response['error'] = "Database error: " . $e->getMessage();
    error_log("Dashboard Error: " . $e->getMessage());
}

echo json_encode($response);