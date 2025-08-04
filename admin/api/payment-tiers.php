<?php
/**
 * HabeshaEqub - Payment Tiers Management API
 * Professional API for managing payment tiers with validation
 */

// Define skip auth check to prevent automatic redirect
define('SKIP_ADMIN_AUTH_CHECK', true);

// Start session first
session_start();

// Include required files
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Check admin authentication manually
if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

function json_response($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Only POST requests allowed');
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update_tiers':
            updatePaymentTiers();
            break;
        
        case 'get_tiers':
            getPaymentTiers();
            break;
        
        default:
            json_response(false, 'Invalid action');
    }
} catch (Exception $e) {
    error_log("Payment Tiers API Error: " . $e->getMessage());
    json_response(false, 'An error occurred: ' . $e->getMessage());
}

function updatePaymentTiers() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    $tiers_json = $_POST['tiers'] ?? '';
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        $tiers = json_decode($tiers_json, true);
        if (!is_array($tiers) || empty($tiers)) {
            json_response(false, 'Invalid payment tiers data');
        }
        
        // Validate tiers
        foreach ($tiers as $tier) {
            if (!isset($tier['amount']) || !isset($tier['tag']) || 
                $tier['amount'] <= 0 || empty(trim($tier['tag']))) {
                json_response(false, 'All tiers must have valid amount and tag');
            }
        }
        
        // Update database
        $stmt = $pdo->prepare("
            UPDATE equb_settings 
            SET payment_tiers = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $result = $stmt->execute([json_encode($tiers), $equb_id]);
        
        if ($result) {
            json_response(true, 'Payment tiers updated successfully', $tiers);
        } else {
            json_response(false, 'Failed to update payment tiers');
        }
        
    } catch (Exception $e) {
        error_log("Error updating payment tiers: " . $e->getMessage());
        json_response(false, 'Database error occurred');
    }
}

function getPaymentTiers() {
    global $pdo;
    
    $equb_id = intval($_POST['equb_id'] ?? 0);
    
    if (!$equb_id) {
        json_response(false, 'EQUB ID is required');
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT payment_tiers, admin_fee, duration_months 
            FROM equb_settings 
            WHERE id = ?
        ");
        $stmt->execute([$equb_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $tiers = json_decode($data['payment_tiers'], true) ?: [];
            json_response(true, 'Payment tiers retrieved', [
                'tiers' => $tiers,
                'admin_fee' => $data['admin_fee'],
                'duration_months' => $data['duration_months']
            ]);
        } else {
            json_response(false, 'EQUB term not found');
        }
        
    } catch (Exception $e) {
        error_log("Error getting payment tiers: " . $e->getMessage());
        json_response(false, 'Database error occurred');
    }
}
?>