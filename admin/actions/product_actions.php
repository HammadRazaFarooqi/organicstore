<?php
session_start();
require_once '../config/database.php';
$uploadDir = '../assets/uploads/products/';
// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

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
        addProduct($db);
        break;
    case 'edit':
        editProduct($db);
        break;
    case 'delete':
        deleteProduct($db);
        break;
    case 'toggle_status':
        toggleProductStatus($db);
        break;
    default:
        // Handle form submission without explicit action (add)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            addProduct($db);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;
}

function addProduct($db) {
    try {
        $name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['product_description'] ?? '');
        $price = $_POST['product_price'] ?? 0;
        $category_id = $_POST['product_category'] ?? 0;
        $stock_quantity = $_POST['stock_quantity'] ?? 0;
        $status = $_POST['product_status'] ?? 'active';
        
        // Validation
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Product name is required']);
            return;
        }
        
        if ($price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid product price is required']);
            return;
        }
        
        if (empty($category_id)) {
            echo json_encode(['success' => false, 'message' => 'Please select a category']);
            return;
        }
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadProductImage($_FILES['product_image']);
            if (!$image_path) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image. Please check the file format and size.']);
                return;
            }
        }
        
        // Check if product already exists
        $checkQuery = "SELECT id FROM products WHERE name = :name";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':name', $name);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // If image was uploaded but product exists, clean up the image
            if ($image_path && file_exists('../assets/uploads/products/' . $image_path)) {
                unlink('../assets/uploads/products/' . $image_path);
            }
            echo json_encode(['success' => false, 'message' => 'Product with this name already exists']);
            return;
        }
        
        // Insert new product
        $query = "INSERT INTO products (name, description, price, category_id, image, stock_quantity, status, created_at) 
                  VALUES (:name, :description, :price, :category_id, :image, :stock_quantity, :status, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image_path);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product added successfully']);
        } else {
            // If database insert fails, clean up uploaded image
            if ($image_path && file_exists('../assets/uploads/products/' . $image_path)) {
                unlink('../assets/uploads/products/' . $image_path);
            }
            echo json_encode(['success' => false, 'message' => 'Failed to add product']);
        }
        
    } catch (Exception $e) {
        // If there's an exception and image was uploaded, clean it up
        if (isset($image_path) && $image_path && file_exists('../assets/uploads/products/' . $image_path)) {
            unlink('../assets/uploads/products/' . $image_path);
        }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function editProduct($db) {
    try {
        $id = $_POST['product_id'] ?? 0;
        $name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['product_description'] ?? '');
        $price = $_POST['product_price'] ?? 0;
        $category_id = $_POST['product_category'] ?? 0;
        $stock_quantity = $_POST['stock_quantity'] ?? 0;
        $status = $_POST['product_status'] ?? 'active';
        
        // Validation
        if (empty($id) || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Product ID and name are required']);
            return;
        }
        
        if ($price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Valid product price is required']);
            return;
        }
        
        // Check if product exists
        $checkQuery = "SELECT image FROM products WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        $existingProduct = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingProduct) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        // Handle image upload
        $image_path = $existingProduct['image'];
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $new_image_path = uploadProductImage($_FILES['product_image']);
            if ($new_image_path) {
                // Delete old image if it exists
                if ($image_path && file_exists('../assets/uploads/products/' . $image_path)) {
                    unlink('../assets/uploads/products/' . $image_path);
                }
                $image_path = $new_image_path;
            }
        }
        
        // Check if name is taken by another product
        $nameCheckQuery = "SELECT id FROM products WHERE name = :name AND id != :id";
        $nameCheckStmt = $db->prepare($nameCheckQuery);
        $nameCheckStmt->bindParam(':name', $name);
        $nameCheckStmt->bindParam(':id', $id);
        $nameCheckStmt->execute();
        
        if ($nameCheckStmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Product with this name already exists']);
            return;
        }
        
        // Update product
        $query = "UPDATE products SET name = :name, description = :description, price = :price, 
                  category_id = :category_id, image = :image, stock_quantity = :stock_quantity, 
                  status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image_path);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteProduct($db) {
    try {
        $id = $_POST['product_id'] ?? $_GET['id'] ?? 0;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        // Get product image for deletion
        $imageQuery = "SELECT image FROM products WHERE id = :id";
        $imageStmt = $db->prepare($imageQuery);
        $imageStmt->bindParam(':id', $id);
        $imageStmt->execute();
        $product = $imageStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        // Delete product
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            // Delete image file if it exists
            if ($product['image'] && file_exists('../assets/uploads/products/' . $product['image'])) {
                unlink('../assets/uploads/products/' . $product['image']);
            }
            
            if (isset($_GET['id'])) {
                $_SESSION['success_message'] = 'Product deleted successfully';
                header('Location: ../pages/products.php');
                exit();
            } else {
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function toggleProductStatus($db) {
    try {
        $id = $_POST['product_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if (empty($id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Product ID and status are required']);
            return;
        }
        
        $query = "UPDATE products SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product status']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function uploadProductImage($file) {
    $uploadDir = '../assets/uploads/products/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        error_log("Upload directory is not writable: " . $uploadDir);
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        error_log("No file uploaded or file is empty");
        return false;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $file['error']);
        return false;
    }
    
    // Validate file type by MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        error_log("Invalid file type: " . $mimeType);
        return false;
    }
    
    // Validate file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        error_log("Invalid file extension: " . $extension);
        return false;
    }
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        error_log("File size exceeds limit: " . $file['size']);
        return false;
    }
    
    // Validate that it's actually an image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        error_log("File is not a valid image");
        return false;
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Verify file was actually moved
        if (file_exists($uploadPath)) {
            return $filename;
        } else {
            error_log("File was not successfully moved to: " . $uploadPath);
            return false;
        }
    } else {
        error_log("Failed to move uploaded file to: " . $uploadPath);
        return false;
    }
}
?>