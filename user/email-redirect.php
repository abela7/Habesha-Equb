<?php
/**
 * Email Redirect Handler
 * Forces links from emails to open in browser instead of email client
 */

// Get the target URL from parameter
$target = $_GET['to'] ?? '';

// Validate the target URL to prevent open redirect attacks
$allowed_domains = [
    $_SERVER['HTTP_HOST'],
    'localhost',
    '127.0.0.1'
];

// Parse the target URL
$parsed = parse_url($target);
$is_safe = false;

if ($parsed && isset($parsed['host'])) {
    foreach ($allowed_domains as $domain) {
        if ($parsed['host'] === $domain || str_ends_with($parsed['host'], '.' . $domain)) {
            $is_safe = true;
            break;
        }
    }
} elseif (str_starts_with($target, '/')) {
    // Relative URL is safe
    $is_safe = true;
    $target = 'https://' . $_SERVER['HTTP_HOST'] . $target;
}

// If target is not safe, redirect to login page
if (!$is_safe || empty($target)) {
    $target = 'https://' . $_SERVER['HTTP_HOST'] . '/user/login.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Dashboard - HabeshaEqub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #F1ECE2 0%, #E8DCC6 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .redirect-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(48, 25, 52, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .logo {
            color: #301934;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #F1ECE2;
            border-top: 3px solid #DAA520;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            color: #4D4052;
            margin: 20px 0;
            font-size: 16px;
        }
        .manual-link {
            background: linear-gradient(135deg, #DAA520 0%, #CDAF56 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 15px;
            font-weight: 600;
        }
        .manual-link:hover {
            opacity: 0.9;
        }
    </style>
    <script>
        // Multiple redirect methods to ensure it works across all email clients
        setTimeout(function() {
            // Method 1: Standard redirect
            window.location.href = '<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>';
        }, 2000);
        
        // Method 2: Try to open in new window (fallback)
        setTimeout(function() {
            try {
                window.open('<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>', '_blank');
            } catch(e) {
                console.log('New window blocked, using standard redirect');
            }
        }, 500);
        
        // Method 3: Immediate redirect (for compatible clients)
        window.addEventListener('load', function() {
            setTimeout(function() {
                location.replace('<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>');
            }, 3000);
        });
    </script>
</head>
<body>
    <div class="redirect-container">
        <div class="logo">🏛️ HabeshaEqub</div>
        <div class="spinner"></div>
        <div class="message">
            <strong>Redirecting to your dashboard...</strong><br>
            This should open in your browser automatically.
        </div>
        <a href="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>" class="manual-link">
            Click here if not redirected
        </a>
    </div>
</body>
</html>