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

    <!-- 🔍 Filter & Sort Form -->
    <form method="GET" class="filters" style="margin-bottom: 20px;">
        <!-- Sort Dropdown -->
        <label for="sort" style="font-weight: 500;">Sort By:</label>
        <select name="sort" id="sort" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px; min-width: 140px;">
            <option value="">-- Select --</option>
            <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="name_asc" <?= ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Name: A-Z</option>
            <option value="name_desc" <?= ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name: Z-A</option>
        </select>

        <!-- Price Filters -->
        <label for="min_price" style="font-weight: 500;">Min Price:</label>
        <input type="number" name="min_price" value="<?= $_GET['min_price'] ?? '' ?>" min="0" style="padding: 8px 12px; font-size: 15px; border: 1px solid #ccc; border-radius: 5px; width: 140px;" />

        <label for="max_price" style="font-weight: 500;">Max Price:</label>
        <input type="number" name="max_price" value="<?= $_GET['max_price'] ?? '' ?>" min="0" style="padding: 8px 12px; font-size: 15px; border: 1px solid #ccc; border-radius: 5px; width: 140px;" />

        <!-- Category Dropdown -->
        <label for="category" style="font-weight: 500;">Category:</label>
        <select name="category" id="category" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px; min-width: 140px;">
            <option value="">-- Select Category --</option>
            <?php
            // Fetch categories from the database
            $categoryQuery = "SELECT id, name FROM categories";
            $categoryResult = mysqli_query($conn, $categoryQuery);
            while ($category = mysqli_fetch_assoc($categoryResult)) {
                echo "<option value='" . htmlspecialchars($category['id']) . "' " . (isset($_GET['category']) && $_GET['category'] === $category['id'] ? 'selected' : '') . ">" . htmlspecialchars($category['name']) . "</option>";
            }
            ?>
        </select>

        <!-- Brand Dropdown (Optional if brand table exists) -->
        <label for="brand" style="font-weight: 500;">Brand:</label>
        <select name="brand" id="brand" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px; min-width: 140px;">
            <option value="">-- Select Brand --</option>
            <?php
            // Fetch brands from the database (if brand table exists)
            $brandQuery = "SELECT id, name FROM brands";
            $brandResult = mysqli_query($conn, $brandQuery);
            while ($brand = mysqli_fetch_assoc($brandResult)) {
                echo "<option value='" . htmlspecialchars($brand['id']) . "' " . (isset($_GET['brand']) && $_GET['brand'] === $brand['id'] ? 'selected' : '') . ">" . htmlspecialchars($brand['name']) . "</option>";
            }
            ?>
        </select>

        <button type="submit" style="padding: 10px 15px; background-color: #ff6600; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 10px;">Apply</button>
    </form>

    <div class="product-grid">
        <?php
        // 🛠 Build dynamic query based on filters
        $conditions = [];
        $params = [];

        // Price filters
        if (!empty($_GET['min_price'])) {
            $conditions[] = "price >= ?";
            $params[] = (float)$_GET['min_price'];
        }
        if (!empty($_GET['max_price'])) {
            $conditions[] = "price <= ?";
            $params[] = (float)$_GET['max_price'];
        }

        // Category Filter
        if (!empty($_GET['category'])) {
            $conditions[] = "category_id = ?";
            $params[] = (int)$_GET['category'];
        }

        // Brand Filter (optional)
        if (!empty($_GET['brand'])) {
            $conditions[] = "brand_id = ?";
            $params[] = (int)$_GET['brand'];
        }

        $query = "SELECT * FROM products";

        // Add WHERE if there are any filters
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Sorting logic
        $sortOption = $_GET['sort'] ?? '';
        switch ($sortOption) {
            case 'price_asc':
                $query .= " ORDER BY price ASC";
                break;
            case 'price_desc':
                $query .= " ORDER BY price DESC";
                break;
            case 'name_asc':
                $query .= " ORDER BY name ASC";
                break;
            case 'name_desc':
                $query .= " ORDER BY name DESC";
                break;
        }

        // Prepare and execute query
        $stmt = $conn->prepare($query);

        if (!empty($params)) {
            $types = str_repeat('d', count($params)); // All are numbers, use 'd'
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Display products
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
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

        $stmt->close();
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
