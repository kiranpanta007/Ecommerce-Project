<?php
session_start();

// Output buffering for performance
ob_start();

// CSRF token generation (for forms)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 20px auto;
        }
        .stat {
            margin-bottom: 15px;
        }
        .chart-container {
            width: 100%;
            max-width: 800px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h1>

<div class="stat">Total Revenue: <b id="total_revenue">Loading...</b></div>
<div class="stat">Total Orders: <b id="total_orders">Loading...</b></div>
<div class="stat">New Customers (last 30 days): <b id="new_customers">Loading...</b></div>
<div class="stat">Low Stock Products: <b id="inventory_alert">Loading...</b></div>

<div class="chart-container">
    <h3>Orders and Revenue Last 7 Days</h3>
    <canvas id="salesChart"></canvas>
</div>

<div class="chart-container">
    <h3>Order Status Distribution</h3>
    <canvas id="statusChart"></canvas>
</div>

<div class="chart-container">
    <h3>Revenue by Payment Method</h3>
    <canvas id="paymentChart"></canvas>
</div>

<script>
async function loadDashboard() {
    const res = await fetch('dashboard_data.php');
    const data = await res.json();

    document.getElementById('total_revenue').textContent = 'NRS' + Number(data.total_revenue).toLocaleString();
    document.getElementById('total_orders').textContent = data.total_orders;
    document.getElementById('new_customers').textContent = data.new_customers;
    document.getElementById('inventory_alert').textContent = data.inventory_alert;

    const labels = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        labels.push(d.toISOString().slice(0, 10));
    }

    const salesByDate = {};
    data.sales_analytics.forEach(item => {
        salesByDate[item.date] = item;
    });

    const ordersData = labels.map(date => salesByDate[date] ? salesByDate[date].orders_count : 0);
    const revenueData = labels.map(date => salesByDate[date] ? salesByDate[date].revenue : 0);

    new Chart(document.getElementById('salesChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Orders',
                    data: ordersData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    yAxisID: 'y',
                },
                {
                    label: 'Revenue (NRS)',
                    data: revenueData,
                    backgroundColor: 'rgba(255, 206, 86, 0.7)',
                    yAxisID: 'y1',
                },
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: { display: true, text: 'Orders' }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Revenue (NRS)' }
                }
            }
        }
    });

    const statusLabels = Object.keys(data.order_status);
    const statusCounts = Object.values(data.order_status);
    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: ['#4e79a7', '#f28e2c', '#e15759', '#76b7b2', '#59a14f']
            }]
        }
    });

    const paymentLabels = data.revenue_source.map(x => x.payment_method);
    const paymentRevenue = data.revenue_source.map(x => x.revenue);
    new Chart(document.getElementById('paymentChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: paymentLabels,
            datasets: [{
                data: paymentRevenue,
                backgroundColor: ['#e15759', '#4e79a7', '#f28e2c', '#76b7b2', '#59a14f']
            }]
        }
    });
}

loadDashboard();
</script>
</body>
</html>


// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Output buffering for performance
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Modern E-Commerce</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --secondary: #8b5cf6;
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

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .date-display {
            background: var(--card-bg);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .stat-card:hover {
            transform: translateY(-0.25rem);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0.25rem;
            height: 100%;
            background: var(--primary);
        }

        .stat-card.success::before {
            background: var(--success);
        }

        .stat-card.warning::before {
            background: var(--warning);
        }

        .stat-card.danger::before {
            background: var(--danger);
        }

        .stat-card.info::before {
            background: var(--info);
        }

        .stat-card h3 {
            font-size: 0.9375rem;
            color: var(--gray-600);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }

        .stat-card .change {
            font-size: 0.8125rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--gray-500);
        }

        .change.up {
            color: var(--success);
        }

        .change.down {
            color: var(--danger);
        }

        /* Charts Section */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1200px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        .chart-container {
            background: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .chart-container h2 {
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chart-container h2 i {
            color: var(--primary);
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        /* Recent Orders Table */
        .table-container {
            background: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            overflow-x: auto;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .table-container h2 {
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-container h2 i {
            color: var(--primary);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        th {
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            background: rgba(0, 0, 0, 0.02);
        }

        tr:hover {
            background: rgba(99, 102, 241, 0.03);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        /* Buttons */
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.625rem 1.125rem;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            line-height: 1;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn i {
            font-size: 0.875rem;
        }

        /* Loading States */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .spinner {
            width: 2.5rem;
            height: 2.5rem;
            border: 0.25rem solid rgba(99, 102, 241, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-300) 50%, var(--gray-200) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 0.5rem;
            min-height: 7.5rem;
        }

        .table-skeleton {
            width: 100%;
        }

        .skeleton-row {
            height: 3.125rem;
            background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-300) 50%, var(--gray-200) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 0.25rem;
            margin-bottom: 0.625rem;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(1.25rem); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        /* Mobile Toggle */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 1.25rem;
            left: 1.25rem;
            z-index: 1000;
            background: var(--primary);
            color: white;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .menu-toggle:hover {
            transform: scale(1.1);
        }

        /* Responsive */
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

        /* Dark mode toggle (optional) */
        .dark-mode-toggle {
            position: relative;
            width: 3rem;
            height: 1.5rem;
            background: var(--gray-300);
            border-radius: 1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .dark-mode-toggle::before {
            content: '';
            position: absolute;
            top: 0.125rem;
            left: 0.125rem;
            width: 1.25rem;
            height: 1.25rem;
            background: white;
            border-radius: 50%;
            transition: var(--transition);
        }

        body.dark-mode {
            background-color: var(--gray-900);
            color: var(--gray-100);
        }

        body.dark-mode .sidebar {
            background: rgba(15, 23, 42, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .card-bg {
            background: rgba(30, 41, 59, 0.9);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 0.375rem;
            height: 0.375rem;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-200);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gray-400);
            border-radius: 0.1875rem;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        body.dark-mode ::-webkit-scrollbar-track {
            background: var(--gray-800);
        }

        body.dark-mode ::-webkit-scrollbar-thumb {
            background: var(--gray-600);
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button> -->

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-store"></i> MeroShopping</h2>
            <small>Admin Dashboard</small>
        </div>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <div class="nav-item active" aria-current="page">
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
            <div class="chart-container">
                <h2><i class="fas fa-bell"></i> Recent Activity</h2>
                <div class="activity-feed" id="activity-feed">
                    <div class="activity-item">
                        <div class="activity-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <p>Order #3254 completed</p>
                            <small>2 minutes ago</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon primary">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <p>New customer registered</p>
                            <small>15 minutes ago</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="activity-content">
                            <p>Low stock alert for Product X</p>
                            <small>1 hour ago</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon info">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="activity-content">
                            <p>Order #3251 shipped</p>
                            <small>3 hours ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<script src="js/dashboard.js"></script>
</body>
</html>
<?php
// Minify HTML output
$buffer = ob_get_contents();
ob_end_clean();
echo preg_replace(['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'], ['>', '<', '\\1'], $buffer);
?>