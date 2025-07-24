<?php
require_once __DIR__ . '/../includes/db.php';
$order_id = 1; // Use a known good ID
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
print_r($order);