<?php
// Include database connection
include_once 'includes/db.php';
include 'includes/stock_functions.php'; // Include stock functions
// session_start();

// Check if product ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Sanitize input

    // Fetch product details with stock status
    $query = "SELECT *, 
              CASE 
                  WHEN stock <= 0 THEN 'out_of_stock'
                  WHEN stock <= IFNULL(min_stock_level, 5) THEN 'low_stock'
                  ELSE 'in_stock'
              END as calculated_status
              FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Update stock status in database if needed
        if (!isset($product['stock_status']) || $product['calculated_status'] != $product['stock_status']) {
            updateStockStatus($product_id, $conn);
            // Refresh product data
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        }
    } else {
        die("Product not found.");
    }
} else {
    die("Invalid product ID.");
}

// Fetch random recommendations (excluding current product)
$randomQuery = "SELECT * FROM products WHERE id != ? AND stock > 0 ORDER BY RAND() LIMIT 4";
$randomStmt = $conn->prepare($randomQuery);
$randomStmt->bind_param("i", $product_id);
$randomStmt->execute();
$randomResult = $randomStmt->get_result();

// Include header
include 'includes/header.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php elseif (isset($_SESSION['error'])): ?>
    <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Product Details -->
<div class="product-details">
    <div class="product-image">
        <img src="assets/images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
    </div>
    <div class="product-info">
        <h1><?= htmlspecialchars($product['name']); ?></h1>
        <p class="price"><?= CURRENCY_SYMBOL . " " . number_format($product['price'], 2); ?></p>
        
        <!-- Stock Status Display -->
        <div class="stock-status">
            <?php if ($product['stock'] <= 0): ?>
                <span class="stock-badge out-of-stock">Out of Stock</span>
            <?php elseif ($product['stock'] <= ($product['min_stock_level'] ?? 5)): ?>
                <span class="stock-badge low-stock">Only <?= $product['stock'] ?> left!</span>
            <?php else: ?>
                <span class="stock-badge in-stock">In Stock (<?= $product['stock'] ?> available)</span>
            <?php endif; ?>
        </div>
        
        <p class="description"><?= htmlspecialchars($product['description'] ?: "No description available."); ?></p>
        
        <?php if ($product['stock'] > 0): ?>
        <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
            
            <!-- Quantity Selector -->
            <div class="quantity-selector">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" 
                       min="1" max="<?= $product['stock'] ?>" 
                       value="1" class="qty-input">
            </div>
            
            <button type="submit" class="btn">Add to Cart</button>
        </form>
        <?php else: ?>
        <button class="btn" disabled>Out of Stock</button>
        <?php endif; ?>
    </div>
</div>

<!-- Reviews Section -->
<section class="reviews" style="padding: 20px; background-color: #f9f9f9; border-radius: 8px; margin-top: 30px; text-align: center;">
    <h2 style="font-size: 24px; color: #333; margin-bottom: 15px;">Customer Reviews</h2>
    <p>
        <a href="reviews.php?id=<?= $product['id']; ?>" 
           class="btn" 
           style="display: inline-block; padding: 12px 25px; background-color: #ff6600; color: #fff; text-decoration: none; border-radius: 5px; font-size: 16px; transition: background-color 0.3s ease;">
            View All Reviews
        </a>
    </p>
</section>

<!-- Recommendations -->
<section class="recommendations">
    <h2>You Might Also Like</h2>
    <div class="product-list">
        <?php while ($recProduct = $randomResult->fetch_assoc()): ?>
            <div class="product-card">
                <img src="assets/images/<?= htmlspecialchars($recProduct['image'] ?: 'default.png'); ?>" alt="<?= htmlspecialchars($recProduct['name']); ?>">
                <h4><?= htmlspecialchars($recProduct['name']); ?></h4>
                <p><?= CURRENCY_SYMBOL . " " . number_format($recProduct['price'], 2); ?></p>
                <a href="product.php?id=<?= $recProduct['id']; ?>" class="btn">View Details</a>
            </div>
        <?php endwhile; $randomStmt->close(); ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
    /* Stock Status Styles */
    .stock-status {
        margin: 15px 0;
        font-weight: bold;
    }
    .stock-badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
    }
    .in-stock {
        background-color: #d4edda;
        color: #155724;
    }
    .low-stock {
        background-color: #fff3cd;
        color: #856404;
    }
    .out-of-stock {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Quantity Input */
    .quantity-selector {
        margin: 15px 0;
    }
    .qty-input {
        width: 60px;
        padding: 8px;
        margin-left: 10px;
    }
    
    /* Disabled Button */
    .btn[disabled] {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>