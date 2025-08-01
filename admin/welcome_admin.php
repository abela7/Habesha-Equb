<?php
/**
 * HabeshaEqub - NEW ADMIN WELCOME DASHBOARD 
 * Fresh start with working translations
 */

require_once '../includes/db.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// SIMPLE LANGUAGE SETUP - NO COMPLEXITY
$lang = 'en'; // Default to English

// Check URL parameter first
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'am'])) {
    $lang = $_GET['lang'];
    
    // Update database preference
    try {
        $lang_pref = ($lang === 'am') ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE admins SET language_preference = ? WHERE id = ?");
        $stmt->execute([$lang_pref, $admin_id]);
    } catch (Exception $e) {
        error_log("Failed to update language preference: " . $e->getMessage());
    }
} else {
    // Load from database
    try {
        $stmt = $pdo->prepare("SELECT language_preference FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin_data = $stmt->fetch();
        
        if ($admin_data && $admin_data['language_preference'] == 1) {
            $lang = 'am'; // Amharic
        }
    } catch (Exception $e) {
        error_log("Failed to load language preference: " . $e->getMessage());
    }
}

// SIMPLE TRANSLATION FUNCTION - NO JSON FILES
function simple_t($key, $lang = 'en') {
    $translations = [
        'en' => [
            'welcome_back' => 'Welcome back, {username}!',
            'welcome_subtitle' => "Here's what's happening with your HabeshaEqub community today",
            'total_members' => 'Total Members',
            'total_collected' => 'Total Collected',
            'completed_payouts' => 'Completed Payouts',
            'pending_approvals' => 'Pending Approvals',
            'management_center' => 'Management Center',
            'management_center_desc' => 'Access all administrative tools and features',
            'members_management' => 'Members Management',
            'members_management_desc' => 'Register new members, manage profiles, track status and handle member operations',
            'manage_members' => 'Manage Members',
            'payment_tracking' => 'Payment Tracking',
            'payment_tracking_desc' => 'Monitor monthly payments, verify payments and track financial records',
            'track_payments' => 'Track Payments',
            'payout_management' => 'Payout Management',
            'payout_management_desc' => 'Schedule and process member payouts, manage distribution cycles',
            'manage_payouts' => 'Manage Payouts',
            'reports_analytics' => 'Reports & Analytics',
            'reports_analytics_desc' => 'Generate financial reports, member analytics and system insights',
            'view_reports' => 'View Reports',
            'system_settings' => 'System Settings',
            'system_settings_desc' => 'Configure system preferences, rules and administrative settings',
            'configure_system' => 'Configure System'
        ],
        'am' => [
            'welcome_back' => '{username} እንኳን ደህና መጡ!',
            'welcome_subtitle' => 'አሁን ላይ በእቁቡ ዙሪያ እየሆነ ያለው ነገር ይሄ ነው',
            'total_members' => 'ጠቅላላ አባላት',
            'total_collected' => 'ጠቅላላ የተሰበሰበ',
            'completed_payouts' => 'የተጠናቀቁ ክፍያዎች',
            'pending_approvals' => 'በመጠባበቅ ላይ ያሉ ማጽደቂያዎች',
            'management_center' => 'የአስተዳደር ማዕከል',
            'management_center_desc' => 'ሁሉንም የአስተዳደር መሳሪያዎችና ባህሪያት ይድረሱ',
            'members_management' => 'የአባላት አስተዳደር',
            'members_management_desc' => 'አዲስ አባላትን ያመዝግቡ፣ መገለጫዎችን ያስተዳድሩ፣ ሁኔታን ይከታተሉ እና የአባላት ስራዎችን ይስሩ',
            'manage_members' => 'አባላትን አስተዳድር',
            'payment_tracking' => 'የክፍያ ክትትል',
            'payment_tracking_desc' => 'ወርሃዊ አስተዋጾዎችን ይከታተሉ፣ ክፍያዎችን ያረጋግጡ እና የገንዘብ መዝገቦችን ይከታተሉ',
            'track_payments' => 'ክፍያዎችን ይከታተሉ',
            'payout_management' => 'የክፍያ አስተዳደር',
            'payout_management_desc' => 'የአባላት ክፍያዎችን ያቅዱ እና ያስኬዱ፣ የክፍፍል ዑደቶችን ያስተዳድሩ',
            'manage_payouts' => 'ክፍያዎችን አስተዳድር',
            'reports_analytics' => 'ሪፖርቶች እና ትንታኔዎች',
            'reports_analytics_desc' => 'የገንዘብ ሪፖርቶችን ያውጡ፣ የአባላት ትንታኔዎችን እና የስርዓት ግንዛቤዎችን ያውጡ',
            'view_reports' => 'ሪፖርቶችን ይመልከቱ',
            'system_settings' => 'የስርዓት ቅንጅቶች',
            'system_settings_desc' => 'የስርዓት ምርጫዎችን፣ ህጎችን እና የአስተዳደር ቅንጅቶችን ያስተካክሉ',
            'configure_system' => 'ስርዓቱን አቀናብር'
        ]
    ];
    
    return $translations[$lang][$key] ?? $key;
}

// Get dashboard statistics
try {
    // Members statistics
    $members_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_members,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_members,
            COUNT(CASE WHEN is_approved = 1 THEN 1 END) as approved_members,
            COUNT(CASE WHEN is_approved = 0 THEN 1 END) as pending_members
        FROM members
    ")->fetch();
    
    // Financial statistics
    $financial_stats = $pdo->query("
        SELECT 
            COALESCE(SUM(amount), 0) as total_collected,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_payments
        FROM payments
    ")->fetch();
    
    // Payout statistics
    $payout_stats = $pdo->query("
        SELECT 
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payouts,
            COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_payouts
        FROM payouts
    ")->fetch();
    
} catch (Exception $e) {
    // Default values in case of error
    $members_stats = ['total_members' => 0, 'active_members' => 0, 'pending_members' => 0];
    $financial_stats = ['total_collected' => 0, 'completed_payments' => 0, 'pending_payments' => 0];
    $payout_stats = ['completed_payouts' => 0, 'scheduled_payouts' => 0];
    error_log("Dashboard stats error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HabeshaEqub</title>
    <meta name="description" content="HabeshaEqub Admin Dashboard - Manage your equb community">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* === ENHANCED TOP-TIER ADMIN DASHBOARD DESIGN === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Enhanced Welcome Section */
        .enhanced-welcome {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
        }

        /* Quick Stats Grid */
        .quick-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .quick-stat-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .quick-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .quick-stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .quick-stat-label {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Management Modules Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .enhanced-module-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .enhanced-module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
            border-radius: 20px 20px 0 0;
        }

        .enhanced-module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .module-icon {
            font-size: 3rem;
            color: #fff;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .module-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
        }

        .module-description {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .module-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .module-stat {
            text-align: center;
            color: white;
        }

        .module-stat strong {
            display: block;
            font-size: 1.4rem;
            margin-bottom: 5px;
        }

        .module-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .module-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            color: white;
            text-decoration: none;
        }

        /* Language Switcher */
        .language-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .lang-btn {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }

        .lang-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }
            
            .enhanced-welcome {
                padding: 25px;
            }
            
            .welcome-title {
                font-size: 2rem;
            }
            
            .quick-stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="language-switcher">
        <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">
            <i class="fas fa-globe"></i> English
        </a>
        <a href="?lang=am" class="lang-btn <?php echo $lang === 'am' ? 'active' : ''; ?>">
            <i class="fas fa-globe"></i> አማርኛ
        </a>
    </div>

    <div class="dashboard-container">
        <!-- Enhanced Welcome Section -->
        <section class="enhanced-welcome">
            <div class="welcome-content">
                <h1 class="welcome-title">
                    <?php echo str_replace('{username}', htmlspecialchars($admin_username), simple_t('welcome_back', $lang)); ?>
                </h1>
                <p class="welcome-subtitle">
                    <?php echo simple_t('welcome_subtitle', $lang); ?>
                </p>
            </div>
            
            <!-- Quick Stats Grid -->
            <div class="quick-stats-grid">
                <div class="quick-stat-card">
                    <div class="quick-stat-value"><?php echo $members_stats['total_members']; ?></div>
                    <div class="quick-stat-label"><?php echo simple_t('total_members', $lang); ?></div>
                </div>
                <div class="quick-stat-card">
                    <div class="quick-stat-value">£<?php echo number_format($financial_stats['total_collected'], 0); ?></div>
                    <div class="quick-stat-label"><?php echo simple_t('total_collected', $lang); ?></div>
                </div>
                <div class="quick-stat-card">
                    <div class="quick-stat-value"><?php echo $payout_stats['completed_payouts']; ?></div>
                    <div class="quick-stat-label"><?php echo simple_t('completed_payouts', $lang); ?></div>
                </div>
                <div class="quick-stat-card">
                    <div class="quick-stat-value"><?php echo $members_stats['pending_members']; ?></div>
                    <div class="quick-stat-label"><?php echo simple_t('pending_approvals', $lang); ?></div>
                </div>
            </div>
        </section>

        <!-- Management Center -->
        <section class="management-section">
            <h2 style="color: white; font-size: 2rem; margin-bottom: 10px; text-align: center;">
                <?php echo simple_t('management_center', $lang); ?>
            </h2>
            <p style="color: rgba(255, 255, 255, 0.9); text-align: center; margin-bottom: 40px;">
                <?php echo simple_t('management_center_desc', $lang); ?>
            </p>
            
            <!-- Management Modules Grid -->
            <div class="modules-grid">
                <!-- Members Management -->
                <div class="enhanced-module-card">
                    <div class="module-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="module-title"><?php echo simple_t('members_management', $lang); ?></h3>
                    <p class="module-description"><?php echo simple_t('members_management_desc', $lang); ?></p>
                    
                    <div class="module-stats">
                        <div class="module-stat">
                            <strong><?php echo $members_stats['total_members']; ?></strong>
                            <span>Total</span>
                        </div>
                        <div class="module-stat">
                            <strong><?php echo $members_stats['active_members']; ?></strong>
                            <span>Active</span>
                        </div>
                        <div class="module-stat">
                            <strong><?php echo $members_stats['pending_members']; ?></strong>
                            <span>Pending</span>
                        </div>
                    </div>
                    
                    <a href="members.php" class="module-btn">
                        <i class="fas fa-arrow-right"></i>
                        <?php echo simple_t('manage_members', $lang); ?>
                    </a>
                </div>

                <!-- Payment Tracking -->
                <div class="enhanced-module-card">
                    <div class="module-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="module-title"><?php echo simple_t('payment_tracking', $lang); ?></h3>
                    <p class="module-description"><?php echo simple_t('payment_tracking_desc', $lang); ?></p>
                    
                    <div class="module-stats">
                        <div class="module-stat">
                            <strong>£<?php echo number_format($financial_stats['total_collected'], 0); ?></strong>
                            <span>Collected</span>
                        </div>
                        <div class="module-stat">
                            <strong><?php echo $financial_stats['completed_payments']; ?></strong>
                            <span>Payments</span>
                        </div>
                    </div>
                    
                    <a href="payments.php" class="module-btn">
                        <i class="fas fa-arrow-right"></i>
                        <?php echo simple_t('track_payments', $lang); ?>
                    </a>
                </div>

                <!-- Payout Management -->
                <div class="enhanced-module-card">
                    <div class="module-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="module-title"><?php echo simple_t('payout_management', $lang); ?></h3>
                    <p class="module-description"><?php echo simple_t('payout_management_desc', $lang); ?></p>
                    
                    <div class="module-stats">
                        <div class="module-stat">
                            <strong><?php echo $payout_stats['completed_payouts']; ?></strong>
                            <span>Completed</span>
                        </div>
                        <div class="module-stat">
                            <strong><?php echo $payout_stats['scheduled_payouts']; ?></strong>
                            <span>Scheduled</span>
                        </div>
                    </div>
                    
                    <a href="payouts.php" class="module-btn">
                        <i class="fas fa-arrow-right"></i>
                        <?php echo simple_t('manage_payouts', $lang); ?>
                    </a>
                </div>

                <!-- Reports & Analytics -->
                <div class="enhanced-module-card">
                    <div class="module-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="module-title"><?php echo simple_t('reports_analytics', $lang); ?></h3>
                    <p class="module-description"><?php echo simple_t('reports_analytics_desc', $lang); ?></p>
                    
                    <div class="module-stats">
                        <div class="module-stat">
                            <strong>Financial</strong>
                            <span>Reports</span>
                        </div>
                        <div class="module-stat">
                            <strong>Member</strong>
                            <span>Analytics</span>
                        </div>
                    </div>
                    
                    <a href="reports.php" class="module-btn">
                        <i class="fas fa-arrow-right"></i>
                        <?php echo simple_t('view_reports', $lang); ?>
                    </a>
                </div>

                <!-- System Settings -->
                <div class="enhanced-module-card">
                    <div class="module-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3 class="module-title"><?php echo simple_t('system_settings', $lang); ?></h3>
                    <p class="module-description"><?php echo simple_t('system_settings_desc', $lang); ?></p>
                    
                    <div class="module-stats">
                        <div class="module-stat">
                            <strong>Multilingual</strong>
                            <span>Support</span>
                        </div>
                        <div class="module-stat">
                            <strong>Secure</strong>
                            <span>System</span>
                        </div>
                    </div>
                    
                    <a href="settings.php" class="module-btn">
                        <i class="fas fa-arrow-right"></i>
                        <?php echo simple_t('configure_system', $lang); ?>
                    </a>
                </div>
            </div>
        </section>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Language switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const langButtons = document.querySelectorAll('.lang-btn');
            
            langButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(window.location);
                    url.searchParams.set('lang', this.href.split('lang=')[1]);
                    
                    // Simple redirect with language parameter
                    window.location.href = url.toString();
                });
            });
        });

        console.log('🎉 NEW HabeshaEqub Admin Dashboard loaded successfully!');
    </script>
</body>
</html>