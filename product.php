<?php
// Include database connection and stock functions
include_once 'includes/db.php';
include 'includes/stock_functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}
$product_id = intval($_GET['id']);

// Fetch product details and calculated stock status
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

if ($result->num_rows === 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
$stmt->close();

// Sync stock status in database if outdated
if (!isset($product['stock_status']) || $product['stock_status'] !== $product['calculated_status']) {
    updateStockStatus($product_id, $conn);

    // Re-fetch updated product info
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get recommended products (excluding this one)
$recQuery = "SELECT * FROM products WHERE id != ? AND stock > 0 ORDER BY RAND() LIMIT 4";
$recStmt = $conn->prepare($recQuery);
$recStmt->bind_param("i", $product_id);
$recStmt->execute();
$recommendations = $recStmt->get_result();

// Include page header
include 'includes/header.php';
?>

<!-- Flash Messages -->
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

        <!-- Stock Display -->
        <div class="stock-status">
            <?php if ($product['stock'] <= 0): ?>
                <span class="stock-badge out-of-stock">Out of Stock</span>
            <?php elseif ($product['stock'] <= ($product['min_stock_level'] ?? 5)): ?>
                <span class="stock-badge low-stock">Only <?= $product['stock']; ?> left!</span>
            <?php else: ?>
                <span class="stock-badge in-stock">In Stock (<?= $product['stock']; ?>)</span>
            <?php endif; ?>
        </div>

        <p class="description"><?= htmlspecialchars($product['description'] ?: 'No description available.'); ?></p>

        <!-- Add to Cart Form -->
        <?php if ($product['stock'] > 0): ?>
            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?= $product['stock']; ?>" value="1" class="qty-input">
                </div>

                <button type="submit" class="btn">Add to Cart</button>
            </form>
        <?php else: ?>
            <button class="btn" disabled>Out of Stock</button>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Reviews -->
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
        <?php while ($rec = $recommendations->fetch_assoc()): ?>
            <div class="product-card">
                <img src="assets/images/<?= htmlspecialchars($rec['image'] ?? 'default.png'); ?>" alt="<?= htmlspecialchars($rec['name']); ?>">
                <h4><?= htmlspecialchars($rec['name']); ?></h4>
                <p><?= CURRENCY_SYMBOL . " " . number_format($rec['price'], 2); ?></p>
                <a href="product.php?id=<?= $rec['id']; ?>" class="btn">View Details</a>
            </div>
        <?php endwhile; $recStmt->close(); ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- CSS Styling -->
<style>


    .stock-status { margin: 15px 0; font-weight: bold; }
    .stock-badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
    }
    .in-stock { background-color: #d4edda; color: #155724; }
    .low-stock { background-color: #fff3cd; color: #856404; }
    .out-of-stock { background-color: #f8d7da; color: #721c24; }

    .quantity-selector { margin: 15px 0; }
    .qty-input { width: 60px; padding: 8px; margin-left: 10px; }

    .btn[disabled] { opacity: 0.6; cursor: not-allowed; }

    .product-card { padding: 10px; border: 2px solid #eee; border-radius: 6px; text-align: center; }
</style>
