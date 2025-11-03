<?php
/**
 * HabeshaEqub - PWA Installation Tracking API
 * Tracks which users have installed the PWA
 */

header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../languages/translator.php';

// Check if user is logged in (member or admin)
session_start();
$user_id = $_SESSION['user_id'] ?? null;
$member_id = $_SESSION['member_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

if ($action === 'record_installation') {
    try {
        // Create table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pwa_installations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                member_id VARCHAR(50) NULL,
                admin_id INT NULL,
                device_fingerprint VARCHAR(64) NULL,
                device_info TEXT,
                browser_info TEXT,
                install_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                install_count INT DEFAULT 1,
                is_active TINYINT(1) DEFAULT 1,
                INDEX idx_user (user_id),
                INDEX idx_member (member_id),
                INDEX idx_admin (admin_id),
                INDEX idx_fingerprint (device_fingerprint),
                INDEX idx_install_date (install_date),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Generate device fingerprint for guest users
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $screen_data = $input['screen'] ?? [];
        $platform = $input['platform'] ?? '';
        
        // Create device fingerprint from multiple sources
        $fingerprint_data = $user_agent . '|' . $accept_language . '|' . $platform;
        if (!empty($screen_data)) {
            $fingerprint_data .= '|' . ($screen_data['width'] ?? '') . 'x' . ($screen_data['height'] ?? '');
        }
        $device_fingerprint = 'pwa_' . substr(hash('sha256', $fingerprint_data), 0, 32);
        
        $device_info = json_encode([
            'user_agent' => $user_agent,
            'platform' => $platform,
            'language' => $accept_language,
            'screen' => $input['screen'] ?? null,
            'is_standalone' => $input['is_standalone'] ?? false,
            'visit_only' => $input['visit_only'] ?? false,
            'installation_completed' => $input['installation_completed'] ?? false
        ]);
        
        $browser_info = json_encode([
            'browser' => $input['browser'] ?? '',
            'version' => $input['version'] ?? '',
            'os' => $input['os'] ?? ''
        ]);
        
        // Only track actual installations, not page visits
        // Explicit check: treat as installation only when installation_completed is explicitly true
        // AND visit_only is NOT explicitly true
        // This prevents default/empty requests from being treated as installations
        $is_installation = !empty($input['installation_completed']) && 
                           !(isset($input['visit_only']) && $input['visit_only'] === true);
        
        // Check for existing installation
        // Priority: user_id/member_id/admin_id > device_fingerprint (for guests)
        // Also check time window (within last 5 minutes to prevent rapid duplicates)
        $checkSql = null;
        $params = [];
        
        if ($member_id) {
            $checkSql = "SELECT id, install_count, install_date FROM pwa_installations WHERE member_id = ? AND install_date >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY install_date DESC LIMIT 1";
            $params = [$member_id];
        } elseif ($user_id) {
            $checkSql = "SELECT id, install_count, install_date FROM pwa_installations WHERE user_id = ? AND install_date >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY install_date DESC LIMIT 1";
            $params = [$user_id];
        } elseif ($admin_id) {
            $checkSql = "SELECT id, install_count, install_date FROM pwa_installations WHERE admin_id = ? AND install_date >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY install_date DESC LIMIT 1";
            $params = [$admin_id];
        } else {
            // For guests, use device fingerprint
            $checkSql = "SELECT id, install_count, install_date FROM pwa_installations WHERE device_fingerprint = ? AND device_fingerprint IS NOT NULL AND install_date >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY install_date DESC LIMIT 1";
            $params = [$device_fingerprint];
        }
        
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute($params);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing installation (only increment if it's an actual installation, not just a visit)
            if ($is_installation) {
                $updateStmt = $pdo->prepare("
                    UPDATE pwa_installations 
                    SET install_count = install_count + 1,
                        last_seen = NOW(),
                        is_active = 1,
                        device_info = ?,
                        browser_info = ?,
                        device_fingerprint = COALESCE(device_fingerprint, ?)
                    WHERE id = ?
                ");
                $updateStmt->execute([$device_info, $browser_info, $device_fingerprint, $existing['id']]);
            } else {
                // Just update last_seen for visits without incrementing count
                $updateStmt = $pdo->prepare("
                    UPDATE pwa_installations 
                    SET last_seen = NOW(),
                        device_info = ?,
                        browser_info = ?,
                        device_fingerprint = COALESCE(device_fingerprint, ?)
                    WHERE id = ?
                ");
                $updateStmt->execute([$device_info, $browser_info, $device_fingerprint, $existing['id']]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $is_installation ? 'Installation updated' : 'Visit tracked',
                'install_count' => $existing['install_count'] + ($is_installation ? 1 : 0),
                'existing' => true
            ]);
        } else {
            // Only create new record for actual installations, not page visits
            if (!$is_installation) {
                // Just track visit without creating duplicate - silently succeed
                echo json_encode([
                    'success' => true,
                    'message' => 'Visit tracked (no duplicate created)',
                    'existing' => false
                ]);
                exit;
            }
            
            // Create new installation record
            $insertStmt = $pdo->prepare("
                INSERT INTO pwa_installations 
                (user_id, member_id, admin_id, device_fingerprint, device_info, browser_info, install_date, last_seen, install_count, is_active)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), 1, 1)
            ");
            
            $insertStmt->execute([
                $user_id ?: null,
                $member_id ?: null,
                $admin_id ?: null,
                ($user_id || $member_id || $admin_id) ? null : $device_fingerprint, // Only use fingerprint for guests
                $device_info,
                $browser_info
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Installation recorded',
                'install_id' => $pdo->lastInsertId(),
                'existing' => false
            ]);
        }
        
    } catch (Exception $e) {
        error_log("PWA Installation Tracking Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to record installation: ' . $e->getMessage()
        ]);
    }
} elseif ($action === 'get_statistics') {
    // Admin only - require admin auth
    require_once '../includes/admin_auth_guard.php';
    $admin_id = get_current_admin_id();
    
    if (!$admin_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    try {
        // Total installations
        $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM pwa_installations WHERE is_active = 1");
        $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Member installations
        $memberStmt = $pdo->query("SELECT COUNT(DISTINCT member_id) as count FROM pwa_installations WHERE member_id IS NOT NULL AND is_active = 1");
        $memberCount = $memberStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Admin installations
        $adminStmt = $pdo->query("SELECT COUNT(DISTINCT admin_id) as count FROM pwa_installations WHERE admin_id IS NOT NULL AND is_active = 1");
        $adminCount = $adminStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Installations by device
        $deviceStmt = $pdo->query("
            SELECT 
                JSON_EXTRACT(device_info, '$.platform') as platform,
                COUNT(*) as count
            FROM pwa_installations
            WHERE is_active = 1
            GROUP BY platform
            ORDER BY count DESC
        ");
        $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent installations (last 30 days)
        $recentStmt = $pdo->query("
            SELECT COUNT(*) as count 
            FROM pwa_installations 
            WHERE install_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_active = 1
        ");
        $recent = $recentStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode([
            'success' => true,
            'statistics' => [
                'total' => (int)$total,
                'members' => (int)$memberCount,
                'admins' => (int)$adminCount,
                'recent_30_days' => (int)$recent,
                'devices' => $devices
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("PWA Statistics Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif ($action === 'get_installations') {
    // Admin only - require admin auth
    require_once '../includes/admin_auth_guard.php';
    $admin_id = get_current_admin_id();
    
    if (!$admin_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = ($page - 1) * $limit;
        
        $search = $_GET['search'] ?? '';
        $where = "WHERE pi.is_active = 1";
        $params = [];
        
        if ($search) {
            $where .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR m.member_id LIKE ? OR a.username LIKE ?)";
            $searchParam = "%$search%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        $sql = "
            SELECT 
                pi.*,
                m.first_name as member_first_name,
                m.last_name as member_last_name,
                m.member_id as member_code,
                m.email as member_email,
                a.username as admin_username,
                a.email as admin_email
            FROM pwa_installations pi
            LEFT JOIN members m ON pi.member_id = m.member_id
            LEFT JOIN admins a ON pi.admin_id = a.id
            $where
            ORDER BY pi.install_date DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $installations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countSql = "
            SELECT COUNT(*) as total
            FROM pwa_installations pi
            LEFT JOIN members m ON pi.member_id = m.member_id
            LEFT JOIN admins a ON pi.admin_id = a.id
            $where
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true,
            'installations' => $installations,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit
        ]);
        
    } catch (Exception $e) {
        error_log("PWA Installations List Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

