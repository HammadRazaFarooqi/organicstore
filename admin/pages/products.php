<?php
require_once '../config/database.php';
requireLogin();

$message = '';
$messageType = '';

// Initialize $pdo from Database class
$database = new Database();
$pdo = $database->getConnection();

// Handle form database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $image = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $uploadDir = '../assets/uploads/products/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        // Validate file type
                        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        $fileType = $_FILES['image']['type'];
                        $fileSize = $_FILES['image']['size'];
                        
                        if (!in_array($fileType, $allowedTypes)) {
                            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP files are allowed.');
                        }
                        
                        if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                            throw new Exception('File size too large. Maximum 5MB allowed.');
                        }
                        
                        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image = time() . '_' . uniqid() . '.' . $fileExtension;
                        
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
                            throw new Exception('Failed to upload image.');
                        }
                    }
                    
                    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        trim($_POST['name']),
                        trim($_POST['description']),
                        $_POST['price'],
                        $_POST['category_id'],
                        $image,
                        $_POST['status']
                    ]);
                    $message = "Product added successfully!";
                    $messageType = "success";
                    break;
                    
                case 'edit':
                    $image = $_POST['current_image'];
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $uploadDir = '../assets/uploads/products/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        // Validate file type
                        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        $fileType = $_FILES['image']['type'];
                        $fileSize = $_FILES['image']['size'];
                        
                        if (!in_array($fileType, $allowedTypes)) {
                            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP files are allowed.');
                        }
                        
                        if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                            throw new Exception('File size too large. Maximum 5MB allowed.');
                        }
                        
                        // Delete old image if exists
                        if ($image && file_exists($uploadDir . $image)) {
                            unlink($uploadDir . $image);
                        }
                        
                        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image = time() . '_' . uniqid() . '.' . $fileExtension;
                        
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
                            throw new Exception('Failed to upload image.');
                        }
                    }
                    
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ?, status = ? WHERE id = ?");
                    $stmt->execute([
                        trim($_POST['name']),
                        trim($_POST['description']),
                        $_POST['price'],
                        $_POST['category_id'],
                        $image,
                        $_POST['status'],
                        $_POST['id']
                    ]);
                    $message = "Product updated successfully!";
                    $messageType = "success";
                    break;
                    
                case 'delete':
                    // Get product details first to delete image
                    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $product = $stmt->fetch();
                    
                    // Delete product from database
                    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    // Delete associated image file
                    if ($product && $product['image']) {
                        $imagePath = '../assets/uploads/products/' . $product['image'];
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                    
                    $message = "Product deleted successfully!";
                    $messageType = "success";
                    break;
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
        
        // Redirect to prevent form resubmission
        if ($messageType == 'success') {
            header('Location: products.php?msg=' . urlencode($message) . '&type=' . $messageType);
            exit();
        }
    }
}

// Handle messages from redirects
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'];
}

