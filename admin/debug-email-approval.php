<?php
/**
 * HabeshaEqub - Email System Diagnostic Tool
 * Complete test of approval email system
 */

require_once '../includes/db.php';
require_once '../includes/email/EmailService.php';

// Start output buffering for clean output
ob_start();
echo "<!DOCTYPE html>\n<html><head><title>Email System Diagnostic</title>";
echo "<style>body{font-family:monospace;margin:20px;background:#f5f5f5;} .result{padding:10px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;} .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;} .info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;} h2{color:#333;} .code{background:#2d3748;color:#68d391;padding:10px;border-radius:5px;overflow-x:auto;}</style>";
echo "</head><body>";

echo "<h1>🔧 HabeshaEqub Email System Diagnostic</h1>";
echo "<p><strong>Testing complete email approval system...</strong></p>";

$results = [];

// Test 1: Database Connection
echo "<h2>📊 Test 1: Database Connection</h2>";
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "<div class='result success'>✅ PDO connection available</div>";
        $results['db'] = true;
    } elseif (isset($db) && $db instanceof PDO) {
        $pdo = $db; // Use $db if $pdo not available
        echo "<div class='result success'>✅ Database connection available (using \$db)</div>";
        $results['db'] = true;
    } else {
        throw new Exception("No database connection found");
    }
} catch (Exception $e) {
    echo "<div class='result error'>❌ Database Error: " . $e->getMessage() . "</div>";
    $results['db'] = false;
}

// Test 2: System Settings (SMTP Config)
echo "<h2>📧 Test 2: SMTP Configuration</h2>";
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'email'");
    $stmt->execute();
    
    $smtp_settings = [];
    while ($row = $stmt->fetch()) {
        $smtp_settings[$row['setting_key']] = $row['setting_value'];
    }
    
    if (empty($smtp_settings)) {
        echo "<div class='result error'>❌ No SMTP settings found in database!</div>";
        echo "<div class='result info'>💡 Go to admin/email-notifications.php to configure SMTP</div>";
        $results['smtp_config'] = false;
    } else {
        echo "<div class='result success'>✅ SMTP settings found:</div>";
        foreach ($smtp_settings as $key => $value) {
            if (strpos($key, 'password') !== false) {
                $value = str_repeat('*', strlen($value)); // Hide password
            }
            echo "<div class='result info'>• {$key}: {$value}</div>";
        }
        $results['smtp_config'] = true;
    }
} catch (Exception $e) {
    echo "<div class='result error'>❌ SMTP Config Error: " . $e->getMessage() . "</div>";
    $results['smtp_config'] = false;
}

// Test 3: EmailService Class
echo "<h2>🔧 Test 3: EmailService Initialization</h2>";
try {
    $emailService = new EmailService($pdo);
    echo "<div class='result success'>✅ EmailService created successfully</div>";
    $results['email_service'] = true;
} catch (Exception $e) {
    echo "<div class='result error'>❌ EmailService Error: " . $e->getMessage() . "</div>";
    $results['email_service'] = false;
}

// Test 4: Email Template
echo "<h2>📄 Test 4: Email Template Loading</h2>";
try {
    $template_path = '../includes/email/templates/account_approved.html';
    if (file_exists($template_path)) {
        $content = file_get_contents($template_path);
        if (strlen($content) > 100) {
            echo "<div class='result success'>✅ Template file exists and has content (" . strlen($content) . " characters)</div>";
            $results['template'] = true;
        } else {
            echo "<div class='result error'>❌ Template file is too small (" . strlen($content) . " characters)</div>";
            $results['template'] = false;
        }
    } else {
        echo "<div class='result error'>❌ Template file not found: {$template_path}</div>";
        $results['template'] = false;
    }
} catch (Exception $e) {
    echo "<div class='result error'>❌ Template Error: " . $e->getMessage() . "</div>";
    $results['template'] = false;
}

// Test 5: Email Sending (if all previous tests pass)
echo "<h2>📨 Test 5: Complete Email Sending Test</h2>";

if ($results['db'] && $results['smtp_config'] && $results['email_service'] && $results['template']) {
    try {
        // Test email data
        $test_email = 'test@example.com';
        $test_name = 'Test User';
        $test_variables = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'member_id' => 'HE-TEST-001',
            'email' => $test_email,
            'login_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/user/login.php'
        ];
        
        echo "<div class='result info'>🧪 Attempting to send test email...</div>";
        echo "<div class='result info'>To: {$test_email}</div>";
        echo "<div class='result info'>Template: account_approved</div>";
        
        $result = $emailService->send('account_approved', $test_email, $test_name, $test_variables);
        
        if ($result['success']) {
            echo "<div class='result success'>✅ EMAIL SENT SUCCESSFULLY!</div>";
            echo "<div class='result success'>📊 Delivery time: " . ($result['delivery_time'] ?? 'N/A') . "ms</div>";
            echo "<div class='result success'>📝 Message: " . ($result['message'] ?? 'Success') . "</div>";
        } else {
            echo "<div class='result error'>❌ EMAIL SENDING FAILED!</div>";
            echo "<div class='result error'>📝 Error: " . ($result['message'] ?? 'Unknown error') . "</div>";
            echo "<div class='result error'>⏱️ Time: " . ($result['delivery_time'] ?? 'N/A') . "ms</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='result error'>❌ EMAIL TEST EXCEPTION: " . $e->getMessage() . "</div>";
        echo "<div class='result error'>📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "</div>";
    }
} else {
    echo "<div class='result error'>❌ Skipping email test - previous tests failed</div>";
    echo "<div class='result info'>💡 Fix the above issues first</div>";
}

// Test 6: Database Tables Check
echo "<h2>🗂️ Test 6: Required Tables Check</h2>";
$required_tables = ['email_rate_limits', 'user_otps', 'notifications', 'system_settings'];
foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM {$table} LIMIT 1");
        echo "<div class='result success'>✅ Table '{$table}' exists</div>";
    } catch (Exception $e) {
        echo "<div class='result error'>❌ Table '{$table}' missing or inaccessible</div>";
    }
}

// Summary
echo "<h2>📋 SUMMARY</h2>";
$all_passed = array_reduce($results, function($carry, $item) { return $carry && $item; }, true);

if ($all_passed) {
    echo "<div class='result success'>🎉 ALL TESTS PASSED! Email system should work.</div>";
    echo "<div class='result info'>💡 If emails still don't work, check your server's email logs or contact your hosting provider.</div>";
} else {
    echo "<div class='result error'>❌ Some tests failed. Fix the issues above.</div>";
    
    if (!$results['smtp_config']) {
        echo "<div class='result info'>🔧 <strong>Action Required:</strong> Configure SMTP in admin/email-notifications.php</div>";
    }
}

echo "<hr><p><small>Diagnostic completed at " . date('Y-m-d H:i:s') . "</small></p>";
echo "</body></html>";

$output = ob_get_clean();
echo $output;
?>