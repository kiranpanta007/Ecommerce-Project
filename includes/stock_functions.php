<?php
/**
 * Stock Management Functions for E-Commerce Project
 */

/**
 * Updates stock status based on current quantity
 */
function updateStockStatus($productId, $conn) {
    $stmt = $conn->prepare("UPDATE products SET 
                          stock_status = CASE 
                              WHEN stock <= 0 THEN 'out_of_stock'
                              WHEN stock <= IFNULL(min_stock_level, 5) THEN 'low_stock'
                              ELSE 'in_stock'
                          END
                          WHERE id = ?");
    $stmt->bind_param("i", $productId);
    return $stmt->execute();
}

/**
 * Reduces product stock after purchase
 */
function reduceProductStock($productId, $quantity, $conn) {
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    $stmt->bind_param("iii", $quantity, $productId, $quantity);
    $success = $stmt->execute();
    
    if ($success) {
        updateStockStatus($productId, $conn);
    }
    
    return $success;
}

/**
 * Checks if product is available in required quantity
 */
function checkStockAvailability($productId, $requiredQuantity, $conn) {
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) return false;
    
    $product = $result->fetch_assoc();
    return $product['stock'] >= $requiredQuantity;
}

/**
 * Gets current stock status
 */
function getStockStatus($productId, $conn) {
    $stmt = $conn->prepare("SELECT stock_status FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc()['stock_status'] : null;
}
?>