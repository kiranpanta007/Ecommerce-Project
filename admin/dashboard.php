<?php
// dashboard.php
session_start();

// Security checks
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

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

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <div class="page-title">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="header-actions">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo date('F j, Y'); ?></span>
                </div>
                <button id="refresh-btn" class="btn">
                    <i class="fas fa-sync-alt"></i>
                    <span>Refresh</span>
                </button>
                <?php
$initial = $_SESSION['admin_name'] ?? 'A';
$initial = htmlspecialchars($initial);
$initial = substr($initial, 0, 1);
$initial = strtoupper($initial);
?>
<div class="user-avatar" id="userMenu" aria-haspopup="true" aria-expanded="false">
    <?php echo $initial; ?>
</div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" id="stats-grid">
            <div class="stat-card animate-fade delay-1">
                <h3><i class="fas fa-dollar-sign"></i> Total Revenue</h3>
                <div class="value" id="total-revenue">$0</div>
                <div class="change up">
                    <i class="fas fa-arrow-up"></i> <span id="revenue-change">0%</span> from last month
                </div>
            </div>
            <div class="stat-card success animate-fade delay-2">
                <h3><i class="fas fa-shopping-bag"></i> Total Orders</h3>
                <div class="value" id="total-orders">0</div>
                <div class="change up">
                    <i class="fas fa-arrow-up"></i> <span id="orders-change">0%</span> from yesterday
                </div>
            </div>
            <div class="stat-card info animate-fade delay-3">
                <h3><i class="fas fa-users"></i> New Customers</h3>
                <div class="value" id="new-customers">0</div>
                <div class="change down">
                    <i class="fas fa-arrow-down"></i> <span id="customers-change">0%</span> from last week
                </div>
            </div>
            <div class="stat-card warning animate-fade delay-4">
                <h3><i class="fas fa-box"></i> Inventory Alert</h3>
                <div class="value" id="low-stock">0</div>
                <div class="change up">
                    <i class="fas fa-arrow-up"></i> <span id="stock-change">0</span> items need restock
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <div class="chart-container">
                <h2><i class="fas fa-chart-line"></i> Sales Analytics</h2>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <h2><i class="fas fa-chart-pie"></i> Revenue Sources</h2>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="charts-row">
            <div class="chart-container">
                <h2><i class="fas fa-chart-bar"></i> Order Status</h2>
                <div class="chart-wrapper">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
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

        <!-- Recent Orders Table -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-clock"></i> Recent Orders</h2>
                <a href="orders.php" class="btn btn-sm">View All</a>
            </div>
            <div id="recent-orders">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script>
    // Toggle sidebar on mobile
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
        this.setAttribute('aria-expanded', 
            document.getElementById('sidebar').classList.contains('active'));
    });

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Set current date
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', options);
        
        // Load dashboard data
        fetchDashboardData();
        
        // Set up real-time updates
        setupRealTimeUpdates();
    });

    // Refresh button
    document.getElementById('refresh-btn').addEventListener('click', function() {
        const btn = this;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing';
        btn.disabled = true;
        
        fetchDashboardData();
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            btn.disabled = false;
        }, 1000);
    });
    
    // Fetch dashboard data
    async function fetchDashboardData() {
        try {
            // Show skeleton loading states
            showSkeletonLoading();
            
            // Simulate API call (replace with actual fetch)
            const response = await simulateApiCall();
            
            if (response.success) {
                updateStats(response.data);
                renderCharts(response.data);
                renderRecentOrders(response.data.recent_orders);
                renderActivityFeed(response.data.recent_activity);
            } else {
                showError(response.error || 'Failed to load dashboard data');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Failed to connect to server');
        }
    }
    
    // Simulate API call (replace with actual fetch)
    function simulateApiCall() {
        return new Promise(resolve => {
            setTimeout(() => {
                resolve({
                    success: true,
                    data: generateMockData()
                });
            }, 800); // Simulate network delay
        });
    }
    
    // Generate mock data for demonstration
    function generateMockData() {
        // Monthly sales data
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const currentMonth = new Date().getMonth();
        const monthlyLabels = months.slice(Math.max(0, currentMonth - 5), currentMonth + 1);
        const monthlySales = monthlyLabels.map((_, i) => 
            Math.floor(Math.random() * 50000) + 10000 + (i * 5000));
        
        // Top products
        const topProducts = [
            { name: 'Wireless Headphones', total_sold: 342, total_revenue: 25650 },
            { name: 'Smart Watch', total_sold: 278, total_revenue: 41700 },
            { name: 'Bluetooth Speaker', total_sold: 195, total_revenue: 14625 },
            { name: 'USB-C Cable', total_sold: 512, total_revenue: 7680 },
            { name: 'Phone Case', total_sold: 423, total_revenue: 12690 }
        ];
        
        // Order status data
        const orderStatus = [
            { status: 'Completed', count: 125 },
            { status: 'Processing', count: 42 },
            { status: 'Shipped', count: 78 },
            { status: 'Cancelled', count: 15 }
        ];
        
        // Revenue sources
        const revenueSources = [
            { source: 'Electronics', revenue: 45600 },
            { source: 'Clothing', revenue: 23400 },
            { source: 'Home Goods', revenue: 18700 },
            { source: 'Accessories', revenue: 12300 }
        ];
        
        // Recent orders
        const recentOrders = [
            { id: 3254, customer_name: 'John Smith', order_date: new Date(), total_price: 125.99, status: 'Completed' },
            { id: 3253, customer_name: 'Sarah Johnson', order_date: new Date(Date.now() - 86400000), total_price: 89.50, status: 'Shipped' },
            { id: 3252, customer_name: 'Michael Brown', order_date: new Date(Date.now() - 172800000), total_price: 234.75, status: 'Processing' },
            { id: 3251, customer_name: 'Emily Davis', order_date: new Date(Date.now() - 259200000), total_price: 56.25, status: 'Completed' },
            { id: 3250, customer_name: 'Robert Wilson', order_date: new Date(Date.now() - 345600000), total_price: 178.99, status: 'Cancelled' }
        ];
        
        // Recent activity
        const recentActivity = [
            { type: 'order_completed', message: 'Order #3254 completed', time: '2 minutes ago' },
            { type: 'new_customer', message: 'New customer registered', time: '15 minutes ago' },
            { type: 'low_stock', message: 'Low stock alert for Product X', time: '1 hour ago' },
            { type: 'order_shipped', message: 'Order #3251 shipped', time: '3 hours ago' }
        ];
        
        return {
            metrics: {
                total_revenue: monthlySales.reduce((a, b) => a + b, 0),
                total_orders: 245,
                new_customers: 32,
                low_stock_items: 8,
                revenue_change: 12.5,
                orders_change: 5.2,
                customers_change: -2.3,
                stock_change: 3
            },
            monthly_labels: monthlyLabels,
            monthly_sales: monthlySales,
            top_products: topProducts,
            order_status: orderStatus,
            revenue_sources: revenueSources,
            recent_orders: recentOrders,
            recent_activity: recentActivity
        };
    }
    
    // Show skeleton loading states
    function showSkeletonLoading() {
        document.getElementById('stats-grid').innerHTML = `
            <div class="stat-card skeleton"></div>
            <div class="stat-card skeleton"></div>
            <div class="stat-card skeleton"></div>
            <div class="stat-card skeleton"></div>
        `;
        
        document.getElementById('recent-orders').innerHTML = `
            <div class="table-skeleton">
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
            </div>
        `;
    }
    
    // Update stats cards
    function updateStats(data) {
        const metrics = data.metrics;
        
        document.getElementById('total-revenue').textContent = '$' + formatNumber(metrics.total_revenue);
        document.getElementById('total-orders').textContent = formatNumber(metrics.total_orders);
        document.getElementById('new-customers').textContent = formatNumber(metrics.new_customers);
        document.getElementById('low-stock').textContent = formatNumber(metrics.low_stock_items);
        
        // Update change percentages
        document.getElementById('revenue-change').textContent = metrics.revenue_change.toFixed(1) + '%';
        document.getElementById('orders-change').textContent = metrics.orders_change.toFixed(1) + '%';
        document.getElementById('customers-change').textContent = Math.abs(metrics.customers_change).toFixed(1) + '%';
        document.getElementById('stock-change').textContent = metrics.stock_change;
        
        // Update change indicators
        document.querySelector('#revenue-change').closest('.change').className = 
            `change ${metrics.revenue_change >= 0 ? 'up' : 'down'}`;
        document.querySelector('#orders-change').closest('.change').className = 
            `change ${metrics.orders_change >= 0 ? 'up' : 'down'}`;
        document.querySelector('#customers-change').closest('.change').className = 
            `change ${metrics.customers_change >= 0 ? 'up' : 'down'}`;
    }
    
    // Render charts
    function renderCharts(data) {
        // Destroy existing charts if they exist
        if (window.salesChart) window.salesChart.destroy();
        if (window.revenueChart) window.revenueChart.destroy();
        if (window.ordersChart) window.ordersChart.destroy();
        
        // Sales Chart (Line)
        renderSalesChart(data);
        
        // Revenue Chart (Doughnut)
        renderRevenueChart(data);
        
        // Orders Chart (Bar)
        renderOrdersChart(data);
    }
    
    // Render sales chart
    function renderSalesChart(data) {
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        window.salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: data.monthly_labels,
                datasets: [{
                    label: 'Sales',
                    data: data.monthly_sales,
                    borderColor: 'rgba(99, 102, 241, 1)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'white',
                    pointBorderColor: 'rgba(99, 102, 241, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: getChartOptions('Sales', 'currency')
        });
    }
    
    // Render revenue chart
    function renderRevenueChart(data) {
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        window.revenueChart = new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: data.revenue_sources.map(p => p.source),
                datasets: [{
                    data: data.revenue_sources.map(p => p.revenue),
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(217, 70, 239, 0.7)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            color: 'var(--gray-700)'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.95)',
                        callbacks: {
                            label: function(context) {
                                const source = data.revenue_sources[context.dataIndex];
                                return [
                                    `Revenue: $${formatNumber(source.revenue)}`,
                                    `${((source.revenue / data.metrics.total_revenue) * 100).toFixed(1)}% of total`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Render orders chart
    function renderOrdersChart(data) {
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        window.ordersChart = new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: data.order_status.map(o => o.status),
                datasets: [{
                    label: 'Orders',
                    data: data.order_status.map(o => o.count),
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)'
                    ],
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: getChartOptions('Orders', 'number')
        });
    }
    
    // Common chart options
    function getChartOptions(title, valueType) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            if (valueType === 'currency') {
                                return ' $' + context.raw.toLocaleString();
                            } else {
                                return ' ' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            if (valueType === 'currency') {
                                return '$' + (value / 1000) + 'k';
                            } else {
                                return value;
                            }
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        };
    }
    
    // Render recent orders table
    function renderRecentOrders(orders) {
        const tableBody = document.getElementById('recent-orders');
        if (!orders || orders.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No recent orders found</td></tr>';
            return;
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        orders.forEach(order => {
            const date = new Date(order.order_date);
            const statusClass = getStatusClass(order.status);
            
            html += `
                <tr>
                    <td>#${order.id}</td>
                    <td>${order.customer_name || 'Guest'}</td>
                    <td>${date.toLocaleDateString()}</td>
                    <td>$${order.total_price.toFixed(2)}</td>
                    <td><span class="badge ${statusClass}">${order.status}</span></td>
                    <td>
                        <button class="btn btn-sm" onclick="viewOrder(${order.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;
        
        tableBody.innerHTML = html;
    }
    
    // Render activity feed
    function renderActivityFeed(activities) {
        const feedContainer = document.getElementById('activity-feed');
        if (!activities || activities.length === 0) {
            feedContainer.innerHTML = '<p>No recent activity</p>';
            return;
        }
        
        let html = '';
        
        activities.forEach(activity => {
            const iconClass = getActivityIconClass(activity.type);
            
            html += `
                <div class="activity-item">
                    <div class="activity-icon ${iconClass.class}">
                        <i class="${iconClass.icon}"></i>
                    </div>
                    <div class="activity-content">
                        <p>${activity.message}</p>
                        <small>${activity.time}</small>
                    </div>
                </div>
            `;
        });
        
        feedContainer.innerHTML = html;
    }
    
    // Get status class for badges
    function getStatusClass(status) {
        const statusText = status.toLowerCase();
        if (statusText.includes('completed')) return 'badge-success';
        if (statusText.includes('shipped')) return 'badge-info';
        if (statusText.includes('processing')) return 'badge-warning';
        if (statusText.includes('cancelled')) return 'badge-danger';
        return 'badge-info';
    }
    
    // Get icon class for activity items
    function getActivityIconClass(type) {
        switch(type) {
            case 'order_completed':
                return { class: 'success', icon: 'fas fa-check-circle' };
            case 'new_customer':
                return { class: 'primary', icon: 'fas fa-user-plus' };
            case 'low_stock':
                return { class: 'warning', icon: 'fas fa-exclamation-triangle' };
            case 'order_shipped':
                return { class: 'info', icon: 'fas fa-truck' };
            default:
                return { class: 'primary', icon: 'fas fa-info-circle' };
        }
    }
    
    // Format numbers with commas
    function formatNumber(num) {
        return new Intl.NumberFormat('en-US').format(num);
    }
    
    // Set up real-time updates
    function setupRealTimeUpdates() {
        // In a real app, you would use WebSocket or Server-Sent Events
        // For this demo, we'll simulate with setInterval
        setInterval(() => {
            // Only update if the tab is visible
            if (!document.hidden) {
                fetchDashboardData();
            }
        }, 30000); // Update every 30 seconds
    }
    
    // View order details
    function viewOrder(orderId) {
        // In a real app, this would redirect to the order details page
        console.log('Viewing order:', orderId);
        alert(`Viewing order #${orderId} - this would redirect to the order details page in a real app`);
    }
    
    // Show error message
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.style.background = 'rgba(239, 68, 68, 0.1)';
        errorDiv.style.color = 'var(--danger)';
        errorDiv.style.padding = '1rem';
        errorDiv.style.borderRadius = '0.5rem';
        errorDiv.style.marginBottom = '1.25rem';
        errorDiv.style.display = 'flex';
        errorDiv.style.alignItems = 'center';
        errorDiv.style.gap = '0.75rem';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        document.querySelector('.main-content').prepend(errorDiv);
        setTimeout(() => errorDiv.remove(), 5000);
    }
    </script>
</body>
</html>
<?php
// Minify HTML output
$buffer = ob_get_contents();
ob_end_clean();
echo preg_replace(['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'], ['>', '<', '\\1'], $buffer);
?>