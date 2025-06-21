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
                    $stmt = $pdo->prepare("INSERT INTO discounts (name, type, value, product_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        trim($_POST['name']),
                        $_POST['type'],
                        $_POST['value'],
                        $_POST['product_id'] ?: null,
                        $_POST['start_date'],
                        $_POST['end_date'],
                        $_POST['status']
                    ]);
                    $message = "Discount added successfully!";
                    $messageType = "success";
                    break;
                    
                case 'edit':
                    $stmt = $pdo->prepare("UPDATE discounts SET name = ?, type = ?, value = ?, product_id = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
                    $stmt->execute([
                        trim($_POST['name']),
                        $_POST['type'],
                        $_POST['value'],
                        $_POST['product_id'] ?: null,
                        $_POST['start_date'],
                        $_POST['end_date'],
                        $_POST['status'],
                        $_POST['id']
                    ]);
                    $message = "Discount updated successfully!";
                    $messageType = "success";
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM discounts WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = "Discount deleted successfully!";
                    $messageType = "success";
                    break;
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
        
        // Redirect to prevent form resubmission
        if ($messageType == 'success') {
            header('Location: sales.php?msg=' . urlencode($message) . '&type=' . $messageType);
            exit();
        }
    }
}

// Handle messages from redirects
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'];
}

// Get discounts with search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE d.name LIKE ? OR p.name LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM discounts d LEFT JOIN products p ON d.product_id = p.id $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalDiscounts = $countStmt->fetchColumn();
$totalPages = ceil($totalDiscounts / $limit);

// Get discounts with product information
$sql = "SELECT d.*, p.name as product_name, p.price as product_price, p.image as product_image
        FROM discounts d 
        LEFT JOIN products p ON d.product_id = p.id 
        $whereClause 
        ORDER BY d.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$discounts = $stmt->fetchAll();

// Get all products for the dropdown
$productsStmt = $pdo->query("SELECT id, name, price FROM products WHERE status = 'active' ORDER BY name");
$products = $productsStmt->fetchAll();

// Get discount for editing
$editDiscount = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM discounts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editDiscount = $stmt->fetch();
}

// Get products with active discounts (sales products)
$salesProducts = $pdo->query("
    SELECT p.*, c.name as category_name, d.id as discount_id, d.name as discount_name, d.value as discount_value, d.type as discount_type, d.start_date, d.end_date
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN discounts d ON p.id = d.product_id 
    WHERE d.status = 'active' AND d.start_date <= CURDATE() AND d.end_date >= CURDATE()
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<?php
require_once '../config/database.php';
requireLogin();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $pdo->prepare("INSERT INTO discounts (name, type, value, product_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        trim($_POST['name']),
                        $_POST['type'],
                        $_POST['value'],
                        $_POST['product_id'] ?: null,
                        $_POST['start_date'],
                        $_POST['end_date'],
                        $_POST['status']
                    ]);
                    $message = "Discount added successfully!";
                    $messageType = "success";
                    break;
                    
                case 'edit':
                    $stmt = $pdo->prepare("UPDATE discounts SET name = ?, type = ?, value = ?, product_id = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
                    $stmt->execute([
                        trim($_POST['name']),
                        $_POST['type'],
                        $_POST['value'],
                        $_POST['product_id'] ?: null,
                        $_POST['start_date'],
                        $_POST['end_date'],
                        $_POST['status'],
                        $_POST['id']
                    ]);
                    $message = "Discount updated successfully!";
                    $messageType = "success";
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM discounts WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = "Discount deleted successfully!";
                    $messageType = "success";
                    break;
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
        
        // Redirect to prevent form resubmission
        if ($messageType == 'success') {
            header('Location: sales.php?msg=' . urlencode($message) . '&type=' . $messageType);
            exit();
        }
    }
}

// Handle messages from redirects
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'];
}

// Get discounts with search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE d.name LIKE ? OR p.name LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM discounts d LEFT JOIN products p ON d.product_id = p.id $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalDiscounts = $countStmt->fetchColumn();
$totalPages = ceil($totalDiscounts / $limit);

// Get discounts with product information
$sql = "SELECT d.*, p.name as product_name, p.price as product_price, p.image as product_image
        FROM discounts d 
        LEFT JOIN products p ON d.product_id = p.id 
        $whereClause 
        ORDER BY d.created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$discounts = $stmt->fetchAll();

// Get all products for the dropdown
$productsStmt = $pdo->query("SELECT id, name, price FROM products WHERE status = 'active' ORDER BY name");
$products = $productsStmt->fetchAll();

// Get discount for editing
$editDiscount = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM discounts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editDiscount = $stmt->fetch();
}

