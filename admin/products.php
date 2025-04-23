<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Fetch products
$stmt = $conn->query("SELECT id, name, price FROM products");
$products = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
    <header>Manage Products</header>
    <nav>
        <a href="index.php">🏠 Dashboard</a>
        <a href="add_product.php">➕ Add Product</a>
    </nav>
    <main>
        <h2>Product List</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo $product['name']; ?></td>
                    <td>$<?php echo $product['price']; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
