<?php
require_once '../config/database.php';
requireLogin();

$message = '';
$messageType = '';

// Initialize $pdo from Database class
$database = new Database();
$pdo = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description, status) VALUES (?, ?, ?)");
                    $stmt->execute([
                        trim($_POST['name']), 
                        trim($_POST['description']), 
                        $_POST['status']
                    ]);
                    $message = "Category added successfully!";
                    $messageType = "success";
                    break;
                    
                case 'edit':
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?");
                    $stmt->execute([
                        trim($_POST['name']), 
                        trim($_POST['description']), 
                        $_POST['status'], 
                        $_POST['id']
                    ]);
                    $message = "Category updated successfully!";
                    $messageType = "success";
                    break;
                    
                case 'delete':
                    // Check if category is being used by products (optional safety check)
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                    $checkStmt->execute([$_POST['id']]);
                    $productCount = $checkStmt->fetchColumn();
                    
                    if ($productCount > 0) {
                        $message = "Cannot delete category. It is being used by $productCount product(s).";
                        $messageType = "error";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                        $stmt->execute([$_POST['id']]);
                        $message = "Category deleted successfully!";
                        $messageType = "success";
                    }
                    break;
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
        
        // Redirect to prevent form resubmission
        if ($messageType == 'success') {
            header('Location: categories.php?msg=' . urlencode($message) . '&type=' . $messageType);
            exit();
        }
    }
}

// Handle messages from redirects
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'];
}

// Get categories with search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE name LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM categories $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalCategories = $countStmt->fetchColumn();
$totalPages = ceil($totalCategories / $limit);

// Get categories
$sql = "SELECT * FROM categories $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll();

// Get category for editing
$editCategory = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editCategory = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Categories - E-commerce Admin</title>
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

    .search-container {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        padding: 0.6rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.95rem;
        min-width: 250px;
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

    .data-table tr:hover {
        background-color: #f9fafb;
    }

    .status-badge {
        padding: 0.35rem 0.7rem;
        font-size: 0.8rem;
        border-radius: 20px;
        text-transform: capitalize;
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

    .modal-content {
        background: white;
        padding: 2rem;
        width: 100%;
        max-width: 500px;
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
        margin: 0;
    }

    .form-group {
        margin-bottom: 1.2rem;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 0.4rem;
        display: block;
        color: #374151;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.95rem;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.8rem;
        margin-top: 1.5rem;
    }

    .close {
        background: transparent;
        font-size: 1.6rem;
        border: none;
        cursor: pointer;
        color: #64748b;
    }

    .close:hover {
        color: #374151;
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

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-container {
            justify-content: stretch;
        }
        
        .search-box {
            min-width: auto;
            flex: 1;
        }
        
        .data-table {
            font-size: 0.85rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.75rem;
        }
    }
</style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1>Categories (<?php echo $totalCategories; ?>)</h1>
                <div class="search-container">
                    <form method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="text" name="search" class="search-box" placeholder="Search categories..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-secondary">Search</button>
                        <?php if ($search): ?>
                            <a href="categories.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn btn-primary" onclick="showModal('categoryModal')">+ Add Category</button>
                </div>
            </div>

            <div class="table-container">
                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <h3>No categories found</h3>
                        <p><?php echo $search ? 'No categories match your search criteria.' : 'Start by adding your first category.'; ?></p>
                        <?php if (!$search): ?>
                            <button class="btn btn-primary" onclick="showModal('categoryModal')">Add First Category</button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <table class="data-table" aria-label="Categories Table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($category['description'], 0, 100)) . (strlen($category['description']) > 100 ? '...' : ''); ?></td>
                                <td><span class="status-badge <?php echo $category['status']; ?>"><?php echo ucfirst($category['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-secondary" 
                                       aria-label="Edit category <?php echo htmlspecialchars($category['name']); ?>">Edit</a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete the category \'<?php echo htmlspecialchars($category['name']); ?>\'? This action cannot be undone.')">
                                        <input type="hidden" name="action" value="delete" />
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>" />
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                aria-label="Delete category <?php echo htmlspecialchars($category['name']); ?>">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">&laquo; Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"><?php echo $editCategory ? 'Edit Category' : 'Add Category'; ?></h2>
                <button class="close" aria-label="Close modal" onclick="hideModal('categoryModal')">&times;</button>
            </div>

            <form method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="action" value="<?php echo $editCategory ? 'edit' : 'add'; ?>" />
                <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>" />
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required maxlength="255"
                           value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>" 
                           placeholder="Enter category name" />
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" maxlength="1000"
                              placeholder="Enter category description (optional)"><?php echo $editCategory ? htmlspecialchars($editCategory['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo ($editCategory && $editCategory['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editCategory && $editCategory['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('categoryModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editCategory ? 'Update' : 'Add'; ?> Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Show modal function
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Hide modal function
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset form if it's add mode
            const form = document.querySelector('#' + modalId + ' form');
            if (form && !form.querySelector('input[name="id"]')) {
                form.reset();
            }
        }

        // Form validation
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            if (name.length < 2) {
                alert('Category name must be at least 2 characters long.');
                return false;
            }
            return true;
        }

        // Auto-show modal if editing
        <?php if ($editCategory): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('categoryModal');
        });
        <?php endif; ?>

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('categoryModal');
            if (e.target === modal) {
                hideModal('categoryModal');
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideModal('categoryModal');
            }
        });

        // Sidebar toggle functionality
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

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>