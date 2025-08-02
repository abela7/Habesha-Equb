<?php
/**
 * HabeshaEqub - Clean Waiting for Approval Page
 * Simple, elegant design matching login.php styling
 */

// Skip auth check since this is for users waiting for approval
define('SKIP_AUTH_CHECK', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../languages/translator.php';

// Start session to check if user just registered
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user has a pending registration
$user_email = isset($_SESSION['pending_email']) ? $_SESSION['pending_email'] : null;
$user_name = isset($_SESSION['pending_name']) ? $_SESSION['pending_name'] : null;

// If no pending registration in session, check if we have email in URL (from registration redirect)
if (!$user_email && isset($_GET['email'])) {
    $user_email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
}

// Set language from user preference or default
if (!isset($_SESSION['app_language'])) {
    setLanguage('en');
}

// If still no email, redirect to registration
if (!$user_email) {
    header('Location: login.php?msg=' . urlencode('Please register first'));
    exit;
}

// Check user status in database
try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.member_id, m.first_name, m.last_name, m.email, 
               m.is_approved, m.is_active, m.email_verified, m.created_at
        FROM members m
        WHERE m.email = ?
    ");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: login.php?msg=' . urlencode('Registration not found. Please register again.'));
        exit;
    }
    
    // If user is already approved, redirect to login
    if ($user['is_approved'] == 1) {
        unset($_SESSION['pending_email'], $_SESSION['pending_name']);
        header('Location: login.php?msg=' . urlencode('Your account has been approved! Please log in.'));
        exit;
    }
    
    // If user is declined (inactive), show message
    $is_declined = ($user['is_active'] == 0);
    
} catch (Exception $e) {
    error_log("Waiting approval page error: " . $e->getMessage());
    header('Location: login.php?msg=' . urlencode('An error occurred. Please try again.'));
    exit;
}

$user_name = $user['first_name'] . ' ' . $user['last_name'];
$registration_date = new DateTime($user['created_at']);
$waiting_time = (new DateTime())->diff($registration_date);
$waiting_hours = $waiting_time->days * 24 + $waiting_time->h;
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Under Review - HabeshaEqub</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png">
    
    <style>
        /* Beautiful Color Palette - Same as login.php */
        :root {
            --cream: #F1ECE2;
            --dark-purple: #4D4052;
            --darker-purple: #301934;
            --gold: #DAA520;
            --light-gold: #CDAF56;
            --brown: #5D4225;
            --white: #FFFFFF;
            --success: #28a745;
            --error: #dc3545;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-lg: 0 25px 50px -12px rgba(48, 25, 52, 0.25);
            --shadow-xl: 0 35px 60px -15px rgba(48, 25, 52, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--darker-purple) 0%, var(--dark-purple) 40%, rgba(241, 236, 226, 0.3) 70%, var(--cream) 100%);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Animated Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(218, 165, 32, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(77, 64, 82, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(241, 236, 226, 0.2) 0%, transparent 50%);
            filter: blur(80px);
            animation: float 25s ease-in-out infinite;
            z-index: -1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-15px) rotate(120deg); }
            66% { transform: translateY(-10px) rotate(240deg); }
        }
        
        /* Main Container */
        .waiting-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        
        .waiting-header {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            padding: 50px 40px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .waiting-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle, rgba(255, 255, 255, 0.2) 1px, transparent 1px),
                radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px, 60px 60px;
            background-position: 0 0, 15px 15px;
            opacity: 0.6;
            animation: patternMove 20s linear infinite;
        }
        
        @keyframes patternMove {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(30px) translateY(30px); }
        }
        
        .status-icon {
            font-size: 64px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .status-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-subtitle {
            font-size: 18px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .waiting-content {
            padding: 50px 40px;
            text-align: center;
        }
        
        .user-greeting {
            font-size: 28px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 24px;
        }
        
        .waiting-message {
            font-size: 18px;
            color: var(--dark-purple);
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .status-details {
            background: linear-gradient(135deg, var(--cream) 0%, rgba(255, 255, 255, 0.8) 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border-left: 4px solid var(--gold);
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(77, 64, 82, 0.1);
        }
        
        .detail-label {
            font-size: 12px;
            color: var(--dark-purple);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .detail-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--darker-purple);
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--dark-purple) 0%, var(--darker-purple) 100%);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(77, 64, 82, 0.3);
            margin-top: 30px;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(77, 64, 82, 0.4);
            text-decoration: none;
            color: white;
        }
        
        .back-button:active {
            transform: translateY(0);
        }
        
        /* Declined Status */
        .declined .waiting-header {
            background: linear-gradient(135deg, var(--error) 0%, #c82333 100%);
        }
        
        .declined-message {
            background: rgba(220, 53, 69, 0.1);
            border: 2px solid rgba(220, 53, 69, 0.2);
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            color: var(--error);
            font-weight: 500;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .waiting-header {
                padding: 40px 30px;
            }
            
            .waiting-content {
                padding: 40px 30px;
            }
            
            .status-title {
                font-size: 24px;
            }
            
            .user-greeting {
                font-size: 24px;
            }
            
            .waiting-message {
                font-size: 16px;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .detail-item {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="waiting-container <?= $is_declined ? 'declined' : '' ?>">
        <div class="waiting-header">
            <div class="status-icon">
                <?= $is_declined ? '❌' : '⏳' ?>
            </div>
            <h1 class="status-title">
                <?= $is_declined ? 'Registration Declined' : 'Under Review' ?>
            </h1>
            <p class="status-subtitle">
                <?= $is_declined ? 'Your application was not approved' : 'Your registration is being reviewed by our admin' ?>
            </p>
        </div>
        
        <div class="waiting-content">
            <h2 class="user-greeting">Hello <?= htmlspecialchars($user['first_name']) ?>! 👋</h2>
            
            <p class="waiting-message">
                <?php if ($is_declined): ?>
                    Unfortunately, your registration for HabeshaEqub could not be approved at this time. Please contact our support team for more information.
                <?php else: ?>
                    Thank you for joining HabeshaEqub! Your registration has been submitted and is currently under review. You'll receive an email notification once your account is approved.
                <?php endif; ?>
            </p>
            
            <div class="status-details">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Member ID</div>
                        <div class="detail-value"><?= htmlspecialchars($user['member_id']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Registration Date</div>
                        <div class="detail-value"><?= $registration_date->format('M j, Y') ?></div>
                    </div>
                </div>
                
                <?php if (!$is_declined): ?>
                <p style="text-align: center; color: var(--dark-purple); font-size: 14px; margin: 0;">
                    <i class="fas fa-clock"></i> Typical approval time: 24-48 hours
                </p>
                <?php endif; ?>
            </div>
            
            <?php if ($is_declined): ?>
            <div class="declined-message">
                <strong>📞 Contact Support:</strong><br>
                If you believe this is an error, please contact our support team at support@habeshaequb.com
            </div>
            <?php endif; ?>
            
            <a href="login.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
        </div>
    </div>

    <script>
        // Auto-refresh page every 5 minutes to check for approval status changes
        <?php if (!$is_declined): ?>
        setInterval(() => {
            fetch('api/check-approval-status.php?email=<?= urlencode($user_email) ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.approved) {
                        window.location.href = 'login.php?msg=' + encodeURIComponent('Your account has been approved! Please log in.');
                    }
                })
                .catch(error => {
                    console.log('Status check failed:', error);
                });
        }, 300000); // 5 minutes
        <?php endif; ?>
    </script>
</body>
</html>