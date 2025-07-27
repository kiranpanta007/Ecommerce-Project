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
        /* Modern font & reset */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 80px;
        }

        header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            width: 100%;
            position: fixed;
            top: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .back-button {
            background: #3b82f6; /* Blue */
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            position: absolute;
            right: 20px;
            top: 22px;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background: #1e40af; /* Darker blue */
            color: white;
        }

        main {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
            width: 90%;
            max-width: 450px;
            box-sizing: border-box;
            margin: auto 0;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
            font-size: 1rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 1rem;
            border: 1.5px solid #ced4da;
            border-radius: 6px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="file"]:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(59,130,246,0.25);
        }

        .submit-button {
            width: 100%;
            background: #28a745;
            color: white;
            padding: 14px 0;
            font-size: 18px;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-button:hover {
            background: #218838;
        }

        .error {
            color: #dc3545;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .success {
            color: #28a745;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1rem;
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
