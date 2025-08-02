<?php
/**
 * OTP DEBUG - Check table structure and recent OTPs
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 OTP SYSTEM DEBUG</h1>";

try {
    // Database connection
    require_once __DIR__ . '/../../includes/db.php';
    $database = isset($pdo) ? $pdo : $db;
    echo "✅ Database connected<br>";
    
    echo "<h2>📋 Check user_otps Table</h2>";
    
    // Check if table exists
    $stmt = $database->prepare("SHOW TABLES LIKE 'user_otps'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "✅ user_otps table exists<br>";
        
        // Show table structure
        $stmt = $database->prepare("DESCRIBE user_otps");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show recent OTPs
        echo "<h3>Recent OTPs (last 10):</h3>";
        $stmt = $database->prepare("SELECT * FROM user_otps ORDER BY id DESC LIMIT 10");
        $stmt->execute();
        $otps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($otps) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Email</th><th>Code</th><th>Type</th><th>Expires</th><th>Used</th><th>Attempts</th></tr>";
            foreach ($otps as $otp) {
                echo "<tr>";
                echo "<td>{$otp['id']}</td>";
                echo "<td>{$otp['user_id']}</td>";
                echo "<td>{$otp['email']}</td>";
                echo "<td><strong>{$otp['otp_code']}</strong></td>";
                echo "<td>{$otp['otp_type']}</td>";
                echo "<td>{$otp['expires_at']}</td>";
                echo "<td>" . ($otp['is_used'] ? 'YES' : 'NO') . "</td>";
                echo "<td>{$otp['attempt_count']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No OTPs found<br>";
        }
        
    } else {
        echo "❌ user_otps table does not exist!<br>";
        echo "<p><strong>SOLUTION:</strong> Run this SQL in phpMyAdmin:</p>";
        echo "<pre>
CREATE TABLE user_otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    otp_type VARCHAR(50) DEFAULT 'email_verification',
    expires_at TIMESTAMP NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    attempt_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(email, otp_type),
    INDEX(expires_at)
);
        </pre>";
    }
    
    echo "<h2>🚨 SCHEMA PROBLEM DETECTED!</h2>";
    echo "<p><strong style='color: red;'>The otp_type column is an ENUM that doesn't allow 'otp_login'!</strong></p>";
    echo "<p><strong style='color: red;'>That's why all Type fields are EMPTY and verification fails!</strong></p>";
    
    echo "<h3>🔧 IMMEDIATE FIX REQUIRED:</h3>";
    echo "<p>Copy and paste this SQL into phpMyAdmin:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>
-- FIX OTP TABLE SCHEMA
ALTER TABLE user_otps MODIFY COLUMN otp_type 
ENUM('email_verification','login','otp_login') NOT NULL DEFAULT 'email_verification';

ALTER TABLE user_otps MODIFY COLUMN otp_code VARCHAR(10) NOT NULL;

DELETE FROM user_otps;
    </pre>";
    
    echo "<h2>📋 Test EmailService</h2>";
    require_once __DIR__ . '/../../includes/email/EmailService.php';
    $emailService = new EmailService($database);
    echo "✅ EmailService created<br>";
    
    // Get a test user
    $stmt = $database->prepare("SELECT id, email, first_name FROM members WHERE is_approved = 1 LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Test user: {$user['email']}<br>";
        
        // Generate OTP with otp_login type
        echo "<h3>Testing with 'otp_login' type:</h3>";
        try {
            $otp = $emailService->generateOTP($user['id'], $user['email'], 'otp_login');
            echo "Generated OTP: <strong>$otp</strong><br>";
            
            // Try to verify immediately
            $verify_result = $emailService->verifyOTP($user['email'], $otp, 'otp_login');
            echo "Verify result: " . ($verify_result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
            echo "<p><strong>This confirms the ENUM problem - fix the schema first!</strong></p>";
        }
        
    } else {
        echo "❌ No test user found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Check server error log for detailed OTP debug messages!</strong></p>";
?>