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
               CASE 
                   WHEN u.name IS NOT NULL AND u.name <> '' THEN u.name
                   WHEN u.username IS NULL OR u.username = '' THEN 'Guest' 
                   ELSE u.username 
               END AS customer_name,
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
               CASE 
                   WHEN u.name IS NOT NULL AND u.name <> '' THEN u.name
                   WHEN u.username IS NULL OR u.username = '' THEN 'Guest' 
                   ELSE u.username 
               END AS customer_name,
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
  :root {
    --color-primary: #5c6ac4;
    --color-primary-light: #8a98f8;
    --color-success: #3ac47d;
    --color-warning: #ffb822;
    --color-danger: #f65058;
    --color-info: #17a2b8;
    --color-bg: #f9fafc;
    --color-white: #fff;
    --color-text: #333;
    --color-text-light: #666;
    --shadow: 0 4px 8px rgba(0,0,0,0.05);
    --border-radius: 8px;
    --transition: 0.3s ease;
  }
  * {
    box-sizing: border-box;
  }
  body {
    font-family: 'Inter', sans-serif;
    background: var(--color-bg);
    color: var(--color-text);
    margin: 0;
    min-height: 100vh;
    overflow-x: hidden;
  }
  /* Sidebar */
  .sidebar {
    position: fixed;
    top: 0; left: 0;
    width: 260px;
    height: 100vh;
    background: var(--color-white);
    box-shadow: var(--shadow);
    padding: 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    transition: transform var(--transition);
    z-index: 100;
  }
  .sidebar.collapsed {
    transform: translateX(-100%);
  }
  .sidebar-header {
    font-weight: 700;
    font-size: 1.5rem;
    color: var(--color-primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .sidebar-header i {
    font-size: 1.8rem;
  }
  .sidebar small {
    color: var(--color-text-light);
    margin-bottom: 2rem;
    font-size: 0.875rem;
  }
  nav.sidebar-nav {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  .nav-item a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--color-text);
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    transition: background var(--transition), color var(--transition);
  }
  .nav-item a i {
    min-width: 24px;
    font-size: 1.2rem;
  }
  .nav-item.active a,
  .nav-item a:hover {
    background: var(--color-primary);
    color: var(--color-white);
  }
  .nav-item.mt-auto {
    margin-top: auto;
  }

  /* Main Content */
  main.main-content {
    margin-left: 260px;
    padding: 2rem 2.5rem;
    transition: margin-left var(--transition);
  }
  main.main-content.collapsed {
    margin-left: 0;
  }

  /* Header */
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
  }
  .page-title h1 {
    font-weight: 700;
    font-size: 2rem;
  }
  .page-title p {
    color: var(--color-text-light);
    font-size: 1rem;
  }
  .user-avatar {
    background: var(--color-primary);
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    font-weight: 700;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: default;
    box-shadow: 0 0 6px var(--color-primary-light);
    user-select: none;
  }

  /* Orders container */
  .orders-container {
    background: var(--color-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem 2rem;
  }

  /* Search and filter */
  .search-filter {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }
  .search-filter input,
  .search-filter select {
    flex-grow: 1;
    padding: 0.6rem 1rem;
    border: 1.5px solid #d2d6de;
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: border-color var(--transition), box-shadow var(--transition);
  }
  .search-filter input:focus,
  .search-filter select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 5px var(--color-primary-light);
  }
  /* Table */
  table.order-table {
    width: 100%;
    border-collapse: collapse;
  }
  thead tr {
    background: #f0f4ff;
  }
  th, td {
    padding: 1rem 1.25rem;
    text-align: left;
    border-bottom: 1px solid #e3e8ff;
    font-size: 0.95rem;
  }
  th {
    color: var(--color-primary);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  tbody tr:hover {
    background: #f9faff;
  }
  .no-orders {
    text-align: center;
    color: var(--color-text-light);
    padding: 2rem;
    font-style: italic;
  }

  /* Status badges */
  .status-pending {
    color: #d97706;
    font-weight: 700;
  }
  .status-processing {
    color: var(--color-info);
    font-weight: 700;
  }
  .status-completed {
    color: var(--color-success);
    font-weight: 700;
  }
  .status-cancelled {
    color: var(--color-danger);
    font-weight: 700;
  }

  /* Pagination */
  .pagination {
    margin-top: 1.5rem;
    text-align: right;
  }
  .pagination a,
  .pagination span {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    margin-left: 0.25rem;
    color: var(--color-primary);
    font-weight: 600;
    border-radius: var(--border-radius);
    border: 1.5px solid transparent;
    cursor: pointer;
    user-select: none;
    transition: all var(--transition);
    text-decoration: none;
  }
  .pagination a:hover {
    background: var(--color-primary-light);
    color: white;
    border-color: var(--color-primary);
  }
  .pagination .current-page {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
    cursor: default;
  }

  /* Responsive */
  @media (max-width: 900px) {
    .sidebar {
      position: fixed;
      z-index: 200;
      transform: translateX(-100%);
    }
    .sidebar.open {
      transform: translateX(0);
    }
    main.main-content {
      margin-left: 0;
      padding: 1.5rem 1rem;
    }
    .header {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
    }
  }

  /* Toggle button */
  .sidebar-toggle {
    position: fixed;
    top: 1rem;
    left: 1rem;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    font-size: 1.3rem;
    cursor: pointer;
    display: none;
    z-index: 300;
    box-shadow: 0 0 8px var(--color-primary-light);
  }
  @media (max-width: 900px) {
    .sidebar-toggle {
      display: block;
    }
  }

  /* Button inside table */
  .btn-view {
    background: var(--color-primary);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: background var(--transition);
  }
  .btn-view:hover {
    background: var(--color-primary-light);
  }
</style>
</head>
<body>

<button class="sidebar-toggle" aria-label="Toggle sidebar"><i class="fa fa-bars"></i></button>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <i class="fa fa-cogs"></i> Admin Panel
  </div>
  <small>Manage Orders and More</small>
  <nav class="sidebar-nav" role="navigation" aria-label="Admin navigation">
    <div class="nav-item"><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></div>
    <div class="nav-item active"><a href="#"><i class="fa fa-shopping-cart"></i> Orders</a></div>
    <div class="nav-item"><a href="products.php"><i class="fa fa-box"></i> Products</a></div>
    <div class="nav-item"><a href="customers.php"><i class="fa fa-users"></i> customers</a></div>
    <div class="nav-item mt-auto"><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></div>
  </nav>
</aside>

<main class="main-content" id="mainContent" role="main" tabindex="-1">

  <header class="header">
    <div class="page-title">
      <h1>Orders</h1>
      <p>Manage all customer orders efficiently</p>
    </div>
    <div class="user-avatar" aria-label="Admin user initial">A</div>
  </header>

  <section class="orders-container" aria-labelledby="ordersHeading">
  <h2 id="ordersHeading" class="sr-only">Orders List</h2>

  <form method="get" action="" class="search-filter" role="search" aria-label="Search and filter orders">
    <input
      type="search"
      name="search"
      placeholder="Search orders, usernames, emails, transaction IDs..."
      aria-label="Search orders"
      value="<?= htmlspecialchars($search) ?>"
      id="searchInput"
      autocomplete="off"
    />
    <button type="submit" class="btn-view" aria-label="Search orders"><i class="fa fa-search"></i></button>
  </form>

  <!-- Create Order button added here -->
  <a href="edit_order.php" class="btn-view" style="background-color: #007bff; margin: 1rem 0; display: inline-block;">
    + Create Order
  </a>

  <?php if (!empty($error_message)): ?>
    <p class="no-orders" role="alert"><?= htmlspecialchars($error_message) ?></p>
  <?php elseif (count($orders) === 0): ?>
    <p class="no-orders">No orders found.</p>
  <?php else: ?>
    <table class="order-table" aria-describedby="ordersHeading">
      <thead>
        <tr>
          <th scope="col">Order ID</th>
          <th scope="col">User</th>
          <th scope="col">Email</th>
          <th scope="col">Order Date</th>
          <th scope="col">Amount</th>
          <th scope="col">Status</th>
          <th scope="col">Payment</th>
          <th scope="col">Shipping</th>
          <th scope="col">Transaction ID</th>
          <th scope="col" aria-label="Actions"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
          <td>#<?= htmlspecialchars($order['id']) ?></td>
          <td><?= htmlspecialchars($order['customer_name']) ?></td>
          <td><?= htmlspecialchars($order['email']) ?></td>
          <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
          <td><?= CURRENCY_SYMBOL . ' ' . number_format($order['total_amount'], 2) ?></td>
          <td class="status-<?= strtolower($order['status']) ?>"><?= ucfirst(htmlspecialchars($order['status'])) ?></td>
          <td><?= htmlspecialchars($order['payment_method']) ?></td>
          <td><?= htmlspecialchars($order['shipping_status']) ?></td>
          <td><?= htmlspecialchars($order['transaction_uuid']) ?></td>
          <td>
            <a
              class="btn-view"
              href="view_order.php?id=<?= urlencode($order['id']) ?>"
              aria-label="View details for order #<?= htmlspecialchars($order['id']) ?>"
            >
              View
            </a>

            <!-- Edit button added here -->
            <a
              class="btn-view"
              href="edit_order.php?id=<?= urlencode($order['id']) ?>"
              aria-label="Edit order #<?= htmlspecialchars($order['id']) ?>"
              style="background-color: #28a745; margin-left: 8px;"
            >
              Edit
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <nav aria-label="Pagination" class="pagination" role="navigation">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous page">&laquo;</a>
      <?php endif; ?>

      <?php
      $startPage = max(1, $page - 2);
      $endPage = min($total_pages, $page + 2);

      if ($startPage > 1) {
        echo '<a href="?page=1&search=' . urlencode($search) . '">1</a>';
        if ($startPage > 2) echo '<span>...</span>';
      }

      for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $page) {
          echo '<span class="current-page">' . $i . '</span>';
        } else {
          echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a>';
        }
      }

      if ($endPage < $total_pages) {
        if ($endPage < $total_pages - 1) echo '<span>...</span>';
        echo '<a href="?page=' . $total_pages . '&search=' . urlencode($search) . '">' . $total_pages . '</a>';
      }
      ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" aria-label="Next page">&raquo;</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</section>

</main>

<script>
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const toggleBtn = document.querySelector('.sidebar-toggle');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });

  // Close sidebar when clicking outside on small screens
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 900) {
      if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    }
  });

  // Keyboard accessibility: close sidebar with ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && sidebar.classList.contains('open')) {
      sidebar.classList.remove('open');
      toggleBtn.focus();
    }
  });
</script>

</body>
</html>
