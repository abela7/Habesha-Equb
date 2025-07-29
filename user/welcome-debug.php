<?php
/**
 * HabeshaEqub - Welcome Page DEBUG VERSION
 * This version will show exactly what's causing the 500 error
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to catch any errors
ob_start();

echo "<!-- Debug: Starting welcome page -->\n";

try {
    echo "<!-- Debug: Checking session -->\n";
    
    // Skip the normal auth check since this is part of the auth flow
    define('SKIP_AUTH_CHECK', true);
    
    echo "<!-- Debug: Including session config -->\n";
    if (file_exists(__DIR__ . '/includes/session_config.php')) {
        require_once __DIR__ . '/includes/session_config.php';
        echo "<!-- Debug: Session config loaded -->\n";
    } else {
        die("Session config file not found");
    }
    
    echo "<!-- Debug: Including database -->\n";
    if (file_exists(__DIR__ . '/../includes/db.php')) {
        require_once __DIR__ . '/../includes/db.php';
        echo "<!-- Debug: Database loaded -->\n";
    } else {
        die("Database file not found");
    }
    
    echo "<!-- Debug: Including translator -->\n";
    if (file_exists(__DIR__ . '/../languages/translator.php')) {
        require_once __DIR__ . '/../languages/translator.php';
        echo "<!-- Debug: Translator loaded -->\n";
    } else {
        die("Translator file not found");
    }
    
    echo "<!-- Debug: Checking session variables -->\n";
    
    // Check if user is logged in but hasn't completed welcome flow
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        die("Not logged in - redirecting");
    }
    
    $user_id = $_SESSION['user_id'];
    echo "<!-- Debug: User ID: $user_id -->\n";
    
    echo "<!-- Debug: Checking database connection -->\n";
    
    // Check database connection
    if (!isset($db) && !isset($pdo)) {
        die("No database connection available");
    }
    
    $db_conn = isset($db) ? $db : $pdo;
    echo "<!-- Debug: Database connection OK -->\n";
    
    echo "<!-- Debug: Querying user data -->\n";
    
    // First, let's check what columns exist in the members table
    try {
        $columns_stmt = $db_conn->query("DESCRIBE members");
        $columns = $columns_stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<!-- Debug: Members table columns: ";
        foreach ($columns as $col) {
            echo $col['Field'] . " ";
        }
        echo "-->\n";
    } catch (Exception $e) {
        echo "<!-- Debug: Error checking columns: " . $e->getMessage() . " -->\n";
    }
    
    // Try to get user data without rules_agreed column first
    try {
        $stmt = $db_conn->prepare("
            SELECT id, member_id, first_name, last_name, language_preference, is_approved 
            FROM members 
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            die("User not found in database");
        }
        
        echo "<!-- Debug: User found: " . $user['first_name'] . " " . $user['last_name'] . " -->\n";
        
        // Check if rules_agreed column exists
        $rules_agreed = 0; // Default value
        try {
            $rules_stmt = $db_conn->prepare("
                SELECT rules_agreed 
                FROM members 
                WHERE id = ?
            ");
            $rules_stmt->execute([$user_id]);
            $rules_result = $rules_stmt->fetch(PDO::FETCH_ASSOC);
            if ($rules_result) {
                $rules_agreed = $rules_result['rules_agreed'];
                echo "<!-- Debug: rules_agreed column exists, value: $rules_agreed -->\n";
            }
        } catch (Exception $e) {
            echo "<!-- Debug: rules_agreed column doesn't exist: " . $e->getMessage() . " -->\n";
        }
        
    } catch (Exception $e) {
        die("Database query error: " . $e->getMessage());
    }
    
    if (!$user['is_approved']) {
        die("User not approved - redirecting");
    }
    
    // If user has already agreed to rules, redirect to dashboard
    if ($rules_agreed == 1) {
        echo "Rules already agreed, should redirect to dashboard";
        // For debugging, don't redirect yet
        // header('Location: dashboard.php');
        // exit;
    }
    
    echo "<!-- Debug: Getting equb rules -->\n";
    
    // Get equb rules
    try {
        $rules_stmt = $db_conn->prepare("
            SELECT rule_number, rule_en, rule_am 
            FROM equb_rules 
            WHERE is_active = 1 
            ORDER BY rule_number ASC
        ");
        $rules_stmt->execute();
        $rules = $rules_stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<!-- Debug: Found " . count($rules) . " rules -->\n";
    } catch (Exception $e) {
        echo "<!-- Debug: Error getting rules: " . $e->getMessage() . " -->\n";
        $rules = [];
    }
    
    // Set initial language preference
    $current_lang = $user['language_preference'] == 1 ? 'am' : 'en';
    echo "<!-- Debug: Language: $current_lang -->\n";
    
    try {
        // Set language in session for translator
        $_SESSION['app_language'] = $current_lang;
        $translator = Translator::getInstance();
        echo "<!-- Debug: Translator created -->\n";
    } catch (Exception $e) {
        echo "<!-- Debug: Translator error: " . $e->getMessage() . " -->\n";
        die("Translator initialization failed");
    }
    
} catch (Exception $e) {
    die("Fatal error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}

// Clear the output buffer
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Debug - HabeshaEqub</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .debug-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff; }
        .success { border-left-color: #28a745; }
        .error { border-left-color: #dc3545; }
        .warning { border-left-color: #ffc107; }
        h1 { color: #333; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Welcome Page Debug Information</h1>
    
    <div class="debug-box success">
        <h3>✅ Success: Welcome page loaded successfully!</h3>
        <p>User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <p>User ID: <?php echo $user_id; ?></p>
        <p>Language: <?php echo $current_lang; ?></p>
        <p>Rules Agreed: <?php echo $rules_agreed ? 'Yes' : 'No'; ?></p>
        <p>Number of rules found: <?php echo count($rules); ?></p>
    </div>
    
    <?php if ($rules_agreed == 1): ?>
    <div class="debug-box warning">
        <h3>⚠️ Note: User has already agreed to rules</h3>
        <p>Normally this would redirect to dashboard.php</p>
    </div>
    <?php endif; ?>
    
    <?php if (!isset($rules_result)): ?>
    <div class="debug-box error">
        <h3>❌ Missing Column: rules_agreed</h3>
        <p>The rules_agreed column doesn't exist in the members table.</p>
        <p>Run this SQL in phpMyAdmin:</p>
        <pre>ALTER TABLE members ADD COLUMN rules_agreed tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Agreed to rules, 0=Not agreed' AFTER language_preference;</pre>
    </div>
    <?php endif; ?>
    
    <div class="debug-box">
        <h3>🔧 Next Steps:</h3>
        <ol>
            <li>If you see the "Missing Column" error above, run the SQL command in phpMyAdmin</li>
            <li>If everything looks good, the original welcome.php should work</li>
            <li>If you still get errors, check the member table structure</li>
        </ol>
        <p><a href="welcome.php">→ Try Original Welcome Page</a></p>
        <p><a href="login.php">← Back to Login</a></p>
    </div>
    
    <div class="debug-box">
        <h3>📋 Members Table Columns:</h3>
        <pre><?php
        if (isset($columns)) {
            foreach ($columns as $col) {
                echo $col['Field'] . " (" . $col['Type'] . ")\n";
            }
        }
        ?></pre>
    </div>
    
    <?php if (!empty($rules)): ?>
    <div class="debug-box">
        <h3>📜 Available Rules:</h3>
        <?php foreach ($rules as $rule): ?>
            <p><strong>Rule <?php echo $rule['rule_number']; ?>:</strong> <?php echo htmlspecialchars($rule['rule_en']); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html> 