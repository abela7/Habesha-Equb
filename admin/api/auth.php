<?php
/**
 * HabeshaEqub Admin Authentication API
 * Clean JSON-only API with no HTML output
 */

// Prevent any output
ob_start();

// Suppress all errors that could cause HTML output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Start session silently
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Set JSON headers immediately
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit('{}');
}

/**
 * Clean JSON response function
 */
function json_response($success, $message = '', $data = []) {
    // Clear any output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Database connection with error handling
try {
    $host = 'localhost';
    $dbname = 'habeshjv_habeshaequb';
    $username = 'habeshjv_abel';
    $password = '2121@Habesha';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    json_response(false, 'Database connection failed');
}

// Ensure admins table exists
try {
    $pdo->query("SELECT 1 FROM admins LIMIT 1");
} catch (PDOException $e) {
    // Create table if it doesn't exist
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                language_preference TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (PDOException $e) {
        json_response(false, 'Database setup failed');
    }
}

// CSRF functions
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (empty($action)) {
    json_response(false, 'No action specified');
}

// Handle actions
switch ($action) {
    case 'register':
        // Verify CSRF
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            json_response(false, 'Security token mismatch');
        }
        
        // Get and validate input
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($username)) {
            json_response(false, 'Username is required');
        }
        if (strlen($username) < 3 || strlen($username) > 50) {
            json_response(false, 'Username must be 3-50 characters');
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            json_response(false, 'Username can only contain letters, numbers, and underscores');
        }
        if (empty($password)) {
            json_response(false, 'Password is required');
        }
        if (strlen($password) < 6) {
            json_response(false, 'Password must be at least 6 characters');
        }
        if (!preg_match('/(?=.*[a-zA-Z])(?=.*\d)/', $password)) {
            json_response(false, 'Password must contain both letters and numbers');
        }
        if ($password !== $confirm_password) {
            json_response(false, 'Passwords do not match');
        }
        
        // Check if username exists
        try {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn()) {
                json_response(false, 'Username already exists');
            }
        } catch (PDOException $e) {
            json_response(false, 'Database error');
        }
        
        // Create admin
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, is_active, language_preference) VALUES (?, ?, 1, 1)");
            $stmt->execute([$username, $password_hash]);
            
            json_response(true, 'Admin account created successfully! You can now login.');
        } catch (PDOException $e) {
            json_response(false, 'Failed to create admin account');
        }
        break;
        
    case 'login':
        // Verify CSRF
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            json_response(false, 'Security token mismatch');
        }
        
        // Get input
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($username)) {
            json_response(false, 'Username is required');
        }
        if (empty($password)) {
            json_response(false, 'Password is required');
        }
        
        // Authenticate
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, is_active FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                json_response(false, 'Invalid username or password');
            }
            
            if (!$admin['is_active']) {
                json_response(false, 'Account is deactivated');
            }
            
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                json_response(true, 'Login successful!', ['redirect' => 'dashboard.php']);
            } else {
                json_response(false, 'Invalid username or password');
            }
        } catch (PDOException $e) {
            json_response(false, 'Authentication failed');
        }
        break;
        
    case 'logout':
        $_SESSION = [];
        session_destroy();
        json_response(true, 'Logged out successfully');
        break;
        
    case 'check_auth':
        $authenticated = isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
        json_response(true, 'Auth status checked', [
            'authenticated' => $authenticated,
            'admin_id' => $_SESSION['admin_id'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null
        ]);
        break;
        
    default:
        json_response(false, 'Invalid action');
}
?> 