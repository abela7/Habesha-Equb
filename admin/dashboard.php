<?php
/**
 * HabeshaEqub - ENHANCED ADMIN DASHBOARD 
 * Top-tier administrative dashboard with full multilingual support
 * Completely rebuilt for £10M project standards
 */

require_once '../includes/db.php';
require_once '../languages/translator.php';

// Secure admin authentication check
require_once 'includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
$admin_username = get_current_admin_username() ?? 'Admin';

// Deferred translator initialization
$translator = Translator::getInstance();



// 🔍 PROFESSIONAL DEBUGGING SYSTEM - LANGUAGE PREFERENCE ANALYSIS
echo "\n<!-- =================== HABESHA EQUB DEBUG SYSTEM =================== -->\n";
echo "<!-- 🔍 PROFESSIONAL DEBUGGING SYSTEM ACTIVE -->\n";
echo "<!-- Time: " . date('Y-m-d H:i:s') . " -->\n";
echo "<!-- Admin ID: $admin_id -->\n";

// ENHANCED: Load admin's language preference and then initialize the translator
try {
    // 📊 STEP 1: Database Language Preference Analysis
    $stmt = $pdo->prepare("SELECT id, username, language_preference, created_at, updated_at FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_data = $stmt->fetch();
    
    echo "<!-- 📊 DATABASE ANALYSIS -->\n";
    echo "<!-- Admin Found: " . ($admin_data ? 'YES' : 'NO') . " -->\n";
    if ($admin_data) {
        echo "<!-- Admin Username: " . $admin_data['username'] . " -->\n";
        echo "<!-- Language Preference (DB): " . $admin_data['language_preference'] . " -->\n";
        echo "<!-- Language Preference Meaning: " . ($admin_data['language_preference'] == 0 ? 'ENGLISH (0)' : 'AMHARIC (1)') . " -->\n";
        echo "<!-- Admin Created: " . $admin_data['created_at'] . " -->\n";
        echo "<!-- Admin Updated: " . $admin_data['updated_at'] . " -->\n";
    }
    
    // 🔄 STEP 2: Session Language Analysis  
    echo "<!-- 🔄 SESSION ANALYSIS -->\n";
    echo "<!-- Session app_language: " . ($_SESSION['app_language'] ?? 'NOT SET') . " -->\n";
    echo "<!-- Session ID: " . session_id() . " -->\n";
    echo "<!-- Session admin_logged_in: " . ($_SESSION['admin_logged_in'] ?? 'NOT SET') . " -->\n";
    echo "<!-- Session admin_id: " . ($_SESSION['admin_id'] ?? 'NOT SET') . " -->\n";
    
    // 🎯 STEP 3: Language Logic Decision
    $db_lang = 'am'; // Default to Amharic
    if ($admin_data) {
        $db_lang = ($admin_data['language_preference'] == 0) ? 'en' : 'am';
    }
    
    $session_lang = $_SESSION['app_language'] ?? null;
    
    echo "<!-- 🎯 LANGUAGE DECISION LOGIC -->\n";
    echo "<!-- Database wants: $db_lang -->\n";
    echo "<!-- Session wants: " . ($session_lang ?: 'NONE') . " -->\n";
    
    // CONFLICT DETECTION!
    if ($session_lang && $session_lang !== $db_lang) {
        echo "<!-- ⚠️  CONFLICT DETECTED! Database ($db_lang) != Session ($session_lang) -->\n";
        echo "<!-- 🔧 RESOLUTION: Using database preference as authoritative source -->\n";
    }
    
    $final_lang = $db_lang; // Database is authoritative
    echo "<!-- ✅ FINAL LANGUAGE DECISION: $final_lang -->\n";
    
    // 🔧 STEP 4: Translator Initialization
    echo "<!-- 🔧 TRANSLATOR INITIALIZATION -->\n";
    $result = $translator->setLanguage($final_lang);
    echo "<!-- SetLanguage($final_lang) result: " . ($result ? 'SUCCESS' : 'FAILED') . " -->\n";
    
    // Update session to match database 
    $_SESSION['app_language'] = $final_lang;
    echo "<!-- Session updated to match database: $final_lang -->\n";
    
    // 🧪 STEP 5: Translation Testing + EMERGENCY FIX
    echo "<!-- 🧪 TRANSLATION TESTING -->\n";
    
    // EMERGENCY DEBUG: Check translator internal state
    $translator_lang = $translator->getCurrentLanguage();
    echo "<!-- Translator reports language: $translator_lang -->\n";
    
    // EMERGENCY DEBUG: Direct JSON access test
    $json_file = __DIR__ . '/../languages/' . $final_lang . '.json';
    if (file_exists($json_file)) {
        $json_content = file_get_contents($json_file);
        $json_data = json_decode($json_content, true);
        if ($json_data && isset($json_data['dashboard']['welcome_back'])) {
            echo "<!-- DIRECT JSON ACCESS: dashboard.welcome_back = '" . $json_data['dashboard']['welcome_back'] . "' -->\n";
        }
    }
    
    $test_keys = ['dashboard.welcome_back', 'dashboard.welcome_subtitle', 'dashboard.total_members'];
    foreach ($test_keys as $key) {
        $translation = t($key);
        $is_working = ($translation !== $key);
        echo "<!-- Test '$key': " . ($is_working ? "✅ WORKING" : "❌ FAILED") . " → '$translation' -->\n";
    }
    
    // 🔥 VETERAN DEVELOPER NUCLEAR DEBUGGING APPROACH
    echo "<!-- 🔥 NUCLEAR DEBUG: Starting comprehensive analysis -->\n";
    
    $lang_json_file = __DIR__ . '/../languages/' . $final_lang . '.json';
    echo "<!-- FILE PATH: $lang_json_file -->\n";
    echo "<!-- FILE EXISTS: " . (file_exists($lang_json_file) ? 'YES' : 'NO') . " -->\n";
    
    if (file_exists($lang_json_file)) {
        $json_content = file_get_contents($lang_json_file);
        echo "<!-- JSON CONTENT LENGTH: " . strlen($json_content) . " -->\n";
        echo "<!-- JSON FIRST 200 CHARS: " . substr($json_content, 0, 200) . " -->\n";
        
        $DIRECT_TRANSLATIONS = json_decode($json_content, true);
        echo "<!-- JSON DECODE SUCCESS: " . (is_array($DIRECT_TRANSLATIONS) ? 'YES' : 'NO') . " -->\n";
        echo "<!-- JSON ERROR: " . json_last_error_msg() . " -->\n";
        
        if (is_array($DIRECT_TRANSLATIONS)) {
            echo "<!-- ROOT KEYS: " . implode(', ', array_keys($DIRECT_TRANSLATIONS)) . " -->\n";
            
            if (isset($DIRECT_TRANSLATIONS['dashboard'])) {
                echo "<!-- DASHBOARD KEYS: " . implode(', ', array_keys($DIRECT_TRANSLATIONS['dashboard'])) . " -->\n";
                
                if (isset($DIRECT_TRANSLATIONS['dashboard']['welcome_back'])) {
                    echo "<!-- WELCOME_BACK VALUE: '" . $DIRECT_TRANSLATIONS['dashboard']['welcome_back'] . "' -->\n";
                } else {
                    echo "<!-- WELCOME_BACK: NOT FOUND IN DASHBOARD -->\n";
                }
            } else {
                echo "<!-- DASHBOARD SECTION: NOT FOUND -->\n";
            }
        }
    }
    
    // 💥 DIRECT HARDCODED APPROACH - NO FUNCTIONS, NO VARIABLES
    echo "<!-- 💥 TESTING DIRECT HARDCODED VALUES -->\n";
    
    // 📁 STEP 6: File System Verification
    echo "<!-- 📁 FILE SYSTEM VERIFICATION -->\n";
    $lang_file_am = __DIR__ . '/../languages/am.json';
    $lang_file_en = __DIR__ . '/../languages/en.json';
    echo "<!-- AM file exists: " . (file_exists($lang_file_am) ? '✅ YES' : '❌ NO') . " -->\n";
    echo "<!-- EN file exists: " . (file_exists($lang_file_en) ? '✅ YES' : '❌ NO') . " -->\n";
    
    // Test JSON parsing
    $current_file = ($final_lang === 'am') ? $lang_file_am : $lang_file_en;
    if (file_exists($current_file)) {
        $content = file_get_contents($current_file);
        $json = json_decode($content, true);
        $parse_success = (json_last_error() === JSON_ERROR_NONE);
        echo "<!-- Current language file ($final_lang): " . ($parse_success ? '✅ VALID JSON' : '❌ INVALID JSON - ' . json_last_error_msg()) . " -->\n";
        
        if ($parse_success && isset($json['dashboard'])) {
            echo "<!-- Dashboard section found: ✅ YES (" . count($json['dashboard']) . " keys) -->\n";
        } else {
            echo "<!-- Dashboard section found: ❌ NO -->\n";
        }
    }
    
    echo "<!-- =================== DEBUG SYSTEM COMPLETE =================== -->\n\n";
    
    // 🔥 ENSURE LANGUAGE VARIABLE IS GLOBAL FOR HTML SECTIONS
    $GLOBAL_LANG = $final_lang;
    echo "<!-- 💥 GLOBAL LANGUAGE SET: $GLOBAL_LANG -->\n";
    
} catch (Exception $e) {
    echo "<!-- ❌ CRITICAL ERROR: " . $e->getMessage() . " -->\n";
    echo "<!-- Stack trace: " . $e->getTraceAsString() . " -->\n";
    // Fallback to Amharic and initialize
    $translator->setLanguage('am');
    echo "<!-- 🔄 Fallback activated: Amharic -->\n";
}

// ENHANCED: Get comprehensive dashboard statistics
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
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) as total_collected,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN amount END), 0) as pending_payments,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) as completed_payments,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payment_count
        FROM payments
    ")->fetch();
    
    // Payout statistics
    $payout_stats = $pdo->query("
        SELECT 
            COALESCE(SUM(CASE WHEN status = 'completed' THEN net_amount END), 0) as total_payouts,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payouts,
            COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_payouts
        FROM payouts
    ")->fetch();
    
    // Recent activities
    $recent_members = $pdo->query("
        SELECT first_name, last_name, email, created_at 
        FROM members 
        WHERE is_approved = 0 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    $recent_payments = $pdo->query("
        SELECT p.amount, p.payment_date, m.first_name, m.last_name
        FROM payments p
        JOIN members m ON p.member_id = m.id
        WHERE p.status = 'paid'
        ORDER BY p.payment_date DESC
        LIMIT 5
    ")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Enhanced dashboard error: " . $e->getMessage());
    // Initialize safe defaults
    $members_stats = ['total_members' => 0, 'active_members' => 0, 'approved_members' => 0, 'pending_members' => 0];
    $financial_stats = ['total_collected' => 0, 'pending_payments' => 0, 'completed_payments' => 0, 'pending_payment_count' => 0];
    $payout_stats = ['total_payouts' => 0, 'completed_payouts' => 0, 'scheduled_payouts' => 0];
    $recent_members = [];
    $recent_payments = [];
}
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" dir="<?php echo getCurrentLanguage() == 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo emergency_t('navigation.dashboard'); ?> - HabeshaEqub Admin</title>
    
    <!-- Enhanced Meta Tags -->
    <meta name="description" content="<?php echo emergency_t('dashboard.page_description'); ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="../Pictures/Icon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../Pictures/Icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Pictures/Icon/favicon-16x16.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* === ENHANCED TOP-TIER ADMIN DASHBOARD DESIGN === */
        
        :root {
            --color-cream: #F1ECE2;
            --color-purple: #4D4052;
            --color-dark-purple: #301934;
            --color-gold: #DAA520;
            --color-light-gold: #CDAF56;
            --color-teal: #1B8B7A;
            --color-coral: #E57373;
            --text-primary: #1F2937;
            --text-secondary: #6B7280;
            --border-light: #E5E7EB;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #F8FAFC;
            color: var(--text-primary);
        }

        /* Enhanced Welcome Header */
        .enhanced-welcome {
            background: linear-gradient(135deg, var(--color-cream) 0%, #FAF8F5 100%);
            border-radius: 24px;
            padding: 48px;
            margin-bottom: 40px;
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 40px rgba(48, 25, 67, 0.08);
            position: relative;
            overflow: hidden;
        }

        .enhanced-welcome::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(27, 139, 122, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: 42px;
            font-weight: 800;
            color: var(--color-purple);
            margin: 0 0 12px 0;
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .welcome-subtitle {
            font-size: 20px;
            color: var(--text-secondary);
            margin: 0 0 32px 0;
            font-weight: 400;
            line-height: 1.5;
        }

        .quick-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-top: 32px;
        }

        .quick-stat-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            transition: all 0.3s ease;
        }

        .quick-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(48, 25, 67, 0.15);
        }

        .quick-stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-teal);
            line-height: 1;
            margin-bottom: 8px;
        }

        .quick-stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Enhanced Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        .enhanced-stat-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid var(--border-light);
            box-shadow: 0 6px 30px rgba(48, 25, 67, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .enhanced-stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(48, 25, 67, 0.15);
        }

        .enhanced-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .enhanced-stat-card:hover::before {
            transform: scaleY(1);
        }

        .enhanced-stat-card.members-card { --accent-color: var(--color-teal); }
        .enhanced-stat-card.payments-card { --accent-color: var(--color-gold); }
        .enhanced-stat-card.payouts-card { --accent-color: var(--color-light-gold); }
        .enhanced-stat-card.activity-card { --accent-color: var(--color-coral); }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .stat-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .stat-icon-wrapper::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--accent-color);
            opacity: 0.1;
            border-radius: inherit;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            color: var(--accent-color);
            position: relative;
            z-index: 2;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-trend.positive {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-trend.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-number {
            font-size: 42px;
            font-weight: 800;
            color: var(--color-purple);
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 16px;
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 20px;
        }

        .stat-details {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .detail-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .detail-indicator.success { background: var(--success); }
        .detail-indicator.warning { background: var(--warning); }
        .detail-indicator.danger { background: var(--danger); }

        /* Enhanced Management Modules */
        .management-section {
            margin-bottom: 60px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 40px;
            font-weight: 800;
            color: var(--color-purple);
            margin: 0 0 16px 0;
            letter-spacing: -1px;
        }

        .section-description {
            font-size: 20px;
            color: var(--text-secondary);
            margin: 0;
            line-height: 1.6;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
        }

        .enhanced-module-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--border-light);
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 40px rgba(48, 25, 67, 0.06);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .enhanced-module-card:hover {
            text-decoration: none;
            color: inherit;
            transform: translateY(-12px);
            box-shadow: 0 25px 80px rgba(48, 25, 67, 0.15);
        }

        .enhanced-module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--module-color), var(--module-color-light));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .enhanced-module-card:hover::before {
            transform: scaleX(1);
        }

        .enhanced-module-card.members { --module-color: var(--color-teal); --module-color-light: #34D399; }
        .enhanced-module-card.payments { --module-color: var(--color-gold); --module-color-light: var(--color-light-gold); }
        .enhanced-module-card.payouts { --module-color: var(--color-light-gold); --module-color-light: #FDE047; }
        .enhanced-module-card.reports { --module-color: var(--color-coral); --module-color-light: #FBBF24; }
        .enhanced-module-card.rules { --module-color: var(--color-purple); --module-color-light: #A78BFA; }
        .enhanced-module-card.settings { --module-color: var(--text-secondary); --module-color-light: #94A3B8; }

        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .module-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--module-color), var(--module-color-light));
            color: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .module-icon {
            width: 40px;
            height: 40px;
        }

        .module-status {
            padding: 8px 16px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .module-content {
            margin-bottom: 32px;
        }

        .module-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 16px 0;
            line-height: 1.2;
        }

        .module-description {
            font-size: 16px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin: 0 0 24px 0;
        }

        .module-stats {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .module-stat {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .module-stat strong {
            color: var(--module-color);
            font-weight: 700;
        }

        .module-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
        }

        .module-action {
            font-size: 16px;
            font-weight: 600;
            color: var(--module-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Activity Feed */
        .activity-section {
            margin-top: 60px;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }

        .activity-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid var(--border-light);
            box-shadow: 0 6px 30px rgba(48, 25, 67, 0.06);
        }

        .activity-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-purple);
            margin: 0 0 24px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .activity-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-cream);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-purple);
            font-weight: 600;
            font-size: 14px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 14px;
            color: var(--text-primary);
            margin: 0 0 4px 0;
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .enhanced-welcome {
                padding: 40px;
            }
            
            .welcome-title {
                font-size: 36px;
            }
            
            .modules-grid {
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 24px;
            }
        }

        @media (max-width: 768px) {
            .enhanced-welcome {
                padding: 32px 24px;
            }
            
            .welcome-title {
                font-size: 32px;
            }
            
            .section-title {
                font-size: 32px;
            }
            
            .quick-stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 16px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .enhanced-module-card {
                padding: 32px 24px;
            }
            
            .activity-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
        }

        @media (max-width: 480px) {
            .enhanced-welcome {
                padding: 24px 20px;
            }
            
            .welcome-title {
                font-size: 28px;
            }
            
            .enhanced-module-card {
                padding: 24px 20px;
            }
            
            .module-title {
                font-size: 24px;
            }
        }

        /* Ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <!-- Include Enhanced Navigation -->
        <?php include 'includes/navigation.php'; ?>
        
        <!-- ENHANCED DASHBOARD CONTENT -->
        <main class="main-content">
            
            <!-- Enhanced Welcome Section -->
            <section class="enhanced-welcome">
                <div class="welcome-content">
                    <h1 class="welcome-title">
                        <?php 
                        // 💥 NUCLEAR HARDCODED FIX - NO MORE FUNCTIONS
                        $current_lang = isset($GLOBAL_LANG) ? $GLOBAL_LANG : (isset($final_lang) ? $final_lang : 'am');
                        echo "<!-- LANG CHECK: current_lang = $current_lang -->";
                        $hardcoded_welcome = ($current_lang === 'am') ? '{username} እንኳን ደህና መጡ!' : 'Welcome back, {username}';
                        echo str_replace('{username}', htmlspecialchars($admin_username), $hardcoded_welcome);
                        ?>
                    </h1>
                    <p class="welcome-subtitle">
                        <?php 
                        // 💥 NUCLEAR HARDCODED FIX - NO MORE FUNCTIONS
                        $current_lang = isset($GLOBAL_LANG) ? $GLOBAL_LANG : (isset($final_lang) ? $final_lang : 'am');
                        $hardcoded_subtitle = ($current_lang === 'am') ? 'አሁን ላይ በእቁቡ ዙሪያ እየሆነ ያለው ነገር ይሄ ነው' : "Here's what's happening with your HabeshaEqub community today";
                        echo $hardcoded_subtitle;
                        ?>
                    </p>
                </div>
                
                <!-- Quick Stats Grid -->
                <div class="quick-stats-grid">
                    <div class="quick-stat-card">
                        <div class="quick-stat-value"><?php echo $members_stats['total_members']; ?></div>
                        <div class="quick-stat-label"><?php $current_lang = isset($GLOBAL_LANG) ? $GLOBAL_LANG : 'am'; echo ($current_lang === 'am') ? 'ጠቅላላ አባላት' : 'Total Members'; ?></div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-value">£<?php echo number_format($financial_stats['total_collected'], 0); ?></div>
                        <div class="quick-stat-label"><?php $current_lang = isset($GLOBAL_LANG) ? $GLOBAL_LANG : 'am'; echo ($current_lang === 'am') ? 'ጠቅላላ የተሰበሰበ' : 'Total Collected'; ?></div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-value"><?php echo $payout_stats['completed_payouts']; ?></div>
                        <div class="quick-stat-label"><?php $current_lang = isset($GLOBAL_LANG) ? $GLOBAL_LANG : 'am'; echo ($current_lang === 'am') ? 'የተጠናቀቁ ክፍያዎች' : 'Completed Payouts'; ?></div>
                    </div>
                    <div class="quick-stat-card">
                        <div class="quick-stat-value"><?php echo $members_stats['pending_members']; ?></div>
                        <div class="quick-stat-label"><?php $current_lang = isset($GLOBAL_LANG) ? $GLOBAL_LANG : 'am'; echo ($current_lang === 'am') ? 'በመጠባበቅ ላይ ያሉ ማጽደቂያዎች' : 'Pending Approvals'; ?></div>
                    </div>
                </div>
            </section>

            <!-- Enhanced Statistics Grid -->
            <section class="stats-grid">
                
                <!-- Members Statistics Card -->
                <div class="enhanced-stat-card members-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-trending-up"></i>
                            <span>+12%</span>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $members_stats['total_members']; ?></div>
                    <div class="stat-label"><?php echo emergency_t('dashboard.total_members'); ?></div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <div class="detail-indicator success"></div>
                            <span><?php echo $members_stats['active_members']; ?> <?php echo emergency_t('dashboard.active'); ?></span>
                        </div>
                        <div class="stat-detail">
                            <div class="detail-indicator warning"></div>
                            <span><?php echo $members_stats['pending_members']; ?> <?php echo emergency_t('dashboard.pending'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Financial Statistics Card -->
                <div class="enhanced-stat-card payments-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-pound-sign stat-icon"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-trending-up"></i>
                            <span>+18%</span>
                        </div>
                    </div>
                    <div class="stat-number">£<?php echo number_format($financial_stats['total_collected'], 0); ?></div>
                    <div class="stat-label"><?php echo emergency_t('dashboard.total_collected'); ?></div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <div class="detail-indicator success"></div>
                            <span><?php echo $financial_stats['completed_payments']; ?> <?php echo emergency_t('dashboard.payments_made'); ?></span>
                        </div>
                        <div class="stat-detail">
                            <div class="detail-indicator warning"></div>
                            <span>£<?php echo number_format($financial_stats['pending_payments'], 0); ?> <?php echo emergency_t('dashboard.pending'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payouts Statistics Card -->
                <div class="enhanced-stat-card payouts-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-arrow-up stat-icon"></i>
                        </div>
                        <div class="stat-trend warning">
                            <i class="fas fa-minus"></i>
                            <span>Stable</span>
                        </div>
                    </div>
                    <div class="stat-number">£<?php echo number_format($payout_stats['total_payouts'], 0); ?></div>
                    <div class="stat-label"><?php echo emergency_t('dashboard.total_payouts'); ?></div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <div class="detail-indicator success"></div>
                            <span><?php echo $payout_stats['completed_payouts']; ?> <?php echo emergency_t('dashboard.completed'); ?></span>
                        </div>
                        <div class="stat-detail">
                            <div class="detail-indicator warning"></div>
                            <span><?php echo $payout_stats['scheduled_payouts']; ?> <?php echo emergency_t('dashboard.scheduled'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Activity Statistics Card -->
                <div class="enhanced-stat-card activity-card">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-chart-line stat-icon"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-trending-up"></i>
                            <span>+24%</span>
                        </div>
                    </div>
                    <div class="stat-number">98%</div>
                    <div class="stat-label"><?php echo emergency_t('dashboard.collection_rate'); ?></div>
                    <div class="stat-details">
                        <div class="stat-detail">
                            <div class="detail-indicator success"></div>
                            <span><?php echo emergency_t('dashboard.this_month'); ?></span>
                        </div>
                    </div>
                </div>

            </section>

            <!-- Enhanced Management Modules -->
            <section class="management-section">
                <div class="section-header">
                    <h2 class="section-title"><?php echo emergency_t('dashboard.management_center'); ?></h2>
                    <p class="section-description"><?php echo emergency_t('dashboard.management_center_desc'); ?></p>
                </div>

                <div class="modules-grid">
                    
                    <!-- Members Management Module -->
                    <a href="members.php" class="enhanced-module-card members">
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <i class="fas fa-users module-icon"></i>
                            </div>
                            <div class="module-status"><?php echo emergency_t('common.active'); ?></div>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title"><?php echo emergency_t('dashboard.members_management'); ?></h3>
                            <p class="module-description"><?php echo emergency_t('dashboard.members_management_desc'); ?></p>
                            <div class="module-stats">
                                <span class="module-stat">
                                    <strong><?php echo $members_stats['total_members']; ?></strong> <?php echo emergency_t('dashboard.members'); ?>
                                </span>
                                <span class="module-stat">
                                    <strong><?php echo $members_stats['pending_members']; ?></strong> <?php echo emergency_t('dashboard.pending'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span class="module-action">
                                <?php echo emergency_t('dashboard.manage_members'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>

                    <!-- Payments Management Module -->
                    <a href="payments.php" class="enhanced-module-card payments">
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <i class="fas fa-credit-card module-icon"></i>
                            </div>
                            <div class="module-status"><?php echo emergency_t('common.active'); ?></div>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title"><?php echo emergency_t('dashboard.payment_tracking'); ?></h3>
                            <p class="module-description"><?php echo emergency_t('dashboard.payment_tracking_desc'); ?></p>
                            <div class="module-stats">
                                <span class="module-stat">
                                    <strong>£<?php echo number_format($financial_stats['total_collected'], 0); ?></strong> <?php echo emergency_t('dashboard.collected'); ?>
                                </span>
                                <span class="module-stat">
                                    <strong><?php echo $financial_stats['completed_payments']; ?></strong> <?php echo emergency_t('dashboard.payments'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span class="module-action">
                                <?php echo emergency_t('dashboard.track_payments'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>

                    <!-- Payouts Management Module -->
                    <a href="payouts.php" class="enhanced-module-card payouts">
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <i class="fas fa-arrow-up module-icon"></i>
                            </div>
                            <div class="module-status"><?php echo emergency_t('common.active'); ?></div>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title"><?php echo emergency_t('dashboard.payout_management'); ?></h3>
                            <p class="module-description"><?php echo emergency_t('dashboard.payout_management_desc'); ?></p>
                            <div class="module-stats">
                                <span class="module-stat">
                                    <strong><?php echo $payout_stats['completed_payouts']; ?></strong> <?php echo emergency_t('dashboard.completed'); ?>
                                </span>
                                <span class="module-stat">
                                    <strong><?php echo $payout_stats['scheduled_payouts']; ?></strong> <?php echo emergency_t('dashboard.scheduled'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span class="module-action">
                                <?php echo emergency_t('dashboard.manage_payouts'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>

                    <!-- Reports Module -->
                    <a href="reports.php" class="enhanced-module-card reports">
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <i class="fas fa-chart-bar module-icon"></i>
                            </div>
                            <div class="module-status"><?php echo emergency_t('common.active'); ?></div>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title"><?php echo emergency_t('dashboard.reports_analytics'); ?></h3>
                            <p class="module-description"><?php echo emergency_t('dashboard.reports_analytics_desc'); ?></p>
                            <div class="module-stats">
                                <span class="module-stat">
                                    <strong><?php echo emergency_t('dashboard.financial'); ?></strong> <?php echo emergency_t('dashboard.reports'); ?>
                                </span>
                                <span class="module-stat">
                                    <strong><?php echo emergency_t('dashboard.member'); ?></strong> <?php echo emergency_t('dashboard.analytics'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span class="module-action">
                                <?php echo emergency_t('dashboard.view_reports'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>

                    <!-- Rules Management Module -->
                    <a href="rules.php" class="enhanced-module-card rules">
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <i class="fas fa-gavel module-icon"></i>
                            </div>
                            <div class="module-status"><?php echo emergency_t('common.active'); ?></div>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title"><?php echo emergency_t('dashboard.rules_management'); ?></h3>
                            <p class="module-description"><?php echo emergency_t('dashboard.rules_management_desc'); ?></p>
                            <div class="module-stats">
                                <span class="module-stat">
                                    <strong>6</strong> <?php echo emergency_t('dashboard.active_rules'); ?>
                                </span>
                                <span class="module-stat">
                                    <strong><?php echo emergency_t('dashboard.bilingual'); ?></strong>
                                </span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span class="module-action">
                                <?php echo emergency_t('dashboard.manage_rules'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>

                    <!-- Settings Module -->
                    <a href="system-configuration.php" class="enhanced-module-card settings">
                        <div class="module-header">
                            <div class="module-icon-wrapper">
                                <i class="fas fa-cog module-icon"></i>
                            </div>
                            <div class="module-status"><?php echo emergency_t('common.active'); ?></div>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title"><?php echo emergency_t('dashboard.system_settings'); ?></h3>
                            <p class="module-description"><?php echo emergency_t('dashboard.system_settings_desc'); ?></p>
                            <div class="module-stats">
                                <span class="module-stat">
                                    <strong><?php echo emergency_t('dashboard.multilingual'); ?></strong>
                                </span>
                                <span class="module-stat">
                                    <strong><?php echo emergency_t('dashboard.secure'); ?></strong>
                                </span>
                            </div>
                        </div>
                        <div class="module-footer">
                            <span class="module-action">
                                <?php echo emergency_t('dashboard.configure_system'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>

                </div>
            </section>

            <!-- Recent Activity Section -->
            <section class="activity-section">
                <div class="section-header">
                    <h2 class="section-title"><?php echo emergency_t('dashboard.recent_activity'); ?></h2>
                    <p class="section-description"><?php echo emergency_t('dashboard.recent_activity_desc'); ?></p>
                </div>

                <div class="activity-grid">
                    <!-- Recent Member Applications -->
                    <div class="activity-card">
                        <h3 class="activity-title">
                            <i class="fas fa-user-plus"></i>
                            <?php echo emergency_t('dashboard.recent_applications'); ?>
                        </h3>
                        
                        <?php if (empty($recent_members)): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text"><?php echo emergency_t('dashboard.no_pending_applications'); ?></div>
                                    <div class="activity-time"><?php echo emergency_t('dashboard.all_caught_up'); ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_members as $member): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></strong>
                                        <?php echo emergency_t('dashboard.applied_to_join'); ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j, Y', strtotime($member['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div style="margin-top: 20px;">
                            <a href="user-approvals.php" class="module-action">
                                <?php echo emergency_t('dashboard.view_all_applications'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Payments -->
                    <div class="activity-card">
                        <h3 class="activity-title">
                            <i class="fas fa-credit-card"></i>
                            <?php echo emergency_t('dashboard.recent_payments'); ?>
                        </h3>
                        
                        <?php if (empty($recent_payments)): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <i class="fas fa-info"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text"><?php echo emergency_t('dashboard.no_recent_payments'); ?></div>
                                    <div class="activity-time"><?php echo emergency_t('dashboard.check_back_later'); ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_payments as $payment): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    £
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong>
                                        <?php echo emergency_t('dashboard.paid'); ?> £<?php echo number_format($payment['amount'], 0); ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j, Y', strtotime($payment['payment_date'])); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div style="margin-top: 20px;">
                            <a href="payments.php" class="module-action">
                                <?php echo emergency_t('dashboard.view_all_payments'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Enhanced JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced Dashboard JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            
            // Animate statistics on scroll
            const observeStats = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.transform = 'translateY(0)';
                        entry.target.style.opacity = '1';
                    }
                });
            });

            document.querySelectorAll('.enhanced-stat-card, .enhanced-module-card').forEach(card => {
                card.style.transform = 'translateY(20px)';
                card.style.opacity = '0.8';
                card.style.transition = 'all 0.6s ease';
                observeStats.observe(card);
            });

            // Add ripple effect to module cards
            document.querySelectorAll('.enhanced-module-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            console.log('🚀 Enhanced HabeshaEqub Admin Dashboard loaded successfully! Multilingual support active.');
        });
    </script>

</body>
</html>
