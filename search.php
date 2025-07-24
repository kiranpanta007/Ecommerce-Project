<?php
// Include the database connection file
include('includes/db.php');

// Get the search query from the URL and sanitize it
if (isset($_GET['query'])) {
    $searchQuery = strtolower(trim($_GET['query'])); 
} else {
    $searchQuery = ''; // Default to empty if no query is provided
}

if (empty($searchQuery)) {
    header("Location: shop.php"); // Redirect to the shop page if query is empty
    exit();
}

// Fetch products matching the search query
$sql = "SELECT * FROM products WHERE LOWER(name) LIKE ? OR LOWER(description) LIKE ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$searchTerm = "%$searchQuery%";
$stmt->bind_param('ss', $searchTerm, $searchTerm); // Prevent SQL injection
$stmt->execute();
$result = $stmt->get_result();

// Fetch results
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - MyShop</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="search-results-container" style="font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: auto;">
    <h1 style="text-align: center; color: #333; margin-bottom: 30px;">
        Search Results for "<?= htmlspecialchars($searchQuery); ?>"
    </h1>

    <?php if (!empty($products)): ?>
        <div class="product-list" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
            <?php foreach ($products as $product): ?>
                <?php
                    $imagePath = "assets/images/" . htmlspecialchars($product['image']);
                ?>
                <div class="product-item" style="border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; width: 300px; text-align: center;">
                    <?php if (file_exists($imagePath) && !empty($product['image'])): ?>
                        <img src="<?= $imagePath; ?>" alt="<?= htmlspecialchars($product['name']); ?>" style="width: 100%; height: 200px; object-fit: contain; background-color: #f9f9f9;">
                    <?php else: ?>
                        <img src="assets/images/default.png" alt="No Image Available" style="width: 100%; height: 200px; object-fit: contain; background-color: #f9f9f9;">
                    <?php endif; ?>

                    <h2 style="font-size: 1.5em; color: #333; margin: 15px 0;">
                        <?= htmlspecialchars($product['name']); ?>
                    </h2>
                    <p style="color: #666; padding: 0 10px;">
                        <?= htmlspecialchars(substr($product['description'] ?? '', 0, 100)) . '...'; ?>

                    </p>
                    <p style="font-weight: bold; color: #000; font-size: 1.2em;">
                        <?= CURRENCY_SYMBOL . " " . htmlspecialchars(number_format($product['price'], 2)); ?>
                    </p>
                    <div style="margin-bottom: 15px;">
                        <a href="product.php?id=<?= $product['id']; ?>" class="btn" style="background-color: #007bff; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none;">
                            View Details
                        </a>
                       
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #ff0000; font-size: 1.2em;">
            No products found matching your search.
        </p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
