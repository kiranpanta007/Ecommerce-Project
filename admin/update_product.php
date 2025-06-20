<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/stock_functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: products.php");
    exit();
}

// Validate product ID
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
if ($product_id <= 0) {
    $_SESSION['error'] = "Invalid product ID";
    header("Location: products.php");
    exit();
}

// Get current product data (for image handling)
$current_product = $conn->prepare("SELECT image FROM products WHERE id = ?");
$current_product->bind_param("i", $product_id);
$current_product->execute();
$current_result = $current_product->get_result();
$current_data = $current_result->fetch_assoc();

// Initialize variables
$image_path = $current_data['image'] ?? '';
$upload_dir = __DIR__ . '/../uploads/';

// Handle file upload
if (isset($_FILES['image_file']['tmp_name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $_FILES['image_file']['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['error'] = "Only JPG, PNG, GIF, and WebP images are allowed";
        header("Location: edit_product.php?id=$product_id");
        exit();
    }

    // Generate unique filename
    $file_ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
    $new_filename = 'product_' . uniqid() . '.' . strtolower($file_ext);
    $destination = $upload_dir . $new_filename;

    // Move uploaded file
    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $destination)) {
        // Delete old image if it exists and isn't a URL
        if (!empty($current_data['image']) && !filter_var($current_data['image'], FILTER_VALIDATE_URL)) {
            $old_image_path = $upload_dir . basename($current_data['image']);
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
        $image_path = $new_filename;
    } else {
        $_SESSION['error'] = "Failed to upload image";
        header("Location: edit_product.php?id=$product_id");
        exit();
    }
} 
// Handle image URL if provided
elseif (!empty(trim($_POST['image_url']))) {
    $image_url = filter_var(trim($_POST['image_url']), FILTER_VALIDATE_URL);
    if ($image_url) {
        // Delete old image if it exists and isn't a URL
        if (!empty($current_data['image']) && !filter_var($current_data['image'], FILTER_VALIDATE_URL)) {
            $old_image_path = $upload_dir . basename($current_data['image']);
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
        $image_path = $image_url;
    } else {
        $_SESSION['error'] = "Invalid image URL";
        header("Location: edit_product.php?id=$product_id");
        exit();
    }
}

// Prepare and execute update
try {
    $stmt = $conn->prepare("UPDATE products SET 
                          name = ?,
                          description = ?,
                          price = ?,
                          image = ?,
                          category_id = ?,
                          stock = ?,
                          min_stock_level = ?
                          WHERE id = ?");
    
    $stmt->bind_param("ssdsiiii",
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $image_path,
        $_POST['category_id'],
        $_POST['stock'],
        $_POST['min_stock_level'],
        $product_id
    );

    if ($stmt->execute()) {
        // Update stock status
        updateStockStatus($product_id, $conn);
        
        $_SESSION['success'] = "Product updated successfully";
        header("Location: edit_product.php?id=$product_id");
        exit();
    } else {
        throw new Exception("Database update failed");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error updating product: " . $e->getMessage();
    header("Location: edit_product.php?id=$product_id");
    exit();
}
?>