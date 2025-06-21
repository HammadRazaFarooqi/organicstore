<?php
require_once '../config/database.php';
requireLogin();
// Initialize $pdo from Database class
$database = new Database();
$pdo = $database->getConnection();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    // Check if email already exists
                    $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $emailCheck->execute([trim($_POST['email'])]);
                    if ($emailCheck->fetch()) {
                        throw new Exception('Email address already exists.');
                    }
                    
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, address, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        trim($_POST['name']),
                        trim($_POST['email']),
                        trim($_POST['phone']),
                        trim($_POST['address']),
                        $_POST['status']
                    ]);
                    $message = "User added successfully!";
                    $messageType = "success";
                    break;
                    
                case 'edit':
                    // Check if email already exists for other users
                    $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $emailCheck->execute([trim($_POST['email']), $_POST['id']]);
                    if ($emailCheck->fetch()) {
                        throw new Exception('Email address already exists.');
                    }
                    
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, status = ? WHERE id = ?");
                    $stmt->execute([
                        trim($_POST['name']),
                        trim($_POST['email']),
                        trim($_POST['phone']),
                        trim($_POST['address']),
                        $_POST['status'],
                        $_POST['id']
                    ]);
                    $message = "User updated successfully!";
                    $messageType = "success";
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = "User deleted successfully!";
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
            header('Location: users.php?msg=' . urlencode($message) . '&type=' . $messageType);
            exit();
        }
    }
}

// Handle messages from redirects
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'];
}

// Get users with search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($statusFilter)) {
    $conditions[] = "status = ?";
    $params[] = $statusFilter;
}

if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(" AND ", $conditions);
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM users $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

// Get users
$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get user for editing
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Users - E-commerce Admin</title>
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
            max-width: 600px;
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

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #2563eb, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-details h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .user-details p {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
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
                <h1 id="pageTitle">Users (<?php echo $totalUsers; ?>)</h1>
                <button class="btn btn-primary" type="button" onclick="showModal('userModal')">+ Add User</button>
            </div>

            <!-- Quick Stats -->
            <?php
            $activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
            $inactiveUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();
            $recentUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
            ?>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $activeUsers; ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $inactiveUsers; ?></div>
                    <div class="stat-label">Inactive Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $recentUsers; ?></div>
                    <div class="stat-label">New This Month</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; width: 100%;">
                    <input type="text" name="search" class="search-box" placeholder="Search users by name, email, or phone..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $statusFilter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $statusFilter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    
                    <?php if ($search || $statusFilter): ?>
                        <a href="users.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-container" role="region" aria-label="Users list">
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <h3>No users found</h3>
                        <p><?php echo ($search || $statusFilter) ? 'No users match your search criteria.' : 'Start by adding your first user.'; ?></p>
                        <?php if (!$search && !$statusFilter): ?>
                            <button class="btn btn-primary" onclick="showModal('userModal')">Add First User</button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <table class="data-table" cellspacing="0" cellpadding="0" role="table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">User</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Status</th>
                                <th scope="col">Joined</th>
                                <th scope="col" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                                                <?php if ($user['address']): ?>
                                                    <p><?php echo htmlspecialchars(substr($user['address'], 0, 30)) . (strlen($user['address']) > 30 ? '...' : ''); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary" 
                                           aria-label="Edit <?php echo htmlspecialchars($user['name']); ?>">Edit</a>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars($user['name']); ?>\'? This action cannot be undone.');" 
                                              aria-label="Delete <?php echo htmlspecialchars($user['name']); ?>">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>" />
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
                                <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $statusFilter ? '&status='.$statusFilter : ''; ?>">&laquo; Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $statusFilter ? '&status='.$statusFilter : ''; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $statusFilter ? '&status='.$statusFilter : ''; ?>">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-hidden="true" tabindex="-1">
        <div class="modal-content" role="document">
            <div class="modal-header">
                <h2 id="modalTitle"><?php echo $editUser ? 'Edit User' : 'Add User'; ?></h2>
                <button class="close" aria-label="Close modal" onclick="hideModal('userModal')">&times;</button>
            </div>

            <form method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>" />
                <?php if ($editUser): ?>
                    <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>" />
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required maxlength="255"
                               value="<?php echo $editUser ? htmlspecialchars($editUser['name']) : ''; ?>" />
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required maxlength="255"
                               value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" maxlength="20"
                               value="<?php echo $editUser ? htmlspecialchars($editUser['phone']) : ''; ?>" />
                    </div>
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo ($editUser && $editUser['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editUser && $editUser['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" placeholder="Optional"><?php echo $editUser ? htmlspecialchars($editUser['address']) : ''; ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('userModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?php echo $editUser ? 'Update' : 'Add'; ?> User</button>
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

        // Form validation
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!name) {
                alert('Please enter a name.');
                return false;
            }
            
            if (!email) {
                alert('Please enter an email address.');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }
            
            return true;
        }

        // Close modal on ESC
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                const modal = document.querySelector('.modal.show');
                if (modal) hideModal(modal.id);
            }
        });

        // Close modal when clicking outside
        document.addEventListener('click', e => {
            if (e.target.classList.contains('modal')) {
                hideModal(e.target.id);
            }
        });

        <?php if ($editUser): ?>
        // Open modal automatically for editing
        showModal('userModal');
        <?php endif; ?>

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
    </script>
</body>
</html>