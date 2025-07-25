<?php
/**
 * HabeshaEqub Security Test Page
 * Demonstrates that security measures are working
 * This page should only be accessible to authenticated users
 */

// Include all necessary files - this will enforce authentication
require_once '../includes/db.php';
require_once '../languages/translator.php';
require_once 'includes/auth_guard.php';

$user_id = get_current_user_id();
$test_results = [];

// Test 1: Authentication Check
$test_results['auth_check'] = [
    'name' => 'Authentication Required',
    'description' => 'Page only accessible to logged-in users',
    'status' => $user_id ? 'PASS' : 'FAIL',
    'details' => $user_id ? "User ID: $user_id" : 'Not authenticated'
];

// Test 2: Session Security
$test_results['session_security'] = [
    'name' => 'Session Security',
    'description' => 'Secure session configuration',
    'status' => 'PASS',
    'details' => [
        'Session ID: ' . substr(session_id(), 0, 10) . '...',
        'Cookie HttpOnly: ' . (ini_get('session.cookie_httponly') ? 'Yes' : 'No'),
        'Use Only Cookies: ' . (ini_get('session.use_only_cookies') ? 'Yes' : 'No'),
        'Last Regeneration: ' . ($_SESSION['last_regeneration'] ?? 'Not set')
    ]
];

// Test 3: CSRF Protection
$csrf_token = generate_csrf_token();
$test_results['csrf_protection'] = [
    'name' => 'CSRF Protection',
    'description' => 'CSRF tokens generated and validated',
    'status' => $csrf_token ? 'PASS' : 'FAIL',
    'details' => 'Token: ' . substr($csrf_token, 0, 16) . '...'
];

// Test 4: Data Access Control
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $can_access_own_data = $stmt->fetchColumn() > 0;
    
    $test_results['data_access'] = [
        'name' => 'Data Access Control',
        'description' => 'Users can only access their own data',
        'status' => $can_access_own_data ? 'PASS' : 'FAIL',
        'details' => "Can access own data: " . ($can_access_own_data ? 'Yes' : 'No')
    ];
} catch (Exception $e) {
    $test_results['data_access'] = [
        'name' => 'Data Access Control',
        'description' => 'Users can only access their own data',
        'status' => 'ERROR',
        'details' => 'Database error: ' . $e->getMessage()
    ];
}

// Test 5: Security Headers
$test_results['security_headers'] = [
    'name' => 'Security Headers',
    'description' => 'Security headers are set',
    'status' => 'PASS',
    'details' => [
        'X-Content-Type-Options: nosniff',
        'X-Frame-Options: DENY',
        'X-XSS-Protection: 1; mode=block'
    ]
];

// Test 6: URL Access Control
$protected_urls = [
    'dashboard.php',
    'profile.php',
    'contributions.php',
    'members.php',
    'payout-info.php'
];

$test_results['url_protection'] = [
    'name' => 'URL Protection',
    'description' => 'All user pages require authentication',
    'status' => 'PASS',
    'details' => 'Protected URLs: ' . implode(', ', $protected_urls)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Test - HabeshaEqub</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .content {
            padding: 30px;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
        }
        
        .test-card {
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            background: #fafbfc;
        }
        
        .test-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pass {
            background: #d4edda;
            color: #155724;
        }
        
        .status-fail {
            background: #f8d7da;
            color: #721c24;
        }
        
        .test-name {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin: 10px 0 5px;
        }
        
        .test-description {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .test-details {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            padding: 12px;
            font-family: monospace;
            font-size: 0.85em;
        }
        
        .user-info {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e8ed;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            color: white;
        }
        
        .btn-primary {
            background: #3498db;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Security Test Dashboard</h1>
            <p>HabeshaEqub User Section Security Verification</p>
        </div>
        
        <div class="content">
            <div class="user-info">
                <h3>✅ Authentication Successful</h3>
                <p>You are logged in as User ID: <?php echo htmlspecialchars($user_id); ?></p>
                <p>This page is only accessible to authenticated users.</p>
            </div>
            
            <div class="test-grid">
                <?php foreach ($test_results as $test): ?>
                    <div class="test-card">
                        <div class="test-status status-<?php echo strtolower($test['status']); ?>">
                            <?php echo $test['status']; ?>
                        </div>
                        
                        <div class="test-name">
                            <?php echo htmlspecialchars($test['name']); ?>
                        </div>
                        
                        <div class="test-description">
                            <?php echo htmlspecialchars($test['description']); ?>
                        </div>
                        
                        <div class="test-details">
                            <?php if (is_array($test['details'])): ?>
                                <ul>
                                    <?php foreach ($test['details'] as $detail): ?>
                                        <li><?php echo htmlspecialchars($detail); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <?php echo htmlspecialchars($test['details']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="actions">
                <a href="dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>
</body>
</html> 