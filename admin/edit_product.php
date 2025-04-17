<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php'; // Correct path to db.php

// Ensure database connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Validate product ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product ID is missing.");
}

$id = intval($_GET['id']); // Secure ID input
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding-top: 80px;
            margin: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        header {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            position: absolute;
            right: 20px;
            top: 15px;
        }
        main {
            margin-top: 100px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .submit-button {
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <header>
        <h1>Edit Product</h1>
        <a href="products.php" class="back-button">Back to Products</a>
    </header>

    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="update_product.php" method="POST">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($id); ?>">

            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" value="<?= htmlspecialchars($product['price']); ?>" required>
            </div>

            <div class="form-group">
                <label for="image">Product Image URL:</label>
                <input type="text" id="image" name="image" value="<?= htmlspecialchars($product['image']); ?>">
            </div>

            <div class="form-group">
                <label for="stock">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($product['stock']); ?>" min="0" required>
            </div>

            <button type="submit" class="submit-button">Update Product</button>
        </form>
    </main>
</body>
</html>
