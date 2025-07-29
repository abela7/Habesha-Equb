<?php
/**
 * HabeshaEqub - Admin Management API
 * Handle CRUD operations for admin accounts
 */

require_once '../../includes/db.php';

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

/**
 * JSON response helper
 */
function json_response($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
        'data' => $data,
        'timestamp' => date('c')
    ]);
    exit;
}

// Simple admin authentication check for API
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    json_response(false, 'Unauthorized access');
}

// Get current admin info
$current_admin_id = $_SESSION['admin_id'];

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $admin_id = $_GET['id'] ?? '';
    
    if ($action === 'get' && $admin_id) {
        try {
            // Validate admin_id is numeric
            if (!is_numeric($admin_id)) {
                json_response(false, 'Invalid admin ID');
            }
            
            $stmt = $pdo->prepare("SELECT id, username, email, phone, is_active, language_preference FROM admins WHERE id = ?");
            $stmt->execute([(int)$admin_id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                json_response(true, 'Admin data retrieved successfully', ['admin' => $admin]);
            } else {
                json_response(false, 'Admin not found');
            }
        } catch (Exception $e) {
            error_log("Admin Management API GET error: " . $e->getMessage());
            json_response(false, 'Failed to retrieve admin data: ' . $e->getMessage());
        }
    } else {
        json_response(false, 'Invalid GET request - missing action or ID');
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '') ?: null;
                $phone = trim($_POST['phone'] ?? '') ?: null;
                $password = $_POST['password'] ?? '';
                $language_preference = (int)($_POST['language_preference'] ?? 0);
                $is_active = (int)($_POST['is_active'] ?? 1);
                
                // Validation
                if (empty($username) || empty($password)) {
                    json_response(false, 'Username and password are required');
                }
                
                if (strlen($password) < 6) {
                    json_response(false, 'Password must be at least 6 characters long');
                }
                
                // Check if username exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    json_response(false, 'Username already exists. Please choose a different username.');
                }
                
                // Check if email exists (if provided)
                if ($email) {
                    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        json_response(false, 'Email already exists. Please use a different email.');
                    }
                }
                
                // Create admin
                $password_hash = password_hash($password, PASSWORD_ARGON2ID);
                $stmt = $pdo->prepare("
                    INSERT INTO admins (username, email, phone, password, is_active, language_preference, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$username, $email, $phone, $password_hash, $is_active, $language_preference]);
                
                json_response(true, 'Admin account created successfully');
                break;
                
            case 'update':
                $admin_id = (int)($_POST['admin_id'] ?? 0);
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '') ?: null;
                $phone = trim($_POST['phone'] ?? '') ?: null;
                $language_preference = (int)($_POST['language_preference'] ?? 0);
                $is_active = (int)($_POST['is_active'] ?? 1);
                
                if (!$admin_id || empty($username)) {
                    json_response(false, 'Admin ID and username are required');
                }
                
                // Check if admin exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ?");
                $stmt->execute([$admin_id]);
                if (!$stmt->fetch()) {
                    json_response(false, 'Admin not found');
                }
                
                // Check if username exists for other admins
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
                $stmt->execute([$username, $admin_id]);
                if ($stmt->fetch()) {
                    json_response(false, 'Username already exists. Please choose a different username.');
                }
                
                // Check if email exists for other admins (if provided)
                if ($email) {
                    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $admin_id]);
                    if ($stmt->fetch()) {
                        json_response(false, 'Email already exists. Please use a different email.');
                    }
                }
                
                // Update admin
                $stmt = $pdo->prepare("
                    UPDATE admins 
                    SET username = ?, email = ?, phone = ?, is_active = ?, language_preference = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $phone, $is_active, $language_preference, $admin_id]);
                
                json_response(true, 'Admin account updated successfully');
                break;
                
            case 'toggle_status':
                $admin_id = (int)($_POST['admin_id'] ?? 0);
                $is_active = (int)($_POST['is_active'] ?? 1);
                
                if (!$admin_id) {
                    json_response(false, 'Admin ID is required');
                }
                
                // Prevent deactivating self
                if ($admin_id == $current_admin_id && !$is_active) {
                    json_response(false, 'You cannot deactivate your own account');
                }
                
                // Check if admin exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ?");
                $stmt->execute([$admin_id]);
                if (!$stmt->fetch()) {
                    json_response(false, 'Admin not found');
                }
                
                // Update status
                $stmt = $pdo->prepare("UPDATE admins SET is_active = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$is_active, $admin_id]);
                
                $action_text = $is_active ? 'activated' : 'deactivated';
                json_response(true, "Admin account {$action_text} successfully");
                break;
                
            case 'delete':
                $admin_id = (int)($_POST['admin_id'] ?? 0);
                
                if (!$admin_id) {
                    json_response(false, 'Admin ID is required');
                }
                
                // Prevent deleting self
                if ($admin_id == $current_admin_id) {
                    json_response(false, 'You cannot delete your own account');
                }
                
                // Check if admin exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ?");
                $stmt->execute([$admin_id]);
                if (!$stmt->fetch()) {
                    json_response(false, 'Admin not found');
                }
                
                // Check if this is the last active admin
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE is_active = 1 AND id != ?");
                $stmt->execute([$admin_id]);
                $active_count = $stmt->fetchColumn();
                
                if ($active_count == 0) {
                    json_response(false, 'Cannot delete the last active admin account');
                }
                
                // Delete admin
                $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                $stmt->execute([$admin_id]);
                
                json_response(true, 'Admin account deleted successfully');
                break;
                
            default:
                json_response(false, 'Invalid action');
        }
        
    } catch (Exception $e) {
        error_log("Admin Management API error: " . $e->getMessage());
        json_response(false, 'An error occurred while processing your request');
    }
}

json_response(false, 'Invalid request method');
?> 