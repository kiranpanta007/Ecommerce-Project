<?php
include 'includes/db.php'; // Include database connection

// Start session for wishlist functionality
session_start();

// Get the category ID from the URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$category_name = 'All Products'; // Default category name if no category is selected

// Fetch the category name based on the category ID
if ($category_id > 0) {
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($category_name);
    $stmt->fetch();
    $stmt->close();
}

// Pagination setup
$limit = 12; // Number of products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Sorting setup
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc'; // Default sorting
switch ($sort) {
    case 'price_asc':
        $order_by = 'price ASC';
        break;
    case 'price_desc':
        $order_by = 'price DESC';
        break;
    case 'name_asc':
        $order_by = 'name ASC';
        break;
    case 'name_desc':
        $order_by = 'name DESC';
        break;
    case 'rating_asc':
        $order_by = 'rating ASC';
        break;
    case 'rating_desc':
        $order_by = 'rating DESC';
        break;
    default:
        $order_by = 'price ASC';
}

// Price filter
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 999999;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = !empty($search) ? " AND name LIKE '%$search%'" : '';

// Filter by attributes (Color, Size, Brand)
$color_filter = isset($_GET['color']) ? $_GET['color'] : '';
$size_filter = isset($_GET['size']) ? $_GET['size'] : '';
$brand_filter = isset($_GET['brand']) ? $_GET['brand'] : '';

