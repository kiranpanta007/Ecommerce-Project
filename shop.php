<?php
// Include database connection
include 'includes/db.php';

// Include header
include 'includes/header.php';

// Ensure database connection exists
if (!isset($conn)) {
    die("Database connection failed. Please check db.php");
}
?>

<div class="shop">
    <h2>Shop Our Products</h2>
    <div class="product-grid">
        <?php
        // Fetch products from the database
        $query = "SELECT * FROM products";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='product-card'>
                    <img src='assets/images/" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "'>
                    <h3>" . htmlspecialchars($row['name']) . "</h3>
                    <p>" . CURRENCY_SYMBOL . " " . number_format($row['price'], 2) . "</p>
                    <a href='product.php?id=" . (int)$row['id'] . "' class='btn'>View Details</a>
                </div>";
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
