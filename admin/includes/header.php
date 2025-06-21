<header class="admin-header">
  <div class="header-left">
    <button class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
    <h1>E-commerce Admin</h1>
  </div>

  <div class="header-right">
    <div class="admin-info">
      <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>
</header>
