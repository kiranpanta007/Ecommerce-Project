<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop - Home</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        /* Hover effect for category items */
        .category-item:hover {
            transform: scale(1.05); /* Slightly enlarge the category on hover */
            transition: transform 0.3s ease; /* Add smooth transition */
        }

        @media (max-width: 768px) {
    .category-item, .product-card {
        flex: 1 0 48%; /* Two items per row on medium screens */
    }
}

@media (max-width: 480px) {
    .category-item, .product-card {
        flex: 1 0 100%; /* One item per row on small screens */
    }
}
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover the Best Products</h1>
            <p>Shop with confidence and get the best deals today!</p>
            <a href="shop.php" class="btn">Start Shopping</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <?php
            // Fetch featured products from the database
            $query = "SELECT * FROM products ORDER BY RAND() LIMIT 4"; // Randomly show 4 products
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
    </section>

    <!-- Categories Section
      "Shop by Categories" Heading
      <h2 style="text-align: center; font-size: 32px; margin-bottom: 40px; color: #333;">Shop by Category</h2>
      
    <section class="categories-section" style="padding: 50px 0; background-color: #f4f4f4;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
       

        Categories Grid
        <div class="categories-container" style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; max-width: 1200px; margin: 0 auto;">
            <?php
            // Fetch categories from the database
            // $query = "SELECT * FROM categories";
            // $result = mysqli_query($conn, $query);

            // if ($result && mysqli_num_rows($result) > 0) {
            //     while ($row = mysqli_fetch_assoc($result)) {
            //         echo "<div class='category-item' style='flex: 1 0 22%; box-sizing: border-box; text-align: center; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;'>
            //             <a href='category.php?id=" . (int)$row['id'] . "' style='display: block; text-decoration: none;'>
            //                 <img src='assets/images/" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "' style='width: 100%; height: 200px; object-fit: cover; border-bottom: 1px solid #ddd;'>
            //                 <h3 style='font-size: 18px; color: #333; margin-top: 15px;'>" . htmlspecialchars($row['name']) . "</h3>
            //             </a>
            //         </div>";
                // }
            // } else {
            //     echo "<p style='text-align: center; font-size: 18px; color: #777;'>No categories found.</p>";
            // }
            ?>
        </div>
    </div>
</section> -->



    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>
