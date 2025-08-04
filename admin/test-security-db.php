<?php
/**
 * EMERGENCY DATABASE TEST for Security Settings
 * This script tests database connectivity and data availability
 */

require_once '../includes/db.php';
require_once 'includes/admin_auth_guard.php';

echo "<h1>🔒 Security Database Test</h1>";
echo "<p>Testing database connectivity and data availability...</p>";

try {
    // Test 1: Basic database connection
    echo "<h3>✅ Test 1: Database Connection</h3>";
    $test = $pdo->query("SELECT 1 as test")->fetch();
    echo "Database connection: <strong style='color: green;'>WORKING ✓</strong><br>";
    
    // Test 2: Members table
    echo "<h3>📊 Test 2: Members Table</h3>";
    $member_count = $pdo->query("SELECT COUNT(*) as count FROM members")->fetch()['count'];
    echo "Total members in database: <strong>{$member_count}</strong><br>";
    
    $members_with_login = $pdo->query("SELECT COUNT(*) as count FROM members WHERE last_login IS NOT NULL")->fetch()['count'];
    echo "Members with login history: <strong>{$members_with_login}</strong><br>";
    
    $members_never_logged = $pdo->query("SELECT COUNT(*) as count FROM members WHERE last_login IS NULL")->fetch()['count'];
    echo "Members never logged in: <strong>{$members_never_logged}</strong><br>";
    
    // Test 3: Sample member data
    echo "<h3>👥 Test 3: Sample Member Data</h3>";
    $sample_members = $pdo->query("
        SELECT member_id, first_name, last_name, email, last_login, is_active, is_approved
        FROM members 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    if (!empty($sample_members)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Member ID</th><th>Name</th><th>Email</th><th>Last Login</th><th>Status</th></tr>";
        foreach ($sample_members as $member) {
            $status = ($member['is_active'] && $member['is_approved']) ? 'Active' : 'Inactive';
            $last_login = $member['last_login'] ? date('Y-m-d H:i:s', strtotime($member['last_login'])) : 'Never';
            echo "<tr>";
            echo "<td>{$member['member_id']}</td>";
            echo "<td>{$member['first_name']} {$member['last_name']}</td>";
            echo "<td>{$member['email']}</td>";
            echo "<td>{$last_login}</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No member data found!</p>";
    }
    
    // Test 4: OTP table
    echo "<h3>🔐 Test 4: OTP Activities</h3>";
    $otp_count = $pdo->query("SELECT COUNT(*) as count FROM user_otps")->fetch()['count'];
    echo "Total OTP records: <strong>{$otp_count}</strong><br>";
    
    $recent_otps = $pdo->query("SELECT COUNT(*) as count FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOURS)")->fetch()['count'];
    echo "OTP requests (24h): <strong>{$recent_otps}</strong><br>";
    
    // Test 5: Device tracking
    echo "<h3>📱 Test 5: Device Tracking</h3>";
    $device_count = $pdo->query("SELECT COUNT(*) as count FROM device_tracking")->fetch()['count'];
    echo "Total device records: <strong>{$device_count}</strong><br>";
    
    // Test 6: Security statistics query
    echo "<h3>📈 Test 6: Security Statistics Query</h3>";
    $stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM members WHERE last_login >= DATE_SUB(NOW(), INTERVAL 1 DAY)) as active_24h,
            (SELECT COUNT(*) FROM members WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAYS)) as active_7d,
            (SELECT COUNT(*) FROM members WHERE last_login IS NULL) as never_logged_in,
            (SELECT COUNT(*) FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)) as otp_requests_24h,
            (SELECT COUNT(*) FROM user_otps WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND is_used = 1) as successful_logins_24h,
            (SELECT COUNT(*) FROM members) as total_members
    ")->fetch();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Metric</th><th>Value</th></tr>";
    echo "<tr><td>Active Members (24h)</td><td><strong>{$stats['active_24h']}</strong></td></tr>";
    echo "<tr><td>Active Members (7d)</td><td><strong>{$stats['active_7d']}</strong></td></tr>";
    echo "<tr><td>Never Logged In</td><td><strong>{$stats['never_logged_in']}</strong></td></tr>";
    echo "<tr><td>OTP Requests (24h)</td><td><strong>{$stats['otp_requests_24h']}</strong></td></tr>";
    echo "<tr><td>Successful Logins (24h)</td><td><strong>{$stats['successful_logins_24h']}</strong></td></tr>";
    echo "<tr><td>Total Members</td><td><strong>{$stats['total_members']}</strong></td></tr>";
    echo "</table>";
    
    echo "<h3 style='color: green;'>🎉 ALL TESTS PASSED!</h3>";
    echo "<p>The database is working correctly. If Security Settings page is empty, there might be a different issue.</p>";
    
    echo "<hr>";
    echo "<h3>🔧 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Check browser console for JavaScript errors</li>";
    echo "<li>Verify admin authentication is working</li>";
    echo "<li>Check error logs in cPanel or server logs</li>";
    echo "<li>Clear browser cache and refresh Security Settings page</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ DATABASE ERROR FOUND!</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<hr>";
    echo "<h3>🔧 How to Fix:</h3>";
    echo "<ol>";
    echo "<li>Check database connection settings in includes/db.php</li>";
    echo "<li>Verify database tables exist and have correct structure</li>";
    echo "<li>Check database user permissions</li>";
    echo "<li>Contact hosting provider if issue persists</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='security-settings.php'>← Back to Security Settings</a></p>";
echo "<p><small>Generated at: " . date('Y-m-d H:i:s') . "</small></p>";
?>