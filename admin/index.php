<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
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
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
        }

        /* Header & Navbar */
        header {
            background-color: #000;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        nav {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-top: 10px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            transition: 0.3s ease-in-out;
        }

        nav a:hover {
            background: #333;
            border-radius: 5px;
        }

        /* Main Content */
        main {
            text-align: center;
            margin-top: 60px;
            padding: 30px;
            background: white;
            width: 50%;
            margin: 50px auto;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        p {
            font-size: 18px;
            color: #555;
        }

        /* Footer */
        footer {
            margin-top: 30px;
            text-align: center;
            padding: 15px;
            background: #000;
            color: white;
            font-size: 14px;
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.2);
        }
        .admin-header{
            height: 30rem;
        }
    </style>
</head>
<body>
<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="products.php">Manage Products</a>
        <a href="orders.php">Manage Orders</a>
        <a href="users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="admin-header">

<main>
    <h2>Welcome, <?php echo $_SESSION['admin_name']; ?>!</h2>
    <p>Manage your eCommerce site efficiently.</p>
</main>
</div>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Mero Shopping. All Rights Reserved.</p>
</footer>

</body>
</html>
