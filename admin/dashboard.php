<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>

<header>
    Admin Dashboard
</header>

<nav>
    <a href="products.php">Manage Products</a>
    <a href="orders.php">Manage Orders</a>
    <a href="users.php">Manage Users</a>
    <a href="logout.php" class="logout">Logout</a>
</nav>

<div class="container">
    <h1>Welcome, Admin!</h1>
    <p>Manage your eCommerce site he