// Get products with active discounts (sales products)
$salesProducts = $pdo->query("
    SELECT p.*, c.name as category_name, d.id as discount_id, d.name as discount_name, d.value as discount_value, d.type as discount_type, d.start_date, d.end_date
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN discounts d ON p.id = d.product_id 
    WHERE d.status = 'active' AND d.start_date <= CURDATE() AND d.end_date >= CURDATE()
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Discounts - E-commerce Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

    /* Page Header */
    /* Page Header Styles - Sales & Discounts */
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
    margin: 0;
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
    box-sizing: border-box;
}

.search-box:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-box::placeholder {
    color: #94a3b8;
}

/* Button Styles */
.btn {
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    font-weight: 500;
}

.btn-primary {
    background-color: #2563eb;
    color: white;
}

.btn-primary:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-secondary {
    background-color: #64748b;
    color: white;
}

.btn-secondary:hover {
    background-color: #475569;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .page-header h1 {
        font-size: 1.8rem;
        text-align: center;
    }
    
    .search-container {
        justify-content: stretch;
        flex-direction: column;
    }
    
    .search-container form {
        display: flex !important;
        gap: 0.5rem;
        width: 100%;
    }
    
    .search-box {
        min-width: auto;
        flex: 1;
        width: 100%;
    }
    
    .btn {
        padding: 0.7rem 1rem;
    }
}

