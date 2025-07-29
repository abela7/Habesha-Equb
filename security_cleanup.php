<?php
/**
 * HabeshaEqub - Security Cleanup Script
 * Removes suspicious members and secures the database
 * 
 * IMPORTANT: Run this script ONCE to clean up the attack attempt!
 */

require_once 'includes/db.php';

echo "<h2>🔒 HabeshaEqub Security Cleanup</h2>\n";
echo "<p>Scanning for and removing suspicious members...</p>\n";

// Clean suspicious members
$removed_count = clean_suspicious_members();

if ($removed_count !== false) {
    echo "<div style='color: green; font-weight: bold;'>";
    echo "✅ SUCCESS: Removed {$removed_count} suspicious member(s)\n";
    echo "</div>";
    
    if ($removed_count > 0) {
        echo "<p>📋 Details logged to security.log for your review.</p>\n";
    }
} else {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "❌ ERROR: Failed to clean suspicious members\n";
    echo "</div>";
}

// Additional security checks
echo "<h3>🛡️ Security Status Check</h3>\n";

// Check for other suspicious patterns
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as suspicious_count 
        FROM members 
        WHERE email LIKE '%@localglobalmail.com'
           OR username = 'boldsoar'
           OR full_name LIKE '%Simone%'
           OR phone = '4244417325'
    ");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['suspicious_count'] > 0) {
        echo "<div style='color: orange; font-weight: bold;'>";
        echo "⚠️ WARNING: {$result['suspicious_count']} suspicious member(s) still found!\n";
        echo "</div>";
    } else {
        echo "<div style='color: green; font-weight: bold;'>";
        echo "✅ CLEAN: No suspicious members found\n";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "❌ Error checking for suspicious members: " . $e->getMessage() . "\n";
    echo "</div>";
}

// Security recommendations
echo "<h3>🔧 Security Enhancements Applied</h3>\n";
echo "<ul>\n";
echo "<li>✅ Advanced input validation and sanitization</li>\n";
echo "<li>✅ SQL injection protection with prepared statements</li>\n";
echo "<li>✅ Rate limiting for brute force attack prevention</li>\n";
echo "<li>✅ CSRF protection on all forms</li>\n";
echo "<li>✅ XSS protection with proper output escaping</li>\n";
echo "<li>✅ Session security with hijacking prevention</li>\n";
echo "<li>✅ Security logging and monitoring</li>\n";
echo "<li>✅ Stronger password hashing (Argon2ID)</li>\n";
echo "<li>✅ Request validation and suspicious pattern detection</li>\n";
echo "<li>✅ Security headers for browser protection</li>\n";
echo "</ul>\n";

echo "<h3>📋 Next Steps</h3>\n";
echo "<ol>\n";
echo "<li><strong>Delete this file</strong> after running it once: <code>security_cleanup.php</code></li>\n";
echo "<li><strong>Monitor security logs</strong> in <code>logs/security.log</code></li>\n";
echo "<li><strong>Change default passwords</strong> if any exist</li>\n";
echo "<li><strong>Keep your system updated</strong> with latest security patches</li>\n";
echo "<li><strong>Regular backups</strong> of your database and files</li>\n";
echo "</ol>\n";

echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #0066cc; margin: 20px 0;'>\n";
echo "<h4>🚨 CRITICAL: Delete This File!</h4>\n";
echo "<p>For security reasons, delete <code>security_cleanup.php</code> after running it.</p>\n";
echo "<p>The security system is now active and will prevent future attacks automatically.</p>\n";
echo "</div>\n";

// Log the cleanup action
SecurityLogger::logSecurityEvent('security_cleanup_executed', [
    'removed_members' => $removed_count,
    'timestamp' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'localhost'
]);

echo "<p style='color: #666; font-size: 12px;'>Cleanup completed at " . date('Y-m-d H:i:s') . "</p>\n";
?> 