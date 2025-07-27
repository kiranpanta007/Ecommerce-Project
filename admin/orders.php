<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', 'NRS');
if (!defined('ORDERS_PER_PAGE')) define('ORDERS_PER_PAGE', 10);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * ORDERS_PER_PAGE;
$limit = ORDERS_PER_PAGE;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = '%' . $search . '%';

$orders = [];
$total_orders = 0;
$total_pages = 1;

try {
    // Count total (adjust for search)
    if (!empty($search)) {
        $countStmt = $conn->prepare("
            SELECT COUNT(*) FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR o.transaction_uuid LIKE ?
        ");
        $countStmt->bind_param('ssss', $search_param, $search_param, $search_param, $search_param);
        $countStmt->execute();
        $total_orders = (int)$countStmt->get_result()->fetch_row()[0];
        $countStmt->close();
    } else {
        $total_orders = (int)$conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
    }
    $total_pages = max(1, (int)ceil($total_orders / ORDERS_PER_PAGE));

    if (!empty($search)) {
        $sql = "
            SELECT o.id, o.user_id,
                   COALESCE(u.username, 'Guest') AS username,
                   COALESCE(u.email, 'N/A') AS email,
                   o.order_date,
                   COALESCE(o.total_price, 0) AS total_amount,
                   COALESCE(o.status, 'pending') AS status,
                   o.payment_method,
                   o.shipping_status,
                   o.transaction_uuid
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE
                o.id LIKE ? OR
                u.username LIKE ? OR
                u.email LIKE ? OR
                o.transaction_uuid LIKE ?
            ORDER BY o.order_date DESC
            LIMIT ?, ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssii', $search_param, $search_param, $search_param, $search_param, $start, $limit);
    } else {
        $sql = "
            SELECT o.id, o.user_id,
                   COALESCE(u.username, 'Guest') AS username,
                   COALESCE(u.email, 'N/A') AS email,
                   o.order_date,
                   COALESCE(o.total_price, 0) AS total_amount,
                   COALESCE(o.status, 'pending') AS status,
                   o.payment_method,
                   o.shipping_status,
                   o.transaction_uuid
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.order_date DESC
            LIMIT ?, ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $start, $limit);
    }

    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        error_log("Order fetch error: " . $conn->error);
        $error_message = "Error loading orders.";
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $error_message = "An unexpected error occurred.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Orders | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

        /* Orders Container */
        .orders-container {
            background: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-filter input, 
        .search-filter select {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .search-filter input:focus, 
        .search-filter select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        /* Order Table */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .order-table th, 
        .order-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .order-table th {
            background-color: var(--gray-100);
            color: var(--gray-600);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .order-table tr:hover {
            background-color: var(--gray-100);
        }

        /* Status Badges */
        .status-pending {
            color: var(--warning);
            font-weight: 600;
        }

        .status-processing {
            color: var(--info);
            font-weight: 600;
        }

        .status-completed {
            color: var(--success);
            font-weight: 600;
        }

        .status-cancelled {
            color: var(--danger);
            font-weight: 600;
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

        .view-btn {
            background-color: var(--info);
            color: white;
        }

        .view-btn:hover {
            background-color: #1d4ed8;
        }

        .update-btn {
            background-color: var(--warning);
            color: var(--gray-900);
        }

        .update-btn:hover {
            background-color: #d97706;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.375rem;
            text-decoration: none;
            color: var(--gray-700);
            transition: var(--transition);
        }

        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination a:hover:not(.active) {
            background-color: var(--gray-100);
        }

        /* Error Message */
        .error-message {
            color: var(--danger);
            padding: 1rem;
            background-color: rgba(239, 68, 68, 0.1);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        /* No Orders */
        .no-orders {
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
            <div class="nav-item active">
                <a href="orders.php" role="menuitem">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Orders</span>
                </a>
            </div>
            <div class="nav-item">
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
                <h1>Order Management</h1>
                <p>View and manage customer orders</p>
            </div>
            <div class="user-avatar" title="<?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin' ?>">
                <?php
                $initial = $_SESSION['admin_name'] ?? 'A';
                $initial = htmlspecialchars($initial);
                $initial = mb_substr($initial, 0, 1);
                echo strtoupper($initial);
                ?>
            </div>
        </div>

        <div class="orders-container">
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <div class="search-filter">
                <input type="text" id="search-input" placeholder="Search by order ID or customer" aria-label="Search orders" />
                <select id="status-filter" aria-label="Filter orders by status">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <table class="order-table" aria-describedby="order-list">
                <thead>
                    <tr>
                        <th scope="col">Order ID</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Date</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="no-orders">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['id']) ?></td>
                            <td>
                                <?= htmlspecialchars($order['username']) ?><br />
                                <small><?= htmlspecialchars($order['email']) ?></small>
                            </td>
                            <td><?= isset($order['order_date']) ? date('M d, Y h:i A', strtotime($order['order_date'])) : 'N/A' ?></td>
                            <td><?= CURRENCY_SYMBOL ?> <?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="status-<?= htmlspecialchars(strtolower($order['status'])) ?>">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_details.php?id=<?= urlencode($order['id']) ?>" class="action-btn view-btn" aria-label="View order #<?= htmlspecialchars($order['id']) ?>">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="update_order.php?id=<?= urlencode($order['id']) ?>" class="action-btn update-btn" aria-label="Update order #<?= htmlspecialchars($order['id']) ?>">
                                    <i class="fas fa-edit"></i> Update
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="orders.php?page=<?= $page - 1 ?>" aria-label="Previous page">&laquo;</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="orders.php?page=<?= $i ?>" <?= ($i === $page) ? 'class="active" aria-current="page"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="orders.php?page=<?= $page + 1 ?>" aria-label="Next page">&raquo;</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function () {
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
            menuToggle.setAttribute('aria-label', 'Toggle menu');
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

            menuToggle.addEventListener('click', function () {
                sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
            });

            window.addEventListener('resize', checkMobile);
            checkMobile();
        });

        // Live search functionality
        document.getElementById('search-input').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.order-table tbody tr');

            rows.forEach(row => {
                if (row.classList.contains('no-orders')) return;

                const orderId = row.cells[0].textContent.toLowerCase();
                const customer = row.cells[1].textContent.toLowerCase();

                if (orderId.includes(searchTerm) || customer.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Status filter functionality
        document.getElementById('status-filter').addEventListener('change', function () {
            const status = this.value.toLowerCase();
            const rows = document.querySelectorAll('.order-table tbody tr');

            rows.forEach(row => {
                if (row.classList.contains('no-orders')) return;

                const statusCell = row.cells[4].textContent.toLowerCase();

                if (status === '' || statusCell === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
