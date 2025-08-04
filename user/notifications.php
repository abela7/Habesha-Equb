<?php
/**
 * HabeshaEqub - MEMBER NOTIFICATION CENTER
 * Modern social media-style notification feed for members
 * Multilingual support with beautiful, responsive design
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure authentication check
require_once 'auth_guard.php';
$user_id = get_current_user_id();

// Get member's language preference
$stmt = $pdo->prepare("SELECT language_preference FROM members WHERE id = ?");
$stmt->execute([$user_id]);
$member_language = $stmt->fetchColumn() ?: 'en';

// Set language preference for translator
if ($member_language === 'am') {
    $_SESSION['language'] = 'am';
} else {
    $_SESSION['language'] = 'en';
}

// Pagination
$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Get notifications for this member
try {
    // Get total count for pagination
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM member_message_reads mmr
        JOIN member_messages mm ON mmr.message_id = mm.id
        WHERE mmr.member_id = ? AND mm.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $total_notifications = $stmt->fetchColumn();
    
    // Get member messages with read status
    $stmt = $pdo->prepare("
        SELECT mm.id, mm.message_id,
               CASE WHEN ? = 'am' THEN mm.title_am ELSE mm.title_en END as title,
               CASE WHEN ? = 'am' THEN mm.content_am ELSE mm.content_en END as content,
               mm.message_type, mm.priority, mm.created_at, mm.created_by_admin_name,
               mmr.is_read, mmr.read_at
        FROM member_messages mm
        JOIN member_message_reads mmr ON mm.id = mmr.message_id
        WHERE mmr.member_id = ? AND mm.status = 'active'
        ORDER BY mm.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$member_language, $member_language, $user_id, $limit, $offset]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN mmr.is_read = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) as read
        FROM member_message_reads mmr
        JOIN member_messages mm ON mmr.message_id = mm.id
        WHERE mmr.member_id = ? AND mm.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    $notifications = [];
    $stats = ['total' => 0, 'unread' => 0, 'read' => 0];
    $total_notifications = 0;
}

$total_pages = ceil($total_notifications / $limit);
?>

<!DOCTYPE html>
<html lang="<?php echo $member_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - HabeshaEqub</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* FORCE PROPER COLORS - FIX BLACK/WHITE ISSUE */
        body {
            background: #FEFDF8 !important;
            color: #1F2937 !important;
        }
        
        /* FORCE VISIBLE TEXT COLORS */
        .notification-card {
            background: white !important;
            color: #1F2937 !important;
            border: 1px solid #E5E7EB !important;
        }
        
        .notification-title {
            color: #301943 !important;
        }
        
        .notification-content {
            color: #4B5563 !important;
        }
        
        .stat-card {
            background: white !important;
            color: #1F2937 !important;
            border: 1px solid #E5E7EB !important;
        }
        
        .notifications-header {
            background: linear-gradient(135deg, #301943, #51258F) !important;
            color: white !important;
        }
        
        .btn-modern {
            background: #301943 !important;
            color: white !important;
            border: none !important;
        }
        /* ======================================= */
        /* ULTRA-MODERN NOTIFICATION CENTER STYLES */
        /* ======================================= */
        
        :root {
            --color-purple: #301943;
            --darker-purple: #51258F;
            --gold: #FBB724;
            --light-gold: #FCD34D;
            --white: #FFFFFF;
            --border-light: #E5E7EB;
            --text-primary: #1F2937;
            --text-muted: #6B7280;
            --cream-bg: #FEFDF8;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #3B82F6;
        }
        
        .notifications-header {
            background: linear-gradient(135deg, 
                var(--color-purple) 0%, 
                var(--darker-purple) 50%,
                #6B46C1 100%);
            color: var(--white);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(48, 25, 67, 0.3);
        }
        
        .notifications-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 120%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: headerShimmer 4s ease-in-out infinite;
        }
        
        @keyframes headerShimmer {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
            50% { transform: scale(1.1) rotate(3deg); opacity: 0.6; }
        }
        
        .header-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 400;
        }
        
        /* Statistics Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(48, 25, 67, 0.08);
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
            background: linear-gradient(90deg, var(--gold), var(--light-gold));
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(48, 25, 67, 0.15);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--darker-purple);
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        /* Notification Feed */
        .notifications-feed {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 40px rgba(48, 25, 67, 0.08);
        }
        
        .feed-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-light);
        }
        
        .feed-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--darker-purple);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .feed-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-modern {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--color-purple), var(--darker-purple));
            color: var(--white);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
            color: var(--white);
        }
        
        .btn-secondary-modern {
            background: var(--white);
            color: var(--text-muted);
            border: 2px solid var(--border-light);
        }
        
        .btn-secondary-modern:hover {
            border-color: var(--color-purple);
            color: var(--color-purple);
        }
        
        /* Notification Cards */
        .notification-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.4s ease;
            position: relative;
            cursor: pointer;
        }
        
        .notification-card.unread {
            border-left: 5px solid var(--gold);
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.05), rgba(245, 158, 11, 0.05));
        }
        
        .notification-card.read {
            border-left: 5px solid var(--border-light);
            opacity: 0.8;
        }
        
        .notification-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(48, 25, 67, 0.12);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .notification-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--darker-purple);
            margin: 0 0 8px 0;
            line-height: 1.3;
        }
        
        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .meta-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .priority-urgent { background: linear-gradient(135deg, #DC2626, #EF4444); color: white; animation: urgent-glow 2s infinite; }
        .priority-high { background: linear-gradient(135deg, #EF4444, #F87171); color: white; }
        .priority-medium { background: linear-gradient(135deg, #F59E0B, #FCD34D); color: white; }
        .priority-low { background: linear-gradient(135deg, #10B981, #34D399); color: white; }
        
        @keyframes urgent-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
            50% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
        }
        
        .type-general { background: linear-gradient(135deg, #6B7280, #9CA3AF); color: white; }
        .type-payment_reminder { background: linear-gradient(135deg, #8B5CF6, #A78BFA); color: white; }
        .type-payout_announcement { background: linear-gradient(135deg, #059669, #10B981); color: white; }
        .type-system_update { background: linear-gradient(135deg, #DC2626, #EF4444); color: white; }
        .type-announcement { background: linear-gradient(135deg, #2563EB, #3B82F6); color: white; }
        
        .notification-time {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .notification-content {
            color: var(--text-primary);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .notification-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid var(--border-light);
        }
        
        .notification-author {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .read-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-read {
            color: var(--success);
        }
        
        .status-unread {
            color: var(--gold);
        }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
        }
        
        .page-link {
            padding: 12px 16px;
            border: 2px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .page-link:hover,
        .page-link.active {
            background: linear-gradient(135deg, var(--color-purple), var(--darker-purple));
            border-color: var(--color-purple);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--border-light);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--text-muted);
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .notifications-header {
                padding: 25px;
                text-align: center;
            }
            
            .header-title {
                font-size: 2rem;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .notifications-feed {
                padding: 20px;
            }
            
            .feed-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .notification-card {
                padding: 20px;
            }
            
            .card-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .notification-meta {
                justify-content: center;
            }
            
            .notification-footer {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .notifications-header {
                padding: 20px;
            }
            
            .header-title {
                font-size: 1.7rem;
            }
            
            .notification-card {
                padding: 15px;
            }
            
            .feed-title {
                font-size: 1.5rem;
            }
        }
        
        /* Enhanced Visual Effects & Mobile Responsiveness */
        body {
            background: linear-gradient(135deg, var(--cream-bg) 0%, #F9FAFB 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .app-container {
            background: transparent;
        }
        
        .notification-card {
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(48, 25, 67, 0.1);
        }
        
        .notification-card:hover {
            border-color: var(--gold);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(48, 25, 67, 0.15);
        }
        
        .stat-card {
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .notifications-feed {
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Improved Button Styles */
        .btn-modern {
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            padding: 12px 24px;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--color-purple), var(--darker-purple));
            color: var(--white);
            box-shadow: 0 4px 15px rgba(48, 25, 67, 0.3);
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
            color: var(--white);
        }
        
        /* Notification Status Indicators */
        .notification-card.unread::before {
            content: '';
            position: absolute;
            top: 20px;
            right: 20px;
            width: 12px;
            height: 12px;
            background: var(--gold);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(251, 183, 36, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(251, 183, 36, 0); }
            100% { box-shadow: 0 0 0 0 rgba(251, 183, 36, 0); }
        }
        
        /* Enhanced Mobile Responsiveness */
        @media (max-width: 768px) {
            .header-title { font-size: 2rem; }
            .notification-card { padding: 20px; margin-bottom: 15px; }
            .notification-title { font-size: 1.1rem; }
            .notification-meta { flex-direction: column; align-items: flex-start; gap: 8px; }
            .meta-badge { font-size: 0.75rem; padding: 4px 10px; }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .stat-card { padding: 20px; }
            .notifications-header { padding: 30px 20px; }
            .notifications-feed { padding: 20px; }
            .feed-actions { 
                flex-direction: column; 
                gap: 10px;
                align-items: stretch;
            }
            .btn-modern {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .header-title { font-size: 1.8rem; }
            .notifications-header { padding: 25px 15px; }
            .notification-card { padding: 15px; }
            .pagination-container { padding: 15px; }
            .btn-modern { padding: 12px 20px; font-size: 0.9rem; }
            .stats-row { grid-template-columns: 1fr; }
            .notification-content { font-size: 1rem; }
            .meta-badge { font-size: 0.7rem; padding: 3px 8px; }
            .feed-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            .feed-title {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="page-container">
        <!-- Header -->
        <div class="notifications-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="header-title">
                        <i class="fas fa-bell me-3"></i>
                        <?php echo $member_language === 'am' ? 'ማሳወቂያዎች' : 'Notifications'; ?>
                    </h1>
                    <p class="header-subtitle">
                        <?php echo $member_language === 'am' ? 'የእርስዎን ማሳወቂያዎች እና መልዕክቶች ይመልከቱ' : 'Stay updated with important messages and announcements'; ?>
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="header-stats">
                        <div class="text-white">
                            <span class="fs-3 fw-bold"><?php echo $stats['unread']; ?></span>
                            <div class="small opacity-75">
                                <?php echo $member_language === 'am' ? 'ያልተነበቡ' : 'Unread'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">
                    <?php echo $member_language === 'am' ? 'ጠቅላላ ማሳወቂያዎች' : 'Total Notifications'; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['unread']; ?></div>
                <div class="stat-label">
                    <?php echo $member_language === 'am' ? 'ያልተነበቡ' : 'Unread'; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['read']; ?></div>
                <div class="stat-label">
                    <?php echo $member_language === 'am' ? 'የተነበቡ' : 'Read'; ?>
                </div>
            </div>
        </div>

        <!-- Notifications Feed -->
        <div class="notifications-feed">
            <div class="feed-header">
                <h2 class="feed-title">
                    <i class="fas fa-list"></i>
                    <?php echo $member_language === 'am' ? 'የመልዕክት ዝርዝር' : 'Message Feed'; ?>
                </h2>
                <div class="feed-actions">
                    <button class="btn-modern btn-secondary-modern" onclick="markAllAsRead()">
                        <i class="fas fa-check-double"></i>
                        <?php echo $member_language === 'am' ? 'ሁሉንም እንደተነበቡ ምልክት ያድርጉ' : 'Mark All Read'; ?>
                    </button>
                    <button class="btn-modern btn-primary-modern" onclick="refreshNotifications()">
                        <i class="fas fa-sync"></i>
                        <?php echo $member_language === 'am' ? 'አድስ' : 'Refresh'; ?>
                    </button>
                </div>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3><?php echo $member_language === 'am' ? 'ምንም ማሳወቂያዎች የሉም' : 'No notifications yet'; ?></h3>
                    <p><?php echo $member_language === 'am' ? 'አዳዲስ ማሳወቂያዎች እንዲመጡ ይጠብቁ' : 'Check back later for new messages and updates'; ?></p>
                </div>
            <?php else: ?>
                <div id="notificationsList">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                             data-id="<?php echo $notification['id']; ?>"
                             onclick="markAsRead(<?php echo $notification['id']; ?>)">
                            
                            <div class="card-header">
                                <div>
                                    <h3 class="notification-title">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h3>
                                    <div class="notification-meta">
                                        <span class="meta-badge priority-<?php echo $notification['priority']; ?>">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo ucfirst($notification['priority']); ?>
                                        </span>
                                        <span class="meta-badge type-<?php echo $notification['notification_type']; ?>">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo str_replace('_', ' ', ucfirst($notification['notification_type'])); ?>
                                        </span>
                                        <span class="notification-time">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('M d, Y g:i A', strtotime($notification['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="read-status <?php echo $notification['is_read'] ? 'status-read' : 'status-unread'; ?>">
                                    <i class="fas fa-<?php echo $notification['is_read'] ? 'check-circle' : 'circle'; ?>"></i>
                                    <?php echo $notification['is_read'] ? 
                                        ($member_language === 'am' ? 'ተነብቧል' : 'Read') : 
                                        ($member_language === 'am' ? 'አልተነበበም' : 'Unread'); ?>
                                </div>
                            </div>

                            <div class="notification-content">
                                <?php echo nl2br(htmlspecialchars($notification['content'])); ?>
                            </div>

                            <div class="notification-footer">
                                <div class="notification-author">
                                    <i class="fas fa-user-shield me-1"></i>
                                    <?php echo $member_language === 'am' ? 'በ' : 'By'; ?> <?php echo htmlspecialchars($notification['created_by_admin_name']); ?>
                                </div>
                                <?php if ($notification['is_read']): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo $member_language === 'am' ? 'የተነበበበት ጊዜ' : 'Read on'; ?> 
                                        <?php echo date('M d, Y', strtotime($notification['read_at'])); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Mark notification as read
        async function markAsRead(notificationId) {
            try {
                const response = await fetch('../admin/api/notifications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=mark_read&notification_id=${notificationId}&member_id=<?php echo $user_id; ?>`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update card appearance
                    const card = document.querySelector(`[data-id="${notificationId}"]`);
                    if (card) {
                        card.classList.remove('unread');
                        card.classList.add('read');
                        
                        const statusEl = card.querySelector('.read-status');
                        statusEl.classList.remove('status-unread');
                        statusEl.classList.add('status-read');
                        statusEl.innerHTML = '<i class="fas fa-check-circle"></i> <?php echo $member_language === 'am' ? 'ተነብቧል' : 'Read'; ?>';
                    }
                    
                    // Update navigation badge
                    updateNotificationBadge();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        // Mark all notifications as read
        async function markAllAsRead() {
            if (!confirm('<?php echo $member_language === 'am' ? 'ሁሉንም ማሳወቂያዎች እንደተነበቡ ምልክት ማድረግ ይፈልጋሉ?' : 'Mark all notifications as read?'; ?>')) {
                return;
            }
            
            try {
                const promises = [];
                document.querySelectorAll('.notification-card.unread').forEach(card => {
                    const notificationId = card.dataset.id;
                    promises.push(markAsRead(notificationId));
                });
                
                await Promise.all(promises);
                
                // Refresh page to update statistics
                setTimeout(() => location.reload(), 1000);
                
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        }

        // Refresh notifications
        function refreshNotifications() {
            location.reload();
        }

        // Update notification badge in navigation
        function updateNotificationBadge() {
            // This will be updated on next page load
            // For real-time updates, we could implement WebSocket or polling
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            // Subtle refresh without disrupting user experience
            fetch(window.location.href + '&ajax=1')
                .then(response => response.text())
                .then(html => {
                    // Update only if there are new notifications
                    // Implementation could compare notification count
                });
        }, 30000);

        // Smooth scroll to top when clicking pagination
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>