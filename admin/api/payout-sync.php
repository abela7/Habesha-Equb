<?php
/**
 * HabeshaEqub - Payout Synchronization API
 * TOP TIER: Real-time payout date updates and synchronization
 */

require_once '../../includes/db.php';
require_once '../../includes/payout_sync_service.php';

// Set JSON header
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'sync_member':
        syncMemberPayout();
        break;
    case 'sync_equb':
        syncEqubPayouts();
        break;
    case 'get_member_payout':
        getMemberPayoutInfo();
        break;
    case 'get_equb_schedule':
        getEqubSchedule();
        break;
    case 'bulk_sync_all':
        bulkSyncAll();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Sync payout date for a specific member
 */
function syncMemberPayout() {
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Member ID required']);
        return;
    }
    
    try {
        $service = getPayoutSyncService();
        $result = $service->calculateMemberPayoutDate($member_id, true);
        
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Member payout date synchronized successfully',
            'data' => $result
        ]);
        
    } catch (Exception $e) {
        error_log("Sync member payout error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error synchronizing payout date']);
    }
}

/**
 * Sync payout dates for all members in an equb term
 */
function syncEqubPayouts() {
    $equb_settings_id = intval($_POST['equb_settings_id'] ?? 0);
    
    if (!$equb_settings_id) {
        echo json_encode(['success' => false, 'message' => 'Equb settings ID required']);
        return;
    }
    
    try {
        $service = getPayoutSyncService();
        $result = $service->syncEqubPayoutDates($equb_settings_id);
        
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Synchronized {$result['updated_members']} members successfully",
            'data' => $result
        ]);
        
    } catch (Exception $e) {
        error_log("Sync equb payouts error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error synchronizing equb payouts']);
    }
}

/**
 * Get detailed payout information for a member
 */
function getMemberPayoutInfo() {
    $member_id = intval($_POST['member_id'] ?? 0);
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Member ID required']);
        return;
    }
    
    try {
        $service = getPayoutSyncService();
        $result = $service->getMemberPayoutStatus($member_id);
        
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
        
    } catch (Exception $e) {
        error_log("Get member payout info error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error getting payout information']);
    }
}

/**
 * Get complete payout schedule for an equb term
 */
function getEqubSchedule() {
    $equb_settings_id = intval($_POST['equb_settings_id'] ?? 0);
    
    if (!$equb_settings_id) {
        echo json_encode(['success' => false, 'message' => 'Equb settings ID required']);
        return;
    }
    
    try {
        $service = getPayoutSyncService();
        $result = $service->getEqubPayoutSchedule($equb_settings_id);
        
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
        
    } catch (Exception $e) {
        error_log("Get equb schedule error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error getting equb schedule']);
    }
}

/**
 * Bulk synchronize all active equb terms
 */
function bulkSyncAll() {
    global $pdo;
    
    try {
        // Get all active equb terms
        $stmt = $pdo->query("
            SELECT id, equb_name 
            FROM equb_settings 
            WHERE status IN ('active', 'planning')
            ORDER BY created_at DESC
        ");
        $equb_terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $service = getPayoutSyncService();
        $results = [];
        $total_updated = 0;
        
        foreach ($equb_terms as $equb) {
            $result = $service->syncEqubPayoutDates($equb['id']);
            if (!isset($result['error'])) {
                $total_updated += $result['updated_members'];
            }
            $results[] = [
                'equb_id' => $equb['id'],
                'equb_name' => $equb['equb_name'],
                'result' => $result
            ];
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Bulk sync completed. Updated {$total_updated} members across " . count($equb_terms) . " equb terms",
            'data' => [
                'total_equb_terms' => count($equb_terms),
                'total_members_updated' => $total_updated,
                'results' => $results
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Bulk sync error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error performing bulk synchronization']);
    }
}
?>