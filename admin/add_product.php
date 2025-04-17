<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

    // Price validation: Ensure price is greater than zero
    if ($price <= 0) {
        $_SESSION['error'] = "Price must be greater than zero.";
        header("Location: add_product.php");
        exit();
    }

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header("Location: add_product.php");
            exit();
        }

        // Check file size (e.g., 5MB)
        if ($_FILES['image']['size'] > 5000000) {
            $_SESSION['error'] = "File is too large.";
            header("Location: add_product.php");
            exit();
        }

        // Allow only certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['error'] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            header("Location: add_product.php");
            exit();
        }

        // Upload the file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = basename($_FILES['image']['name']);
        } else {
            $_SESSION['error'] = "Failed to upload file.";
            header("Location: add_product.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No image file uploaded.";
        header("Location: add_product.php");
        exit();
    }

    // Insert product into the database
    $stmt = $conn->prepare("INSERT INTO products (name, price, image, stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $price, $image, $stock);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Error adding product!";
    }
    header("Location: products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        /* Your existing styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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

        h1 {
            margin: 0;
            font-size: 24px;
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

        /* Form Styling */
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

        input[type="text"],
        input[type="number"],
        input[type="file"] {
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

        .submit-button:hover {
            background: #218838;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Add New Product</h1>
        <a href="products.php" class="back-button">Back to Products</a>
    </header>
    <main>
        <?php if (isset($_SESSION['error'])) { echo "<p class='error'>" . $_SESSION['error'] . "</p>"; unset($_SESSION['error']); } ?>
        <?php if (isset($_SESSION['success'])) { echo "<p class='success'>" . $_SESSION['success'] . "</p>"; unset($_SESSION['success']); } ?>
        <form action="add_product.php" method="POST" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter product name" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" placeholder="Enter price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" placeholder="Enter stock quantity" min="0" required>
            </div>
            <div class="form-group">
                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image" required>
            </div>
            <button type="submit" class="submit-button">Add Product</button>
        </form>
    </main>
</body>
</html>
