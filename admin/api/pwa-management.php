<?php
/**
 * HabeshaEqub - PWA Management API
 * Handles PWA version updates
 */

header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../languages/translator.php';

// Secure admin authentication check
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();

if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'update_version') {
    $version = trim($input['version'] ?? '');
    $note = trim($input['note'] ?? '');
    
    // Validate version format
    if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid version format. Use Major.Minor.Patch (e.g., 1.0.1)']);
        exit;
    }
    
    try {
        // Update service-worker.js
        $swPath = '../../service-worker.js';
        if (!file_exists($swPath)) {
            throw new Exception('service-worker.js not found');
        }
        
        $swContent = file_get_contents($swPath);
        
        // Replace version number
        // CACHE_NAME uses template literal so it will automatically use the new CACHE_VERSION
        $swContent = preg_replace(
            '/CACHE_VERSION\s*=\s*[\'"]([^\'"]+)[\'"]/',
            "CACHE_VERSION = '$version'",
            $swContent
        );
        
        if (file_put_contents($swPath, $swContent) === false) {
            throw new Exception('Failed to write service-worker.js');
        }
        
        // Update pwa-head.php config if it exists
        $pwaHeadPath = '../../includes/pwa-head.php';
        if (file_exists($pwaHeadPath)) {
            $pwaHeadContent = file_get_contents($pwaHeadPath);
            $pwaHeadContent = preg_replace(
                "/cacheVersion:\s*['\"]([^'\"]+)['\"]/",
                "cacheVersion: '$version'",
                $pwaHeadContent
            );
            file_put_contents($pwaHeadPath, $pwaHeadContent);
        }
        
        // Record update in database
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pwa_updates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(20) NOT NULL,
                updated_by INT NOT NULL,
                update_note TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_version (version),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO pwa_updates (version, updated_by, update_note, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$version, $admin_id, $note ?: null]);
        
        echo json_encode([
            'success' => true,
            'message' => 'PWA version updated successfully',
            'version' => $version
        ]);
        
    } catch (Exception $e) {
        error_log("PWA Update Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update version: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

