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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Product - <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #22c55e;
            --primary-dark: #16a34a;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --font-size: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--gray-100);
            padding-top: 80px;
            color: var(--gray-800);
            font-size: var(--font-size);
            line-height: 1.6;
        }

        header {
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 24px 32px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .back-button {
    background: #3b82f6;        /* Blue 500 */
    color: white;
    padding: 10px 18px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s ease;
    font-size: 0.95rem;
}

.back-button:hover {
    background: #1e40af;        /* Blue 900 - Darker blue */
    color: white;
}


        main {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 50px auto;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 1rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--gray-300);
            border-radius: 10px;
            font-size: 1rem;
            background: white;
            transition: 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        .submit-button {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease, transform 0.2s;
            width: 100%;
        }

        .submit-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.75rem;
            font-weight: 500;
        }

        .success {
            background-color: #dcfce7;
            color: #166534;
        }

        .error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .current-image {
            text-align: center;
            margin-top: 10px;
        }

        .current-image img {
            max-width: 220px;
            max-height: 220px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .form-text {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.4rem;
        }

        .stock-status {
            display: inline-block;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-in_stock {
            background: #dcfce7;
            color: #166534;
        }

        .stock-low_stock {
            background: #fef9c3;
            color: #92400e;
        }

        .stock-out_of_stock {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            main {
                margin: 20px 1rem;
                padding: 1.75rem;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            header h1 {
                font-size: 1.5rem;
            }

            .back-button {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

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

            <!-- <div class="form-group">
                <label for="image_url">Or Image URL</label>
                <input type="url" id="image_url" name="image_url" 
                       value="<?= htmlspecialchars($product['image']); ?>" 
                       placeholder="https://example.com/image.jpg">
            </div> -->

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