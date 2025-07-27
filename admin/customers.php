<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Fetch users
$stmt = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Customers | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --card-bg: rgba(255, 255, 255, 0.98);
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            --sidebar-width: 280px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.5;
            overflow-x: hidden;
        }

        /* Glassmorphism Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding: 1.5rem 0;
            z-index: 100;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .sidebar-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-header small {
            color: var(--gray-500);
            font-size: 0.875rem;
            display: block;
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            padding: 0 1rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-item {
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            transition: var(--transition);
        }

        .nav-item:hover {
            background: rgba(99, 102, 241, 0.1);
        }

        .nav-item.active {
            background: var(--primary);
        }

        .nav-item.active a {
            color: white;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--gray-800);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            gap: 0.75rem;
        }

        .nav-item i {
            font-size: 1.1rem;
            width: 1.5rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: var(--transition);
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .page-title p {
            color: var(--gray-500);
            font-size: 0.9375rem;
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        /* Customers Container */
        .customers-container {
            background: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        /* Search Filter */
        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-filter input {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            width: 300px;
            transition: var(--transition);
        }

        .search-filter input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        /* Customers Table */
        .customers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .customers-table th, 
        .customers-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .customers-table th {
            background-color: var(--gray-100);
            color: var(--gray-600);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .customers-table tr:hover {
            background-color: var(--gray-100);
        }

        /* Action Buttons */
        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            text-decoration: none;
        }

        .delete-btn {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: none;
        }

        .delete-btn:hover {
            background-color: rgba(239, 68, 68, 0.2);
        }

        /* No Customers */
        .no-customers {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-store"></i> MeroShopping</h2>
            <small>Admin Dashboard</small>
        </div>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <div class="nav-item">
                <a href="dashboard.php" role="menuitem">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="products.php" role="menuitem">
                    <i class="fas fa-box-open"></i>
                    <span>Products</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="orders.php" role="menuitem">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Orders</span>
                </a>
            </div>
            <div class="nav-item active">
                <a href="customers.php" role="menuitem">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </div>
            <div class="nav-item mt-auto">
                <a href="logout.php" role="menuitem">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <div class="page-title">
                <h1>Customer Management</h1>
                <p>View and manage your store customers</p>
            </div>
            <div class="user-avatar">
                <?php
                $initial = $_SESSION['admin_name'] ?? 'A';
                $initial = htmlspecialchars($initial);
                $initial = substr($initial, 0, 1);
                echo strtoupper($initial);
                ?>
            </div>
        </div>

        <div class="customers-container">
            <div class="search-filter">
                <input type="text" id="search-input" placeholder="Search by name or email">
            </div>

       <div class="customers-container">

    <?php 
    if (isset($_SESSION['success'])) {
        echo '<p style="color: green; font-weight: bold; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['success']) . '</p>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red; font-weight: bold; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    ?>

    <table class="customers-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" class="no-customers">No customers found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <a href="delete_users.php?id=<?= $user['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this customer?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

    </main>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.createElement('button');
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            menuToggle.style.position = 'fixed';
            menuToggle.style.top = '10px';
            menuToggle.style.left = '10px';
            menuToggle.style.background = 'var(--primary)';
            menuToggle.style.color = 'white';
            menuToggle.style.border = 'none';
            menuToggle.style.borderRadius = '50%';
            menuToggle.style.width = '40px';
            menuToggle.style.height = '40px';
            menuToggle.style.display = 'none';
            menuToggle.style.zIndex = '1000';
            menuToggle.style.cursor = 'pointer';
            document.body.appendChild(menuToggle);

            const sidebar = document.querySelector('.sidebar');

            function checkMobile() {
                if (window.innerWidth <= 992) {
                    menuToggle.style.display = 'flex';
                    sidebar.style.display = 'none';
                } else {
                    menuToggle.style.display = 'none';
                    sidebar.style.display = 'block';
                }
            }

            menuToggle.addEventListener('click', function() {
                sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
            });

            window.addEventListener('resize', checkMobile);
            checkMobile();
        });

        // Live search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.customers-table tbody tr');
            
            rows.forEach(row => {
                if (row.classList.contains('no-customers')) return;
                
                const name = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>