// Get products with search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($categoryFilter)) {
    $conditions[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

if (!empty($statusFilter)) {
    $conditions[] = "p.status = ?";
    $params[] = $statusFilter;
}

if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(" AND ", $conditions);
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Get products
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $whereClause 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter and form
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

// Get product for editing
$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Products - E-commerce Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
         * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        color: #2d3748;
    }

    /* Layout */
    .admin-container {
        display: flex;
        min-height: 100vh;
    }

    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        min-height: 100vh;
    }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filters-container {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        }

        .search-box, .filter-select {
            padding: 0.6rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .search-box {
            min-width: 250px;
            flex: 1;
        }

        .filter-select {
            min-width: 150px;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-size: 0.95rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #16a34a;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 2rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        }

        .data-table th, .data-table td {
            padding: 1rem 1.2rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table thead {
            background-color: #f1f5f9;
        }

        .data-table th {
            font-weight: 600;
            color: #2c3e50;
        }

        .data-table tr:hover {
            background-color: #f9fafb;
        }

        .product-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .no-image {
            color: #64748b;
            font-style: italic;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background-color: #f1f5f9;
            border-radius: 8px;
            border: 2px dashed #cbd5e1;
        }

        .status-badge {
            padding: 0.35rem 0.7rem;
            font-size: 0.8rem;
            border-radius: 20px;
            text-transform: capitalize;
            display: inline-block;
            font-weight: 500;
        }

        .status-badge.active {
            background-color: #d1fae5;
            color: #16a34a;
        }

        .status-badge.inactive {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .price {
            font-weight: 600;
            color: #16a34a;
            font-size: 1.05rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #cbd5e1;
            text-decoration: none;
            color: #64748b;
            border-radius: 6px;
        }

        .pagination a:hover {
            background-color: #f1f5f9;
        }

        .pagination .current {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(3px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            width: 100%;
            max-width: 700px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes fadeIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
        }

        .modal-header h2 {
            font-size: 1.4rem;
            color: #2563eb;
            font-weight: 600;
            margin: 0;
        }

        .close {
            background: transparent;
            font-size: 1.6rem;
            border: none;
            cursor: pointer;
            color: #64748b;
            transition: color 0.2s ease;
        }

        .close:hover {
            color: #2c3e50;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
        }

        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-bottom: 1.2rem;
            min-width: 200px;
        }

        .form-group.full-width {
            width: 100%;
            flex: none;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.4rem;
            display: block;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .current-image {
            margin-top: 0.5rem;
        }

        .current-image img {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.8rem;
            margin-top: 1.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #64748b;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .stats-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            flex: 1;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filters-container {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .form-group {
                min-width: auto;
            }
            
            .data-table {
                font-size: 0.85rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.75rem;
            }
            
            .stats-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content" role="main" aria-labelledby="pageTitle">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1 id="pageTitle">Products (<?php echo $totalProducts; ?>)</h1>
                <button class="btn btn-primary" type="button" onclick="showModal('productModal')">+ Add Product</button>
            </div>

            <!-- Quick Stats -->
            <?php
            $activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
            $inactiveProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'inactive'")->fetchColumn();
            $avgPrice = $pdo->query("SELECT AVG(price) FROM products WHERE status = 'active'")->fetchColumn();
            ?>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $activeProducts; ?></div>
                    <div class="stat-label">Active Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $inactiveProducts; ?></div>
                    <div class="stat-label">Inactive Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">$<?php echo number_format($avgPrice, 2); ?></div>
                    <div class="stat-label">Average Price</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; width: 100%;">
                    <input type="text" name="search" class="search-box" placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $statusFilter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $statusFilter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    
                    <?php if ($search || $categoryFilter || $statusFilter): ?>
                        <a href="products.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-container" role="region" aria-label="Products list">
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <h3>No products found</h3>
                        <p><?php echo ($search || $categoryFilter || $statusFilter) ? 'No products match your search criteria.' : 'Start by adding your first product.'; ?></p>
                        <?php if (!$search && !$categoryFilter && !$statusFilter): ?>
                            <button class="btn btn-primary" onclick="showModal('productModal')">Add First Product</button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <table class="data-table" cellspacing="0" cellpadding="0" role="table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Image</th>
                                <th scope="col">Name</th>
                                <th scope="col">Category</th>
                                <th scope="col">Price</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created</th>
                                <th scope="col" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <?php if ($product['image']): ?>
                                            <img src="../assets/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="product-thumb" loading="lazy" />
                                        <?php else: ?>
                                            <div class="no-image">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?></td>
                                    <td><span class="price">$<?php echo number_format($product['price'], 2); ?></span></td>
                                    <td>
                                        <span class="status-badge <?php echo $product['status']; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary" 
                                           aria-label="Edit <?php echo htmlspecialchars($product['name']); ?>">Edit</a>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars($product['name']); ?>\'? This action cannot be undone.');" 
                                              aria-label="Delete <?php echo htmlspecialchars($product['name']); ?>">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>" />
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?><?php echo $statusFilter ? '&status='.$statusFilter : ''; ?>">&laquo; Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?><?php echo $statusFilter ? '&status='.$statusFilter : ''; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?><?php echo $statusFilter ? '&status='.$statusFilter : ''; ?>">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-hidden="true" tabindex="-1">
        <div class="modal-content" role="document">
            <div class="modal-header">
                <h2 id="modalTitle"><?php echo $editProduct ? 'Edit Product' : 'Add Product'; ?></h2>
                <button class="close" aria-label="Close modal" onclick="hideModal('productModal')">&times;</button>
            </div>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>" />
                <?php if ($editProduct): ?>
                    <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>" />
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editProduct['image']); ?>" />
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required maxlength="255"
                               value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>" />
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category <sup>*</sup></label>
                        <select id="category_id" name="category_id" required>
                            <option value="" disabled <?php echo !$editProduct ? 'selected' : ''; ?>>Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($editProduct && $editProduct['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Optional"><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price ($) <sup>*</sup></label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['price']) : ''; ?>" />
                    </div>
                    <div class="form-group">
                        <label for="status">Status <sup>*</sup></label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo ($editProduct && $editProduct['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editProduct && $editProduct['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*" />
                    <?php if ($editProduct && $editProduct['image']): ?>
                        <div class="current-image" aria-live="polite">
                            <p>Current Image:</p>
                            <img src="../assets/uploads/products/<?php echo htmlspecialchars($editProduct['image']); ?>" alt="Current image of <?php echo htmlspecialchars($editProduct['name']); ?>" />
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('productModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?php echo $editProduct ? 'Update' : 'Add'; ?> Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show/hide modal helpers
        function showModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            // Focus first input in form
            const input = modal.querySelector('input, select, textarea, button');
            if (input) input.focus();
        }
        function hideModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }

        // Close modal on ESC
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                const modal = document.querySelector('.modal.show');
                if (modal) hideModal(modal.id);
            }
        });

        <?php if ($editProduct): ?>
        // Open modal automatically for editing
        showModal('productModal');
        <?php endif; ?>
    </script>
    <script>
  document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    if (toggleButton && sidebar) {
      toggleButton.addEventListener('click', function () {
        if (sidebar.style.display === 'none' || getComputedStyle(sidebar).display === 'none') {
          sidebar.style.display = 'block';
        } else {
          sidebar.style.display = 'none';
        }
      });
    }
  });
</script>
</body>
</html>
