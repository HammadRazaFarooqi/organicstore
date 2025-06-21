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
        addUser($db);
        break;
    case 'edit':
        editUser($db);
        break;
    case 'delete':
        deleteUser($db);
        break;
    case 'toggle_status':
        toggleUserStatus($db);
        break;
    case 'get_user':
        getUser($db);
        break;
    default:
        // Handle form submission without explicit action (add)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            addUser($db);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;
}

function addUser($db) {
    try {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['user_status'] ?? 'active';
        
        // Validation
        if (empty($first_name) || empty($last_name)) {
            echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
            return;
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Valid email address is required']);
            return;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            return;
        }
        
        // Check if email already exists
        $checkQuery = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'User with this email already exists']);
            return;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO users (first_name, last_name, email, password, phone, address, status, created_at) 
                  VALUES (:first_name, :last_name, :email, :password, :phone, :address, :status, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add user']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function editUser($db) {
    try {
        $id = $_POST['user_id'] ?? 0;
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['user_status'] ?? 'active';
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($id) || empty($first_name) || empty($last_name)) {
            echo json_encode(['success' => false, 'message' => 'User ID, first name and last name are required']);
            return;
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Valid email address is required']);
            return;
        }
        
        // Check if user exists
        $checkQuery = "SELECT id FROM users WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Check if email is taken by another user
        $emailCheckQuery = "SELECT id FROM users WHERE email = :email AND id != :id";
        $emailCheckStmt = $db->prepare($emailCheckQuery);
        $emailCheckStmt->bindParam(':email', $email);
        $emailCheckStmt->bindParam(':id', $id);
        $emailCheckStmt->execute();
        
        if ($emailCheckStmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'User with this email already exists']);
            return;
        }
        
        // Prepare update query
        if (!empty($password)) {
            // Update with password
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
                return;
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, 
                      password = :password, phone = :phone, address = :address, status = :status, updated_at = NOW() 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
        } else {
            // Update without password
            $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, 
                      phone = :phone, address = :address, status = :status, updated_at = NOW() 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
        }
        
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteUser($db) {
    try {
        $id = $_POST['user_id'] ?? $_GET['id'] ?? 0;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }
        
        // Check if user exists
        $checkQuery = "SELECT id FROM users WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Delete user
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            if (isset($_GET['id'])) {
                $_SESSION['success_message'] = 'User deleted successfully';
                header('Location: ../pages/users.php');
                exit();
            } else {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function toggleUserStatus($db) {
    try {
        $id = $_POST['user_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if (empty($id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'User ID and status are required']);
            return;
        }
        
        $query = "UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getUser($db) {
    try {
        $id = $_GET['id'] ?? $_POST['user_id'] ?? 0;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }
        
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Don't send password in response
            unset($user['password']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Bulk actions
function bulkDeleteUsers($db) {
    try {
        $user_ids = $_POST['user_ids'] ?? [];
        
        if (empty($user_ids) || !is_array($user_ids)) {
            echo json_encode(['success' => false, 'message' => 'No users selected']);
            return;
        }
        
        // Create placeholders for prepared statement
        $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
        $query = "DELETE FROM users WHERE id IN ($placeholders)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($user_ids)) {
            $deleted_count = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "$deleted_count users deleted successfully"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete users']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function bulkUpdateUserStatus($db) {
    try {
        $user_ids = $_POST['user_ids'] ?? [];
        $status = $_POST['bulk_status'] ?? '';
        
        if (empty($user_ids) || !is_array($user_ids) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
            return;
        }
        
        // Create placeholders for prepared statement
        $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
        $query = "UPDATE users SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)";
        
        // Prepare parameters array
        $params = array_merge([$status], $user_ids);
        
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($params)) {
            $updated_count = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "$updated_count users updated successfully"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update users']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Export users to CSV
function exportUsers($db) {
    try {
        $query = "SELECT id, first_name, last_name, email, phone, address, status, created_at FROM users ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Create file pointer
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Address', 'Status', 'Created At']);
        
        // Add data rows
        foreach ($users as $user) {
            fputcsv($output, $user);
        }
        
        fclose($output);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Handle additional actions
if ($action === 'bulk_delete') {
    bulkDeleteUsers($db);
} elseif ($action === 'bulk_status') {
    bulkUpdateUserStatus($db);
} elseif ($action === 'export') {
    exportUsers($db);
}
?>