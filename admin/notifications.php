<?php
/**
 * HabeshaEqub - ULTRA-MODERN NOTIFICATION MANAGEMENT SYSTEM
 * Social media-style notification platform for top-tier member engagement
 * JAW-DROPPING design with advanced multilingual support
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username();

// Get notification statistics
try {
    // Total member messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM member_messages WHERE status != 'deleted'");
    $total_notifications = $stmt->fetchColumn();
    
    // Active member messages
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM member_messages WHERE status = 'active'");
    $active_notifications = $stmt->fetchColumn();
    
    // Unread member messages (across all members)
    $stmt = $pdo->query("SELECT COUNT(*) as unread FROM member_message_reads WHERE is_read = 0");
    $total_unread = $stmt->fetchColumn();
    
    // Recent engagement
    $stmt = $pdo->query("SELECT COUNT(*) as recent FROM member_message_reads WHERE read_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $recent_reads = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $total_notifications = $active_notifications = $total_unread = $recent_reads = 0;
    error_log("Error fetching notification stats: " . $e->getMessage());
}

// Get all notifications for display
try {
    $stmt = $pdo->query("
        SELECT mm.*, 
               COUNT(mmr.id) as total_delivered,
               SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) as total_read,
               SUM(CASE WHEN mmr.is_read = 0 THEN 1 ELSE 0 END) as total_unread,
               ROUND((SUM(CASE WHEN mmr.is_read = 1 THEN 1 ELSE 0 END) / COUNT(mmr.id)) * 100, 2) as read_percentage
        FROM member_messages mm
        LEFT JOIN member_message_reads mmr ON mm.id = mmr.message_id
        WHERE mm.status != 'deleted'
        GROUP BY mm.id
        ORDER BY mm.created_at DESC
        LIMIT 50
    ");
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $notifications = [];
    error_log("Error fetching notifications: " . $e->getMessage());
}

// Get EQUB terms for targeting
try {
    $stmt = $pdo->query("SELECT id, equb_name, status FROM equb_settings ORDER BY created_at DESC");
    $equb_terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $equb_terms = [];
    error_log("Error fetching EQUB terms: " . $e->getMessage());
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Center - HabeshaEqub Admin</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Quill Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* ========================================= */
        /* ULTRA-MODERN NOTIFICATION CENTER STYLES */
        /* ========================================= */
        
        .notification-header {
            background: linear-gradient(135deg, 
                var(--color-purple) 0%, 
                var(--darker-purple) 40%,
                #6B46C1 70%,
                var(--color-purple) 100%);
            color: var(--white);
            border-radius: 25px;
            padding: 50px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(48, 25, 67, 0.4);
        }
        
        .notification-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 150%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
            50% { transform: scale(1.1) rotate(5deg); opacity: 0.6; }
        }
        
        .notification-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            letter-spacing: -1px;
        }
        
        .notification-subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            font-weight: 400;
            line-height: 1.6;
        }
        
        /* Statistics Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            border: 1px solid var(--border-light);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(48, 25, 67, 0.08);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--gold), var(--light-gold));
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 30px 80px rgba(48, 25, 67, 0.2);
        }
        
        .stat-card:hover::before {
            height: 8px;
            background: linear-gradient(90deg, var(--color-purple), var(--darker-purple));
        }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 25px;
            position: relative;
        }
        
        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--darker-purple);
            margin-bottom: 12px;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Create Notification Section */
        .create-section {
            background: var(--white);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 50px;
            border: 1px solid var(--border-light);
            box-shadow: 0 15px 60px rgba(48, 25, 67, 0.1);
            position: relative;
        }
        
        .create-header {
            display: flex;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid var(--border-light);
        }
        
        .create-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--darker-purple);
            margin: 0;
        }
        
        .create-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gold), var(--light-gold));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--white);
            margin-right: 20px;
        }
        
        /* Language Tabs */
        .language-tabs {
            display: flex;
            background: linear-gradient(135deg, var(--color-cream), #FAF8F5);
            border-radius: 15px;
            padding: 8px;
            margin-bottom: 30px;
            box-shadow: inset 0 2px 8px rgba(48, 25, 67, 0.1);
        }
        
        .language-tab {
            flex: 1;
            padding: 15px 25px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .language-tab.active {
            background: var(--white);
            color: var(--darker-purple);
            box-shadow: 0 4px 20px rgba(48, 25, 67, 0.15);
            transform: translateY(-2px);
        }
        
        .language-tab:hover {
            color: var(--color-purple);
        }
        
        /* Form Styling */
        .modern-form-group {
            margin-bottom: 30px;
        }
        
        .modern-label {
            font-weight: 600;
            color: var(--darker-purple);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }
        
        .modern-label i {
            margin-right: 10px;
            color: var(--gold);
        }
        
        .modern-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            font-size: 1.1rem;
            background: var(--white);
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .modern-input:focus {
            outline: none;
            border-color: var(--color-purple);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
            transform: translateY(-2px);
        }
        
        .modern-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 45px;
        }
        
        /* Rich Text Editor */
        .editor-container {
            border: 2px solid var(--border-light);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .editor-container:focus-within {
            border-color: var(--color-purple);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }
        
        .ql-toolbar {
            background: linear-gradient(135deg, var(--color-cream), #FAF8F5);
            border: none !important;
            border-bottom: 1px solid var(--border-light) !important;
        }
        
        .ql-container {
            border: none !important;
            font-size: 1.1rem;
            min-height: 150px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 35px;
        }
        
        .btn-modern {
            padding: 16px 35px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--color-purple), var(--darker-purple));
            color: var(--white);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
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
            transform: translateY(-2px);
        }
        
        /* Notifications List */
        .notifications-list {
            background: var(--white);
            border-radius: 25px;
            padding: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 15px 60px rgba(48, 25, 67, 0.1);
        }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid var(--border-light);
        }
        
        .list-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--darker-purple);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        /* Notification Cards */
        .notification-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .notification-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(135deg, var(--gold), var(--light-gold));
            transition: all 0.3s ease;
        }
        
        .notification-card:hover {
            box-shadow: 0 20px 60px rgba(48, 25, 67, 0.15);
            transform: translateY(-5px);
        }
        
        .notification-card:hover::before {
            width: 8px;
            background: linear-gradient(135deg, var(--color-purple), var(--darker-purple));
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .notification-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .notification-title-display {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--darker-purple);
            margin: 0;
            line-height: 1.3;
        }
        
        .notification-info {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .info-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .priority-high { background: linear-gradient(135deg, #EF4444, #F87171); color: white; }
        .priority-medium { background: linear-gradient(135deg, #F59E0B, #FCD34D); color: white; }
        .priority-low { background: linear-gradient(135deg, #10B981, #34D399); color: white; }
        .priority-urgent { background: linear-gradient(135deg, #DC2626, #EF4444); color: white; animation: urgent-pulse 2s infinite; }
        
        @keyframes urgent-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
            50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
        }
        
        .type-general { background: linear-gradient(135deg, #6B7280, #9CA3AF); color: white; }
        .type-payment_reminder { background: linear-gradient(135deg, #8B5CF6, #A78BFA); color: white; }
        .type-payout_announcement { background: linear-gradient(135deg, #059669, #10B981); color: white; }
        .type-system_update { background: linear-gradient(135deg, #DC2626, #EF4444); color: white; }
        .type-announcement { background: linear-gradient(135deg, #2563EB, #3B82F6); color: white; }
        
        .notification-content {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 25px;
        }
        
        .notification-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            background: linear-gradient(135deg, var(--color-cream), #FAF8F5);
            border-radius: 15px;
            padding: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--darker-purple);
            display: block;
        }
        
        .stat-text {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-sm-modern {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .notification-header {
                padding: 30px;
                text-align: center;
            }
            
            .notification-title {
                font-size: 2.2rem;
            }
            
            .notification-subtitle {
                font-size: 1.1rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .create-section,
            .notifications-list {
                padding: 25px;
            }
            
            .create-header,
            .list-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .notification-info {
                justify-content: center;
            }
            
            .notification-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .card-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .notification-actions {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
        
        @media (max-width: 480px) {
            .notification-header {
                padding: 20px;
            }
            
            .notification-title {
                font-size: 1.8rem;
            }
            
            .create-section,
            .notifications-list {
                padding: 20px;
            }
            
            .notification-card {
                padding: 20px;
            }
            
            .notification-stats {
                grid-template-columns: 1fr;
            }
        }
        
        /* Loading and Success States */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .success-message {
            background: linear-gradient(135deg, #10B981, #34D399);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }
        
        .error-message {
            background: linear-gradient(135deg, #EF4444, #F87171);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Include Navigation -->
    <?php require_once 'includes/navigation.php'; ?>
    
    <div class="admin-container">
        <!-- Header Section -->
        <div class="notification-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="notification-title">
                        <i class="fas fa-bell me-3"></i>
                        Notification Center
                    </h1>
                    <p class="notification-subtitle">
                        Create and manage notifications for your EQUB members with modern social media-style engagement
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="header-stats">
                        <div class="stat-item">
                            <span class="stat-number text-white"><?php echo $active_notifications; ?></span>
                            <span class="stat-text text-white-50">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #3B82F6, #60A5FA); color: white;">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-value"><?php echo $total_notifications; ?></div>
                <div class="stat-label">Total Notifications</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399); color: white;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $active_notifications; ?></div>
                <div class="stat-label">Active Notifications</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #FCD34D); color: white;">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-value"><?php echo $total_unread; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA); color: white;">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-value"><?php echo $recent_reads; ?></div>
                <div class="stat-label">Recent Views (24h)</div>
            </div>
        </div>

        <!-- Create Notification Section -->
        <div class="create-section">
            <div class="create-header">
                <div class="create-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <h2 class="create-title">Create New Notification</h2>
            </div>

            <form id="notificationForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Language Tabs -->
                <div class="language-tabs">
                    <button type="button" class="language-tab active" data-lang="en">
                        <i class="fas fa-globe me-2"></i>English
                    </button>
                    <button type="button" class="language-tab" data-lang="am">
                        <i class="fas fa-language me-2"></i>አማርኛ (Amharic)
                    </button>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- English Content -->
                        <div class="language-content" data-lang="en">
                            <div class="modern-form-group">
                                <label class="modern-label">
                                    <i class="fas fa-heading"></i>Title (English)
                                </label>
                                <input type="text" name="title_en" class="modern-input" 
                                       placeholder="Enter notification title in English..." required>
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-label">
                                    <i class="fas fa-edit"></i>Content (English)
                                </label>
                                <div class="editor-container">
                                    <div id="editor-en"></div>
                                </div>
                                <input type="hidden" name="content_en" id="content_en">
                            </div>
                        </div>

                        <!-- Amharic Content -->
                        <div class="language-content" data-lang="am" style="display: none;">
                            <div class="modern-form-group">
                                <label class="modern-label">
                                    <i class="fas fa-heading"></i>ርዕስ (አማርኛ)
                                </label>
                                <input type="text" name="title_am" class="modern-input" 
                                       placeholder="በአማርኛ የማሳወቂያ ርዕስ ያስገቡ..." required>
                            </div>

                            <div class="modern-form-group">
                                <label class="modern-label">
                                    <i class="fas fa-edit"></i>ይዘት (አማርኛ)
                                </label>
                                <div class="editor-container">
                                    <div id="editor-am"></div>
                                </div>
                                <input type="hidden" name="content_am" id="content_am">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Notification Settings -->
                                                    <div class="modern-form-group">
                                <label class="modern-label">
                                    <i class="fas fa-tag"></i>Type
                                </label>
                                <select name="message_type" class="modern-input modern-select" required>
                                <option value="general">General Announcement</option>
                                <option value="payment_reminder">Payment Reminder</option>
                                <option value="payout_announcement">Payout Announcement</option>
                                <option value="system_update">System Update</option>
                                <option value="announcement">Important Announcement</option>
                            </select>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-label">
                                <i class="fas fa-exclamation-triangle"></i>Priority
                            </label>
                            <select name="priority" class="modern-input modern-select" required>
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-label">
                                <i class="fas fa-users"></i>Target Audience
                            </label>
                            <select name="target_audience" class="modern-input modern-select" required id="targetAudience">
                                <option value="all_members">All Members</option>
                                <option value="active_members">Active Members Only</option>
                                <option value="specific_equb">Specific EQUB Term</option>
                                <option value="individual">Individual Member</option>
                            </select>
                        </div>

                        <div class="modern-form-group" id="equbSelector" style="display: none;">
                            <label class="modern-label">
                                <i class="fas fa-coins"></i>Select EQUB Term
                            </label>
                            <select name="equb_settings_id" class="modern-input modern-select">
                                <option value="">Choose EQUB Term...</option>
                                <?php foreach ($equb_terms as $equb): ?>
                                    <option value="<?php echo $equb['id']; ?>">
                                        <?php echo htmlspecialchars($equb['equb_name']); ?> 
                                        (<?php echo ucfirst($equb['status']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Notification</span>
                        <div class="loading-spinner" style="display: none;"></div>
                    </button>
                    <button type="button" class="btn-modern btn-secondary-modern" onclick="resetForm()">
                        <i class="fas fa-undo"></i>
                        Reset Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Notifications List -->
        <div class="notifications-list">
            <div class="list-header">
                <h2 class="list-title">
                    <i class="fas fa-history"></i>
                    Recent Notifications
                </h2>
                <div class="list-actions">
                    <button class="btn-modern btn-secondary-modern" onclick="refreshNotifications()">
                        <i class="fas fa-sync"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <div id="notificationsList">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-5x text-muted mb-4"></i>
                        <h4 class="text-muted">No notifications yet</h4>
                        <p class="text-muted">Create your first notification to engage with your members!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card" data-id="<?php echo $notification['id']; ?>">
                            <div class="card-header">
                                <div class="notification-meta">
                                    <h3 class="notification-title-display">
                                        <?php echo htmlspecialchars($notification['title_en']); ?>
                                    </h3>
                                    <div class="notification-info">
                                        <span class="info-badge priority-<?php echo $notification['priority']; ?>">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo ucfirst($notification['priority']); ?>
                                        </span>
                                        <span class="info-badge type-<?php echo $notification['message_type']; ?>">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo str_replace('_', ' ', ucfirst($notification['message_type'])); ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('M d, Y g:i A', strtotime($notification['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn-sm-modern" style="background: linear-gradient(135deg, #3B82F6, #60A5FA); color: white;" 
                                            onclick="viewNotification(<?php echo $notification['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </button>
                                    <button class="btn-sm-modern" style="background: linear-gradient(135deg, #F59E0B, #FCD34D); color: white;" 
                                            onclick="editNotification(<?php echo $notification['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </button>
                                    <button class="btn-sm-modern" style="background: linear-gradient(135deg, #EF4444, #F87171); color: white;" 
                                            onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>

                            <div class="notification-content">
                                <?php echo substr(strip_tags($notification['content_en']), 0, 200) . '...'; ?>
                            </div>

                            <div class="notification-stats">
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $notification['total_delivered'] ?: 0; ?></span>
                                    <span class="stat-text">Delivered</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $notification['total_read'] ?: 0; ?></span>
                                    <span class="stat-text">Read</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $notification['total_unread'] ?: 0; ?></span>
                                    <span class="stat-text">Unread</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo number_format($notification['read_percentage'] ?: 0, 1); ?>%</span>
                                    <span class="stat-text">Read Rate</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Quill Rich Text Editor -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <script>
        // Initialize Rich Text Editors
        const quillEn = new Quill('#editor-en', {
            theme: 'snow',
            placeholder: 'Write your notification content in English...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    ['link', 'blockquote'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        const quillAm = new Quill('#editor-am', {
            theme: 'snow',
            placeholder: 'የማሳወቂያ ይዘትዎን በአማርኛ ይጻፉ...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    ['link', 'blockquote'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Language Tab Switching
        document.querySelectorAll('.language-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const lang = this.dataset.lang;
                
                // Update active tab
                document.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide content
                document.querySelectorAll('.language-content').forEach(content => {
                    content.style.display = content.dataset.lang === lang ? 'block' : 'none';
                });
            });
        });

        // Target Audience Change Handler
        document.getElementById('targetAudience').addEventListener('change', function() {
            const equbSelector = document.getElementById('equbSelector');
            equbSelector.style.display = this.value === 'specific_equb' ? 'block' : 'none';
        });

        // Form Submission
        document.getElementById('notificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.loading-spinner');
            const btnText = submitBtn.querySelector('span');
            
            // Update UI
            submitBtn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Sending...';
            
            // Get editor content
            document.getElementById('content_en').value = quillEn.root.innerHTML;
            document.getElementById('content_am').value = quillAm.root.innerHTML;
            
            try {
                const formData = new FormData(this);
                const response = await fetch('api/notifications.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('Notification sent successfully!', 'success');
                    resetForm();
                    refreshNotifications();
                } else {
                    showMessage('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
            } finally {
                // Reset UI
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'Send Notification';
            }
        });

        // Utility Functions
        function resetForm() {
            document.getElementById('notificationForm').reset();
            quillEn.setContents([]);
            quillAm.setContents([]);
            document.getElementById('equbSelector').style.display = 'none';
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
            messageDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            const form = document.getElementById('notificationForm');
            form.parentNode.insertBefore(messageDiv, form);
            
            setTimeout(() => messageDiv.remove(), 5000);
        }

        function refreshNotifications() {
            location.reload();
        }

        function viewNotification(id) {
            // Create view modal
            showNotificationModal(id, 'view');
        }

        function editNotification(id) {
            // Create edit modal
            showNotificationModal(id, 'edit');
        }

        async function showNotificationModal(id, mode) {
            try {
                const response = await fetch(`api/notifications.php?action=list&id=${id}`);
                const result = await response.json();
                
                if (!result.success || !result.data || result.data.length === 0) {
                    showMessage('Notification not found', 'error');
                    return;
                }
                
                const notification = result.data[0];
                const isEdit = mode === 'edit';
                
                // Create modal HTML - Part 1
                const modalHTML = createNotificationModalHTML(notification, isEdit);
                
                // Remove existing modal if any
                const existingModal = document.getElementById('notificationModal');
                if (existingModal) existingModal.remove();
                
                // Add modal to page
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
                modal.show();
                
            } catch (error) {
                showMessage('Error loading notification details', 'error');
            }
        }

        function createNotificationModalHTML(notification, isEdit) {
            return `
                <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content" style="border-radius: 15px; border: none;">
                            <div class="modal-header" style="background: linear-gradient(135deg, #301943, #51258F); color: white; border-radius: 15px 15px 0 0;">
                                <h5 class="modal-title">
                                    <i class="fas fa-${isEdit ? 'edit' : 'eye'} me-2"></i>
                                    ${isEdit ? 'Edit' : 'View'} Notification
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" style="padding: 30px;">
                                <form id="modalNotificationForm">
                                    <input type="hidden" name="action" value="${isEdit ? 'update' : 'view'}">
                                    <input type="hidden" name="id" value="${notification.id}">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">English Title</label>
                                                <input type="text" class="form-control" name="title_en" value="${notification.title_en}" ${!isEdit ? 'readonly' : ''} required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Amharic Title</label>
                                                <input type="text" class="form-control" name="title_am" value="${notification.title_am}" ${!isEdit ? 'readonly' : ''} required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">English Content</label>
                                                <textarea class="form-control" name="content_en" rows="4" ${!isEdit ? 'readonly' : ''} required>${notification.content_en}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Amharic Content</label>
                                                <textarea class="form-control" name="content_am" rows="4" ${!isEdit ? 'readonly' : ''} required>${notification.content_am}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Type</label>
                                                <select class="form-select" name="message_type" ${!isEdit ? 'disabled' : ''} required>
                                                    <option value="general" ${notification.message_type === 'general' ? 'selected' : ''}>General</option>
                                                    <option value="payment_reminder" ${notification.message_type === 'payment_reminder' ? 'selected' : ''}>Payment Reminder</option>
                                                    <option value="payout_announcement" ${notification.message_type === 'payout_announcement' ? 'selected' : ''}>Payout Announcement</option>
                                                    <option value="system_update" ${notification.message_type === 'system_update' ? 'selected' : ''}>System Update</option>
                                                    <option value="announcement" ${notification.message_type === 'announcement' ? 'selected' : ''}>Announcement</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Priority</label>
                                                <select class="form-select" name="priority" ${!isEdit ? 'disabled' : ''} required>
                                                    <option value="low" ${notification.priority === 'low' ? 'selected' : ''}>Low</option>
                                                    <option value="medium" ${notification.priority === 'medium' ? 'selected' : ''}>Medium</option>
                                                    <option value="high" ${notification.priority === 'high' ? 'selected' : ''}>High</option>
                                                    <option value="urgent" ${notification.priority === 'urgent' ? 'selected' : ''}>Urgent</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Target Audience</label>
                                                <select class="form-select" name="target_audience" ${!isEdit ? 'disabled' : ''} required>
                                                    <option value="all_members" ${notification.target_audience === 'all_members' ? 'selected' : ''}>All Members</option>
                                                    <option value="active_members" ${notification.target_audience === 'active_members' ? 'selected' : ''}>Active Members</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <strong>Statistics:</strong> 
                                        Delivered: ${notification.total_delivered || 0} | 
                                        Read: ${notification.total_read || 0} | 
                                        Unread: ${notification.total_unread || 0} | 
                                        Read Rate: ${notification.read_percentage || 0}%
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                ${isEdit ? '<button type="button" class="btn btn-primary" onclick="saveNotificationChanges()">Save Changes</button>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        async function saveNotificationChanges() {
            const form = document.getElementById('modalNotificationForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('api/notifications.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('Notification updated successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('notificationModal')).hide();
                    refreshNotifications();
                } else {
                    showMessage('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
            }
        }

        async function deleteNotification(id) {
            if (!confirm('Are you sure you want to delete this notification?')) return;
            
            try {
                const response = await fetch('api/notifications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete&id=${id}&csrf_token=<?php echo $csrf_token; ?>`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('Notification deleted successfully!', 'success');
                    refreshNotifications();
                } else {
                    showMessage('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            // Update statistics without full page reload
            fetch('api/notifications.php?action=stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stat cards
                        document.querySelectorAll('.stat-value').forEach((el, index) => {
                            const values = [data.total, data.active, data.unread, data.recent];
                            if (values[index] !== undefined) {
                                el.textContent = values[index];
                            }
                        });
                    }
                });
        }, 30000);
    </script>
</body>
</html>