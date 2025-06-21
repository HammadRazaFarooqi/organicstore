<nav class="sidebar" id="sidebar">
  <ul class="nav-menu">
  <li>
  <a href="../dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
    <span class="nav-icon" aria-hidden="true">🏠</span> <span class="nav-text">Dashboard</span>
  </a>
</li>

<li><a href="/organicstore/admin/pages/categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
  <span class="nav-icon" aria-hidden="true">📦</span> <span class="nav-text">Categories</span>
</a></li>

<li><a href="/organicstore/admin/pages/products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
  <span class="nav-icon" aria-hidden="true">🛍️</span> <span class="nav-text">Products</span>
</a></li>

<li><a href="/organicstore/admin/pages/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
  <span class="nav-icon" aria-hidden="true">👥</span> <span class="nav-text">Users</span>
</a></li>

<li><a href="/organicstore/admin/pages/sales.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>">
  <span class="nav-icon" aria-hidden="true">🔥</span> <span class="nav-text">Sales Products</span>
</a></li>

<li><a href="/organicstore/admin/pages/discounts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'discounts.php' ? 'active' : ''; ?>">
  <span class="nav-icon" aria-hidden="true">💸</span> <span class="nav-text">Discounts</span>
</a></li>



  </ul>
</nav>
