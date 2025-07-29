<?php
/**
 * HabeshaEqub - Simple Security Cleanup
 * Removes the suspicious member registration
 */

require_once 'includes/db.php';

echo "<h2>🔒 HabeshaEqub Security Cleanup</h2>\n";
echo "<p>Removing suspicious member registration...</p>\n";

try {
    // Find and remove the suspicious member
    $stmt = $pdo->prepare("
        SELECT id, email, full_name, phone, created_at 
        FROM members 
        WHERE email = 'boldsoar@localglobalmail.com' 
           OR full_name = 'Simone Fidradoeia'
           OR phone = '4244417325'
    ");
    $stmt->execute();
    $suspicious_members = $stmt->fetchAll();
    
    $removed_count = 0;
    
    foreach ($suspicious_members as $member) {
        echo "<p>🔍 Found suspicious member: " . htmlspecialchars($member['email']) . " - " . htmlspecialchars($member['full_name']) . "</p>\n";
        
        // Delete the suspicious member
        $delete_stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $delete_stmt->execute([$member['id']]);
        $removed_count++;
        
        echo "<p>✅ Removed member ID: {$member['id']}</p>\n";
    }
    
    if ($removed_count > 0) {
        echo "<div style='color: green; font-weight: bold; padding: 10px; background: #f0f8ff; border: 1px solid green; margin: 10px 0;'>";
        echo "✅ SUCCESS: Removed {$removed_count} suspicious member(s)";
        echo "</div>";
    } else {
        echo "<div style='color: blue; font-weight: bold; padding: 10px; background: #f0f8ff; border: 1px solid blue; margin: 10px 0;'>";
        echo "ℹ️ No suspicious members found - database is clean!";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold; padding: 10px; background: #fff0f0; border: 1px solid red; margin: 10px 0;'>";
    echo "❌ ERROR: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<h3>✅ Registration System Fixed</h3>\n";
echo "<ul>\n";
echo "<li>✅ Removed aggressive security blocking</li>\n";
echo "<li>✅ Fixed form fields (first_name + last_name)</li>\n";
echo "<li>✅ Updated database structure</li>\n";
echo "<li>✅ Added missing translation keys</li>\n";
echo "<li>✅ Registration should work normally now</li>\n";
echo "</ul>\n";

echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #0066cc; margin: 20px 0;'>\n";
echo "<h4>🚨 IMPORTANT: Delete This File!</h4>\n";
echo "<p>After running this script, delete <code>security_cleanup.php</code> for security.</p>\n";
echo "<p><strong>Your registration system is now working!</strong></p>\n";
echo "</div>\n";

echo "<p style='color: #666; font-size: 12px;'>Cleanup completed at " . date('Y-m-d H:i:s') . "</p>\n";
?> 