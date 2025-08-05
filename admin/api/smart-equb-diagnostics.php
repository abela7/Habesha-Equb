<?php
/**
 * HabeshaEqub - Smart EQUB Diagnostics API
 * API to detect and fix fundamental EQUB calculation errors
 */

require_once '../../includes/db.php';
require_once '../../includes/smart_pool_calculator.php';

// Set JSON header
header('Content-Type: application/json');

// Security check
require_once '../includes/admin_auth_guard.php';
$admin_id = get_current_admin_id();
if (!$admin_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// CSRF token verification for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid security token. Please refresh the page and try again.'
        ]);
        exit;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'analyze_equb':
            analyzeEqub();
            break;
        case 'fix_equb':
            fixEqub();
            break;
        case 'get_breakdown':
            getDetailedBreakdown();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Smart Diagnostics API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}

/**
 * Analyze EQUB for calculation errors
 */
function analyzeEqub() {
    global $pdo, $admin_id;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    try {
        $calculator = getSmartPoolCalculator();
        
        // Get detailed breakdown
        $breakdown_result = $calculator->generateEqubBreakdown($equb_id);
        
        if (!$breakdown_result['success']) {
            echo json_encode(['success' => false, 'message' => $breakdown_result['message']]);
            return;
        }
        
        $breakdown = $breakdown_result['breakdown'];
        
        // Log the analysis
        error_log("Smart EQUB Analysis performed by admin $admin_id for EQUB $equb_id");
        
        echo json_encode([
            'success' => true,
            'analysis' => $breakdown,
            'message' => 'EQUB analysis completed successfully'
        ]);
        
    } catch (Exception $e) {
        error_log("Error analyzing EQUB $equb_id: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to analyze EQUB']);
    }
}

/**
 * Fix EQUB calculation errors
 */
function fixEqub() {
    global $pdo, $admin_id;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    try {
        $calculator = getSmartPoolCalculator();
        
        // Fix the EQUB calculations
        $fix_result = $calculator->fixEqubDurationAndCalculations($equb_id);
        
        if (!$fix_result['success']) {
            echo json_encode(['success' => false, 'message' => $fix_result['message']]);
            return;
        }
        
        // Log the fix action
        $stmt = $pdo->prepare("
            INSERT INTO financial_audit_trail 
            (equb_settings_id, action_type, amount, description, performed_by_admin_id) 
            VALUES (?, 'financial_adjustment', ?, ?, ?)
        ");
        $stmt->execute([
            $equb_id,
            $fix_result['total_monthly_pool'],
            "SMART FIX: Duration corrected from {$fix_result['old_duration']} to {$fix_result['new_duration']} months. Pool-based calculations applied.",
            $admin_id
        ]);
        
        error_log("Smart EQUB Fix applied by admin $admin_id for EQUB $equb_id: Duration {$fix_result['old_duration']} -> {$fix_result['new_duration']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'EQUB calculations fixed successfully',
            'old_duration' => $fix_result['old_duration'],
            'new_duration' => $fix_result['new_duration'],
            'total_monthly_pool' => $fix_result['total_monthly_pool']
        ]);
        
    } catch (Exception $e) {
        error_log("Error fixing EQUB $equb_id: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fix EQUB calculations']);
    }
}

/**
 * Get detailed breakdown for an EQUB
 */
function getDetailedBreakdown() {
    $equb_id = intval($_GET['equb_id'] ?? 0);
    
    if (!$equb_id) {
        echo json_encode(['success' => false, 'message' => 'EQUB ID is required']);
        return;
    }
    
    try {
        $calculator = getSmartPoolCalculator();
        
        $breakdown_result = $calculator->generateEqubBreakdown($equb_id);
        
        if (!$breakdown_result['success']) {
            echo json_encode(['success' => false, 'message' => $breakdown_result['message']]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'breakdown' => $breakdown_result['breakdown']
        ]);
        
    } catch (Exception $e) {
        error_log("Error getting breakdown for EQUB $equb_id: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get EQUB breakdown']);
    }
}
?>