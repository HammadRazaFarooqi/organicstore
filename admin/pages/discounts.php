<?php
require_once '../config/database.php';
requireLogin();

// Initialize $pdo from Database class
$database = new Database();
$pdo = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO discounts (name, type, value, product_id, start_date, end_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$_POST['name'], $_POST['type'], $_POST['value'], $_POST['product_id'], $_POST['start_date'], $_POST['end_date'], $_POST['status']]);
                break;
                
            case 'edit':
                $stmt = $pdo->prepare("UPDATE discounts SET name = ?, type = ?, value = ?, product_id = ?, start_date = ?, end_date = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['type'], $_POST['value'], $_POST['product_id'], $_POST['start_date'], $_POST['end_date'], $_POST['status'], $_POST['id']]);
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM discounts WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
        }
        header('Location: discounts.php');
        exit();
    }
}

// Get discounts with product names from database
try {
    $discounts = $pdo->query("
        SELECT d.*, p.name as product_name 
        FROM discounts d 
        LEFT JOIN products p ON d.product_id = p.id 
        ORDER BY d.created_at DESC
    ")->fetchAll();
} catch (Exception $e) {
    // Fallback to dummy data if database fails
    $discounts = [];
}

// Get products for dropdown from database
try {
    $products = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY name")->fetchAll();
} catch (Exception $e) {
    // Fallback to dummy products if database fails
    $products = [];
}

// Dummy data for demonstration (use when database is empty or for testing)
$dummyDiscounts = [
    [
        'id' => 1,
        'name' => 'Summer Sale',
        'product_name' => 'iPhone 15 Pro',
        'product_id' => 1,
        'type' => 'percentage',
        'value' => 15,
        'start_date' => '2025-06-01',
        'end_date' => '2025-08-31',
        'status' => 'active',
        'created_at' => '2025-06-01 00:00:00'
    ],
    [
        'id' => 2,
        'name' => 'Flash Sale',
        'product_name' => 'MacBook Air',
        'product_id' => 2,
        'type' => 'fixed',
        'value' => 200,
        'start_date' => '2025-06-15',
        'end_date' => '2025-06-17',
        'status' => 'active',
        'created_at' => '2025-06-15 00:00:00'
    ],
    [
        'id' => 3,
        'name' => 'Winter Clearance',
        'product_name' => 'Samsung Galaxy S24',
        'product_id' => 3,
        'type' => 'percentage',
        'value' => 25,
        'start_date' => '2024-12-01',
        'end_date' => '2025-02-28',
        'status' => 'inactive',
        'created_at' => '2024-12-01 00:00:00'
    ],
    [
        'id' => 4,
        'name' => 'New Customer Discount',
        'product_name' => 'AirPods Pro',
        'product_id' => 4,
        'type' => 'fixed',
        'value' => 50,
        'start_date' => '2025-05-01',
        'end_date' => '2025-12-31',
        'status' => 'active',
        'created_at' => '2025-05-01 00:00:00'
    ]
];

$dummyProducts = [
    ['id' => 1, 'name' => 'iPhone 15 Pro', 'status' => 'active'],
    ['id' => 2, 'name' => 'MacBook Air', 'status' => 'active'],
    ['id' => 3, 'name' => 'Samsung Galaxy S24', 'status' => 'active'],
    ['id' => 4, 'name' => 'AirPods Pro', 'status' => 'active'],
    ['id' => 5, 'name' => 'iPad Pro', 'status' => 'active'],
    ['id' => 6, 'name' => 'Apple Watch', 'status' => 'active']
];

// Use dummy data if database is empty (for demonstration)
if (empty($discounts)) {
    $discounts = $dummyDiscounts;
}

if (empty($products)) {
    $products = $dummyProducts;
}

// Get discount for editing
$editDiscount = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM discounts WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editDiscount = $stmt->fetch();
    } catch (Exception $e) {
        // Fallback to dummy data for editing
        foreach ($dummyDiscounts as $discount) {
            if ($discount['id'] == $_GET['edit']) {
                $editDiscount = $discount;
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discounts - E-commerce Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
/* Layout */
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

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2.2rem;
  font-weight: 700;
  color: #2563eb;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Buttons */
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

/* Table */
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
  width: 50px;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.no-image {
  color: #64748b;
  font-style: italic;
  font-size: 0.875rem;
}

/* Badges */
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

/* Form */
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
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-group.full-width {
  width: 100%;
  margin-bottom: 1.2rem;
}

.current-image img {
  max-width: 100px;
  margin-top: 0.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.8rem;
  margin-top: 1.5rem;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

/* Additional styles */
body {
  margin: 0;
  padding: 0;
  background-color: #f4f7fa;
}

.discount-value {
  font-weight: 600;
  color: #059669;
}
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>💸 Discount Management</h1>
                <button class="btn btn-primary" onclick="showModal('discountModal')">+ Add Discount</button>
            </div>
            
            <!-- Database Status Indicator -->
            <?php if (empty($pdo->query("SHOW TABLES LIKE 'discounts'")->fetch())): ?>
                <div class="alert alert-info" style="margin-bottom: 1rem; padding: 1rem; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px;">
                    <strong>Demo Mode:</strong> Using dummy data. Database table 'discounts' not found.
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Discount Name</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounts as $discount): ?>
                            <tr>
                                <td><?php echo $discount['id']; ?></td>
                                <td><?php echo htmlspecialchars($discount['name']); ?></td>
                                <td><?php echo htmlspecialchars($discount['product_name'] ?? 'N/A'); ?></td>
                                <td><?php echo ucfirst($discount['type']); ?></td>
                                <td class="discount-value">
                                    <?php echo $discount['type'] == 'percentage' ? $discount['value'] . '%' : '$' . number_format($discount['value'], 2); ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($discount['start_date'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($discount['end_date'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $discount['status']; ?>">
                                        <?php 
                                        if ($discount['status'] == 'active' && strtotime($discount['end_date']) < time()) {
                                            echo 'Expired';
                                        } else {
                                            echo ucfirst($discount['status']); 
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="?edit=<?php echo $discount['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this discount?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $discount['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($discounts)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: #64748b;">
                                    No discounts found. <a href="#" onclick="showModal('discountModal')">Add your first discount</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Discount Modal -->
    <div id="discountModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $editDiscount ? 'Edit Discount' : 'Add Discount'; ?></h2>
                <span class="close" onclick="hideModal('discountModal')">&times;</span>
            </div>
            
            <form method="POST" id="discountForm">
                <input type="hidden" name="action" value="<?php echo $editDiscount ? 'edit' : 'add'; ?>">
                <?php if ($editDiscount): ?>
                    <input type="hidden" name="id" value="<?php echo $editDiscount['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Discount Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo $editDiscount ? htmlspecialchars($editDiscount['name']) : ''; ?>"
                           placeholder="Enter discount name">
                </div>
                
                <div class="form-group">
                    <label for="product_id">Product *</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    <?php echo ($editDiscount && $editDiscount['product_id'] == $product['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Discount Type *</label>
                        <select id="type" name="type" required onchange="updateValuePlaceholder()">
                            <option value="percentage" <?php echo ($editDiscount && $editDiscount['type'] == 'percentage') ? 'selected' : ''; ?>>Percentage</option>
                            <option value="fixed" <?php echo ($editDiscount && $editDiscount['type'] == 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="value">Discount Value *</label>
                        <input type="number" id="value" name="value" step="0.01" min="0" required 
                               value="<?php echo $editDiscount ? $editDiscount['value'] : ''; ?>"
                               placeholder="Enter value">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" required 
                               value="<?php echo $editDiscount ? $editDiscount['start_date'] : date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date *</label>
                        <input type="date" id="end_date" name="end_date" required 
                               value="<?php echo $editDiscount ? $editDiscount['end_date'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo ($editDiscount && $editDiscount['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editDiscount && $editDiscount['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('discountModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editDiscount ? 'Update' : 'Add'; ?> Discount
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    
    <script>
        // Modal functions
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Update placeholder based on discount type
        function updateValuePlaceholder() {
            const typeSelect = document.getElementById('type');
            const valueInput = document.getElementById('value');
            
            if (typeSelect.value === 'percentage') {
                valueInput.placeholder = 'Enter percentage (e.g., 15)';
                valueInput.max = '100';
            } else {
                valueInput.placeholder = 'Enter amount (e.g., 50.00)';
                valueInput.removeAttribute('max');
            }
        }

        // Form validation
        document.getElementById('discountForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            const type = document.getElementById('type').value;
            const value = parseFloat(document.getElementById('value').value);
            
            // Validate date range
            if (endDate <= startDate) {
                alert('End date must be after start date');
                e.preventDefault();
                return false;
            }
            
            // Validate percentage value
            if (type === 'percentage' && (value <= 0 || value > 100)) {
                alert('Percentage must be between 1 and 100');
                e.preventDefault();
                return false;
            }
            
            // Validate fixed amount
            if (type === 'fixed' && value <= 0) {
                alert('Fixed amount must be greater than 0');
                e.preventDefault();
                return false;
            }
        });

        // Close modal when clicking outside
        document.getElementById('discountModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal('discountModal');
            }
        });

        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.querySelector('.sidebar-toggle');
            const sidebar = document.getElementById('sidebar');

            if (toggleButton && sidebar) {
                toggleButton.addEventListener('click', function() {
                    if (sidebar.style.display === 'none' || getComputedStyle(sidebar).display === 'none') {
                        sidebar.style.display = 'block';
                    } else {
                        sidebar.style.display = 'none';
                    }
                });
            }

            // Initialize value placeholder
            updateValuePlaceholder();
        });

        <?php if ($editDiscount): ?>
            // Show modal if editing
            document.addEventListener('DOMContentLoaded', function() {
                showModal('discountModal');
            });
        <?php endif; ?>
    </script>
</body>
</html>