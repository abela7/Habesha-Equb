<?php
/**
 * Direct SMS Test Script
 * Tests SMS sending with detailed error output
 * 
 * Usage: Open this file in your browser
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/sms/SmsService.php';

echo "<h1>SMS Test Script</h1>";
echo "<pre>";

try {
    // Initialize SMS service
    $smsService = new SmsService($pdo);
    
    // Get SMS configuration from database
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_category = 'sms'");
    $stmt->execute();
    
    $config = [];
    while ($row = $stmt->fetch()) {
        $config[$row['setting_key']] = $row['setting_value'];
    }
    
    echo "=== SMS Configuration ===\n";
    echo "SMS Enabled: " . ($config['sms_enabled'] ?? 'NOT SET') . "\n";
    echo "API Key: " . (isset($config['sms_api_key']) && !empty($config['sms_api_key']) ? '✓ SET (Hidden for security)' : '✗ NOT SET') . "\n";
    echo "Sender Name: " . ($config['sms_sender_name'] ?? 'NOT SET') . "\n";
    echo "Test Mode: " . ($config['sms_test_mode'] ?? '0') . "\n";
    echo "\n";
    
    // Check if API key is set
    if (empty($config['sms_api_key'])) {
        echo "❌ ERROR: SMS API key is not configured!\n";
        echo "Please add your Brevo API key in: admin/system-configuration.php → SMS tab\n";
        exit;
    }
    
    // Get a test member's phone number
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, phone, language_preference FROM members WHERE is_active=1 AND phone IS NOT NULL AND phone != '' ORDER BY id LIMIT 1");
    $stmt->execute();
    $testMember = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$testMember) {
        echo "❌ ERROR: No active members with phone numbers found!\n";
        exit;
    }
    
    echo "=== Test Member ===\n";
    echo "Name: {$testMember['first_name']} {$testMember['last_name']}\n";
    echo "Phone: {$testMember['phone']}\n";
    echo "Language: " . ($testMember['language_preference'] == 1 ? 'Amharic' : 'English') . "\n";
    echo "\n";
    
    // Prepare test message
    $title_en = "Test SMS";
    $title_am = "የሙከራ መልእክት";
    $body_en = "This is a test SMS from HabeshaEqub system. If you receive this, SMS is working!";
    $body_am = "ይህ ከHabeshaEqub ስርዓት የሙከራ መልእክት ነው። ይህን ካገኙ SMS እየሰራ ነው!";
    
    echo "=== Sending Test SMS ===\n";
    echo "Attempting to send...\n\n";
    
    // Send SMS
    $result = $smsService->sendProgramNotificationToMember(
        $testMember,
        $title_en,
        $title_am,
        $body_en,
        $body_am
    );
    
    echo "=== RESULT ===\n";
    if ($result['success']) {
        echo "✅ SUCCESS!\n";
        echo "Message: {$result['message']}\n";
        echo "Delivery Time: {$result['delivery_time']}ms\n";
        if (isset($result['message_id'])) {
            echo "Message ID: {$result['message_id']}\n";
        }
        if (isset($result['credits_remaining'])) {
            echo "Credits Remaining: {$result['credits_remaining']}\n";
        }
        echo "\n🎉 SMS sent successfully! Check your phone.\n";
    } else {
        echo "❌ FAILED!\n";
        echo "Message: {$result['message']}\n";
        if (isset($result['http_code'])) {
            echo "HTTP Code: {$result['http_code']}\n";
        }
        if (isset($result['error_code'])) {
            echo "Error Code: {$result['error_code']}\n";
        }
        if (isset($result['response'])) {
            echo "Full Response:\n";
            print_r($result['response']);
        }
        
        echo "\n📌 Common Issues:\n";
        echo "- HTTP 400: Bad request (check phone format, API key, or sender name)\n";
        echo "- HTTP 401: Unauthorized (check API key is correct)\n";
        echo "- HTTP 402: Payment required (no SMS credits remaining)\n";
        echo "- HTTP 403: Forbidden (check sender name is approved by Brevo)\n";
        echo "\n💡 Check your error logs for more details\n";
    }
    
    echo "\n=== Debug Info ===\n";
    echo "API Endpoint: https://api.brevo.com/v3/transactionalSMS/send\n";
    echo "Payload sent:\n";
    echo "- sender: {$config['sms_sender_name']}\n";
    echo "- recipient: {$testMember['phone']}\n";
    echo "- type: transactional\n";
    echo "- unicodeEnabled: true\n";
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}

echo "</pre>";

echo "<hr>";
echo "<p><a href='admin/notifications.php'>← Back to Notifications</a></p>";
?>

