<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle different actions
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addCategory($db);
        break;
    case 'edit':
        editCategory($db);
        break;
    case 'delete':
        deleteCategory($db);
        break;
    case 'toggle_status':
        toggleCategoryStatus($db);
        break;
    default:
        // Handle form submission without explicit action (add)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            addCategory($db);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;
}

function addCategory($db) {
    try {
        $name = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['category_description'] ?? '');
        $status = $_POST['category_status'] ?? 'active';
        
        // Validation
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            return;
        }
        
        // Check if category already exists
        $checkQuery = "SELECT id FROM categories WHERE name = :name";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':name', $name);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Category with this name already exists']);
            return;
        }
        
        // Insert new category
        $query = "INSERT INTO categories (name, description, status, created_at) VALUES (:name, :description, :status, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add category']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function editCategory($db) {
    try {
        $id = $_POST['category_id'] ?? 0;
        $name = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['category_description'] ?? '');
        $status = $_POST['category_status'] ?? 'active';
        
        // Validation
        if (empty($id) || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
            return;
        }
        
        // Check if category exists
        $checkQuery = "SELECT id FROM categories WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        // Check if name is taken by another category
        $nameCheckQuery = "SELECT id FROM categories WHERE name = :name AND id != :id";
        $nameCheckStmt = $db->prepare($nameCheckQuery);
        $nameCheckStmt->bindParam(':name', $name);
        $nameCheckStmt->bindParam(':id', $id);
        $nameCheckStmt->execute();
        
        if ($nameCheckStmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Category with this name already exists']);
            return;
        }
        
        // Update category
        $query = "UPDATE categories SET name = :name, description = :description, status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update category']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteCategory($db) {
    try {
        $id = $_POST['category_id'] ?? $_GET['id'] ?? 0;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Category ID is required']);
            return;
        }
        
        // Check if category has products
        $productCheckQuery = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
        $productCheckStmt = $db->prepare($productCheckQuery);
        $productCheckStmt->bindParam(':id', $id);
        $productCheckStmt->execute();
        $productCount = $productCheckStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($productCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete category with existing products']);
            return;
        }
        
        // Delete category
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            if (isset($_GET['id'])) {
                $_SESSION['success_message'] = 'Category deleted successfully';
                header('Location: ../pages/categories.php');
                exit();
            } else {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function toggleCategoryStatus($db) {
    try {
        $id = $_POST['category_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if (empty($id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Category ID and status are required']);
            return;
        }
        
        $query = "UPDATE categories SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update category status']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>