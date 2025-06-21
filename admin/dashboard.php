<?php
require_once 'config/database.php';
requireLogin(); // block access if not logged in

// Initialize $pdo from Database class
$database = new Database();
$pdo = $database->getConnection();

try {
    // Count totals
    $categoriesCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $productsCount   = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $usersCount      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Get recent products (limit 5)
    $recentProducts = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Sample chart data (you can fetch real sales later)
    $salesData = [
        ['month' => 'Jan', 'sales' => 1200],
        ['month' => 'Feb', 'sales' => 1900],
        ['month' => 'Mar', 'sales' => 1500],
        ['month' => 'Apr', 'sales' => 2100],
        ['month' => 'May', 'sales' => 1800],
        ['month' => 'Jun', 'sales' => 2400]
    ];
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-commerce Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
    </header>
    <div class="stats-cards">
        <div class="card">
            <h3><?php echo $categoriesCount; ?></h3>
            <p>Categories</p>
        </div>
        <div class="card">
            <h3><?php echo $productsCount; ?></h3>
            <p>Products</p>
        </div>
        <div class="card">
            <h3><?php echo $usersCount; ?></h3>
            <p>Users</p>
        </div>
        <div class="card">
            <h3>$12,345</h3>
            <p>Revenue</p>
        </div>
    </div>
    <div class="charts">
        <div class="chart">
            <h2>Sales Overview</h2>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="recent-products">
            <h2>Recent Products</h2>
            <ul>
                <?php foreach ($recentProducts as $product): ?>
                    <li>
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <span>$<?php echo number_format($product['price'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

    </div>
    
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($salesData, 'month')); ?>,
                datasets: [{
                    label: 'Sales',
                    data: <?php echo json_encode(array_column($salesData, 'sales')); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
       
  document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    toggleButton.addEventListener('click', function () {
      if (sidebar.style.display === 'none' || getComputedStyle(sidebar).display === 'none') {
        sidebar.style.display = 'block';
      } else {
        sidebar.style.display = 'none';
      }
    });
  });


    </script>
    
    <script src="assets/js/script.js"></script>
</body>
</html>