@media (max-width: 480px) {
    .page-header {
        gap: 1.5rem;
    }
    
    .page-header h1 {
        font-size: 1.6rem;
    }
    
    .search-container form {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

    /* Stats Cards */
   /* Sales Stats Header - Same theme as Users page */
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
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
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
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-container {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
    
    .stat-number {
        font-size: 1.75rem;
    }
    
    .stat-label {
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .stats-container {
        gap: 0.5rem;
    }
    
    .stat-card {
        padding: 1rem;
        border-radius: 8px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
}

    /* Search Container */
    .search-container {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .search-box {
        padding: 0.75rem 1rem;
        border: 2px solid rgba(79, 70, 229, 0.2);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        width: 300px;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .search-box:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        transform: translateY(-1px);
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

    /* Alerts */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        border-left: 4px solid;
        backdrop-filter: blur(10px);
        animation: slideIn 0.3s ease-out;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: #065f46;
        border-left-color: #10b981;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        color: #991b1b;
        border-left-color: #ef4444;
    }

    @keyframes slideIn {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Table */
    .table-container {
        overflow-x: auto;
        margin-bottom: 2rem;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .data-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }

    .data-table th {
        padding: 1.25rem 1.5rem;
        text-align: left;
        font-weight: 700;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid rgba(79, 70, 229, 0.1);
    }

    .data-table td {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        vertical-align: middle;
    }

    .data-table tbody tr {
        transition: all 0.3s ease;
    }

    .data-table tbody tr:hover {
        background: rgba(79, 70, 229, 0.05);
        transform: scale(1.01);
    }

    .product-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .product-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .no-image {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 0.75rem;
        text-align: center;
    }

    /* Status Badges */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .status-badge.paid {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .status-badge.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .status-badge.cancelled {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .payment-method {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
        text-transform: capitalize;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #6b7280;
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        z-index: 2000;
        justify-content: center;
        align-items: center;
        padding: 2rem;
    }

    .modal.show {
        display: flex;
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        padding: 2.5rem;
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 24px;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from { transform: translateY(-50px) scale(0.9); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid rgba(79, 70, 229, 0.1);
    }

    .modal-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .close {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.25rem;
        transition: all 0.3s ease;
    }

    .close:hover {
        background: rgba(239, 68, 68, 0.2);
        transform: rotate(90deg);
    }

    /* Form Styles */
    .form-row {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .form-group {
        flex: 1;
        display: flex;
        flex-direction: column;
        margin-bottom: 1.5rem;
        min-width: 200px;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #374151;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 1rem 1.25rem;
        border: 2px solid rgba(79, 70, 229, 0.1);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        transform: translateY(-1px);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid rgba(79, 70, 229, 0.1);
    }

    /* Price Display */
    .price-display {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 100%);
        border: 2px solid rgba(79, 70, 229, 0.2);
        border-radius: 16px;
        padding: 1.5rem;
        margin: 1.5rem 0;
    }

    .price-display h4 {
        color: #4f46e5;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .price-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(79, 70, 229, 0.1);
    }

    .price-item:last-child {
        border-bottom: none;
        font-weight: 700;
        color: #059669;
        background: rgba(16, 185, 129, 0.1);
        border-radius: 8px;
        padding: 0.75rem;
        margin-top: 0.5rem;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .pagination a,
    .pagination span {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        color: #374151;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
        transform: translateY(-1px);
    }

    .pagination .current {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
        }

        .page-header-content {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            width: 100%;
        }

        .form-row {
            flex-direction: column;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 1rem;
        }

        .page-header {
            padding: 1.5rem;
        }

        .page-header h1 {
            font-size: 2rem;
        }

        .modal-content {
            padding: 1.5rem;
            margin: 1rem;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem;
        }

        .data-table {
            font-size: 0.875rem;
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
                <h1>Sales & Discounts</h1>
                <div class="search-container">
                    <form method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="text" name="search" class="search-box" placeholder="Search discounts or products..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-secondary">Search</button>
                        <?php if ($search): ?>
                            <a href="sales.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                <button class="btn btn-primary" onclick="showModal('discountModal')">+ Add Discount</button>
            </div>

            <!-- Tabs -->
            <!-- Tabs with Stats Card Structure -->
<div class="stats-container">
    <div class="stat-card tab active" onclick="switchTab('active-sales')">
        <div class="stat-number"><?php echo count($salesProducts); ?></div>
        <div class="stat-label">Active Sales</div>
    </div>
    <div class="stat-card tab" onclick="switchTab('all-discounts')">
        <div class="stat-number"><?php echo $totalDiscounts; ?></div>
        <div class="stat-label">All Discounts</div>
    </div>
</div>

            <!-- Active Sales Tab -->
            <div id="active-sales" class="tab-content active">
                <div class="table-container">
                    <?php if (empty($salesProducts)): ?>
                        <div class="empty-state">
                            <h3>No active sales</h3>
                            <p>No products are currently on sale. Create a discount to start promoting products.</p>
                            <button class="btn btn-primary" onclick="showModal('discountModal')">Create First Discount</button>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Original Price</th>
                                    <th>Discount</th>
                                    <th>Sale Price</th>
                                    <th>Valid Until</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salesProducts as $product): ?>
                                    <?php
                                    $originalPrice = $product['price'];
                                    $discountValue = $product['discount_value'];
                                    $discountType = $product['discount_type'];
                                    
                                    if ($discountType == 'percentage') {
                                        $salePrice = $originalPrice - ($originalPrice * $discountValue / 100);
                                        $discountText = $discountValue . '% OFF';
                                    } else {
                                        $salePrice = $originalPrice - $discountValue;
                                        $discountText = '$' . number_format($discountValue, 2) . ' OFF';
                                    }
                                    $savings = $originalPrice - $salePrice;
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image']): ?>
                                                <img src="../assets/uploads/products/<?php echo $product['image']; ?>" alt="Product" class="product-thumb">
                                            <?php else: ?>
                                                <div class="no-image">No Image</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>
                                            <span class="original-price">$<?php echo number_format($originalPrice, 2); ?></span>
                                        </td>
                                        <td>
                                            <span class="discount-badge"><?php echo $discountText; ?></span>
                                        </td>
                                        <td>
                                            <strong class="sale-price">$<?php echo number_format($salePrice, 2); ?></strong>
                                            <br><small>Save $<?php echo number_format($savings, 2); ?></small>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($product['end_date'])); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $product['discount_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to remove this discount?')">
                                                <input type="hidden" name="action" value="delete" />
                                                <input type="hidden" name="id" value="<?php echo $product['discount_id']; ?>" />
                                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

           
        </main>
    </div>

    <!-- Discount Modal -->
    <div id="discountModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"><?php echo $editDiscount ? 'Edit Discount' : 'Add Discount'; ?></h2>
                <button class="close" aria-label="Close modal" onclick="hideModal('discountModal')">&times;</button>
            </div>

            <form method="POST" onsubmit="return validateDiscountForm()">
                <input type="hidden" name="action" value="<?php echo $editDiscount ? 'edit' : 'add'; ?>" />
                <?php if ($editDiscount): ?>
                <input type="hidden" name="id" value="<?php echo $editDiscount['id']; ?>" />
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Discount Name *</label>
                    <input type="text" id="name" name="name" required maxlength="255"
                           value="<?php echo $editDiscount ? htmlspecialchars($editDiscount['name']) : ''; ?>" 
                           placeholder="e.g., Summer Sale, Blessed Friday Deal" />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Discount Type *</label>
                        <select id="type" name="type" required onchange="updateDiscountPreview()">
                            <option value="percentage" <?php echo ($editDiscount && $editDiscount['type'] == 'percentage') ? 'selected' : ''; ?>>Percentage</option>
                            <option value="fixed" <?php echo ($editDiscount && $editDiscount['type'] == 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="value">Discount Value *</label>
                        <input type="number" id="value" name="value" required step="0.01" min="0"
                               value="<?php echo $editDiscount ? $editDiscount['value'] : ''; ?>" 
                               placeholder="Enter amount or percentage" 
                               onchange="updateDiscountPreview()" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="product_id">Apply to Product (Optional)</label>
                    <select id="product_id" name="product_id" onchange="updateDiscountPreview()">
                        <option value="">All Products (Site-wide)</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    data-price="<?php echo $product['price']; ?>"
                                    <?php echo ($editDiscount && $editDiscount['product_id'] == $product['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['name']); ?> - $<?php echo number_format($product['price'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" required
                               value="<?php echo $editDiscount ? $editDiscount['start_date'] : date('Y-m-d'); ?>" />
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date *</label>
                        <input type="date" id="end_date" name="end_date" required
                               value="<?php echo $editDiscount ? $editDiscount['end_date'] : ''; ?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo ($editDiscount && $editDiscount['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($editDiscount && $editDiscount['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <!-- Discount Preview -->
                <div id="discountPreview" class="discount-preview" style="display: none;">
                    <h4>Discount Preview</h4>
                    <div id="previewContent"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('discountModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editDiscount ? 'Update Discount' : 'Add Discount'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Modal functionality
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Focus on first input
            const firstInput = modal.querySelector('input[type="text"], input[type="number"], select');
            if (firstInput) {
                firstInput.focus();
            }
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="flex"]');
                if (openModal) {
                    hideModal(openModal.id);
                }
            }
        });

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideModal(modal.id);
                }
            });
        });

        // Form validation
        function validateDiscountForm() {
            const name = document.getElementById('name').value.trim();
            const value = parseFloat(document.getElementById('value').value);
            const type = document.getElementById('type').value;
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);

            if (!name) {
                alert('Please enter a discount name.');
                return false;
            }

            if (isNaN(value) || value <= 0) {
                alert('Please enter a valid discount value greater than 0.');
                return false;
            }

            if (type === 'percentage' && value > 100) {
                alert('Percentage discount cannot exceed 100%.');
                return false;
            }

            if (startDate >= endDate) {
                alert('End date must be after start date.');
                return false;
            }

            return true;
        }

        // Update discount preview
        function updateDiscountPreview() {
            const type = document.getElementById('type').value;
            const value = parseFloat(document.getElementById('value').value);
            const productSelect = document.getElementById('product_id');
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const preview = document.getElementById('discountPreview');
            const previewContent = document.getElementById('previewContent');

            if (!value || value <= 0) {
                preview.style.display = 'none';
                return;
            }

            let html = '';
            
            if (selectedOption.value && selectedOption.dataset.price) {
                const originalPrice = parseFloat(selectedOption.dataset.price);
                let discountAmount, salePrice;
                
                if (type === 'percentage') {
                    discountAmount = originalPrice * (value / 100);
                    salePrice = originalPrice - discountAmount;
                } else {
                    discountAmount = value;
                    salePrice = originalPrice - discountAmount;
                }
                
                if (salePrice < 0) {
                    html = '<div style="color: #ef4444;">⚠️ Discount exceeds product price!</div>';
                } else {
                    html = `
                        <div class="preview-item">
                            <span>Product:</span>
                            <span>${selectedOption.text.split(' - ')[0]}</span>
                        </div>
                        <div class="preview-item">
                            <span>Original Price:</span>
                            <span>$${originalPrice.toFixed(2)}</span>
                        </div>
                        <div class="preview-item">
                            <span>Discount:</span>
                            <span>${type === 'percentage' ? value + '%' : '$' + value.toFixed(2)} OFF</span>
                        </div>
                        <div class="preview-item">
                            <span>Sale Price:</span>
                            <span><strong>$${salePrice.toFixed(2)}</strong></span>
                        </div>
                        <div class="preview-item savings">
                            <span>Customer Saves:</span>
                            <span>$${discountAmount.toFixed(2)}</span>
                        </div>
                    `;
                }
            } else {
                html = `
                    <div class="preview-item">
                        <span>Discount Type:</span>
                        <span>${type === 'percentage' ? 'Percentage' : 'Fixed Amount'}</span>
                    </div>
                    <div class="preview-item">
                        <span>Discount Value:</span>
                        <span>${type === 'percentage' ? value + '%' : '$' + value.toFixed(2)} OFF</span>
                    </div>
                    <div class="preview-item">
                        <span>Applies to:</span>
                        <span>All Products (Site-wide)</span>
                    </div>
                `;
            }
            
            previewContent.innerHTML = html;
            preview.style.display = 'block';
        }

        // Auto-show modal if editing
        <?php if ($editDiscount): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showModal('discountModal');
            updateDiscountPreview();
        });
        <?php endif; ?>

        // Initialize date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const startDateInput = document.getElementById('start_date');
            
            if (!startDateInput.value) {
                startDateInput.value = today;
            }
            
            // Set minimum dates
            startDateInput.setAttribute('min', today);
            document.getElementById('end_date').setAttribute('min', today);
            
            // Update end date minimum when start date changes
            startDateInput.addEventListener('change', function() {
                document.getElementById('end_date').setAttribute('min', this.value);
            });
        });
    </script></script>
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