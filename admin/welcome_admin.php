<?php
/**
 * HabeshaEqub - ADMIN WELCOME DASHBOARD
 * Fresh, clean admin dashboard with working translations
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

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
            COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_collected,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments
        FROM payments
    ")->fetch();
    
    // Payout statistics
    $payout_stats = $pdo->query("
        SELECT 
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payouts,
            COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_payouts
        FROM payouts
    ")->fetch();
    
    // Recent members (last 5)
    $recent_members = $pdo->query("
        SELECT full_name, email, created_at, is_approved 
        FROM members 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Recent payments (last 5)
    $recent_payments = $pdo->query("
        SELECT p.amount, p.created_at, m.full_name 
        FROM payments p 
        JOIN members m ON p.member_id = m.id 
        WHERE p.status = 'completed'
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Set default values
    $members_stats = ['total_members' => 0, 'active_members' => 0, 'approved_members' => 0, 'pending_members' => 0];
    $financial_stats = ['total_collected' => 0, 'completed_payments' => 0, 'pending_payments' => 0];
    $payout_stats = ['completed_payouts' => 0, 'scheduled_payouts' => 0];
    $recent_members = [];
    $recent_payments = [];
}
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('navigation.dashboard'); ?> - HabeshaEqub Admin</title>
    
    <meta name="description" content="<?php echo t('admin_dashboard.page_description'); ?>">
    
    <!-- External Dependencies -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
    /* === ENHANCED ADMIN DASHBOARD DESIGN === */
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        color: #333;
        overflow-x: hidden;
    }
    
    .admin-container {
        display: flex;
        min-height: 100vh;
    }
    
    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
        }
    }
    
    /* === WELCOME SECTION === */
    .welcome-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .welcome-title {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }
    
    .welcome-subtitle {
        font-size: 1.1rem;
        color: #6b7280;
        font-weight: 400;
    }
    
    /* === STATS GRID === */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 1.5rem;
        color: white;
    }
    
    .stat-icon.members { background: linear-gradient(135deg, #667eea, #764ba2); }
    .stat-icon.finance { background: linear-gradient(135deg, #f093fb, #f5576c); }
    .stat-icon.payouts { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    
    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    /* === ACTIVITY SECTION === */
    .activity-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 968px) {
        .activity-section {
            grid-template-columns: 1fr;
        }
    }
    
    .activity-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .activity-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .activity-title i {
        margin-right: 10px;
        color: #667eea;
    }
    
    .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-text {
        flex: 1;
        font-weight: 500;
        color: #374151;
    }
    
    .activity-time {
        font-size: 0.85rem;
        color: #9ca3af;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    /* === MODULES GRID === */
    .modules-section {
        margin-top: 30px;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
    }
    
    .section-description {
        color: #6b7280;
        margin-bottom: 25px;
    }
    
    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .module-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }
    
    .module-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        text-decoration: none;
        color: inherit;
    }
    
    .module-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 1.8rem;
        color: white;
    }
    
    .module-icon.members { background: linear-gradient(135deg, #667eea, #764ba2); }
    .module-icon.payments { background: linear-gradient(135deg, #f093fb, #f5576c); }
    .module-icon.payouts { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    .module-icon.reports { background: linear-gradient(135deg, #a8edea, #fed6e3); color: #667eea; }
    
    .module-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 8px;
    }
    
    .module-description {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    /* === ANIMATIONS === */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .animate-delay-1 { animation-delay: 0.1s; }
    .animate-delay-2 { animation-delay: 0.2s; }
    .animate-delay-3 { animation-delay: 0.3s; }
    .animate-delay-4 { animation-delay: 0.4s; }
    
    /* === RESPONSIVE === */
    @media (max-width: 768px) {
        .welcome-title {
            font-size: 2rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .modules-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Include Navigation -->
        <?php include 'includes/navigation.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            
            <!-- Welcome Section -->
            <section class="welcome-section animate-fade-in">
                <h1 class="welcome-title">
                    <?php echo str_replace('{username}', htmlspecialchars($admin_username), t('admin_dashboard.welcome_back')); ?>
                </h1>
                <p class="welcome-subtitle">
                    <?php echo t('admin_dashboard.welcome_subtitle'); ?>
                </p>
            </section>
            
            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card animate-fade-in animate-delay-1">
                    <div class="stat-icon members">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $members_stats['total_members']; ?></div>
                    <div class="stat-label"><?php echo t('admin_dashboard.total_members'); ?></div>
                </div>
                
                <div class="stat-card animate-fade-in animate-delay-2">
                    <div class="stat-icon finance">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value">£<?php echo number_format($financial_stats['total_collected'], 0); ?></div>
                    <div class="stat-label"><?php echo t('admin_dashboard.total_collected'); ?></div>
                </div>
                
                <div class="stat-card animate-fade-in animate-delay-3">
                    <div class="stat-icon payouts">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-value"><?php echo $payout_stats['completed_payouts']; ?></div>
                    <div class="stat-label"><?php echo t('admin_dashboard.completed_payouts'); ?></div>
                </div>
                
                <div class="stat-card animate-fade-in animate-delay-4">
                    <div class="stat-icon members">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $members_stats['pending_members']; ?></div>
                    <div class="stat-label"><?php echo t('admin_dashboard.pending_approvals'); ?></div>
                </div>
            </section>
            
            <!-- Activity Section -->
            <section class="activity-section">
                <div class="activity-card animate-fade-in animate-delay-1">
                    <h3 class="activity-title">
                        <i class="fas fa-user-plus"></i>
                        <?php echo t('admin_dashboard.recent_applications'); ?>
                    </h3>
                    
                    <?php if (empty($recent_members)): ?>
                        <div class="activity-item">
                            <div class="activity-text"><?php echo t('admin_dashboard.no_recent_applications'); ?></div>
                            <div class="activity-time"><?php echo t('admin_dashboard.all_caught_up'); ?></div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_members as $member): ?>
                            <div class="activity-item">
                                <div class="activity-text">
                                    <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                                    <?php echo t('admin_dashboard.applied_to_join'); ?>
                                </div>
                                <div>
                                    <span class="status-badge <?php echo $member['is_approved'] ? 'status-approved' : 'status-pending'; ?>">
                                        <?php echo $member['is_approved'] ? t('admin_dashboard.approved') : t('admin_dashboard.pending'); ?>
                                    </span>
                                    <div class="activity-time"><?php echo date('M j', strtotime($member['created_at'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="activity-card animate-fade-in animate-delay-2">
                    <h3 class="activity-title">
                        <i class="fas fa-credit-card"></i>
                        <?php echo t('admin_dashboard.recent_payments'); ?>
                    </h3>
                    
                    <?php if (empty($recent_payments)): ?>
                        <div class="activity-item">
                            <div class="activity-text"><?php echo t('admin_dashboard.no_recent_payments'); ?></div>
                            <div class="activity-time"><?php echo t('admin_dashboard.check_back_later'); ?></div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_payments as $payment): ?>
                            <div class="activity-item">
                                <div class="activity-text">
                                    <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong>
                                    <?php echo t('admin_dashboard.paid'); ?> £<?php echo number_format($payment['amount'], 0); ?>
                                </div>
                                <div class="activity-time"><?php echo date('M j', strtotime($payment['created_at'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Modules Section -->
            <section class="modules-section">
                <h2 class="section-title"><?php echo t('admin_dashboard.management_modules'); ?></h2>
                <p class="section-description"><?php echo t('admin_dashboard.modules_description'); ?></p>
                
                <div class="modules-grid">
                    <a href="members.php" class="module-card animate-fade-in animate-delay-1">
                        <div class="module-icon members">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="module-title"><?php echo t('admin_dashboard.members_management'); ?></h3>
                        <p class="module-description"><?php echo t('admin_dashboard.members_description'); ?></p>
                    </a>
                    
                    <a href="payments.php" class="module-card animate-fade-in animate-delay-2">
                        <div class="module-icon payments">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="module-title"><?php echo t('admin_dashboard.payments_management'); ?></h3>
                        <p class="module-description"><?php echo t('admin_dashboard.payments_description'); ?></p>
                    </a>
                    
                    <a href="payouts.php" class="module-card animate-fade-in animate-delay-3">
                        <div class="module-icon payouts">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h3 class="module-title"><?php echo t('admin_dashboard.payouts_management'); ?></h3>
                        <p class="module-description"><?php echo t('admin_dashboard.payouts_description'); ?></p>
                    </a>
                    
                    <a href="reports.php" class="module-card animate-fade-in animate-delay-4">
                        <div class="module-icon reports">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="module-title"><?php echo t('admin_dashboard.reports_analytics'); ?></h3>
                        <p class="module-description"><?php echo t('admin_dashboard.reports_description'); ?></p>
                    </a>
                </div>
            </section>
            
        </main>
    </div>

    <script>
    // Enhanced Dashboard JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe all animated elements
        document.querySelectorAll('.animate-fade-in').forEach(el => {
            observer.observe(el);
        });
        
        console.log('🚀 Welcome Admin Dashboard loaded successfully! Multilingual support active.');
    });
    </script>
</body>
</html>