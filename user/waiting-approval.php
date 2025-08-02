<?php
/**
 * HabeshaEqub - Modern Waiting for Approval Page
 * Beautiful golden-themed design showing user their status while waiting for admin approval
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

// Load user's language preference if they have an account
if ($user_email) {
    try {
        $lang_stmt = $pdo->prepare("SELECT language_preference FROM members WHERE email = ?");
        $lang_stmt->execute([$user_email]);
        $lang_result = $lang_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lang_result) {
            $user_language = ($lang_result['language_preference'] == 1) ? 'am' : 'en';
            setLanguage($user_language);
        }
    } catch (Exception $e) {
        error_log("Error loading user language preference: " . $e->getMessage());
    }
}

// If still no email, redirect to registration
if (!$user_email) {
    header('Location: login.php?msg=' . urlencode('Please register first'));
    exit;
}

// Check user status in database
try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.member_id, m.first_name, m.last_name, m.email, m.phone, 
               m.is_approved, m.is_active, m.email_verified, m.created_at,
               es.equb_name, es.duration_months, es.max_members, es.current_members
        FROM members m
        LEFT JOIN equb_settings es ON m.equb_settings_id = es.id
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
    
    // If email is not verified, redirect to verification
    if ($user['email_verified'] == 0) {
        header('Location: verify-email.php?email=' . urlencode($user_email));
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
$waiting_days = $waiting_time->days;
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
        /* Beautiful Golden Color Palette */
        :root {
            --cream: #F1ECE2;
            --dark-purple: #4D4052;
            --darker-purple: #301934;
            --gold: #DAA520;
            --light-gold: #CDAF56;
            --white: #FFFFFF;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
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
                radial-gradient(circle at 20% 20%, rgba(218, 165, 32, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(205, 175, 86, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(77, 64, 82, 0.05) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }
        
        /* Top Navigation */
        .top-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-link {
            color: var(--darker-purple);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--gold);
        }
        
        /* Main Container */
        .main-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            align-items: start;
        }
        
        /* Status Card */
        .status-card {
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            position: relative;
        }
        
        .status-header {
            background: linear-gradient(135deg, var(--gold) 0%, var(--light-gold) 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .status-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
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
        }
        
        .status-subtitle {
            font-size: 18px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .status-content {
            padding: 40px 30px;
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .user-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--darker-purple);
            margin-bottom: 8px;
        }
        
        .welcome-text {
            font-size: 18px;
            color: var(--dark-purple);
            line-height: 1.6;
        }
        
        /* Progress Timeline */
        .progress-timeline {
            margin: 40px 0;
        }
        
        .timeline-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 24px;
            text-align: center;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--success) 0%, var(--warning) 50%, #e0e0e0 100%);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 40px;
        }
        
        .timeline-marker {
            position: absolute;
            left: -10px;
            top: 5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: white;
        }
        
        .timeline-marker.completed {
            background: var(--success);
        }
        
        .timeline-marker.current {
            background: var(--warning);
            animation: glow 2s ease-in-out infinite;
        }
        
        .timeline-marker.pending {
            background: #e0e0e0;
            color: var(--dark-purple);
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(255, 193, 7, 0); }
        }
        
        .timeline-content h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 4px;
        }
        
        .timeline-content p {
            font-size: 14px;
            color: var(--dark-purple);
            opacity: 0.8;
            line-height: 1.4;
        }
        
        /* Info Cards */
        .info-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .info-card {
            background: var(--white);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(48, 25, 52, 0.08);
            border-left: 4px solid var(--gold);
        }
        
        .info-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card p {
            font-size: 14px;
            color: var(--dark-purple);
            line-height: 1.6;
            margin-bottom: 12px;
        }
        
        .info-card ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .info-card li {
            font-size: 14px;
            color: var(--dark-purple);
            margin-bottom: 8px;
            line-height: 1.4;
        }
        
        /* Registration Details */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 20px;
        }
        
        .detail-item {
            background: var(--cream);
            padding: 16px;
            border-radius: 8px;
            text-align: center;
        }
        
        .detail-label {
            font-size: 12px;
            color: var(--dark-purple);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--darker-purple);
        }
        
        /* Contact Section */
        .contact-section {
            background: linear-gradient(135deg, var(--darker-purple) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 24px;
            border-radius: 16px;
            text-align: center;
        }
        
        .contact-section h3 {
            color: var(--light-gold);
            margin-bottom: 16px;
        }
        
        .contact-section p {
            opacity: 0.9;
            margin-bottom: 16px;
        }
        
        .contact-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .contact-link {
            color: var(--light-gold);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .contact-link:hover {
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 20px 15px;
            }
            
            .status-header {
                padding: 30px 20px;
            }
            
            .status-content {
                padding: 30px 20px;
            }
            
            .status-title {
                font-size: 24px;
            }
            
            .user-name {
                font-size: 24px;
            }
            
            .welcome-text {
                font-size: 16px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-container {
                padding: 0 15px;
            }
            
            .logo {
                font-size: 20px;
            }
        }
        
        /* Declined Status */
        .declined-status .status-header {
            background: linear-gradient(135deg, var(--error) 0%, #c82333 100%);
        }
        
        .declined-message {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: var(--error);
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <div class="logo">🏛️ HabeshaEqub</div>
            <div class="nav-links">
                <a href="mailto:support@habeshaequb.com" class="nav-link">📧 Support</a>
                <a href="login.php" class="nav-link">🔐 Login</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Main Status Card -->
        <div class="status-card <?= $is_declined ? 'declined-status' : '' ?>">
            <div class="status-header">
                <div class="status-icon">
                    <?= $is_declined ? '❌' : '⏳' ?>
                </div>
                <h1 class="status-title">
                    <?= $is_declined ? 'Registration Declined' : 'Under Review' ?>
                </h1>
                <p class="status-subtitle">
                    <?= $is_declined ? 'Your application was not approved' : 'Your registration is being reviewed by our admin team' ?>
                </p>
            </div>
            
            <div class="status-content">
                <div class="welcome-message">
                    <h2 class="user-name">Dear <?= htmlspecialchars($user['first_name']) ?>,</h2>
                    <p class="welcome-text">
                        <?php if ($is_declined): ?>
                            Unfortunately, your registration for HabeshaEqub could not be approved at this time. Please contact our support team for more information.
                        <?php else: ?>
                            Thank you for registering with HabeshaEqub! We have received your registration and before you can login and access the dashboard, our equb admin needs to review your details and approve your registration. Once your registration is approved, we will send you an email notification. Keep your eyes on your inbox!
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if (!$is_declined): ?>
                <div class="progress-timeline">
                    <h3 class="timeline-title">📋 Registration Progress</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker completed">✓</div>
                            <div class="timeline-content">
                                <h4>Registration Submitted</h4>
                                <p>Your details have been successfully submitted</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker completed">✓</div>
                            <div class="timeline-content">
                                <h4>Email Verified</h4>
                                <p>Your email address has been confirmed</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker current">⏳</div>
                            <div class="timeline-content">
                                <h4>Admin Review (In Progress)</h4>
                                <p>Our administrator is reviewing your registration details and guarantor information</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker pending">📧</div>
                            <div class="timeline-content">
                                <h4>Approval Notification</h4>
                                <p>You'll receive an email when your registration is approved</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker pending">🚀</div>
                            <div class="timeline-content">
                                <h4>Access Dashboard</h4>
                                <p>Login and start managing your equb contributions</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Registration Date</div>
                        <div class="detail-value"><?= $registration_date->format('M j, Y') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Waiting Time</div>
                        <div class="detail-value">
                            <?= $waiting_days > 0 ? $waiting_days . ' day' . ($waiting_days > 1 ? 's' : '') : $waiting_hours . ' hour' . ($waiting_hours != 1 ? 's' : '') ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Member ID</div>
                        <div class="detail-value"><?= htmlspecialchars($user['member_id']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email Status</div>
                        <div class="detail-value">✅ Verified</div>
                    </div>
                </div>
                
                <?php if ($is_declined): ?>
                <div class="declined-message">
                    <h4>📞 Contact Support</h4>
                    <p>If you believe this is an error or would like to discuss your application, please contact our support team for assistance.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Info Sidebar -->
        <div class="info-section">
            <?php if (!$is_declined): ?>
            <div class="info-card">
                <h3>💡 While You Wait</h3>
                <p>The approval process typically takes 24-48 hours. Here's what happens next:</p>
                <ul>
                    <li>Our admin reviews your registration details</li>
                    <li>Your guarantor information is verified</li>
                    <li>You'll receive an email notification once approved</li>
                    <li>Check your spam/junk folder regularly</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h3>📊 Your Registration Details</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($user_name) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                <?php if ($user['equb_name']): ?>
                <p><strong>Equb:</strong> <?= htmlspecialchars($user['equb_name']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="info-card">
                <h3>🔐 Passwordless Login</h3>
                <p>Once approved, you'll use our secure passwordless login system:</p>
                <ul>
                    <li>Enter your email address</li>
                    <li>Receive a verification code</li>
                    <li>Access your dashboard securely</li>
                    <li>No passwords to remember!</li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="contact-section">
                <h3>📞 Need Help?</h3>
                <p>If you have any questions about your registration or the approval process, don't hesitate to contact us:</p>
                <div class="contact-links">
                    <a href="mailto:support@habeshaequb.com">📧 Email Support</a>
                    <a href="tel:+447360436171">📱 Call Us</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh page every 5 minutes to check for approval status changes
        setInterval(() => {
            fetch('api/check-approval-status.php?email=<?= urlencode($user_email) ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.approved) {
                        // Show success message and redirect
                        alert('🎉 Great news! Your registration has been approved. Redirecting to login...');
                        window.location.href = 'login.php?msg=' + encodeURIComponent('Your account has been approved! Please log in.');
                    }
                })
                .catch(error => {
                    console.log('Status check failed:', error);
                });
        }, 300000); // 5 minutes
        
        // Add some subtle animations
        document.addEventListener('DOMContentLoaded', () => {
            // Animate timeline items
            const timelineItems = document.querySelectorAll('.timeline-item');
            timelineItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 200);
            });
            
            // Initialize timeline items as hidden for animation
            timelineItems.forEach(item => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'all 0.5s ease';
            });
        });
    </script>
</body>
</html>