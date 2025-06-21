<?php
session_start();
require_once '../config/database.php';
// Initialize $pdo from Database class
$database = new Database();
$pdo = $database->getConnection();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get current admin info
$adminQuery = "SELECT * FROM admins WHERE id = :id";
$adminStmt = $db->prepare($adminQuery);
$adminStmt->bindParam(':id', $_SESSION['admin_id']);
$adminStmt->execute();
$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

// Get current settings
$settings = [];
$settingsQuery = "SELECT * FROM settings";
$settingsStmt = $db->prepare($settingsQuery);
$settingsStmt->execute();
$settingsResult = $settingsStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($settingsResult as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Check for success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
$info = $_SESSION['info'] ?? '';

// Clear messages after displaying
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['info']);

// Helper function to get setting value
function getSetting($key, $default = '') {
    global $settings;
    return isset($settings[$key]) ? $settings[$key] : $default;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - E-commerce Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .settings-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .settings-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            overflow-x: auto;
        }
        
        .tab-button {
            background: none;
            border: none;
            padding: 15px 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
        }
        
        .tab-button:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        .tab-button.active {
            color: #007bff;
            background: #fff;
            border-bottom-color: #007bff;
        }
        
        .tab-content {
            display: none;
            padding: 30px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .settings-section {
            margin-bottom: 40px;
        }
        
        .settings-section h2 {
            color: #495057;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .settings-form {
            margin-bottom: 30px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-top: 25px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: 500;
        }
        
        .checkbox-label input[type="checkbox"] {
            margin-right: 10px;
            width: auto;
        }
        
        .form-actions {
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .settings-tabs {
                flex-direction: column;
            }
            
            .tab-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-cog"></i> Settings</h1>
                <p>Manage your application settings</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($info): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo htmlspecialchars($info); ?>
                </div>
            <?php endif; ?>
            
            <!-- Settings Tabs -->
            <div class="settings-container">
                <div class="settings-tabs">
                    <button class="tab-button active" onclick="showTab('general')">
                        <i class="fas fa-cogs"></i> General
                    </button>
                    <button class="tab-button" onclick="showTab('profile')">
                        <i class="fas fa-user"></i> Profile
                    </button>
                    <button class="tab-button" onclick="showTab('email')">
                        <i class="fas fa-envelope"></i> Email
                    </button>
                    <button class="tab-button" onclick="showTab('site')">
                        <i class="fas fa-globe"></i> Site
                    </button>
                    <button class="tab-button" onclick="showTab('system')">
                        <i class="fas fa-server"></i> System
                    </button>
                </div>
                
                <!-- General Settings Tab -->
                <div id="general" class="tab-content active">
                    <form method="POST" action="../actions/settings_actions.php" class="settings-form">
                        <input type="hidden" name="action" value="update_general">
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="site_name">Site Name *</label>
                                    <input type="text" id="site_name" name="site_name" 
                                           value="<?php echo htmlspecialchars(getSetting('site_name')); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_email">Site Email *</label>
                                    <input type="email" id="site_email" name="site_email" 
                                           value="<?php echo htmlspecialchars(getSetting('site_email')); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_description">Site Description</label>
                                <textarea id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars(getSetting('site_description')); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="site_phone">Site Phone</label>
                                    <input type="tel" id="site_phone" name="site_phone" 
                                           value="<?php echo htmlspecialchars(getSetting('site_phone')); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <select id="currency" name="currency">
                                        <option value="USD" <?php echo getSetting('currency') == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                        <option value="EUR" <?php echo getSetting('currency') == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                        <option value="GBP" <?php echo getSetting('currency') == 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                        <option value="JPY" <?php echo getSetting('currency') == 'JPY' ? 'selected' : ''; ?>>JPY (¥)</option>
                                        <option value="PKR" <?php echo getSetting('currency') == 'PKR' ? 'selected' : ''; ?>>PKR (₨)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_address">Site Address</label>
                                <textarea id="site_address" name="site_address" rows="3"><?php echo htmlspecialchars(getSetting('site_address')); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="timezone">Timezone</label>
                                    <select id="timezone" name="timezone">
                                        <option value="UTC" <?php echo getSetting('timezone') == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                        <option value="America/New_York" <?php echo getSetting('timezone') == 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                        <option value="America/Chicago" <?php echo getSetting('timezone') == 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                        <option value="America/Denver" <?php echo getSetting('timezone') == 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                        <option value="America/Los_Angeles" <?php echo getSetting('timezone') == 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                        <option value="Europe/London" <?php echo getSetting('timezone') == 'Europe/London' ? 'selected' : ''; ?>>London</option>
                                        <option value="Europe/Paris" <?php echo getSetting('timezone') == 'Europe/Paris' ? 'selected' : ''; ?>>Paris</option>
                                        <option value="Asia/Tokyo" <?php echo getSetting('timezone') == 'Asia/Tokyo' ? 'selected' : ''; ?>>Tokyo</option>
                                        <option value="Asia/Karachi" <?php echo getSetting('timezone') == 'Asia/Karachi' ? 'selected' : ''; ?>>Karachi</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="maintenance_mode" value="1" 
                                                   <?php echo getSetting('maintenance_mode') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Maintenance Mode
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Profile Settings Tab -->
                <div id="profile" class="tab-content">
                    <form method="POST" action="../actions/settings_actions.php" class="settings-form">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-user"></i> Admin Profile</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="admin_name">Full Name *</label>
                                    <input type="text" id="admin_name" name="name" 
                                           value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="admin_email">Email *</label>
                                    <input type="email" id="admin_email" name="email" 
                                           value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_phone">Phone</label>
                                <input type="tel" id="admin_phone" name="phone" 
                                       value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                    
                    <!-- Change Password Form -->
                    <form method="POST" action="../actions/settings_actions.php" class="settings-form">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-lock"></i> Change Password</h2>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">New Password *</label>
                                    <input type="password" id="new_password" name="new_password" 
                                           minlength="6" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Email Settings Tab -->
                <div id="email" class="tab-content">
                    <form method="POST" action="../actions/settings_actions.php" class="settings-form">
                        <input type="hidden" name="action" value="update_email">
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-envelope"></i> SMTP Configuration</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="smtp_host">SMTP Host</label>
                                    <input type="text" id="smtp_host" name="smtp_host" 
                                           value="<?php echo htmlspecialchars(getSetting('smtp_host')); ?>" 
                                           placeholder="smtp.gmail.com">
                                </div>
                                
                                <div class="form-group">
                                    <label for="smtp_port">SMTP Port</label>
                                    <input type="number" id="smtp_port" name="smtp_port" 
                                           value="<?php echo htmlspecialchars(getSetting('smtp_port', '587')); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="smtp_username">SMTP Username</label>
                                    <input type="text" id="smtp_username" name="smtp_username" 
                                           value="<?php echo htmlspecialchars(getSetting('smtp_username')); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="smtp_password">SMTP Password</label>
                                    <input type="password" id="smtp_password" name="smtp_password" 
                                           value="<?php echo htmlspecialchars(getSetting('smtp_password')); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="smtp_encryption">Encryption</label>
                                    <select id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls" <?php echo getSetting('smtp_encryption') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo getSetting('smtp_encryption') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="none" <?php echo getSetting('smtp_encryption') == 'none' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="smtp_from_name">From Name</label>
                                    <input type="text" id="smtp_from_name" name="smtp_from_name" 
                                           value="<?php echo htmlspecialchars(getSetting('smtp_from_name')); ?>" 
                                           placeholder="Your Site Name">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="smtp_auth" value="1" 
                                               <?php echo getSetting('smtp_auth', '1') ? 'checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                        Enable SMTP Authentication
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Email Settings
                            </button>
                            <button type="button" class="btn btn-success" onclick="testEmail()">
                                <i class="fas fa-paper-plane"></i> Test Email
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Site Settings Tab -->
                <div id="site" class="tab-content">
                    <form method="POST" action="../actions/settings_actions.php" class="settings-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_site">
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-globe"></i> Site Appearance</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="site_logo">Site Logo</label>
                                    <input type="file" id="site_logo" name="site_logo" accept="image/*">
                                    <?php if (getSetting('site_logo')): ?>
                                        <div style="margin-top: 10px;">
                                            <img src="../<?php echo htmlspecialchars(getSetting('site_logo')); ?>" 
                                                 alt="Current Logo" style="max-height: 60px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_favicon">Site Favicon</label>
                                    <input type="file" id="site_favicon" name="site_favicon" accept="image/*">
                                    <?php if (getSetting('site_favicon')): ?>
                                        <div style="margin-top: 10px;">
                                            <img src="../<?php echo htmlspecialchars(getSetting('site_favicon')); ?>" 
                                                 alt="Current Favicon" style="max-height: 32px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="theme_color">Theme Color</label>
                                    <input type="color" id="theme_color" name="theme_color" 
                                           value="<?php echo htmlspecialchars(getSetting('theme_color', '#007bff')); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="items_per_page">Items Per Page</label>
                                    <input type="number" id="items_per_page" name="items_per_page" 
                                           value="<?php echo htmlspecialchars(getSetting('items_per_page', '20')); ?>" 
                                           min="5" max="100">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="allow_registrations" value="1" 
                                                   <?php echo getSetting('allow_registrations', '1') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Allow User Registrations
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="email_verification" value="1" 
                                                   <?php echo getSetting('email_verification') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Require Email Verification
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-share-alt"></i> Social Media Links</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="facebook_url">Facebook URL</label>
                                    <input type="url" id="facebook_url" name="facebook_url" 
                                           value="<?php echo htmlspecialchars(getSetting('facebook_url')); ?>" 
                                           placeholder="https://facebook.com/yourpage">
                                </div>
                                
                                <div class="form-group">
                                    <label for="twitter_url">Twitter URL</label>
                                    <input type="url" id="twitter_url" name="twitter_url" 
                                           value="<?php echo htmlspecialchars(getSetting('twitter_url')); ?>" 
                                           placeholder="https://twitter.com/youraccount">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="instagram_url">Instagram URL</label>
                                    <input type="url" id="instagram_url" name="instagram_url" 
                                           value="<?php echo htmlspecialchars(getSetting('instagram_url')); ?>" 
                                           placeholder="https://instagram.com/youraccount">
                                </div>
                                
                                <div class="form-group">
                                    <label for="linkedin_url">LinkedIn URL</label>
                                    <input type="url" id="linkedin_url" name="linkedin_url" 
                                           value="<?php echo htmlspecialchars(getSetting('linkedin_url')); ?>" 
                                           placeholder="https://linkedin.com/company/yourcompany">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Site Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- System Settings Tab -->
                <div id="system" class="tab-content">
                    <form method="POST" action="../actions/settings_actions.php" class="settings-form">
                        <input type="hidden" name="action" value="update_system">
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-server"></i> System Configuration</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="max_upload_size">Max Upload Size (MB)</label>
                                    <input type="number" id="max_upload_size" name="max_upload_size" 
                                           value="<?php echo htmlspecialchars(getSetting('max_upload_size', '5')); ?>" 
                                           min="1" max="100">
                                </div>
                                
                                <div class="form-group">
                                    <label for="session_timeout">Session Timeout (minutes)</label>
                                    <input type="number" id="session_timeout" name="session_timeout" 
                                           value="<?php echo htmlspecialchars(getSetting('session_timeout', '30')); ?>" 
                                           min="5" max="1440">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="backup_frequency">Backup Frequency</label>
                                    <select id="backup_frequency" name="backup_frequency">
                                        <option value="daily" <?php echo getSetting('backup_frequency') == 'daily' ? 'selected' : ''; ?>>Daily</option>
                                        <option value="weekly" <?php echo getSetting('backup_frequency') == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo getSetting('backup_frequency') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                        <option value="never" <?php echo getSetting('backup_frequency') == 'never' ? 'selected' : ''; ?>>Never</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="log_level">Log Level</label>
                                    <select id="log_level" name="log_level">
                                        <option value="error" <?php echo getSetting('log_level') == 'error' ? 'selected' : ''; ?>>Error Only</option>
                                        <option value="warning" <?php echo getSetting('log_level') == 'warning' ? 'selected' : ''; ?>>Warning & Error</option>
                                        <option value="info" <?php echo getSetting('log_level') == 'info' ? 'selected' : ''; ?>>Info, Warning & Error</option>
                                        <option value="debug" <?php echo getSetting('log_level') == 'debug' ? 'selected' : ''; ?>>All (Debug Mode)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="enable_cache" value="1" 
                                                   <?php echo getSetting('enable_cache', '1') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Enable System Cache
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="debug_mode" value="1" 
                                                   <?php echo getSetting('debug_mode') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Enable Debug Mode
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="allowed_file_types">Allowed File Types (comma separated)</label>
                                <input type="text" id="allowed_file_types" name="allowed_file_types" 
                                       value="<?php echo htmlspecialchars(getSetting('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx')); ?>" 
                                       placeholder="jpg,jpeg,png,gif,pdf,doc,docx">
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-shield-alt"></i> Security Settings</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="max_login_attempts">Max Login Attempts</label>
                                    <input type="number" id="max_login_attempts" name="max_login_attempts" 
                                           value="<?php echo htmlspecialchars(getSetting('max_login_attempts', '5')); ?>" 
                                           min="3" max="10">
                                </div>
                                
                                <div class="form-group">
                                    <label for="lockout_duration">Lockout Duration (minutes)</label>
                                    <input type="number" id="lockout_duration" name="lockout_duration" 
                                           value="<?php echo htmlspecialchars(getSetting('lockout_duration', '15')); ?>" 
                                           min="5" max="60">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="enable_2fa" value="1" 
                                                   <?php echo getSetting('enable_2fa') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Enable Two-Factor Authentication
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="force_https" value="1" 
                                                   <?php echo getSetting('force_https') ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Force HTTPS
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <h2><i class="fas fa-tools"></i> System Actions</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <button type="button" class="btn btn-warning" onclick="clearCache()">
                                        <i class="fas fa-broom"></i> Clear System Cache
                                    </button>
                                </div>
                                
                                <div class="form-group">
                                    <button type="button" class="btn btn-success" onclick="createBackup()">
                                        <i class="fas fa-download"></i> Create Backup
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" onclick="viewLogs()">
                                        <i class="fas fa-file-alt"></i> View System Logs
                                    </button>
                                </div>
                                
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" onclick="systemInfo()">
                                        <i class="fas fa-info-circle"></i> System Information
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save System Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab button
            event.target.classList.add('active');
        }
        
        // Password confirmation validation
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Test email functionality
        function testEmail() {
            const formData = new FormData();
            formData.append('action', 'test_email');
            
            fetch('../actions/settings_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email sent successfully!');
                } else {
                    alert('Failed to send test email: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
        
        // Clear cache functionality
        function clearCache() {
            if (confirm('Are you sure you want to clear the system cache?')) {
                const formData = new FormData();
                formData.append('action', 'clear_cache');
                
                fetch('../actions/settings_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('System cache cleared successfully!');
                    } else {
                        alert('Failed to clear cache: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }
        
        // Create backup functionality
        function createBackup() {
            if (confirm('This will create a backup of your database and files. Continue?')) {
                const formData = new FormData();
                formData.append('action', 'create_backup');
                
                fetch('../actions/settings_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Backup created successfully!');
                        if (data.download_url) {
                            window.open(data.download_url, '_blank');
                        }
                    } else {
                        alert('Failed to create backup: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }
        
        // View logs functionality
        function viewLogs() {
            window.open('../admin/logs.php', '_blank');
        }
        
        // System information functionality
        function systemInfo() {
            window.open('../admin/system_info.php', '_blank');
        }
        
        // File upload preview
        document.getElementById('site_logo')?.addEventListener('change', function(e) {
            previewFile(e.target, 'logo-preview');
        });
        
        document.getElementById('site_favicon')?.addEventListener('change', function(e) {
            previewFile(e.target, 'favicon-preview');
        });
        
        function previewFile(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById(previewId);
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = previewId;
                        preview.style.maxHeight = '60px';
                        preview.style.marginTop = '10px';
                        input.parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Theme color preview
        document.getElementById('theme_color')?.addEventListener('change', function() {
            const color = this.value;
            document.documentElement.style.setProperty('--primary-color', color);
        });
        
        // Auto-save functionality for certain fields
        const autoSaveFields = ['site_name', 'site_email', 'site_description'];
        let autoSaveTimeout;
        
        autoSaveFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    clearTimeout(autoSaveTimeout);
                    autoSaveTimeout = setTimeout(() => {
                        // Auto-save logic here
                        console.log('Auto-saving field: ' + fieldId);
                    }, 2000);
                });
            }
        });
        
        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('error');
                        isValid = false;
                    } else {
                        field.classList.remove('error');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
        
        // Initialize tooltips and other UI enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading states to buttons
            document.querySelectorAll('button[type="submit"]').forEach(button => {
                button.addEventListener('click', function() {
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    
                    setTimeout(() => {
                        this.disabled = false;
                        this.innerHTML = this.innerHTML.replace(/<i class="fas fa-spinner fa-spin"><\/i> Saving.../, this.innerHTML);
                    }, 2000);
                });
            });
        });
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