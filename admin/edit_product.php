<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/stock_functions.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product ID is missing.");
}

$id = intval($_GET['id']);

// Fetch product with calculated stock status
$stmt = $conn->prepare("SELECT *, 
                       CASE 
                           WHEN stock <= 0 THEN 'out_of_stock'
                           WHEN stock <= IFNULL(min_stock_level, 5) THEN 'low_stock'
                           ELSE 'in_stock'
                       END as stock_status
                       FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Handle image display
$imageFile = $product['image'];
$imageUrl = '';
$showImage = false;

if (!empty($imageFile) && $imageFile !== '0') {
    if (filter_var($imageFile, FILTER_VALIDATE_URL)) {
        $imageUrl = $imageFile;
        $showImage = true;
    } else {
        $serverPath = __DIR__ . '/../uploads/' . $imageFile;
        $imageUrl = '../uploads/' . $imageFile;
        $showImage = file_exists($serverPath);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Product - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 80px;
            margin: 0;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            width: 100%;
            position: fixed;
            top: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .back-button {
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            position: absolute;
            right: 20px;
            top: 20px;
            transition: background 0.3s;
        }
        .back-button:hover {
            background: #5a6268;
        }
        main {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            width: 90%;
            max-width: 600px;
            margin: 30px auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .submit-button {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            width: 100%;
        }
        .submit-button:hover {
            background: #218838;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .current-image {
            margin: 15px 0;
            text-align: center;
        }
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stock-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 600;
        }
        .stock-in_stock {
            background: #d4edda;
            color: #155724;
        }
        .stock-low_stock {
            background: #fff3cd;
            color: #856404;
        }
        .stock-out_of_stock {
            background: #f8d7da;
            color: #721c24;
        }
        .form-text {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
    </style>
</head>
<body>
    <header>
        <h1 style="margin: 0; font-size: 24px;">Edit Product: <?= htmlspecialchars($product['name']) ?></h1>
        <a href="products.php" class="back-button">Back to Products</a>
    </header>

    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="update_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($id); ?>">

            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (<?= CURRENCY_SYMBOL ?>)</label>
                <input type="number" step="0.01" id="price" name="price" 
                       value="<?= htmlspecialchars($product['price']); ?>" required>
            </div>

            <div class="form-group">
                <label>Current Image</label>
                <div class="current-image">
                    <?php if ($showImage): ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Current Product Image">
                    <?php else: ?>
                        <p style="color: #6c757d;">No image available</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="image_file">Upload New Image</label>
                <input type="file" id="image_file" name="image_file" accept="image/*">
                <small class="form-text">Max size: 2MB (JPEG, PNG, GIF)</small>
            </div>

            <div class="form-group">
                <label for="image_url">Or Image URL</label>
                <input type="url" id="image_url" name="image_url" 
                       value="<?= htmlspecialchars($product['image']); ?>" 
                       placeholder="https://example.com/image.jpg">
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $categories = $conn->query("SELECT * FROM categories ORDER BY name");
                    while ($category = $categories->fetch_assoc()):
                    ?>
                        <option value="<?= $category['id'] ?>" 
                            <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="stock">Stock Quantity</label>
                <input type="number" id="stock" name="stock" 
                       value="<?= htmlspecialchars($product['stock']); ?>" 
                       min="0" required>
                <small class="form-text">Current status: 
                    <span class="stock-status stock-<?= $product['stock_status'] ?>">
                        <?= ucwords(str_replace('_', ' ', $product['stock_status'])) ?>
                    </span>
                </small>
            </div>

            <div class="form-group">
                <label for="min_stock_level">Low Stock Threshold</label>
                <input type="number" id="min_stock_level" name="min_stock_level" 
                       value="<?= htmlspecialchars($product['min_stock_level'] ?? 5); ?>" 
                       min="1">
                <small class="form-text">System will generate alerts when stock falls below this level</small>
            </div>

            <div class="form-group">
                <label>Stock Status</label>
                <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                    <span class="stock-status stock-<?= $product['stock_status'] ?>">
                        <?= ucwords(str_replace('_', ' ', $product['stock_status'])) ?>
                    </span>
                    <small class="form-text">(Automatically calculated based on current stock levels)</small>
                </div>
            </div>

            <button type="submit" class="submit-button">Update Product</button>
        </form>
    </main>
</body>
</html>