$filter_condition = '';
if ($color_filter) {
    $filter_condition .= " AND color = '$color_filter'";
}
if ($size_filter) {
    $filter_condition .= " AND size = '$size_filter'";
}
if ($brand_filter) {
    $filter_condition .= " AND brand = '$brand_filter'";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop by Category</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        /* Header */
        .breadcrumb {
            padding: 10px 0;
            background-color: #fff;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            color: #333;
        }
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Main Container */
        .uni-main-container {
            display: flex;
            margin: 20px;
            gap: 20px;
        }
        .uni-sidebar {
            width: 20%;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .uni-category-title {
            font-size: 18px;
            margin-bottom: 15px;
        }
        .uni-category-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .uni-category-list li {
            margin-bottom: 10px;
        }
        .uni-category-list a {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
        }
        .uni-category-list a:hover {
            text-decoration: underline;
        }
        .uni-category-list .active {
            font-weight: bold;
            color: #0056b3;
        }

        /* Products Section */
        .uni-products-container {
            width: 75%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .uni-products-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .sort-form select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        /* Product Cards */
     /* Product Cards */
.uni-product-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.uni-product-card {
    background-color: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s;
    height: 350px; /* Fixed height to maintain uniformity */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.uni-product-card:hover {
    transform: translateY(-10px);
}

.uni-product-image {
    width: 100%;
    height: 200px; /* Fixed height for product images */
    object-fit: contain; /* Ensures images don't stretch or skew */
    border-radius: 8px;
    margin-bottom: 15px;
}

.uni-product-name {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.uni-product-price {
    font-size: 16px;
    margin-bottom: 10px;
    color: #007bff;
}

.quick-view-btn, .add-to-wishlist-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    font-size: 16px;
    margin-top: 10px;
    transition: background-color 0.3s;
}

.quick-view-btn:hover, .add-to-wishlist-btn:hover {
    background-color: #0056b3;
}

        /* Pagination */
        .uni-pagination {
            text-align: center;
            margin-top: 30px;
        }
        .uni-pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #f4f4f4;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .uni-pagination a:hover {
            background-color: #007bff;
            color: white;
        }
        .uni-pagination .active {
            background-color: #007bff;
            color: white;
        }

        /* Quick View Modal */
        .quick-view-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 60%;
            max-width: 600px;
            text-align: center;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">Home</a> > <a href="category.php?id=<?php echo $category_id; ?>"><?php echo htmlspecialchars($category_name); ?></a>
    </div>

    <!-- Main Container -->
    <div class="uni-main-container">

        <!-- Sidebar -->
        <div class="uni-sidebar">
            <h3 class="uni-category-title">Category</h3>
            <ul class="uni-category-list">
                <?php
                // Fetch categories from the database
                $query = "SELECT * FROM categories";
                $result = mysqli_query($conn, $query);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $active_class = $category_id == $row['id'] ? 'active' : '';
                        echo "<li><a href='category.php?id=" . $row['id'] . "' class='$active_class'>" . htmlspecialchars($row['name']) . "</a></li>";
                    }
                }
                ?>
            </ul>
        </div>

        <!-- Products Content -->
        <div class="uni-products-container">
            <h2>Products in <?php echo htmlspecialchars($category_name); ?></h2>

            <!-- Sort Dropdown -->
            <form method="GET" class="sort-form">
                <select name="sort" onchange="this.form.submit()">
                    <option value="price_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                    <option value="name_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_desc' ? 'selected' : ''; ?>>Name: Z-A</option>
                    <option value="rating_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'rating_asc' ? 'selected' : ''; ?>>Rating: Low to High</option>
                    <option value="rating_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'rating_desc' ? 'selected' : ''; ?>>Rating: High to Low</option>
                </select>
            </form>

            <?php
            // If a valid category is selected
            if ($category_id > 0) {
                // Prepare the product query for the selected category
                $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND price BETWEEN ? AND ? $search_condition $filter_condition ORDER BY $order_by LIMIT ? OFFSET ?");
                $stmt->bind_param("iiiii", $category_id, $min_price, $max_price, $limit, $offset);
                $stmt->execute();
                $product_result = $stmt->get_result();

                if ($product_result && mysqli_num_rows($product_result) > 0) {
                    echo "<div class='uni-product-list'>";
                    while ($product = mysqli_fetch_assoc($product_result)) {
                        // Fallback for missing product images
                        $product_image = !empty($product['image']) ? 'assets/images/' . $product['image'] : 'assets/images/default.jpg';

                        echo "<div class='uni-product-card'>
                            <img src='$product_image' alt='" . htmlspecialchars($product['name']) . "' class='uni-product-image'>
                            <h3 class='uni-product-name'>" . htmlspecialchars($product['name']) . "</h3>
                            <p class='uni-product-price'>" . CURRENCY_SYMBOL . " " . number_format($product['price'], 2) . "</p>
                            <button class='quick-view-btn' data-product-id='" . (int)$product['id'] . "'>Quick View</button>
                            <button class='add-to-wishlist-btn' data-product-id='" . (int)$product['id'] . "'>Add to Wishlist</button>
                        </div>";
                    }
                    echo "</div>";

                    // Pagination controls
                    $stmt->close();
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                    $stmt->bind_param("i", $category_id);
                    $stmt->execute();
                    $stmt->bind_result($total_products);
                    $stmt->fetch();
                    $total_pages = ceil($total_products / $limit);
                    echo "<div class='uni-pagination'>";
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo "<a href='category.php?id=$category_id&page=$i' class='" . ($i == $page ? 'active' : '') . "'>$i</a>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>No products found in this category.</p>";
                }
            } else {
                // If no category is selected, display a message
                echo "<p>Please select a category to view products.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Quick View Modal -->
    <div id="quick-view-modal" class="quick-view-modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div id="quick-view-details"></div>
        </div>
    </div>

    <!-- JavaScript for Quick View and Wishlist -->
    <script>
        // Quick View Modal Functionality
        document.querySelectorAll('.quick-view-btn').forEach(button => {
            button.addEventListener('click', function() {
                let productId = this.getAttribute('data-product-id');
                fetch(`quick-view.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('quick-view-details').innerHTML = `
                            <img src="${data.image}" alt="${data.name}">
                            <h3>${data.name}</h3>
                            <p>${data.description}</p>
                            <p>${data.price}</p>
                        `;
                        document.getElementById('quick-view-modal').style.display = 'block';
                    });
            });
        });

        // Close Quick View Modal
        document.querySelector('.close-btn').addEventListener('click', function() {
            document.getElementById('quick-view-modal').style.display = 'none';
        });

        // Add to Wishlist
        document.querySelectorAll('.add-to-wishlist-btn').forEach(button => {
            button.addEventListener('click', function() {
                let productId = this.getAttribute('data-product-id');
                fetch(`add-to-wishlist.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Product added to wishlist');
                        }
                    });
            });
        });
    </script>

</body>
</html>
