<?php
/**
 * Test script for admin registration debugging
 * This will help identify the exact issue
 */

// Start session
session_start();

// Include database connection
require_once 'includes/db.php';

echo "<h2>Admin Registration Debug Test</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $test_stmt = $pdo->query("SELECT 1");
    echo "✅ Database connection: SUCCESS<br>";
} catch (PDOException $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode() . "<br>";
}

// Test 2: Admins Table
echo "<h3>2. Admins Table Test</h3>";
try {
    $test_stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $admin_count = $test_stmt->fetchColumn();
    echo "✅ Admins table: SUCCESS - Found " . $admin_count . " admins<br>";
} catch (PDOException $e) {
    echo "❌ Admins table: FAILED - " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode() . "<br>";
    
    // Try to create table if it doesn't exist
    if ($e->getCode() == '42S02') {
        echo "Attempting to create admins table...<br>";
        try {
            $create_table_sql = "
                CREATE TABLE IF NOT EXISTS admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    language_preference TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $pdo->exec($create_table_sql);
            echo "✅ Admins table created successfully<br>";
        } catch (PDOException $create_error) {
            echo "❌ Failed to create admins table: " . $create_error->getMessage() . "<br>";
        }
    }
}

// Test 3: CSRF Token Generation
echo "<h3>3. CSRF Token Test</h3>";
try {
    $csrf_token = generate_csrf_token();
    echo "✅ CSRF token generated: " . substr($csrf_token, 0, 10) . "...<br>";
    echo "✅ CSRF token verification: " . (verify_csrf_token($csrf_token) ? 'SUCCESS' : 'FAILED') . "<br>";
} catch (Exception $e) {
    echo "❌ CSRF token error: " . $e->getMessage() . "<br>";
}

// Test 4: Session Test
echo "<h3>4. Session Test</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

// Test 5: File Permissions
echo "<h3>5. File Permissions Test</h3>";
$files_to_check = [
    'admin/api/auth.php',
    'admin/register.php',
    'includes/db.php',
    'admin/includes/admin_auth_guard.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file: EXISTS (readable: " . (is_readable($file) ? 'YES' : 'NO') . ")<br>";
    } else {
        echo "❌ $file: NOT FOUND<br>";
    }
}

// Test 6: PHP Configuration
echo "<h3>6. PHP Configuration Test</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'ENABLED' : 'DISABLED') . "<br>";
echo "Session support: " . (extension_loaded('session') ? 'ENABLED' : 'DISABLED') . "<br>";
echo "Error reporting: " . (error_reporting() ? 'ENABLED' : 'DISABLED') . "<br>";
echo "Display errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";

// Test 7: Simple Registration Test
echo "<h3>7. Registration Function Test</h3>";
try {
    // Test username validation
    $test_username = 'test_admin_' . time();
    $test_password = 'Test123456';
    
    // Check if username exists (should not exist)
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$test_username]);
    $exists = $stmt->fetchColumn() !== false;
    echo "Test username '$test_username' exists: " . ($exists ? 'YES' : 'NO') . "<br>";
    
    if (!$exists) {
        // Try to create admin
        $password_hash = password_hash($test_password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, is_active, language_preference) VALUES (?, ?, 1, 1)");
        $stmt->execute([$test_username, $password_hash]);
        $admin_id = $pdo->lastInsertId();
        echo "✅ Test admin created successfully with ID: $admin_id<br>";
        
        // Clean up - delete test admin
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        echo "✅ Test admin cleaned up<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Registration test failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Debug complete. Check the results above to identify the issue.</strong></p>";
echo "<p>If you see any ❌ errors, those are the likely causes of the registration problem.</p>";
?>