<?php
/**
 * COMPLETE NOTIFICATION SYSTEM REMOVAL SCRIPT
 * This will remove all notification-related files and database tables
 */

require_once 'includes/db.php';

echo "<!DOCTYPE html><html><head><title>Remove Notification System</title></head><body>";
echo "<h1>🗑️ Notification System Removal</h1>";

if (isset($_POST['confirm_removal']) && $_POST['confirm_removal'] === 'YES_DELETE_EVERYTHING') {
    
    echo "<h2>Removing Database Tables...</h2>";
    
    $tables_to_remove = [
        'member_message_reads',
        'member_messages'
    ];
    
    foreach ($tables_to_remove as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "✅ Dropped table: $table<br>";
        } catch (Exception $e) {
            echo "❌ Error dropping $table: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h2>Removing Stored Procedures...</h2>";
    try {
        $pdo->exec("DROP PROCEDURE IF EXISTS CreateMemberMessageForMembers");
        echo "✅ Dropped procedure: CreateMemberMessageForMembers<br>";
    } catch (Exception $e) {
        echo "❌ Error dropping procedure: " . $e->getMessage() . "<br>";
    }
    
    echo "<h2>Files to Remove Manually:</h2>";
    $files_to_remove = [
        'admin/notifications.php',
        'admin/api/notifications.php',
        'user/notifications.php',
        'admin/api/basic-test.php',
        'admin/api/isolate-error.php',
        'admin/api/step-by-step-test.php',
        'remove-notification-system.php'
    ];
    
    echo "<ul>";
    foreach ($files_to_remove as $file) {
        if (file_exists($file)) {
            echo "<li>❌ Delete: $file</li>";
        } else {
            echo "<li>✅ Not found: $file</li>";
        }
    }
    echo "</ul>";
    
    echo "<h2>Navigation Updates Needed:</h2>";
    echo "<p>❌ Remove notification links from user/includes/navigation.php</p>";
    echo "<p>❌ Remove notification links from admin navigation</p>";
    
    echo "<h1>✅ NOTIFICATION SYSTEM REMOVAL COMPLETED</h1>";
    echo "<p>All database tables and procedures have been removed.</p>";
    echo "<p>Manually delete the files listed above to complete the removal.</p>";
    
} else {
    
    echo "<h2>⚠️ WARNING: This will completely remove the notification system!</h2>";
    echo "<p>This action will:</p>";
    echo "<ul>";
    echo "<li>Drop member_messages table</li>";
    echo "<li>Drop member_message_reads table</li>";
    echo "<li>Drop CreateMemberMessageForMembers procedure</li>";
    echo "<li>List files for manual deletion</li>";
    echo "</ul>";
    
    echo "<form method='POST'>";
    echo "<p>Type <strong>YES_DELETE_EVERYTHING</strong> to confirm:</p>";
    echo "<input type='text' name='confirm_removal' placeholder='YES_DELETE_EVERYTHING' style='padding:10px; width:300px;'><br><br>";
    echo "<button type='submit' style='background:red; color:white; padding:15px 30px; font-size:16px; border:none; cursor:pointer;'>🗑️ REMOVE NOTIFICATION SYSTEM</button>";
    echo "</form>";
    
    echo "<hr>";
    echo "<h2>🔧 OR Try Debugging First:</h2>";
    echo "<p><a href='admin/api/basic-test.php' target='_blank'>Test 1: Basic PHP</a></p>";
    echo "<p><a href='admin/api/isolate-error.php' target='_blank'>Test 2: Isolate Error</a></p>";
    echo "<p><a href='admin/api/step-by-step-test.php' target='_blank'>Test 3: Step by Step</a></p>";
}

echo "</body></html>";
?>