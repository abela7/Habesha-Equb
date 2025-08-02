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
    }
    
    echo "<h2>🚨 TIME BUG DETECTED!</h2>";
    echo "<p><strong style='color: red;'>OTPs are EXPIRING BEFORE THEY'RE CREATED!</strong></p>";
    echo "<p><strong style='color: red;'>Check your database: expires_at is BEFORE created_at!</strong></p>";
    
    // Time debugging
    echo "<h3>🕐 TIME DEBUGGING:</h3>";
    echo "Server time (PHP): " . date('Y-m-d H:i:s') . "<br>";
    echo "Database time: ";
    $time_stmt = $database->prepare("SELECT NOW() as db_time");
    $time_stmt->execute();
    $db_time = $time_stmt->fetch();
    echo $db_time['db_time'] . "<br>";
    
    echo "PHP +10 minutes: " . date('Y-m-d H:i:s', strtotime('+10 minutes')) . "<br>";
    
    // Check timezone
    echo "PHP timezone: " . date_default_timezone_get() . "<br>";
    $tz_stmt = $database->prepare("SELECT @@global.time_zone, @@session.time_zone");
    $tz_stmt->execute();
    $tz = $tz_stmt->fetch();
    echo "DB timezone: global={$tz['@@global.time_zone']}, session={$tz['@@session.time_zone']}<br>";
    
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
        
        // First clean all old broken OTPs
        echo "<h3>🧹 Cleaning old broken OTPs...</h3>";
        $clean_stmt = $database->prepare("DELETE FROM user_otps WHERE email = ?");
        $clean_stmt->execute([$user['email']]);
        echo "Old OTPs cleaned<br>";
        
        // Generate OTP with otp_login type
        echo "<h3>Testing with 'otp_login' type:</h3>";
        try {
            $otp = $emailService->generateOTP($user['id'], $user['email'], 'otp_login');
            echo "Generated OTP: <strong>$otp</strong><br>";
            
            // Show the stored OTP details immediately
            $check_stmt = $database->prepare("
                SELECT *, NOW() as current_db_time, 
                       (expires_at > NOW()) as is_valid,
                       TIMESTAMPDIFF(MINUTE, NOW(), expires_at) as minutes_until_expire
                FROM user_otps 
                WHERE email = ? AND otp_type = 'otp_login' 
                ORDER BY id DESC LIMIT 1
            ");
            $check_stmt->execute([$user['email']]);
            $stored = $check_stmt->fetch();
            if ($stored) {
                echo "<p><strong>Stored OTP Details:</strong><br>";
                echo "Code: {$stored['otp_code']}<br>";
                echo "Created: {$stored['created_at']}<br>";
                echo "Expires: {$stored['expires_at']}<br>";
                echo "Current: {$stored['current_db_time']}<br>";
                echo "Valid: " . ($stored['is_valid'] ? 'YES' : 'NO') . "<br>";
                echo "Minutes until expire: {$stored['minutes_until_expire']}<br>";
                echo "</p>";
            }
            
            // Try to verify immediately
            $verify_result = $emailService->verifyOTP($user['email'], $otp, 'otp_login');
            echo "Verify result: " . ($verify_result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
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